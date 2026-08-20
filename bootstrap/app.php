<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── Confiar en todos los proxies de Koyeb (SSL termination) ──
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Procesamiento automático de correos cada 15 minutos.
        $schedule->command('emails:auto-process')
            ->everyFifteenMinutes()
            ->description('Procesar correos automáticamente cada 15 minutos')
            ->runInBackground();

        // Procesamiento completo cada hora.
        $schedule->command('emails:auto-process --force')
            ->hourly()
            ->description('Procesamiento completo cada hora')
            ->runInBackground();

        // Resumen diario de correos y alertas a las 08:00 de Colombia.
        $schedule->command('emails:sync')
            ->dailyAt('08:00')
            ->timezone('America/Bogota')
            ->description('Resumen diario de correos')
            ->runInBackground();

        $schedule->command('notificaciones:resumen-diario')
            ->dailyAt('08:00')
            ->timezone('America/Bogota')
            ->description('Resumen diario de alertas')
            ->runInBackground();

        // Limpieza de logs antiguos.
        $schedule->command('model:prune', [
            '--model' => 'EmailLog',
            '--hours' => '720', // 30 días
        ])->daily()
          ->timezone('America/Bogota')
          ->description('Limpiar logs de correos antiguos');

        // Alertas automáticas por WhatsApp a las 08:00 de Colombia.
        // La zona explícita evita depender de la timezone del contenedor de Koyeb.
        // withoutOverlapping() evita doble ejecución si el comando tarda más de 1 minuto.
        $schedule->command('whatsapp:notificar')
            ->dailyAt('08:00')
            ->timezone('America/Bogota')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/whatsapp-scheduler.log'))
            ->description('Enviar alertas de casos por WhatsApp');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
