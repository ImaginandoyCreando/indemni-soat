<?php

namespace App\Jobs;

use App\Models\Caso;
use App\Models\EmailLog;
use App\Models\Bitacora;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProcessEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Log::info('Iniciando procesamiento de correos electrónicos');

        // Aquí iría la lógica para conectar con Gmail/Outlook API
        // Por ahora, simulamos el proceso

        $this->processNewEmails();
        $this->checkOverdueCases();
        $this->sendAlerts();
    }

    private function processNewEmails()
    {
        // Simulación: buscar correos no procesados
        // En realidad, aquí se conectaría con Gmail API o similar
        
        $emails = $this->fetchEmailsFromProvider();

        foreach ($emails as $email) {
            // Evitar duplicados
            if (EmailLog::where('email_id', $email['id'])->exists()) {
                continue;
            }

            // Detectar aseguradora y tipo
            $insurance = EmailLog::detectInsurance($email['from'], $email['subject'], $email['body']);
            $emailType = EmailLog::classifyEmail($email['subject'], $email['body']);

            // Buscar caso relacionado (por número de caso en asunto/cuerpo)
            $caso = $this->findRelatedCase($email['subject'], $email['body']);

            if ($caso) {
                // Guardar log del correo
                $emailLog = EmailLog::create([
                    'caso_id' => $caso->id,
                    'email_id' => $email['id'],
                    'subject' => $email['subject'],
                    'body' => $email['body'],
                    'from_email' => $email['from'],
                    'from_name' => $email['from_name'],
                    'email_date' => $email['date'],
                    'detected_insurance' => $insurance,
                    'email_type' => $emailType,
                    'extracted_data' => $this->extractData($email['subject'], $email['body']),
                    'processed' => false,
                ]);

                // Actualizar estado del caso según el tipo de correo
                $this->updateCaseStatus($caso, $emailType, $email);

                // Marcar como procesado
                $emailLog->processed = true;
                $emailLog->save();

                Log::info("Correo procesado: {$email['subject']} - Caso: {$caso->numero_caso}");
            }
        }
    }

    private function findRelatedCase($subject, $body)
    {
        // Buscar número de caso en asunto o cuerpo
        $text = $subject . ' ' . $body;
        
        // Patrones comunes de números de caso
        $patterns = [
            '/caso[:\s#]+([A-Z0-9\-]+)/i',
            '/expediente[:\s#]+([A-Z0-9\-]+)/i',
            '/radicado[:\s#]+([A-Z0-9\-]+)/i',
            '/([A-Z]{2,4}\d{4,6})/', // Ej: SOAT123456
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

    private function updateCaseStatus($caso, $emailType, $email)
    {
        $descripcion = "Correo recibido de {$email['from']}: {$email['subject']}";
        
        switch ($emailType) {
            case 'solicitud_enviada':
                $caso->estado = 'Solicitud enviada a aseguradora';
                $caso->fecha_envio_solicitud = now();
                break;
                
            case 'respuesta_positiva':
                $caso->estado = 'Respuesta favorable de aseguradora';
                $caso->fecha_respuesta_aseguradora = now();
                break;
                
            case 'respuesta_negativa':
                $caso->estado = 'Respuesta negativa - Preparar tutela';
                $caso->fecha_respuesta_aseguradora = now();
                break;
                
            case 'en_proceso':
                $caso->estado = 'En estudio por aseguradora';
                break;
                
            case 'requiere_documentos':
                $caso->estado = 'Requiere documentos adicionales';
                break;
                
            case 'citacion':
                $caso->estado = 'Citación programada';
                break;
        }

        $caso->save();

        // Agregar a bitácora
        Bitacora::create([
            'caso_id' => $caso->id,
            'titulo' => "Correo automático: {$emailType}",
            'descripcion' => $descripcion,
            'fecha_evento' => $email['date'],
        ]);
    }

    private function checkOverdueCases()
    {
        // Casos sin respuesta después de 30 días
        $overdueCases = Caso::where('estado', 'Solicitud enviada a aseguradora')
            ->where('fecha_envio_solicitud', '<', now()->subDays(30))
            ->get();

        foreach ($overdueCases as $caso) {
            // Enviar alerta
            $this->sendAlert('overdue', $caso);
            
            // Actualizar estado
            $caso->estado = 'Sin respuesta - Requerimiento';
            $caso->save();
        }
    }

    private function sendAlerts()
    {
        // Enviar alertas diarias/semanales
        // Esta es la lógica para enviar correos de alerta a los abogados
    }

    private function sendAlert($type, $caso)
    {
        // Lógica para enviar alertas por correo
        Log::alert("Alerta {$type} para caso {$caso->numero_caso}");
    }

    private function extractData($subject, $body)
    {
        // Extraer datos importantes del correo
        $data = [];
        
        // Extraer fechas
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $body, $matches)) {
            $data['fecha_mencionada'] = $matches[0];
        }
        
        // Extraer montos
        if (preg_match('/\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/', $body, $matches)) {
            $data['monto_mencionado'] = $matches[1];
        }
        
        return $data;
    }

    private function fetchEmailsFromProvider()
    {
        // Aquí iría la integración real con Gmail API o Outlook
        // Por ahora, retornamos array vacío
        return [];
    }
}
