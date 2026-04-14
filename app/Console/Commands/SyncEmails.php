<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEmailsJob;
use Illuminate\Console\Command;

class SyncEmails extends Command
{
    protected $signature = 'emails:sync';
    protected $description = 'Sincronizar correos electrónicos y actualizar casos automáticamente';

    public function handle()
    {
        $this->info('Iniciando sincronización de correos...');
        
        // Despachar job para procesar correos
        ProcessEmailsJob::dispatch();
        
        $this->info('Sincronización de correos iniciada correctamente.');
        return Command::SUCCESS;
    }
}
