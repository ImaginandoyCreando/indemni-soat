<?php

namespace App\Services;

use App\Models\Caso;
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
        $this->baseUrl    = config('whatsapp.base_url', 'https://api.ultramsg.com');
    }

    // -------------------------------------------------------------------------
    // ENVIO HTTP
    // -------------------------------------------------------------------------

    /**
     * Envia un mensaje de texto a un numero de WhatsApp.
     *
     * @param  string $numero  Numero destino con codigo de pais (ej: 573001234567)
     * @param  string $mensaje Texto del mensaje
     * @return array{ok: bool, respuesta: mixed}
     */
    public function enviar(string $numero, string $mensaje): array
    {
        if (empty($this->instanceId) || empty($this->token)) {
            Log::warning('WhatsApp: WHATSAPP_INSTANCE_ID o WHATSAPP_TOKEN no configurados.');
            return ['ok' => false, 'respuesta' => 'Credenciales no configuradas'];
        }

        try {
            $url = "{$this->baseUrl}/{$this->instanceId}/messages/chat";

            $response = Http::timeout(15)->post($url, [
                'token'  => $this->token,
                'to'     => $numero,
                'body'   => $mensaje,
            ]);

            $datos = $response->json();
            $ok    = $response->successful() && isset($datos['sent']) && $datos['sent'] === 'true';

            if (!$ok) {
                Log::warning("WhatsApp: envio fallido a {$numero}", ['respuesta' => $datos]);
            }

            return ['ok' => $ok, 'respuesta' => $datos];

        } catch (\Throwable $e) {
            Log::error("WhatsApp: excepcion al enviar a {$numero} — {$e->getMessage()}");
            return ['ok' => false, 'respuesta' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // CONSTRUCCION DE MENSAJE
    // -------------------------------------------------------------------------

    /**
     * Construye el texto del mensaje segun la alerta del caso.
     */
    public function construirMensaje(Caso $caso, string $alertaCodigo): string
    {
        $nombre = $caso->nombre_completo;
        $num    = $caso->numero_caso ?? "ID {$caso->id}";

        $textos = [
            'prescripcion_critica' => "⚠️ *PRESCRIPCION CRITICA*\nCaso: {$num} — {$nombre}\nLa fecha de prescripcion esta dentro de los proximos 90 dias. Actuar de inmediato.",
            'prescrito'            => "🔴 *CASO PRESCRITO*\nCaso: {$num} — {$nombre}\nEl caso ha prescrito. Revisar urgentemente.",
            'desacato'             => "🚨 *DESACATO REQUERIDO*\nCaso: {$num} — {$nombre}\nHan pasado mas de 14 dias desde el fallo de tutela concedido sin cumplimiento. Interponer incidente de desacato.",
            'sin_respuesta'        => "⏰ *SIN RESPUESTA ASEGURADORA*\nCaso: {$num} — {$nombre}\nHan pasado mas de 30 dias sin respuesta de la aseguradora. Verificar estado.",
            'seguimiento_tutela'   => "📋 *SEGUIMIENTO TUTELA*\nCaso: {$num} — {$nombre}\nLa tutela lleva mas de 30 dias sin fallo. Hacer seguimiento.",
            'queja'                => "📢 *QUEJA POR NO PAGO*\nCaso: {$num} — {$nombre}\nHan pasado mas de 30 dias desde la reclamacion sin pago. Presentar queja.",
            'cumplimiento_tutela'  => "⚖️ *CUMPLIMIENTO DE TUTELA*\nCaso: {$num} — {$nombre}\nFallo concedido, esperando cumplimiento voluntario (primeros 14 dias).",
            'impugnacion'          => "📝 *IMPUGNACION PENDIENTE*\nCaso: {$num} — {$nombre}\nFallo negado/parcial. Impugnar dentro de los 3 dias habiles.",
            'segunda_instancia'    => "⏳ *SEGUNDA INSTANCIA*\nCaso: {$num} — {$nombre}\nEsperando fallo de segunda instancia.",
            'cumplimiento_segunda_instancia' => "🔄 *CUMPLIMIENTO 2A INSTANCIA*\nCaso: {$num} — {$nombre}\nFallo de segunda instancia revoca. Pendiente cumplimiento.",
            'documentacion_inicial'=> "📄 *DOCUMENTACION INCOMPLETA*\nCaso: {$num} — {$nombre}\nFalta poder o contrato firmado.",
            'poder_pendiente'      => "✍️ *PODER PENDIENTE*\nCaso: {$num} — {$nombre}\nPoder enviado, pendiente firma del cliente.",
            'contrato_pendiente'   => "✍️ *CONTRATO PENDIENTE*\nCaso: {$num} — {$nombre}\nContrato enviado, pendiente firma del cliente.",
            'apelar_dictamen'      => "⚖️ *APELAR DICTAMEN*\nCaso: {$num} — {$nombre}\nAseguradora emitio dictamen. Presentar apelacion.",
            'honorarios_junta'     => "💰 *HONORARIOS JUNTA*\nCaso: {$num} — {$nombre}\nPendiente pago de honorarios para la junta.",
            'alta_ortopedia_pendiente' => "🏥 *ALTA ORTOPEDIA PENDIENTE*\nCaso: {$num} — {$nombre}\nSe requiere el alta de ortopedia antes de enviar a junta.",
            'solicitud_junta'      => "📤 *SOLICITUD A JUNTA*\nCaso: {$num} — {$nombre}\nListo para enviar solicitud a la junta medica.",
            'furpen_pendiente'     => "📋 *FURPEN PENDIENTE*\nCaso: {$num} — {$nombre}\nDictamen recibido, pendiente completar FURPEN.",
            'reclamacion'          => "💼 *LISTA PARA COBRAR*\nCaso: {$num} — {$nombre}\nFURPEN completo. Listo para reclamar a la aseguradora.",
            'pago_pendiente'       => "💳 *PAGO PENDIENTE*\nCaso: {$num} — {$nombre}\nReclamacion enviada, esperando pago de la aseguradora.",
        ];

        return $textos[$alertaCodigo]
            ?? "📌 *ALERTA CASO*\nCaso: {$num} — {$nombre}\nAlerta: {$alertaCodigo}. Revisar en el sistema.";
    }

    // -------------------------------------------------------------------------
    // CLASIFICACION DE ALERTA
    // -------------------------------------------------------------------------

    /**
     * Retorna la prioridad de una alerta: 'critica', 'urgente' o 'normal'.
     */
    public function prioridadAlerta(string $alertaCodigo): string
    {
        $criticas = ['prescripcion_critica', 'prescrito', 'desacato'];
        $urgentes = ['sin_respuesta', 'seguimiento_tutela', 'queja', 'impugnacion', 'cumplimiento_tutela'];

        if (in_array($alertaCodigo, $criticas)) return 'critica';
        if (in_array($alertaCodigo, $urgentes)) return 'urgente';
        return 'normal';
    }

    /**
     * Dias de reenvio segun prioridad. null = no reenviar.
     */
    public function diasReenvio(string $prioridad): ?int
    {
        return config("whatsapp.reenvio_dias.{$prioridad}");
    }
}
