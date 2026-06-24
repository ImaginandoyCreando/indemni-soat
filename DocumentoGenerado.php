<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoGenerado extends Model
{
    use HasFactory;

    protected $table = 'documentos_generados';

    protected $fillable = [
        'caso_id',
        'plantilla_id',
        'tipo',
        'nombre_archivo',
        'archivo',
        'valores_usados',
        'user_id',
    ];

    protected $casts = [
        'valores_usados' => 'array',
    ];

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }

    public function plantilla()
    {
        return $this->belongsTo(PlantillaDocumento::class, 'plantilla_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNombreTipoAttribute(): string
    {
        return PlantillaDocumento::$tiposDisponibles[$this->tipo] ?? $this->tipo;
    }
}
