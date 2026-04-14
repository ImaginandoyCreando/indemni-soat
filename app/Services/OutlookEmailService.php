<?php

namespace App\Services;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use GuzzleHttp\Client;

class OutlookEmailService
{
    private $graph;
    private $accessToken;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
        $this->graph = new Graph();
        $this->graph->setAccessToken($accessToken);
    }

    public function getRecentEmails($limit = 50)
    {
        try {
            // Obtener correos de la bandeja de entrada
            $emails = $this->graph->createRequest("GET", "/me/messages")
                ->setTop($limit)
                ->setOrderBy("receivedDateTime desc")
                ->addHeaders(["Prefer" => "outlook.body-content-type=\"text\""])
                ->execute();

            $processedEmails = [];

            foreach ($emails as $email) {
                $processedEmails[] = [
                    'id' => $email->getId(),
                    'subject' => $email->getSubject(),
                    'body' => $this->cleanBody($email->getBody()->getContent()),
                    'from' => $email->getFrom()->getEmailAddress()->getAddress(),
                    'from_name' => $email->getFrom()->getEmailAddress()->getName(),
                    'date' => new \DateTime($email->getReceivedDateTime()),
                    'to' => $email->getToRecipients(),
                ];
            }

            return $processedEmails;
        } catch (\Exception $e) {
            \Log::error('Error obteniendo correos de Outlook: ' . $e->getMessage());
            return [];
        }
    }

    private function cleanBody($body)
    {
        // Limpiar HTML y extraer texto plano
        $body = strip_tags($body);
        $body = preg_replace('/\s+/', ' ', $body);
        return trim($body);
    }

    public function markAsRead($emailId)
    {
        try {
            $this->graph->createRequest("PATCH", "/me/messages/{$emailId}")
                ->addHeaders(["Content-Type" => "application/json"])
                ->attachBody(["isRead" => true])
                ->execute();
            return true;
        } catch (\Exception $e) {
            \Log::error('Error marcando correo como leído: ' . $e->getMessage());
            return false;
        }
    }

    public function searchEmails($query, $limit = 50)
    {
        try {
            $emails = $this->graph->createRequest("GET", "/me/messages")
                ->setSearch($query)
                ->setTop($limit)
                ->setOrderBy("receivedDateTime desc")
                ->execute();

            $processedEmails = [];

            foreach ($emails as $email) {
                $processedEmails[] = [
                    'id' => $email->getId(),
                    'subject' => $email->getSubject(),
                    'body' => $this->cleanBody($email->getBody()->getContent()),
                    'from' => $email->getFrom()->getEmailAddress()->getAddress(),
                    'from_name' => $email->getFrom()->getEmailAddress()->getName(),
                    'date' => new \DateTime($email->getReceivedDateTime()),
                ];
            }

            return $processedEmails;
        } catch (\Exception $e) {
            \Log::error('Error buscando correos: ' . $e->getMessage());
            return [];
        }
    }
}
