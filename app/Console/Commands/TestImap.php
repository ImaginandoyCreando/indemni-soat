<?php

namespace App\Console\Commands;

use App\Services\ImapEmailService;
use Illuminate\Console\Command;

class TestImap extends Command
{
    protected $signature = 'emails:test-imap';
    protected $description = 'Probar conexión IMAP con Outlook';

    public function handle()
    {
        $this->info('Probando conexión IMAP con Outlook...');
        
        // Verificar credenciales
        if (!env('OUTLOOK_EMAIL') || !env('OUTLOOK_PASSWORD')) {
            $this->error('Faltan credenciales en .env');
            $this->info('Agrega estas líneas a tu .env:');
            $this->line('OUTLOOK_EMAIL=tu_correo@outlook.com');
            $this->line('OUTLOOK_PASSWORD=tu_contraseña');
            return Command::FAILURE;
        }

        try {
            $service = new ImapEmailService();
            $result = $service->testConnection();
            
            if ($result['success']) {
                $this->info('¡Conexión exitosa!');
                $this->line('Total de correos: ' . $result['total_emails']);
                
                // Obtener correos recientes
                $emails = $service->getRecentEmails(5);
                
                $this->info('Correos recientes:');
                foreach ($emails as $email) {
                    $this->line('- De: ' . $email['from_email']);
                    $this->line('  Asunto: ' . substr($email['subject'], 0, 50) . '...');
                    $this->line('  Fecha: ' . $email['date']->format('d/m/Y H:i'));
                    $this->line('');
                }
                
                return Command::SUCCESS;
            } else {
                $this->error('Error de conexión: ' . $result['message']);
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->info('Soluciones comunes:');
            $this->line('1. Verifica que tu correo sea @outlook.com o @hotmail.com');
            $this->line('2. Activa "Acceso de apps menos seguras" en tu cuenta Microsoft');
            $this->line('3. Usa una contraseña de aplicación si tienes 2FA');
            return Command::FAILURE;
        }
    }
}
