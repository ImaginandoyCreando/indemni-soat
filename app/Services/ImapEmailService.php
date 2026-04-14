<?php

namespace App\Services;

use PhpImap\Mailbox;
use App\Models\EmailLog;
use App\Models\Caso;
use App\Models\Bitacora;

class ImapEmailService
{
    private $mailbox;
    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        try {
            $hostname = '{outlook.office365.com:993/imap/ssl}INBOX';
            $username = env('OUTLOOK_EMAIL');
            $password = env('OUTLOOK_PASSWORD');

            $this->mailbox = new Mailbox($hostname, $username, $password);
            
            // Probar conexión
            $this->mailbox->checkImapStream();
            
            \Log::info('Conexión IMAP exitosa a Outlook');
            
        } catch (\Exception $e) {
            \Log::error('Error conectando a IMAP: ' . $e->getMessage());
            throw new \Exception('No se pudo conectar al correo: ' . $e->getMessage());
        }
    }

    public function getRecentEmails($limit = 50)
    {
        try {
            // Obtener correos no leídos
            $emailsIds = $this->mailbox->searchMailbox('UNSEEN');
            
            if (empty($emailsIds)) {
                return [];
            }

            $processedEmails = [];
            $count = 0;

            foreach ($emailsIds as $emailId) {
                if ($count >= $limit) break;

                try {
                    $email = $this->mailbox->getMail($emailId, false);
                    
                    $processedEmails[] = [
                        'id' => $emailId,
                        'subject' => $this->cleanSubject($email->subject),
                        'body' => $this->cleanBody($email->textHtml ?: $email->textPlain),
                        'from_email' => $email->fromAddress,
                        'from_name' => $email->fromName,
                        'date' => new \DateTime($email->date),
                        'to' => $email->to,
                        'message_id' => $email->messageId,
                    ];

                    // Marcar como leído
                    $this->mailbox->markMailAsRead($emailId);
                    
                    $count++;

                } catch (\Exception $e) {
                    \Log::error("Error procesando email ID {$emailId}: " . $e->getMessage());
                    continue;
                }
            }

            return $processedEmails;

        } catch (\Exception $e) {
            \Log::error('Error obteniendo correos: ' . $e->getMessage());
            return [];
        }
    }

    public function searchEmails($query, $limit = 50)
    {
        try {
            // Buscar correos por query
            $searchCriteria = 'ALL SUBJECT "' . $query . '"';
            $emailsIds = $this->mailbox->searchMailbox($searchCriteria);
            
            if (empty($emailsIds)) {
                return [];
            }

            $processedEmails = [];
            $count = 0;

            foreach ($emailsIds as $emailId) {
                if ($count >= $limit) break;

                $email = $this->mailbox->getMail($emailId, false);
                
                $processedEmails[] = [
                    'id' => $emailId,
                    'subject' => $this->cleanSubject($email->subject),
                    'body' => $this->cleanBody($email->textHtml ?: $email->textPlain),
                    'from_email' => $email->fromAddress,
                    'from_name' => $email->fromName,
                    'date' => new \DateTime($email->date),
                ];
                
                $count++;
            }

            return $processedEmails;

        } catch (\Exception $e) {
            \Log::error('Error buscando correos: ' . $e->getMessage());
            return [];
        }
    }

    private function cleanSubject($subject)
    {
        if (!$subject) return 'Sin asunto';
        
        // Decodificar subject si está codificado
        $subject = mb_decode_mimeheader($subject);
        $subject = mb_convert_encoding($subject, 'UTF-8', 'UTF-8, ISO-8859-1');
        
        return trim($subject);
    }

    private function cleanBody($body)
    {
        if (!$body) return '';
        
        // Limpiar HTML
        $body = strip_tags($body);
        
        // Limificar caracteres especiales
        $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        
        // Eliminar espacios múltiples
        $body = preg_replace('/\s+/', ' ', $body);
        
        return trim($body);
    }

    public function testConnection()
    {
        try {
            $this->connect();
            
            // Intentar obtener el primer correo
            $emails = $this->mailbox->searchMailbox('ALL', 1);
            
            if (!empty($emails)) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa',
                    'total_emails' => count($this->mailbox->searchMailbox('ALL'))
                ];
            } else {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa (sin correos)',
                    'total_emails' => 0
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    public function __destruct()
    {
        if ($this->mailbox) {
            $this->mailbox->disconnect();
        }
    }
}
