<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantillas_documento', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->comment(
                'solicitud_calificacion_aseguradora | tutela | desacato | ' .
                'impugnacion | furpen | solicitud_calificacion_junta | inconformidad_dictamen'
            );
            $table->string('nombre');
            $table->string('archivo');
            $table->string('extension')->comment('docx | pdf | xlsx');
            $table->json('variables_detectadas')->nullable()
                  ->comment('Array de nombres de variable encontradas en la plantilla');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_documento');
    }
};
