<?php

namespace App\Mail;

use App\Models\Caso;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertaFlujoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Caso   $caso,
        public string $evento,
        public string $detalle,
        public string $nivel,
        public User   $destinatario,
    ) {}

    public function envelope(): Envelope
    {
        $emoji = match($this->nivel) {
            'critico' => '🔴',
            'urgente' => '🟠',
            default   => '🔵',
        };

        return new Envelope(
            subject: "{$emoji} [{$this->caso->numero_caso}] {$this->evento} — INDEMNI SOAT",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerta_flujo',
        );
    }
}