<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SalarioMinimo extends Model
{
    protected $table = 'salarios_minimos';

    protected $fillable = [
        'anio',
        'smmlv',
        'smldv',
    ];

    protected static function booted(): void
    {
        $forget = fn ($s) => Cache::forget("salario_minimo_{$s->anio}");
        static::saved($forget);
        static::deleted($forget);
    }
}