<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            if (!Schema::hasColumn('casos', 'fecha_envio_solicitud')) {
                $table->timestamp('fecha_envio_solicitud')->nullable();
            }
            if (!Schema::hasColumn('casos', 'fecha_respuesta_aseguradora')) {
                $table->timestamp('fecha_respuesta_aseguradora')->nullable();
            }
            if (!Schema::hasColumn('casos', 'auto_created')) {
                $table->boolean('auto_created')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            $table->dropColumn(['fecha_envio_solicitud', 'fecha_respuesta_aseguradora', 'auto_created']);
        });
    }
};