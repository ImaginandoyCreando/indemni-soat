<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Models\Caso;
use App\Models\Bitacora;
use App\Services\MultiImapService;
use App\Jobs\ProcessEmailsJob;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        // Estadísticas básicas sin EmailIntegration
        $stats = [
            'emails_today' => EmailLog::whereDate('created_at', today())->count(),
            'cases_updated' => EmailLog::whereDate('created_at', today())->distinct('caso_id')->count(),
            'overdue_cases' => Caso::where('estado', 'Solicitud enviada a aseguradora')
                ->where('fecha_envio_solicitud', '<', now()->subDays(30))->count(),
            'pending_alerts' => 0,
            'total_cases' => Caso::count(),
            'auto_cases_today' => Caso::where('auto_created', true)
                ->whereDate('created_at', today())->count(),
        ];
        
        // Correos recientes
        $recentEmails = EmailLog::with('caso')
            ->orderBy('email_date', 'desc')
            ->limit(20)
            ->get();
        
        // Cuentas configuradas (estáticas por ahora)
        $emailIntegrations = collect([
            (object) [
                'email_provider' => 'outlook',
                'email_address' => 'gestionsoat365@outlook.com',
                'is_active' => true,
            ],
            (object) [
                'email_provider' => 'outlook',
                'email_address' => 'reclamacionessoat@hotmail.com',
                'is_active' => true,
            ]
        ]);
        
        return view('emails.index', compact('emailIntegrations', 'stats', 'recentEmails'));
    }
    
    public function sync(Request $request)
    {
        try {
            $service = new MultiImapService();
            $results = $service->processAllAccounts();
            
            $totalProcessed = $results['total_processed'];
            $accountResults = $results['results'];
            
            // Verificar casos vencidos
            $this->checkOverdueCases();
            
            if ($totalProcessed > 0) {
                $message = "Se procesaron {$totalProcessed} correos de 2 cuentas:\n";
                $autoCasesCount = 0;
                
                foreach ($accountResults as $account => $result) {
                    if ($result['success']) {
                        $message .= "- {$account}: {$result['processed']} correos\n";
                    } else {
                        $message .= "- {$account}: Error - {$result['message']}\n";
                    }
                }
                
                // Contar casos creados automáticamente
                $autoCasesCount = Caso::where('auto_created', true)
                    ->where('created_at', '>', now()->subMinutes(5))
                    ->count();
                
                if ($autoCasesCount > 0) {
                    $message .= "\n🎉 {$autoCasesCount} casos nuevos creados automáticamente";
                }
                
                return redirect()->route('emails.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('emails.index')
                    ->with('info', 'No hay correos nuevos para procesar en las 2 cuentas');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('emails.index')
                ->with('error', 'Error procesando correos: ' . $e->getMessage());
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
            // Actualizar estado
            $caso->estado = 'Sin respuesta - Requerimiento';
            $caso->save();
            
            // Agregar a bitácora
            Bitacora::create([
                'caso_id' => $caso->id,
                'titulo' => 'Alerta automática: Caso sin respuesta',
                'descripcion' => 'Han pasado 30 días sin respuesta de la aseguradora',
                'fecha_evento' => now(),
            ]);
        }
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
}
