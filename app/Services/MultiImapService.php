<?php

namespace App\Services;

use PhpImap\Mailbox;
use App\Models\EmailLog;
use App\Models\Caso;
use App\Models\Bitacora;
use App\Services\AutoCaseCreationService;
use App\Services\MicrosoftDeviceCodeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MultiImapService
{
    private array $accounts = [];
    private MicrosoftDeviceCodeService $oauthService;

    public function __construct()
    {
        $this->oauthService = new MicrosoftDeviceCodeService();
        $this->loadAccounts();
    }

    private function loadAccounts(): void
    {
        $this->accounts = [
            [
                'name'     => 'gestionsoat365',
                'email'    => env('GESTION_EMAIL'),
                'password' => env('GESTION_PASSWORD'),
                'priority' => 'high',
                'types'    => ['respuesta_positiva', 'respuesta_negativa', 'en_proceso', 'citacion', 'pago_honorarios', 'fallo_tutela'],
            ],
            [
                'name'     => 'reclamaciones',
                'email'    => env('RECLAMACIONES_EMAIL'),
                'password' => env('RECLAMACIONES_PASSWORD'),
                'priority' => 'high',
                'types'    => ['pago_indemnizacion', 'aviso_pago', 'comprobante', 'soporte_pago'],
            ],
        ];

        $this->accounts = array_values(array_filter($this->accounts, fn($a) => !empty($a['email'])));
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Punto de entrada principal
    // ────────────────────────────────────────────────────────────────────────────
    public function processAllAccounts(): array
    {
        $totalProcessed = 0;
        $results        = [];

        $sorted = array_merge(
            array_values(array_filter($this->accounts, fn($a) => $a['priority'] === 'high')),
            array_values(array_filter($this->accounts, fn($a) => $a['priority'] === 'medium')),
            array_values(array_filter($this->accounts, fn($a) => $a['priority'] === 'low')),
        );

        foreach ($sorted as $account) {
            try {
                $accessToken = $this->oauthService->getValidToken($account['email']);

                if ($accessToken) {
                    $processed = $this->processAccountViaGraph($account, $accessToken);
                    $method    = 'OAuth/Graph';
                } else {
                    // Sin token OAuth → intentar IMAP (puede fallar en cuentas personales Microsoft)
                    $processed = $this->processAccountViaImap($account);
                    $method    = 'IMAP';
                }

                $totalProcessed += $processed;
                $results[$account['name']] = [
                    'success'   => true,
                    'processed' => $processed,
                    'message'   => "Procesados {$processed} correos vía {$method}",
                ];
            } catch (\Exception $e) {
                $results[$account['name']] = [
                    'success'   => false,
                    'processed' => 0,
                    'message'   => $e->getMessage(),
                ];
                Log::error("Error procesando cuenta {$account['name']}: " . $e->getMessage());
            }
        }

        return ['total_processed' => $totalProcessed, 'results' => $results];
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Lectura vía Microsoft Graph API (requiere token OAuth)
    // ────────────────────────────────────────────────────────────────────────────
    private function processAccountViaGraph(array $account, string $accessToken): int
    {
        $processedCount   = 0;
        $limit            = 200;
        $autoCaseService  = new AutoCaseCreationService();

        $alreadyProcessed = EmailLog::whereNotNull('email_id')
            ->pluck('email_id')
            ->flip()
            ->all();

        $since   = now()->subMonths(6)->format('Y-m-d\TH:i:s\Z');
        $nextUrl = 'https://graph.microsoft.com/v1.0/me/messages?' . http_build_query([
            '$top'     => 50,
            '$select'  => 'id,subject,body,from,receivedDateTime,isRead',
            '$orderby' => 'receivedDateTime desc',
            '$filter'  => "receivedDateTime ge {$since}",
        ]);

        while ($nextUrl && $processedCount < $limit) {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->get($nextUrl);

            if ($response->failed()) {
                Log::error("Graph API error para {$account['email']}: " . $response->body());
                break;
            }

            $data     = $response->json();
            $messages = $data['value'] ?? [];
            $nextUrl  = $data['@odata.nextLink'] ?? null;

            foreach ($messages as $message) {
                if ($processedCount >= $limit) break;

                $messageId = $message['id'];
                if (isset($alreadyProcessed[$messageId])) continue;

                $subject   = $message['subject'] ?? 'Sin asunto';
                $bodyHtml  = $message['body']['content'] ?? '';
                $bodyText  = trim(preg_replace('/\s+/', ' ', strip_tags($bodyHtml)));
                $fromEmail = $message['from']['emailAddress']['address'] ?? '';
                $fromName  = $message['from']['emailAddress']['name'] ?? '';
                $emailDate = new \DateTime($message['receivedDateTime']);

                // Objeto mock para reutilizar clasificadores
                $emailObj = (object)[
                    'subject'     => $subject,
                    'textPlain'   => $bodyText,
                    'fromAddress' => $fromEmail,
                    'fromName'    => $fromName,
                    'date'        => $message['receivedDateTime'],
                ];

                $emailType = $this->classifyEmailByAccount($emailObj, $account);
                $insurance = EmailLog::detectInsurance($fromEmail, $subject, $bodyText);

                $emailData = [
                    'id'         => $messageId,
                    'message_id' => $messageId,
                    'subject'    => $subject,
                    'body'       => $bodyText,
                    'from_email' => $fromEmail,
                    'from_name'  => $fromName,
                    'date'       => $emailDate,
                ];

                $caso = $this->findRelatedCase($subject, $bodyText);

                if ($caso) {
                    EmailLog::create([
                        'caso_id'            => $caso->id,
                        'email_id'           => $messageId,
                        'subject'            => $subject,
                        'body'               => substr($bodyText, 0, 5000),
                        'from_email'         => $fromEmail,
                        'from_name'          => $fromName,
                        'email_date'         => $emailDate,
                        'detected_insurance' => $insurance,
                        'email_type'         => $emailType,
                        'extracted_data'     => $this->extractData($subject, $bodyText),
                        'processed'          => true,
                    ]);

                    $this->updateCaseStatusByAccount($caso, $emailType, $account, $emailObj);
                    $processedCount++;
                } else {
                    $result = $autoCaseService->processEmailForNewCase($emailData, $account['name']);
                    if ($result && $result['success']) {
                        $processedCount++;
                    }
                }

                $alreadyProcessed[$messageId] = true;
            }
        }

        return $processedCount;
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Lectura vía IMAP (fallback — puede fallar en cuentas personales Microsoft)
    // ────────────────────────────────────────────────────────────────────────────
    private function processAccountViaImap(array $account): int
    {
        $hostname = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';

        try {
            $mailbox = new Mailbox($hostname, $account['email'], $account['password'], '', 'UTF-8');
            $mailbox->getImapStream();

            $since    = date('d-M-Y', strtotime('-6 months'));
            $emailIds = $mailbox->searchMailbox("SINCE \"{$since}\"");
            rsort($emailIds);
        } catch (\Exception $e) {
            Log::error("IMAP conexión fallida para {$account['email']}: " . $e->getMessage());
            throw new \Exception("No se pudo conectar a {$account['email']} vía IMAP: " . $e->getMessage());
        }

        if (empty($emailIds)) {
            try { $mailbox->disconnect(); } catch (\Exception $e) {}
            return 0;
        }

        $alreadyProcessed = EmailLog::whereNotNull('email_id')
            ->pluck('email_id')
            ->flip()
            ->all();

        $processedCount  = 0;
        $limit           = $account['priority'] === 'high' ? 200 : 100;
        $autoCaseService = new AutoCaseCreationService();

        foreach ($emailIds as $emailId) {
            if ($processedCount >= $limit) break;

            try {
                $email     = $mailbox->getMail($emailId, false);
                $messageId = $email->messageId ?: ('imap-' . $emailId . '-' . $account['email']);

                if (isset($alreadyProcessed[$messageId])) continue;

                $subject   = $this->cleanSubject($email->subject);
                $bodyText  = $this->cleanBody($email->textHtml ?: $email->textPlain);
                $emailType = $this->classifyEmailByAccount($email, $account);
                $insurance = EmailLog::detectInsurance($email->fromAddress, $subject, $bodyText);

                $emailData = [
                    'id'         => $messageId,
                    'message_id' => $messageId,
                    'subject'    => $subject,
                    'body'       => $bodyText,
                    'from_email' => $email->fromAddress ?? '',
                    'from_name'  => $email->fromName ?? '',
                    'date'       => $email->date ? new \DateTime($email->date) : now(),
                ];

                $caso = $this->findRelatedCase($subject, $bodyText);

                if ($caso) {
                    EmailLog::create([
                        'caso_id'            => $caso->id,
                        'email_id'           => $messageId,
                        'subject'            => $subject,
                        'body'               => substr($bodyText, 0, 5000),
                        'from_email'         => $emailData['from_email'],
                        'from_name'          => $emailData['from_name'],
                        'email_date'         => $emailData['date'],
                        'detected_insurance' => $insurance,
                        'email_type'         => $emailType,
                        'extracted_data'     => $this->extractData($subject, $bodyText),
                        'processed'          => true,
                    ]);

                    $this->updateCaseStatusByAccount($caso, $emailType, $account, $email);
                    $processedCount++;
                } else {
                    $result = $autoCaseService->processEmailForNewCase($emailData, $account['name']);
                    if ($result && $result['success']) {
                        $processedCount++;
                    }
                }

                try { $mailbox->markMailAsRead($emailId); } catch (\Exception $e) {}
            } catch (\Exception $e) {
                Log::error("Error procesando email #{$emailId} en {$account['name']}: " . $e->getMessage());
                continue;
            }
        }

        try { $mailbox->disconnect(); } catch (\Exception $e) {}

        return $processedCount;
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Test de conexión (muestra estado OAuth + IMAP)
    // ────────────────────────────────────────────────────────────────────────────
    public function testAllConnections(): array
    {
        $results = [];

        foreach ($this->accounts as $account) {
            // Primero intentar OAuth
            $accessToken = $this->oauthService->getValidToken($account['email']);

            if ($accessToken) {
                $response = Http::withToken($accessToken)
                    ->timeout(15)
                    ->get('https://graph.microsoft.com/v1.0/me/messages?$top=1&$select=id');

                $results[$account['name']] = [
                    'success'      => $response->successful(),
                    'email'        => $account['email'],
                    'priority'     => $account['priority'],
                    'method'       => 'OAuth/Graph',
                    'oauth_active' => true,
                    'message'      => $response->successful() ? 'Conexión OAuth activa' : 'Error Graph: ' . $response->body(),
                ];
                continue;
            }

            // Fallback: IMAP
            try {
                $hostname = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';
                $mailbox  = new Mailbox($hostname, $account['email'], $account['password'], '', 'UTF-8');
                $mailbox->getImapStream();

                $total  = count($mailbox->searchMailbox('ALL'));
                $unseen = count($mailbox->searchMailbox('UNSEEN'));
                $mailbox->disconnect();

                $results[$account['name']] = [
                    'success'       => true,
                    'email'         => $account['email'],
                    'priority'      => $account['priority'],
                    'method'        => 'IMAP',
                    'oauth_active'  => false,
                    'total_emails'  => $total,
                    'unread_emails' => $unseen,
                    'message'       => 'Conexión IMAP exitosa',
                ];
            } catch (\Exception $e) {
                $results[$account['name']] = [
                    'success'      => false,
                    'email'        => $account['email'],
                    'priority'     => $account['priority'],
                    'method'       => 'IMAP',
                    'oauth_active' => false,
                    'message'      => 'Sin OAuth y IMAP falló: ' . $e->getMessage(),
                    'hint'         => 'Conecta esta cuenta con OAuth en /emails',
                ];
            }
        }

        return $results;
    }

    // ────────────────────────────────────────────────────────────────────────────
    // Clasificación de correos
    // ────────────────────────────────────────────────────────────────────────────
    private function classifyEmailByAccount($email, array $account): string
    {
        $subject = strtolower($email->subject ?? '');
        $body    = strtolower($email->textPlain ?? '');
        $full    = $subject . ' ' . $body;

        switch ($account['name']) {
            case 'gestionsoat365':
                if (preg_match('/(tutela|fallo|sentencia|decision)/i', $full))        return 'fallo_tutela';
                if (preg_match('/(honorarios|pago|comision|remuneracion)/i', $full))  return 'pago_honorarios';
                if (preg_match('/(aprobad[ao]|aceptad[ao]|procede|concedid[ao])/i', $full)) return 'respuesta_positiva';
                if (preg_match('/(niega|rechazad[ao]|negad[ao]|improcedente)/i', $full))     return 'respuesta_negativa';
                break;

            case 'reclamaciones':
                if (preg_match('/(indemnizaci[oó]n|pago|abono)/i', $full))          return 'pago_indemnizacion';
                if (preg_match('/(aviso|notificacion|confirmacion)/i', $full))       return 'aviso_pago';
                if (preg_match('/(soporte|comprobante|recibo)/i', $full))            return 'soporte_pago';
                break;
        }

        return EmailLog::classifyEmail($email->subject ?? '', $email->textPlain ?? '');
    }

    private function updateCaseStatusByAccount(Caso $caso, string $emailType, array $account, $email): void
    {
        $descripcion = "Correo de {$account['email']}: " . ($email->subject ?? '');

        switch ($account['name']) {
            case 'gestionsoat365':
                match ($emailType) {
                    'fallo_tutela'       => [$caso->estado = 'Fallo de tutela recibido',                $caso->fecha_fallo_tutela = now()],
                    'pago_honorarios'    => [$caso->estado = 'Honorarios pagados',                      $caso->fecha_pago_honorarios = now()],
                    'respuesta_positiva' => [$caso->estado = 'Respuesta favorable de aseguradora',      $caso->fecha_respuesta_aseguradora = now()],
                    'respuesta_negativa' => [$caso->estado = 'Respuesta negativa - Preparar tutela',    $caso->fecha_respuesta_aseguradora = now()],
                    default              => null,
                };
                break;

            case 'reclamaciones':
                if ($emailType === 'pago_indemnizacion') { $caso->estado = 'Indemnización pagada';      $caso->fecha_indemnizacion = now(); }
                if ($emailType === 'aviso_pago')         { $caso->estado = 'Aviso de pago recibido';   $caso->fecha_aviso_pago = now(); }
                if ($emailType === 'soporte_pago')       { $caso->estado = 'Soporte de pago recibido'; $caso->fecha_soporte_pago = now(); }
                break;
        }

        $caso->save();

        $fechaEvento = null;
        try {
            $fechaEvento = $email->date ? new \DateTime($email->date) : now();
        } catch (\Exception $e) {
            $fechaEvento = now();
        }

        Bitacora::create([
            'caso_id'      => $caso->id,
            'titulo'       => "Correo automático: {$emailType} ({$account['name']})",
            'descripcion'  => $descripcion,
            'fecha_evento' => $fechaEvento,
        ]);
    }

    private function findRelatedCase(string $subject, string $body): ?Caso
    {
        $text     = $subject . ' ' . $body;
        $patterns = [
            '/caso[:\s#]+([A-Z0-9\-]+)/i',
            '/expediente[:\s#]+([A-Z0-9\-]+)/i',
            '/radicado[:\s#]+([A-Z0-9\-]+)/i',
            '/([A-Z]{2,4}\d{4,6})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $caso = Caso::where('numero_caso', 'LIKE', "%{$matches[1]}%")->first();
                if ($caso) return $caso;
            }
        }

        return null;
    }

    private function cleanSubject(?string $subject): string
    {
        if (!$subject) return 'Sin asunto';
        $subject = mb_decode_mimeheader($subject);
        $subject = mb_convert_encoding($subject, 'UTF-8', 'UTF-8, ISO-8859-1');
        return trim($subject);
    }

    private function cleanBody(?string $body): string
    {
        if (!$body) return '';
        $body = strip_tags($body);
        $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        $body = preg_replace('/\s+/', ' ', $body);
        return trim($body);
    }

    private function extractData(string $subject, string $body): array
    {
        $data = [];

        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $body, $m)) {
            $data['fecha_mencionada'] = $m[0];
        }

        if (preg_match('/\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/', $body, $m)) {
            $data['monto_mencionado'] = $m[1];
        }

        if (preg_match('/(\d+)%/', $body, $m)) {
            $data['porcentaje_mencionado'] = $m[1];
        }

        return $data;
    }
}
