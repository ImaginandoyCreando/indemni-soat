<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\User;
use App\Mail\AlertaFlujoMail;
use App\Mail\ResumenDiarioMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificacionService
{
    /**
     * Destinatarios: todos los admin y abogados.
     */
    public static function destinatarios(): \Illuminate\Support\Collection
    {
        return User::whereIn('role', ['admin', 'abogado'])->get();
    }

    /**
     * Envío inmediato cuando ocurre una acción del flujo jurídico.
     *
     * @param  Caso    $caso
     * @param  string  $evento     Texto del evento (ej: "Tutela registrada")
     * @param  string  $detalle    Descripción adicional
     * @param  string  $nivel      'critico' | 'urgente' | 'info'
     */
    public static function enviarAlertaFlujo(
        Caso $caso,
        string $evento,
        string $detalle = '',
        string $nivel = 'info'
    ): void {
        try {
            $destinatarios = self::destinatarios();

            if ($destinatarios->isEmpty()) {
                return;
            }

            foreach ($destinatarios as $usuario) {
                Mail::to($usuario->email)
                    ->queue(new AlertaFlujoMail($caso, $evento, $detalle, $nivel, $usuario));
            }
        } catch (\Throwable $e) {
            // No detener el flujo principal si el correo falla
            Log::error('NotificacionService::enviarAlertaFlujo — ' . $e->getMessage(), [
                'caso_id' => $caso->id,
                'evento'  => $evento,
            ]);
        }
    }

    /**
     * Envío del resumen diario (llamado desde el comando programado).
     */
    public static function enviarResumenDiario(): void
    {
        try {
            $casos = Caso::orderByDesc('id')->get();

            // Solo enviar si hay algo que reportar
            $hayAlertas = $casos->filter(fn ($c) => !$c->estaPagado() && $c->color_alerta !== 'green')->count();

            if ($hayAlertas === 0) {
                return;
            }

            $destinatarios = self::destinatarios();

            foreach ($destinatarios as $usuario) {
                Mail::to($usuario->email)
                    ->queue(new ResumenDiarioMail($casos, $usuario));
            }
        } catch (\Throwable $e) {
            Log::error('NotificacionService::enviarResumenDiario — ' . $e->getMessage());
        }
    }
}