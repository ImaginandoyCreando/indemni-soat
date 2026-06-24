<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaDocumento extends Model
{
    use HasFactory;

    protected $table = 'plantillas_documento';

    protected $fillable = [
        'tipo',
        'nombre',
        'archivo',
        'extension',
        'variables_detectadas',
        'user_id',
    ];

    protected $casts = [
        'variables_detectadas' => 'array',
    ];

    public static array $tiposDisponibles = [
        'solicitud_calificacion_aseguradora' => 'Solicitud de Calificación ante la Aseguradora',
        'tutela'                             => 'Tutela',
        'desacato'                           => 'Incidente de Desacato',
        'impugnacion'                        => 'Impugnación',
        'furpen'                             => 'FURPEN',
        'solicitud_calificacion_junta'       => 'Solicitud de Calificación ante la Junta Regional',
        'inconformidad_dictamen'             => 'Inconformidad de Dictamen',
    ];

    public function getNombreTipoAttribute(): string
    {
        return self::$tiposDisponibles[$this->tipo] ?? $this->tipo;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentosGenerados()
    {
        return $this->hasMany(DocumentoGenerado::class, 'plantilla_id');
    }
}
