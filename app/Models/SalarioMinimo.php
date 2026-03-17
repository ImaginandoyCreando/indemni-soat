<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalarioMinimo extends Model
{
    protected $table = 'salarios_minimos';

    protected $fillable = [
        'anio',
        'smmlv',
        'smldv',
    ];
}