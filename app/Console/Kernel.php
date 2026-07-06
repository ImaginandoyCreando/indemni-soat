<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\EnviarResumenDiario::class,
        Commands\SyncEmails::class,
        Commands\TestImap::class,
        Commands\AutoEmailProcessor::class,
        Commands\EnviarNotificacionesWhatsapp::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Procesamiento automático de correos cada 15 minutos (ALTA PRIORIDAD)
        $schedule->command('emails:auto-process')
            ->everyFifteenMinutes()
            ->description('Procesar correos automáticamente cada 15 minutos')
            ->runInBackground();

        // Procesamiento completo cada hora
        $schedule->command('emails:auto-process --force')
            ->hourly()
            ->description('Procesamiento completo cada hora')
            ->runInBackground();

        // Resumen diario de correos
        $schedule->command('emails:sync')
            ->dailyAt('08:00')
            ->description('Resumen diario de correos')
            ->runInBackground();

        // Limpieza de logs antiguos
        $schedule->command('model:prune', [
            '--model' => 'EmailLog',
            '--hours' => '720', // 30 días
        ])->daily()
          ->description('Limpiar logs de correos antiguos');

        // Notificaciones WhatsApp — 08:00 AM Colombia (13:00 UTC)
        // withoutOverlapping() evita doble ejecucion si el comando tarda mas de 1 minuto
        $schedule->command('whatsapp:notificar')
            ->dailyAt('13:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/whatsapp-scheduler.log'))
            ->description('Enviar alertas de casos por WhatsApp');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
