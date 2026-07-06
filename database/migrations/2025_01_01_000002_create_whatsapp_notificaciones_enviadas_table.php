<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_notificaciones_enviadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caso_id');
            $table->string('alerta_codigo', 80);
            $table->string('numero_whatsapp', 30);
            $table->timestamp('enviado_en')->useCurrent();

            // Un mismo numero no recibe la misma alerta del mismo caso dos veces seguidas
            $table->unique(['caso_id', 'alerta_codigo', 'numero_whatsapp'], 'wa_notif_unica');

            $table->foreign('caso_id')->references('id')->on('casos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notificaciones_enviadas');
    }
};
