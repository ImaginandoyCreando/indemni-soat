<?php

return [
    /*
    |--------------------------------------------------------------------------
    | UltraMsg — WhatsApp API
    |--------------------------------------------------------------------------
    | Registrate en https://ultramsg.com, crea una instancia, conecta tu
    | WhatsApp Business y copia el Instance ID y el Token aqui.
    |
    | Variables de entorno requeridas en Koyeb → Secrets:
    |   WHATSAPP_INSTANCE_ID=tu_instance_id
    |   WHATSAPP_TOKEN=tu_token_secreto
    */

    'instance_id' => env('WHATSAPP_INSTANCE_ID', ''),
    'token'       => env('WHATSAPP_TOKEN', ''),
    'base_url'    => env('WHATSAPP_BASE_URL', 'https://api.ultramsg.com'),

    /*
    | Cuantos dias esperar antes de re-notificar la misma alerta al mismo numero.
    | critica  → cada 7 dias  (prescripcion, desacato)
    | urgente  → cada 3 dias  (sin_respuesta, seguimiento_tutela, queja)
    | normal   → no se repite (se envia una sola vez)
    */
    'reenvio_dias' => [
        'critica'  => 7,
        'urgente'  => 3,
        'normal'   => null, // null = nunca reenviar
    ],
];
