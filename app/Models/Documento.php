<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'caso_id',
        'tipo_documento',
        'archivo',
    ];

    public function caso()
    {
        return $this->belongsTo(Caso::class, 'caso_id');
    }
}