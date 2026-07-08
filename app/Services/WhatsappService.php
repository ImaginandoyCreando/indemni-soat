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
        $this->baseUrl    = 'https://api.ultramsg.com';
    }

    /**
     * Envía un mensaje de WhatsApp a un número.
     *
     * @param  string $numero  Número con código de país. Ej: 573001234567
     * @param  string $mensaje Texto del mensaje
     * @return bool
     */
    public function enviar(string $numero, string $mensaje): bool
    {
        if (empty($this->instanceId) || empty($this->token)) {
            Log::warning('WhatsApp no configurado: faltan WHATSAPP_INSTANCE_ID o WHATSAPP_TOKEN en .env');
            return false;
        }

        // UltraMsg requiere el número con el signo + delante
        $numeroDest = '+' . ltrim(preg_replace('/[^0-9]/', '', $numero), '+');

        try {
            $response = Http::timeout(15)
                ->asForm()
                ->post("{$this->baseUrl}/{$this->instanceId}/messages/chat", [
                    'token' => $this->token,
                    'to'    => $numeroDest,
                    'body'  => $mensaje,
                ]);

            $body = $response->json();

            if ($response->successful() && isset($body['sent']) && $body['sent'] === 'true') {
                return true;
            }

            Log::error('WhatsApp: respuesta inesperada de UltraMsg', [
                'numero'   => $numeroDest,
                'status'   => $response->status(),
                'response' => $body,
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('WhatsApp: excepción al enviar mensaje', [
                'numero'  => $numeroDest,
                'error'   => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Construye el texto de notificación para un caso y su alerta.
     */
    public function construirMensaje(\App\Models\Caso $caso, string $alertaCodigo): string
    {
        $textos = [
            'sin_respuesta'                  => "⚠️ *SIN RESPUESTA ASEGURADORA*\nHan pasado más de 30 días hábiles desde la solicitud de calificación a {$caso->aseguradora}. Considerar acciones legales.",
            'tutela'                         => "⚠️ *SEGUIMIENTO TUTELA*\nLa tutela lleva más de 30 días sin fallo. Verificar estado en el despacho judicial.",
            'seguimiento_tutela'             => "⚠️ *SEGUIMIENTO TUTELA*\nLa tutela lleva más de 30 días sin fallo. Verificar estado en el despacho judicial.",
            'queja'                          => "🔴 *QUEJA POR NO PAGO*\nHan pasado más de 30 días desde la reclamación final sin pago. Proceder con queja ante Superintendencia.",
            'desacato'                       => "🔴 *INCIDENTE DE DESACATO*\nEl fallo de tutela fue concedido hace más de 14 días y la aseguradora no ha cumplido. Presentar incidente de desacato.",
            'prescripcion_critica'           => "🚨 *PRESCRIPCIÓN PRÓXIMA*\nEl caso prescribe en menos de 90 días. Actuar con urgencia.",
            'prescrito'                      => "🔴 *CASO PRESCRITO*\nEl caso ya superó su fecha de prescripción.",
            'documentacion_inicial'          => "📋 *FALTA DOCUMENTACIÓN*\nEl caso no tiene poder y/o contrato firmado.",
            'poder_pendiente'                => "📋 *PODER PENDIENTE DE FIRMA*\nSe entregó poder pero aún no se registra firma del cliente.",
            'contrato_pendiente'             => "📋 *CONTRATO PENDIENTE DE FIRMA*\nSe entregó contrato pero aún no se registra firma del cliente.",
            'apelar_dictamen'                => "⚠️ *APELAR DICTAMEN*\nLa aseguradora emitió dictamen. Pendiente presentar apelación.",
            'impugnacion'                    => "⚠️ *IMPUGNACIÓN PENDIENTE*\nFallo de tutela negado. Pendiente presentar impugnación.",
            'honorarios_junta'               => "💰 *HONORARIOS JUNTA PENDIENTES*\nHay apelación registrada y aún no se pagan honorarios de junta.",
            'alta_ortopedia_pendiente'       => "🏥 *ALTA ORTOPEDIA PENDIENTE*\nPagados honorarios de junta. Pendiente alta de ortopedia.",
            'solicitud_junta'                => "📝 *SOLICITUD JUNTA*\nCliente con alta ortopedia. Pendiente enviar solicitud a junta.",
            'reclamacion'                    => "💼 *LISTO PARA COBRAR*\nDictamen de junta recibido y FURPEN completo. Proceder con reclamación final.",
            'pago_pendiente'                 => "💰 *PAGO PENDIENTE*\nReclamación final enviada. Esperando pago de la aseguradora.",
            'cumplimiento_tutela'            => "⚖️ *CUMPLIMIENTO TUTELA*\nFallo concedido. La aseguradora tiene 14 días para cumplir.",
            'cumplimiento_segunda_instancia' => "⚖️ *CUMPLIMIENTO 2a INSTANCIA*\nSegunda instancia revoca. Pendiente cumplimiento de la aseguradora.",
            'segunda_instancia'              => "⚖️ *SEGUNDA INSTANCIA*\nImpugnación presentada. Esperando fallo de segunda instancia.",
        ];

        $textoPredeterminado = "📌 *ALERTA JURÍDICA*\nEl caso requiere atención en el estado: {$alertaCodigo}.";
        $cuerpo = $textos[$alertaCodigo] ?? $textoPredeterminado;

        return implode("\n", [
            "━━━━━━━━━━━━━━━━━━━━━",
            "🗂️ *INDEMNI-SOAT*",
            "━━━━━━━━━━━━━━━━━━━━━",
            "📁 Caso: *{$caso->numero_caso}*",
            "👤 Cliente: *{$caso->nombre_completo}*",
            "🏢 Aseguradora: *{$caso->aseguradora}*",
            "",
            $cuerpo,
            "",
            "📅 " . now()->format('d/m/Y H:i'),
            "━━━━━━━━━━━━━━━━━━━━━",
        ]);
    }
}
