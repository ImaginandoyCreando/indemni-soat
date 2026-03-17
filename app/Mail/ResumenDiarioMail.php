<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ResumenDiarioMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $casosCriticos;
    public Collection $casosUrgentes;
    public Collection $casosInfo;
    public int        $totalAlertas;

    public function __construct(
        Collection   $casos,
        public User  $destinatario,
    ) {
        // Separar por nivel de alerta
        $this->casosCriticos = $casos->filter(fn ($c) => $c->color_alerta === 'red'    && !$c->estaPagado());
        $this->casosUrgentes = $casos->filter(fn ($c) => $c->color_alerta === 'orange' && !$c->estaPagado());
        $this->casosInfo     = $casos->filter(fn ($c) => in_array($c->color_alerta, ['blue','cyan']) && !$c->estaPagado());
        $this->totalAlertas  = $this->casosCriticos->count() + $this->casosUrgentes->count() + $this->casosInfo->count();
    }

    public function envelope(): Envelope
    {
        $fecha = now()->format('d/m/Y');
        return new Envelope(
            subject: "📋 Resumen diario {$fecha} — {$this->totalAlertas} alerta(s) pendiente(s) | INDEMNI SOAT",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.resumen_diario',
        );
    }
}