<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\Caso;
use App\Models\Bitacora;
use App\Services\AutoCaseCreationService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MicrosoftGraphService
{
    private $clientId;
    private $clientSecret;
    private $tenantId;
    private $redirectUri;
    private $httpClient;

    public function __construct()
    {
        $this->clientId = env('MICROSOFT_GRAPH_CLIENT_ID');
        $this->clientSecret = env('MICROSOFT_GRAPH_CLIENT_SECRET');
        $this->tenantId = env('MICROSOFT_GRAPH_TENANT_ID', 'common');
        $this->redirectUri = env('MICROSOFT_GRAPH_REDIRECT_URI');
        $this->httpClient = new Client();
    }

    /**
     * Obtener token de acceso para Microsoft Graph
     */
    private function getAccessToken($email, $password)
    {
        try {
            $response = $this->httpClient->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'password',
                    'username' => $email,
                    'password' => $password,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['access_token'] ?? null;

        } catch (RequestException $e) {
            \Log::error("Error obteniendo token para {$email}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener correos no leídos de una cuenta
     */
    private function getUnreadEmails($accessToken, $email)
    {
        try {
            $response = $this->httpClient->get("https://graph.microsoft.com/v1.0/users/{$email}/messages", [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    '$filter' => 'isRead eq false',
                    '$select' => 'id,subject,from,body,receivedDateTime',
                    '$orderby' => 'receivedDateTime desc',
                    '$top' => '50'
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['value'] ?? [];

        } catch (RequestException $e) {
            \Log::error("Error obteniendo correos de {$email}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Marcar correo como leído
     */
    private function markEmailAsRead($accessToken, $email, $messageId)
    {
        try {
            $this->httpClient->patch("https://graph.microsoft.com/v1.0/users/{$email}/messages/{$messageId}", [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'isRead' => true,
                ],
            ]);

            return true;

        } catch (RequestException $e) {
            \Log::error("Error marcando correo como leído: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Procesar todas las cuentas configuradas
     */
    public function processAllAccounts()
    {
        $accounts = $this->loadAccounts();
        $totalProcessed = 0;
        $results = [];

        foreach ($accounts as $account) {
            try {
                $processed = $this->processAccount($account);
                $results[$account['name']] = [
                    'success' => true,
                    'processed' => $processed,
                    'message' => 'Procesado correctamente'
                ];
                $totalProcessed += $processed;

            } catch (\Exception $e) {
                \Log::error("Error procesando cuenta {$account['name']}: " . $e->getMessage());
                $results[$account['name']] = [
                    'success' => false,
                    'processed' => 0,
                    'message' => $e->getMessage()
                ];
            }
        }

        return [
            'total_processed' => $totalProcessed,
            'results' => $results
        ];
    }

    /**
     * Procesar una cuenta individual
     */
    private function processAccount($account)
    {
        // Obtener token de acceso
        $accessToken = $this->getAccessToken($account['email'], $account['password']);
        
        if (!$accessToken) {
            throw new \Exception("No se pudo obtener token de acceso para {$account['email']}");
        }

        // Obtener correos no leídos
        $emails = $this->getUnreadEmails($accessToken, $account['email']);
        
        if (empty($emails)) {
            return 0;
        }

        $processedCount = 0;
        $limit = $account['priority'] === 'high' ? 100 : 50;
        $autoCaseService = new AutoCaseCreationService();

        foreach ($emails as $email) {
            if ($processedCount >= $limit) break;

            try {
                // Preparar datos del correo
                $emailData = [
                    'id' => $email['id'],
                    'subject' => $email['subject'] ?? 'Sin asunto',
                    'body' => $this->extractEmailBody($email),
                    'from_email' => $email['from']['emailAddress']['address'] ?? '',
                    'from_name' => $email['from']['emailAddress']['name'] ?? '',
                    'date' => $email['receivedDateTime'] ?? now(),
                ];

                // Clasificar correo según la cuenta
                $emailType = $this->classifyEmailByAccount($emailData, $account);
                
                // Buscar caso relacionado
                $caso = $this->findRelatedCase($emailData['subject'], $emailData['body']);
                
                if ($caso) {
                    // Caso existente - actualizar
                    EmailLog::create([
                        'caso_id' => $caso->id,
                        'email_id' => $emailData['id'],
                        'subject' => $emailData['subject'],
                        'body' => substr($emailData['body'], 0, 5000),
                        'from_email' => $emailData['from_email'],
                        'from_name' => $emailData['from_name'],
                        'email_date' => new \DateTime($emailData['date']),
                        'detected_insurance' => $this->detectInsurance($emailData['from_email'], $emailData['subject']),
                        'email_type' => $emailType,
                        'extracted_data' => json_encode($this->extractData($emailData['subject'], $emailData['body'])),
                        'processed' => true,
                    ]);
                    
                    $this->updateCaseStatusByAccount($caso, $emailType, $account, $emailData);
                    $processedCount++;
                } else {
                    // No hay caso relacionado - intentar crear automáticamente
                    $autoCaseResult = $autoCaseService->processEmailForNewCase($emailData, $account['name']);
                    
                    if ($autoCaseResult && $autoCaseResult['success']) {
                        $processedCount++;
                        \Log::info("Caso automático creado: " . $autoCaseResult['message']);
                    }
                }

                // Marcar como leído
                $this->markEmailAsRead($accessToken, $account['email'], $emailData['id']);
                
            } catch (\Exception $e) {
                \Log::error("Error procesando email ID {$email['id']} en cuenta {$account['name']}: " . $e->getMessage());
                continue;
            }
        }

        return $processedCount;
    }

    /**
     * Extraer cuerpo del correo
     */
    private function extractEmailBody($email)
    {
        if (isset($email['body']['contentType']) && $email['body']['contentType'] === 'text') {
            return strip_tags($email['body']['content'] ?? '');
        }
        
        return strip_tags($email['body']['content'] ?? '');
    }

    /**
     * Cargar cuentas configuradas
     */
    private function loadAccounts()
    {
        return [
            [
                'name' => 'gestionsoat365',
                'email' => env('GESTION_EMAIL'),
                'password' => env('GESTION_PASSWORD'),
                'priority' => 'high',
                'types' => ['respuesta_positiva', 'respuesta_negativa', 'en_proceso', 'citacion', 'pago_honorarios', 'fallo_tutela']
            ],
            [
                'name' => 'reclamaciones',
                'email' => env('RECLAMACIONES_EMAIL'),
                'password' => env('RECLAMACIONES_PASSWORD'),
                'priority' => 'high',
                'types' => ['pago_indemnizacion', 'aviso_pago', 'comprobante', 'soporte_pago']
            ]
        ];
    }

    /**
     * Clasificar correo según cuenta
     */
    private function classifyEmailByAccount($email, $account)
    {
        $subject = strtolower($email['subject']);
        $body = strtolower($email['body']);

        switch ($account['name']) {
            case 'gestionsoat365':
                if (preg_match('/(fallo|tutela)/', $subject . ' ' . $body)) {
                    return 'fallo_tutela';
                }
                if (preg_match('/(pago|honorario)/', $subject . ' ' . $body)) {
                    return 'pago_honorarios';
                }
                if (preg_match('/(aprobada|aceptada|procede)/', $subject . ' ' . $body)) {
                    return 'respuesta_positiva';
                }
                if (preg_match('/(negada|rechazada|no procede)/', $subject . ' ' . $body)) {
                    return 'respuesta_negativa';
                }
                return 'en_proceso';

            case 'reclamaciones':
                if (preg_match('/(indemnizacion|indemnizaci[oó]n|pago|abono)/', $subject . ' ' . $body)) {
                    return 'pago_indemnizacion';
                }
                if (preg_match('/(aviso|notificacion|confirmacion)/', $subject . ' ' . $body)) {
                    return 'aviso_pago';
                }
                if (preg_match('/(soporte|comprobante|recibo)/', $subject . ' ' . $body)) {
                    return 'soporte_pago';
                }
                return 'comprobante';

            default:
                return 'general';
        }
    }

    /**
     * Buscar caso relacionado
     */
    private function findRelatedCase($subject, $body)
    {
        // Buscar por número de caso en asunto o cuerpo
        if (preg_match('/SOAT[-\s]?(\d{4}[-\s]?\d{4})/i', $subject . ' ' . $body, $matches)) {
            $numeroCaso = str_replace(['-', ' '], '', $matches[1]);
            return Caso::where('numero_caso', 'LIKE', "%{$numeroCaso}%")->first();
        }

        // Buscar por nombre de cliente
        if (preg_match('/(?:cliente|nombre|señor[a]?|paciente)[:\s]+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i', $subject . ' ' . $body, $matches)) {
            $nombreCliente = $matches[1];
            return Caso::where('nombres', 'LIKE', "%{$nombreCliente}%")
                ->orWhere('apellidos', 'LIKE', "%{$nombreCliente}%")
                ->first();
        }

        return null;
    }

    /**
     * Detectar aseguradora
     */
    private function detectInsurance($email, $subject)
    {
        $text = strtolower($email . ' ' . $subject);
        
        if (strpos($text, 'sura') !== false) return 'SURA';
        if (strpos($text, 'mapfre') !== false) return 'MAPFRE';
        if (strpos($text, 'hdi') !== false) return 'HDI';
        if (strpos($text, 'axa') !== false) return 'AXA';
        if (strpos($text, 'liberty') !== false) return 'LIBERTY';
        if (strpos($text, 'bolívar') !== false || strpos($text, 'bolivar') !== false) return 'BOLÍVAR';
        if (strpos($text, 'estado solidario') !== false || strpos($text, 'solidaria') !== false) return 'ESTADO SOLIDARIO';
        if (strpos($text, 'allianz') !== false) return 'ALLIANZ';
        if (strpos($text, 'zurich') !== false) return 'ZURICH';
        
        return 'POR IDENTIFICAR';
    }

    /**
     * Extraer datos del correo
     */
    private function extractData($subject, $body)
    {
        return [
            'subject_keywords' => $this->extractKeywords($subject),
            'body_keywords' => $this->extractKeywords($body),
            'has_amount' => preg_match('/\$?\s*\d+(?:\.\d{3})*(?:,\d{2})?/', $subject . ' ' . $body'),
            'has_date' => preg_match('/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/', $subject . ' ' . $body),
            'urgency' => $this->detectUrgency($subject . ' ' . $body),
        ];
    }

    /**
     * Extraer palabras clave
     */
    private function extractKeywords($text)
    {
        $keywords = ['pago', 'indemnizacion', 'fallo', 'tutela', 'dictamen', 'citacion', 'honorarios'];
        $found = [];
        
        foreach ($keywords as $keyword) {
            if (strpos(strtolower($text), $keyword) !== false) {
                $found[] = $keyword;
            }
        }
        
        return $found;
    }

    /**
     * Detectar urgencia
     */
    private function detectUrgency($text)
    {
        $text = strtolower($text);
        
        if (preg_match('/(urgente|inmediato|pronto|ya)/', $text)) {
            return 'high';
        }
        
        if (preg_match('/(próximo|proximamente|en días|semana)/', $text)) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Actualizar estado del caso según cuenta y tipo
     */
    private function updateCaseStatusByAccount($caso, $emailType, $account, $email)
    {
        $descripcion = "Correo automático desde {$account['name']}: {$email['subject']}";

        switch ($account['name']) {
            case 'gestionsoat365':
                if ($emailType === 'fallo_tutela') {
                    $caso->estado = 'Fallo de tutela recibido';
                    $caso->fecha_tutela = now();
                    $descripcion .= "\n\nAcción requerida: Revisar fallo y determinar estrategia";
                }
                if ($emailType === 'pago_honorarios') {
                    $caso->estado = 'Honorarios pagados';
                    $caso->fecha_pago_honorarios = now();
                    $descripcion .= "\n\nAcción: Verificar recepción de honorarios";
                }
                if ($emailType === 'respuesta_positiva') {
                    $caso->estado = 'Respuesta positiva recibida';
                    $caso->fecha_respuesta_aseguradora = now();
                    $descripcion .= "\n\nAcción: Preparar documentos siguientes";
                }
                if ($emailType === 'respuesta_negativa') {
                    $caso->estado = 'Respuesta negativa recibida';
                    $caso->fecha_respuesta_aseguradora = now();
                    $descripcion .= "\n\nAcción: Preparar apelación o tutela";
                }
                break;

            case 'reclamaciones':
                if ($emailType === 'pago_indemnizacion') {
                    $caso->estado = 'Indemnización pagada';
                    $caso->fecha_pago_final = now();
                    $descripcion .= "\n\nAcción: Verificar desembolso y cerrar caso";
                }
                if ($emailType === 'aviso_pago') {
                    $caso->estado = 'Aviso de pago recibido';
                    $caso->fecha_pago_final = now();
                    $descripcion .= "\n\nAcción: Confirmar recepción de fondos";
                }
                if ($emailType === 'soporte_pago') {
                    $caso->estado = 'Soporte de pago recibido';
                    $descripcion .= "\n\nAcción: Verificar documentación";
                }
                break;
        }

        $caso->save();

        // Agregar a bitácora
        Bitacora::create([
            'caso_id' => $caso->id,
            'titulo' => "Correo automático: {$emailType} ({$account['name']})",
            'descripcion' => $descripcion,
            'fecha_evento' => now(),
            'auto_generada' => true,
            'tipo_alerta' => 'correo_procesado',
            'prioridad' => $this->getPriorityByType($emailType),
        ]);
    }

    /**
     * Obtener prioridad según tipo de correo
     */
    private function getPriorityByType($emailType)
    {
        $highPriority = ['fallo_tutela', 'pago_indemnizacion', 'respuesta_positiva'];
        $mediumPriority = ['pago_honorarios', 'aviso_pago', 'soporte_pago'];
        
        if (in_array($emailType, $highPriority)) {
            return 'alta';
        }
        
        if (in_array($emailType, $mediumPriority)) {
            return 'media';
        }
        
        return 'baja';
    }
}
