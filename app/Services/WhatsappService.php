<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    private string $instanceId;
    private string $token;
    private string $baseUrl;

    public function __construct()
    {
        $this->instanceId = config('whatsapp.instance_id', '');
        $this->token      = config('whatsapp.token', '');
        $this->baseUrl    = rtrim((string) config('whatsapp.base_url', 'https://api.ultramsg.com'), '/');
    }

    /**
     * Envía un mensaje de WhatsApp a un número.
     *
     * @param  string $numero  Número con código de país. Ej: 573001234567
     * @param  string $mensaje Texto del mensaje
     */
    public function enviar(string $numero, string $mensaje): bool
    {
        if (empty($this->instanceId) || empty($this->token)) {
            Log::warning('WhatsApp no configurado: faltan WHATSAPP_INSTANCE_ID o WHATSAPP_TOKEN en .env');
            return false;
        }

        $numeroDest = $this->normalizarNumero($numero);

        if ($numeroDest === null) {
            Log::warning('WhatsApp: número de destino inválido', [
                'numero_original' => $numero,
            ]);
            return false;
        }

        try {
            $response = Http::timeout(15)
                ->connectTimeout(10)
                ->acceptJson()
                ->asForm()
                ->post("{$this->baseUrl}/{$this->instanceId}/messages/chat", [
                    'token' => $this->token,
                    'to'    => $numeroDest,
                    'body'  => $mensaje,
                ]);

            $body = $response->json();
            $sent = is_array($body) ? ($body['sent'] ?? null) : null;
            $accepted = in_array($sent, [true, 'true', 1, '1'], true);

            if ($response->successful() && $accepted) {
                return true;
            }

            Log::error('WhatsApp: respuesta inesperada de UltraMsg', [
                'numero'   => $numeroDest,
                'status'   => $response->status(),
                'response' => $body ?? $response->body(),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('WhatsApp: excepción al enviar mensaje', [
                'numero' => $numeroDest,
                'error'  => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Convierte un número local colombiano o internacional a formato E.164.
     * UltraMsg documenta el destino con prefijo internacional y signo +.
     */
    private function normalizarNumero(string $numero): ?string
    {
        $digitos = preg_replace('/\\D+/', '', trim($numero));

        if (!is_string($digitos) || $digitos === '') {
            return null;
        }

        // Los contactos de la aplicación se registran normalmente como celulares colombianos.
        if (strlen($digitos) === 10) {
            $digitos = '57' . $digitos;
        }

        if (strlen($digitos) < 11 || strlen($digitos) > 15) {
            return null;
        }

        return '+' . $digitos;
    }

    /**
     * Construye el texto de notificación para un caso y su alerta.
     *
     * Cubre todo el flujo jurídico SOAT:
     *   Solicitud → Sin respuesta → Tutela → Fallo → Cumplimiento/Desacato
     *   → Impugnación → Segunda instancia → Junta → Cobro → Pago
     */
    public function construirMensaje(\App\Models\Caso $caso, string $alertaCodigo): string
    {
        $aseg = $caso->aseguradora ?? 'la aseguradora';

        $textos = [

            // ── URGENCIA EXTREMA ──────────────────────────────────────────────

            'impugnacion_urgente' =>
                "🚨 *URGENTE — IMPUGNACIÓN*\n" .
                "El fallo de tutela fue *NEGADO*. La ley otorga solo *3 días hábiles* para presentar la impugnación ante el despacho judicial.\n" .
                "⏰ Actuar de inmediato.",

            // ── MUY URGENTE ───────────────────────────────────────────────────

            'presentar_tutela' =>
                "🔴 *PRESENTAR TUTELA YA*\n" .
                "{$aseg} *negó o no respondió* la solicitud de calificación en el plazo legal de 30 días hábiles.\n" .
                "Proceder a presentar tutela para calificación.",

            'prescripcion_critica' =>
                "🚨 *PRESCRIPCIÓN PRÓXIMA*\n" .
                "El caso prescribe en *menos de 90 días*. El negocio tiene un plazo máximo de 18 meses desde el accidente.\n" .
                "Actuar con urgencia para no perder el derecho del cliente.",

            'prescrito' =>
                "🔴 *CASO PRESCRITO*\n" .
                "El caso ya superó la fecha de prescripción (18 meses). Revisar opciones legales disponibles.",

            'segunda_instancia_calificar' =>
                "🔴 *ACCIÓN URGENTE — 2ª INSTANCIA*\n" .
                "La segunda instancia *revocó* y ordenó a {$aseg} calificar al cliente.\n" .
                "Exigir calificación inmediata. Si no cumple, proceder con desacato.",

            'segunda_instancia_honorarios' =>
                "🔴 *ACCIÓN URGENTE — 2ª INSTANCIA*\n" .
                "La segunda instancia *revocó* y ordenó a {$aseg} pagar los honorarios de junta.\n" .
                "Exigir pago inmediato. Si no cumple, proceder con desacato.",

            'desacato_posible' =>
                "⚠️ *PRESENTAR DESACATO*\n" .
                "El fallo de tutela fue *CONCEDIDO* y ya han pasado más de *14 días* sin que {$aseg} cumpla.\n" .
                "Presentar incidente de desacato ante el despacho judicial.",

            // ── URGENTE ───────────────────────────────────────────────────────

            'sin_respuesta' =>
                "⚠️ *SIN RESPUESTA ASEGURADORA*\n" .
                "Han pasado más de *30 días hábiles* desde que se elevó la solicitud de calificación a {$aseg}.\n" .
                "Si no hay respuesta, proceder a presentar tutela para calificación.",

            // alias que puede devolver el modelo del sistema
            'tutela' =>
                "⚠️ *SIN RESPUESTA ASEGURADORA*\n" .
                "Han pasado más de *30 días hábiles* desde que se elevó la solicitud de calificación a {$aseg}.\n" .
                "Si no hay respuesta, proceder a presentar tutela para calificación.",

            'seguimiento_tutela' =>
                "⚠️ *SEGUIMIENTO TUTELA*\n" .
                "La tutela presentada lleva más de *30 días* sin fallo del despacho judicial.\n" .
                "Verificar estado y hacer seguimiento al juez de conocimiento.",

            'fallo_tutela_pendiente' =>
                "⚠️ *ESPERANDO FALLO DE TUTELA*\n" .
                "La tutela presentada lleva más de *30 días* sin fallo.\n" .
                "Verificar en el despacho judicial y hacer seguimiento activo.",

            'desacato_seguimiento' =>
                "⚠️ *SEGUIMIENTO INCIDENTE DE DESACATO*\n" .
                "El incidente de desacato fue presentado. {$aseg} debe cumplir el fallo.\n" .
                "Verificar si ya hay resolución del despacho judicial.",

            'desacato' =>
                "⚠️ *SEGUIMIENTO DESACATO*\n" .
                "Hay un incidente de desacato activo contra {$aseg}.\n" .
                "Verificar avance y respuesta del despacho judicial.",

            'impugnacion_presentada' =>
                "⚠️ *SEGUIMIENTO IMPUGNACIÓN*\n" .
                "La impugnación fue presentada. Esperando fallo de segunda instancia.\n" .
                "Hacer seguimiento periódico en el despacho de segunda instancia.",

            'impugnacion' =>
                "⚠️ *IMPUGNACIÓN PENDIENTE*\n" .
                "El fallo de tutela fue negado. Pendiente presentar impugnación.\n" .
                "Verificar plazo legal (3 días hábiles).",

            'segunda_instancia' =>
                "⚠️ *SEGUNDA INSTANCIA*\n" .
                "Impugnación presentada. Esperando fallo de segunda instancia.\n" .
                "Hacer seguimiento en el despacho de segunda instancia.",

            'pago_final_pendiente' =>
                "⚠️ *SEGUIMIENTO PAGO FINAL*\n" .
                "El cobro fue enviado a {$aseg} y han pasado más de *30 días* sin pago.\n" .
                "Si no hay respuesta, proceder con queja ante Superintendencia Financiera.",

            'queja' =>
                "🔴 *QUEJA POR NO PAGO*\n" .
                "Han pasado más de 30 días desde la reclamación final sin que {$aseg} pague.\n" .
                "Proceder con queja ante la Superintendencia Financiera de Colombia.",

            'dictamen_aseguradora_pendiente' =>
                "⚠️ *PENDIENTE DICTAMEN ASEGURADORA*\n" .
                "La tutela fue cumplida pero {$aseg} aún no emite dictamen de calificación.\n" .
                "Hacer seguimiento para que califiquen al cliente.",

            'pago_honorarios_junta' =>
                "⚠️ *PENDIENTE PAGO HONORARIOS JUNTA*\n" .
                "La tutela fue cumplida y el cliente está pendiente de que {$aseg} pague los honorarios de la junta médica.\n" .
                "Exigir el pago de honorarios para iniciar el proceso ante la junta.",

            'cumplimiento_tutela' =>
                "⚖️ *CUMPLIMIENTO TUTELA*\n" .
                "El fallo de tutela fue *CONCEDIDO*. {$aseg} tiene *14 días* para calificar al cliente.\n" .
                "Si no cumple en el plazo, presentar incidente de desacato.",

            'cumplimiento_segunda_instancia' =>
                "⚖️ *CUMPLIMIENTO 2ª INSTANCIA*\n" .
                "La segunda instancia revocó el fallo. {$aseg} debe cumplir la orden judicial.\n" .
                "Hacer seguimiento al cumplimiento. Si no cumple, proceder con desacato.",

            // ── PENDIENTES (una sola notificación) ────────────────────────────

            'documentacion_inicial' =>
                "📋 *FALTA DOCUMENTACIÓN*\n" .
                "El caso no tiene poder y/o contrato firmado por el cliente.\n" .
                "Gestionar firma antes de continuar con el proceso.",

            'poder_pendiente' =>
                "📋 *PODER PENDIENTE DE FIRMA*\n" .
                "El poder fue entregado pero el cliente aún no lo firma.\n" .
                "Contactar al cliente para obtener la firma.",

            'contrato_pendiente' =>
                "📋 *CONTRATO PENDIENTE DE FIRMA*\n" .
                "El contrato fue entregado pero el cliente aún no lo firma.\n" .
                "Contactar al cliente para obtener la firma.",

            'apelacion_dictamen_pendiente' =>
                "⚠️ *APELACIÓN DE DICTAMEN PRESENTADA*\n" .
                "Se presentó apelación del dictamen de {$aseg} ante la junta médica.\n" .
                "Verificar si ya se fijaron los honorarios de junta para pagar.",

            'apelar_dictamen' =>
                "⚠️ *APELAR DICTAMEN*\n" .
                "{$aseg} emitió dictamen. Pendiente presentar apelación ante la junta médica.\n" .
                "Gestionar el pago de honorarios de junta para radicar la apelación.",

            'honorarios_junta' =>
                "💰 *HONORARIOS JUNTA PENDIENTES*\n" .
                "Hay apelación del dictamen registrada pero aún no se pagan los honorarios de junta.\n" .
                "Pagar honorarios para que la junta médica califique al cliente.",

            'alta_ortopedia_pendiente' =>
                "🏥 *ALTA ORTOPEDIA PENDIENTE*\n" .
                "Los honorarios de junta fueron pagados. Pendiente que ortopedia genere el alta médica.\n" .
                "Hacer seguimiento con el médico tratante para el alta.",

            'solicitud_junta_urgente' =>
                "📝 *ENVIAR SOLICITUD A JUNTA*\n" .
                "El caso está listo con el alta de ortopedia. Pendiente enviar la solicitud formal a la junta médica.\n" .
                "Radicar solicitud a la junta de calificación de invalidez.",

            'solicitud_junta' =>
                "📝 *SOLICITUD A JUNTA*\n" .
                "Pendiente enviar solicitud a la junta médica de calificación.\n" .
                "Radicar la documentación completa ante la junta.",

            'dictamen_junta_recibido' =>
                "💼 *DICTAMEN JUNTA RECIBIDO — PROCEDER CON COBRO*\n" .
                "Se recibió el dictamen de la junta médica. El caso está listo para la reclamación final.\n" .
                "Preparar y enviar el cobro a {$aseg} con toda la documentación.",

            'cobro_listo' =>
                "💼 *LISTO PARA COBRAR*\n" .
                "El caso tiene dictamen de junta y documentación completa. Listo para enviar reclamación final a {$aseg}.\n" .
                "Radicar el cobro formal ante la aseguradora.",

            'reclamacion' =>
                "💼 *LISTO PARA COBRAR*\n" .
                "El caso tiene dictamen de junta y FURPEN completo.\n" .
                "Proceder con la reclamación final ante {$aseg}.",

            'pago_pendiente' =>
                "💰 *PAGO PENDIENTE*\n" .
                "La reclamación final fue enviada a {$aseg}. Tienen *30 días* para pagar.\n" .
                "Hacer seguimiento. Si no pagan a tiempo, iniciar proceso de queja.",
        ];

        $textoPredeterminado =
            "📌 *ALERTA JURÍDICA*\n" .
            "El caso requiere atención. Estado actual: {$caso->estado}\n" .
            "Revisar y gestionar con el abogado asignado.";

        $cuerpo = $textos[$alertaCodigo] ?? $textoPredeterminado;

        return implode("\n", [
            "━━━━━━━━━━━━━━━━━━━━━",
            "🗂️ *INDEMNI-SOAT*",
            "━━━━━━━━━━━━━━━━━━━━━",
            "📁 Caso: *{$caso->numero_caso}*",
            "👤 Cliente: *{$caso->nombre_completo}*",
            "🏢 Aseguradora: *{$aseg}*",
            "",
            $cuerpo,
            "",
            "📅 " . now()->format('d/m/Y H:i'),
            "━━━━━━━━━━━━━━━━━━━━━",
        ]);
    }
}
