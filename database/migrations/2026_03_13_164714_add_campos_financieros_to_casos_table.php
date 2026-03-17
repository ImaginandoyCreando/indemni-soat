<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            $table->decimal('porcentaje_honorarios', 5, 2)->nullable()->after('valor_pagado');
            $table->decimal('ganancia_equipo', 15, 2)->nullable()->after('porcentaje_honorarios');
            $table->decimal('valor_neto_cliente', 15, 2)->nullable()->after('ganancia_equipo');
        });
    }

    public function down(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            $table->dropColumn([
                'porcentaje_honorarios',
                'ganancia_equipo',
                'valor_neto_cliente',
            ]);
        });
    }
};