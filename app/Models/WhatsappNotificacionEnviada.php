<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappNotificacionEnviada extends Model
{
    protected $table = 'whatsapp_notificaciones_enviadas';

    // La tabla no tiene created_at / updated_at; usa enviado_en como fecha de envío.
    public $timestamps = false;

    protected $fillable = [
        'caso_id',
        'alerta_codigo',
        'numero_whatsapp',
        'enviado_en',
    ];

    protected $casts = [
        'enviado_en' => 'datetime',
    ];

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }
}
