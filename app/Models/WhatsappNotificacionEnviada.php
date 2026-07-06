<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappNotificacionEnviada extends Model
{
    protected $table = 'whatsapp_notificaciones_enviadas';

    // Sin created_at / updated_at; usamos enviado_en
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
