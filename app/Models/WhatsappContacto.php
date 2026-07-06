<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappContacto extends Model
{
    protected $table = 'whatsapp_contactos';

    protected $fillable = [
        'nombre',
        'numero',
        'rol',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Devuelve el numero limpio: solo digitos.
     * Si empieza con 57 (Colombia) lo deja igual; si no, lo agrega.
     */
    public function getNumeroLimpioAttribute(): string
    {
        $solo = preg_replace('/\D/', '', $this->numero);

        if (str_starts_with($solo, '57') && strlen($solo) === 12) {
            return $solo;
        }

        // Si tiene 10 digitos (numero local colombiano) agrega prefijo 57
        if (strlen($solo) === 10) {
            return '57' . $solo;
        }

        return $solo;
    }
}
