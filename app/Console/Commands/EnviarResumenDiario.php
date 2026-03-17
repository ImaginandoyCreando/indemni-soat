<?php

namespace App\Console\Commands;

use App\Services\NotificacionService;
use Illuminate\Console\Command;

class EnviarResumenDiario extends Command
{
    protected $signature   = 'notificaciones:resumen-diario';
    protected $description = 'Envía el resumen diario de alertas a admin y abogados';

    public function handle(): void
    {
        $this->info('Enviando resumen diario...');
        NotificacionService::enviarResumenDiario();
        $this->info('✅ Resumen diario enviado.');
    }
}