<?php

namespace App\Console\Commands;

use App\Services\MultiImapService;
use App\Models\Caso;
use App\Models\Bitacora;
use App\Models\EmailLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AutoEmailProcessor extends Command
{
    protected $signature = 'emails:auto-process {--force : Forzar procesamiento completo}';
    protected $description = 'Procesamiento automático de correos 24/7 con detección inteligente';

    public function handle()
    {
        $this->info('🚀 Iniciando procesamiento automático de correos...');
        
        try {
            $service = new MultiImapService();
            $results = $service->processAllAccounts();
            
            $totalProcessed = $results['total_processed'];
            $accountResults = $results['results'];
            
            // Mostrar resultados
            if ($totalProcessed > 0) {
                $this->info("✅ Se procesaron {$totalProcessed} correos:");
                
                foreach ($accountResults as $account => $result) {
                    if ($result['success']) {
                        $this->line("   📧 {$account}: {$result['processed']} correos procesados");
                    } else {
                        $this->error("   ❌ {$account}: {$result['message']}");
                    }
                }
                
                // Contar casos creados automáticamente
                $autoCasesCount = Caso::where('auto_created', true)
                    ->where('created_at', '>', now()->subMinutes(5))
                    ->count();
                
                if ($autoCasesCount > 0) {
                    $this->info("🎉 {$autoCasesCount} casos nuevos creados automáticamente");
                }
                
                // Generar alertas inteligentes
                $this->generateIntelligentAlerts();
                
            } else {
                $this->info('📭 No hay correos nuevos para procesar');
            }
            
            // Verificar casos críticos
            $this->checkCriticalCases();
            
            $this->info('✅ Procesamiento automático completado.');
            
        } catch (\Exception $e) {
            $this->error('❌ Error en procesamiento automático: ' . $e->getMessage());
            \Log::error("AutoEmailProcessor Error: " . $e->getMessage());
        }
        
        return Command::SUCCESS;
    }
    
    private function generateIntelligentAlerts()
    {
        $this->info('🔍 Generando alertas inteligentes...');
        
        // Casos sin respuesta por más de 7 días
        $pendingCases = Caso::where('estado', 'Solicitud enviada a aseguradora')
            ->where('fecha_envio_solicitud', '<', now()->subDays(7))
            ->whereDoesntHave('bitacoras', function($query) {
                $query->where('titulo', 'like', '%Alerta%')
                    ->where('created_at', '>', now()->subDays(1));
            })
            ->get();
            
        foreach ($pendingCases as $caso) {
            $this->createAlertBitacora($caso, 'alerta_no_respuesta', 
                '⚠️ Caso sin respuesta de aseguradora por más de 7 días',
                'Se recomienda enviar recordatorio o iniciar contacto directo con la aseguradora.');
        }
        
        // Casos próximos a vencer (30 días)
        $overdueCases = Caso::where('estado', 'Solicitud enviada a aseguradora')
            ->where('fecha_envio_solicitud', '<', now()->subDays(25))
            ->where('fecha_envio_solicitud', '>=', now()->subDays(30))
            ->whereDoesntHave('bitacoras', function($query) {
                $query->where('titulo', 'like', '%Próximo a vencer%')
                    ->where('created_at', '>', now()->subDays(3));
            })
            ->get();
            
        foreach ($overdueCases as $caso) {
            $this->createAlertBitacora($caso, 'alerta_proximo_vencer', 
                '⏰ Caso próximo a vencer (30 días)',
                'El caso está próximo a cumplir 30 días sin respuesta. Preparar para siguiente acción.');
        }
        
        // Casos con pagos recientes
        $recentPayments = EmailLog::where('email_type', 'pago_indemnizacion')
            ->where('created_at', '>', now()->subHours(1))
            ->with('caso')
            ->get();
            
        foreach ($recentPayments as $email) {
            if ($email->caso) {
                $this->createAlertBitacora($email->caso, 'pago_detectado', 
                    '💰 Pago de indemnización detectado',
                    'Se ha recibido notificación de pago. Verificar desembolso y actualizar estado final.');
            }
        }
    }
    
    private function checkCriticalCases()
    {
        $this->info('🚨 Verificando casos críticos...');
        
        // Casos con fallos de tutela
        $tutelaCases = EmailLog::where('email_type', 'fallo_tutela')
            ->where('created_at', '>', now()->subHours(2))
            ->with('caso')
            ->get();
            
        foreach ($tutelaCases as $email) {
            if ($email->caso) {
                $this->createAlertBitacora($email->caso, 'fallo_tutela', 
                    '⚖️ Fallo de tutela recibido',
                    'Acción inmediata requerida: Revisar fallo y determinar pasos siguientes.');
            }
        }
        
        // Casos con respuestas positivas
        $positiveResponses = EmailLog::where('email_type', 'respuesta_positiva')
            ->where('created_at', '>', now()->subHours(2))
            ->with('caso')
            ->get();
            
        foreach ($positiveResponses as $email) {
            if ($email->caso) {
                $this->createAlertBitacora($email->caso, 'respuesta_positiva', 
                    '✅ Respuesta positiva de aseguradora',
                    'La aseguradora ha respondido favorablemente. Preparar documentos para proceder.');
            }
        }
    }
    
    private function createAlertBitacora($caso, $alertType, $titulo, $descripcion)
    {
        // Evitar duplicados en las últimas 24 horas
        $exists = Bitacora::where('caso_id', $caso->id)
            ->where('titulo', $titulo)
            ->where('created_at', '>', now()->subDay())
            ->exists();
            
        if (!$exists) {
            Bitacora::create([
                'caso_id' => $caso->id,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'fecha_evento' => now(),
                'tipo_alerta' => $alertType,
                'auto_generada' => true
            ]);
            
            $this->line("   📝 Alerta creada para caso {$caso->numero_caso}: {$titulo}");
        }
    }
}
