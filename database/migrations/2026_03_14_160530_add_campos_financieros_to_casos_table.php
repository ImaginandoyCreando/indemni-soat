<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            $table->text('observacion_pago')->nullable();
            $table->text('observacion_reclamacion')->nullable();
            $table->string('direccion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            $table->dropColumn([
                'observacion_pago',
                'observacion_reclamacion',
                'direccion',
            ]);
        });
    }
};