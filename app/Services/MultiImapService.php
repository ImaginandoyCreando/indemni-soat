<?php

namespace App\Services;

use PhpImap\Mailbox;
use App\Models\EmailLog;
use App\Models\Caso;
use App\Models\Bitacora;

class MultiImapService
{
    private $accounts = [];
    private $connection;

    public function __construct()
    {
        $this->loadAccounts();
    }

    private function loadAccounts()
    {
        // Cargar solo las 2 cuentas principales con ALTA PRIORIDAD
        $this->accounts = [
            [
                'name' => 'gestionsoat365',
                'email' => env('GESTION_EMAIL'),
                'password' => env('GESTION_PASSWORD'),
                'priority' => 'high', // Respuestas aseguradoras, tutelas, honorarios
                'types' => ['respuesta_positiva', 'respuesta_negativa', 'en_proceso', 'citacion', 'pago_honorarios', 'fallo_tutela']
            ],
            [
                'name' => 'reclamaciones',
                'email' => env('RECLAMACIONES_EMAIL'),
                'password' => env('RECLAMACIONES_PASSWORD'),
                'priority' => 'high', // Pagos e indemnizaciones - ahora ALTA PRIORIDAD
                'types' => ['pago_indemnizacion', 'aviso_pago', 'comprobante', 'soporte_pago']
            ]
        ];

        // Filtrar cuentas que tengan credenciales configuradas
        $this->accounts = array_filter($this->accounts, function($account) {
            return $account['email'] && $account['password'];
        });
    }

    public function processAllAccounts()
    {
        $totalProcessed = 0;
        $results = [];

        // Ordenar por prioridad
        $highPriority = array_filter($this->accounts, fn($a) => $a['priority'] === 'high');
        $mediumPriority = array_filter($this->accounts, fn($a) => $a['priority'] === 'medium');
        $lowPriority = array_filter($this->accounts, fn($a) => $a['priority'] === 'low');

        // Procesar en orden de prioridad
        foreach (array_merge($highPriority, $mediumPriority, $lowPriority) as $account) {
            try {
                $processed = $this->processAccount($account);
                $totalProcessed += $processed;
                $results[$account['name']] = [
                    'success' => true,
                    'processed' => $processed,
                    'message' => "Procesados {$processed} correos"
                ];
            } catch (\Exception $e) {
                $results[$account['name']] = [
                    'success' => false,
                    'processed' => 0,
                    'message' => 'Error: ' . $e->getMessage()
                ];
                \Log::error("Error procesando cuenta {$account['name']}: " . $e->getMessage());
            }
        }

        return [
            'total_processed' => $totalProcessed,
            'results' => $results
        ];
    }

    private function processAccount($account)
    {
        $hostname = '{outlook.office365.com:993/imap/ssl}INBOX';
        
        $mailbox = new Mailbox($hostname, $account['email'], $account['password']);
        $mailbox->checkImapStream();

        // Obtener correos no leídos
        $emailsIds = $mailbox->searchMailbox('UNSEEN');
        
        if (empty($emailsIds)) {
            $mailbox->disconnect();
            return 0;
        }

        $processedCount = 0;
        $limit = $account['priority'] === 'high' ? 100 : 50;

        foreach ($emailsIds as $emailId) {
            if ($processedCount >= $limit) break;

            try {
                $email = $mailbox->getMail($emailId, false);
                
                // Clasificar correo según la cuenta
                $emailType = $this->classifyEmailByAccount($email, $account);
                $insurance = EmailLog::detectInsurance($email->fromAddress, $email->subject, $email->textPlain);
                
                // Buscar caso relacionado
                $caso = $this->findRelatedCase($email->subject, $email->textPlain);
                
                if ($caso) {
                    // Guardar log del correo
                    EmailLog::create([
                        'caso_id' => $caso->id,
                        'email_id' => $email->messageId,
                        'subject' => $this->cleanSubject($email->subject),
                        'body' => $this->cleanBody($email->textHtml ?: $email->textPlain),
                        'from_email' => $email->fromAddress,
                        'from_name' => $email->fromName,
                        'email_date' => new \DateTime($email->date),
                        'detected_insurance' => $insurance,
                        'email_type' => $emailType,
                        'extracted_data' => $this->extractData($email->subject, $email->textPlain),
                        'processed' => true,
                    ]);
                    
                    // Actualizar estado del caso según tipo y cuenta
                    $this->updateCaseStatusByAccount($caso, $emailType, $account, $email);
                    
                    $processedCount++;
                }

                // Marcar como leído
                $mailbox->markMailAsRead($emailId);
                
            } catch (\Exception $e) {
                \Log::error("Error procesando email ID {$emailId} en cuenta {$account['name']}: " . $e->getMessage());
                continue;
            }
        }

        $mailbox->disconnect();
        return $processedCount;
    }

    private function classifyEmailByAccount($email, $account)
    {
        $subject = strtolower($email->subject);
        $body = strtolower($email->textPlain ?: '');

        // Clasificación específica por cuenta
        switch ($account['name']) {
            case 'gestionsoat365':
                // Respuestas de aseguradoras, tutelas, honorarios
                if (preg_match('/(tutela|fallo|sentencia|decision)/', $subject . ' ' . $body)) {
                    return 'fallo_tutela';
                }
                if (preg_match('/(honorarios|pago|comision|remuneracion)/', $subject . ' ' . $body)) {
                    return 'pago_honorarios';
                }
                if (preg_match('/(aprobad[ao]|aceptad[ao]|procede|concedid[ao])/i', $subject . ' ' . $body)) {
                    return 'respuesta_positiva';
                }
                if (preg_match('/(niega|rechazad[ao]|negad[ao]|improcedente)/i', $subject . ' ' . $body)) {
                    return 'respuesta_negativa';
                }
                break;

            case 'labatalla':
                // Dictámenes de junta de invalidez
                if (preg_match('/(dictamen|junta|invalidez|calificacion|perdida)/', $subject . ' ' . $body)) {
                    return 'dictamen_junta';
                }
                if (preg_match('/(pago|soporte|comprobante|recibo)/', $subject . ' ' . $body)) {
                    return 'soporte_pago';
                }
                break;

            case 'reclamaciones':
                // Pagos e indemnizaciones (ALTA PRIORIDAD)
                if (preg_match('/(indemnizacion|indemnizaci[oó]n|pago|abono)/', $subject . ' ' . $body)) {
                    return 'pago_indemnizacion';
                }
                if (preg_match('/(aviso|notificacion|confirmacion)/', $subject . ' ' . $body)) {
                    return 'aviso_pago';
                }
                if (preg_match('/(soporte|comprobante|recibo)/', $subject . ' ' . $body)) {
                    return 'soporte_pago';
                }
                break;

            case 'dicami':
                return 'info_general';

            case 'seg_asesorias':
                return 'documento_legal';
        }

        // Clasificación general si no coincide con nada específico
        return EmailLog::classifyEmail($email->subject, $email->textPlain);
    }

    private function updateCaseStatusByAccount($caso, $emailType, $account, $email)
    {
        $descripcion = "Correo de {$account['email']}: {$email->subject}";
        
        // Actualizaciones específicas por cuenta
        switch ($account['name']) {
            case 'gestionsoat365':
                switch ($emailType) {
                    case 'fallo_tutela':
                        $caso->estado = 'Fallo de tutela recibido';
                        $caso->fecha_fallo_tutela = now();
                        break;
                    case 'pago_honorarios':
                        $caso->estado = 'Honorarios pagados';
                        $caso->fecha_pago_honorarios = now();
                        break;
                    case 'respuesta_positiva':
                        $caso->estado = 'Respuesta favorable de aseguradora';
                        $caso->fecha_respuesta_aseguradora = now();
                        break;
                    case 'respuesta_negativa':
                        $caso->estado = 'Respuesta negativa - Preparar tutela';
                        $caso->fecha_respuesta_aseguradora = now();
                        break;
                }
                break;

            case 'labatalla':
                if ($emailType === 'dictamen_junta') {
                    $caso->estado = 'Dictamen de junta recibido';
                    $caso->fecha_dictamen_junta = now();
                }
                break;

            case 'reclamaciones':
                if ($emailType === 'pago_indemnizacion') {
                    $caso->estado = 'Indemnización pagada';
                    $caso->fecha_indemnizacion = now();
                }
                if ($emailType === 'aviso_pago') {
                    $caso->estado = 'Aviso de pago recibido';
                    $caso->fecha_aviso_pago = now();
                }
                if ($emailType === 'soporte_pago') {
                    $caso->estado = 'Soporte de pago recibido';
                    $caso->fecha_soporte_pago = now();
                }
                break;
        }

        $caso->save();

        // Agregar a bitácora
        Bitacora::create([
            'caso_id' => $caso->id,
            'titulo' => "Correo automático: {$emailType} ({$account['name']})",
            'descripcion' => $descripcion,
            'fecha_evento' => new \DateTime($email->date),
        ]);
    }

    private function findRelatedCase($subject, $body)
    {
        $text = $subject . ' ' . $body;
        
        $patterns = [
            '/caso[:\s#]+([A-Z0-9\-]+)/i',
            '/expediente[:\s#]+([A-Z0-9\-]+)/i',
            '/radicado[:\s#]+([A-Z0-9\-]+)/i',
            '/([A-Z]{2,4}\d{4,6})/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $numeroCaso = $matches[1];
                $caso = Caso::where('numero_caso', 'LIKE', "%{$numeroCaso}%")->first();
                if ($caso) {
                    return $caso;
                }
            }
        }
        
        return null;
    }

    private function cleanSubject($subject)
    {
        if (!$subject) return 'Sin asunto';
        $subject = mb_decode_mimeheader($subject);
        $subject = mb_convert_encoding($subject, 'UTF-8', 'UTF-8, ISO-8859-1');
        return trim($subject);
    }

    private function cleanBody($body)
    {
        if (!$body) return '';
        $body = strip_tags($body);
        $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        $body = preg_replace('/\s+/', ' ', $body);
        return trim($body);
    }

    private function extractData($subject, $body)
    {
        $data = [];
        
        // Extraer fechas
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $body, $matches)) {
            $data['fecha_mencionada'] = $matches[0];
        }
        
        // Extraer montos
        if (preg_match('/\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/', $body, $matches)) {
            $data['monto_mencionado'] = $matches[1];
        }
        
        // Extraer porcentajes
        if (preg_match('/(\d+)%/', $body, $matches)) {
            $data['porcentaje_mencionado'] = $matches[1];
        }
        
        return $data;
    }

    public function testAllConnections()
    {
        $results = [];
        
        foreach ($this->accounts as $account) {
            try {
                $hostname = '{outlook.office365.com:993/imap/ssl}INBOX';
                $mailbox = new Mailbox($hostname, $account['email'], $account['password']);
                $mailbox->checkImapStream();
                
                $totalEmails = count($mailbox->searchMailbox('ALL'));
                $unreadEmails = count($mailbox->searchMailbox('UNSEEN'));
                
                $results[$account['name']] = [
                    'success' => true,
                    'email' => $account['email'],
                    'priority' => $account['priority'],
                    'total_emails' => $totalEmails,
                    'unread_emails' => $unreadEmails,
                    'message' => 'Conexión exitosa'
                ];
                
                $mailbox->disconnect();
                
            } catch (\Exception $e) {
                $results[$account['name']] = [
                    'success' => false,
                    'email' => $account['email'],
                    'priority' => $account['priority'],
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}
