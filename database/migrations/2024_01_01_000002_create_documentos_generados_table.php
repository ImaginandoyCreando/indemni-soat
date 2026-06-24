<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_generados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caso_id');
            $table->unsignedBigInteger('plantilla_id')->nullable();
            $table->string('tipo');
            $table->string('nombre_archivo');
            $table->string('archivo');
            $table->json('valores_usados')->nullable()
                  ->comment('Los valores del formulario con que se generó');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('caso_id')->references('id')->on('casos')->cascadeOnDelete();
            $table->foreign('plantilla_id')->references('id')->on('plantillas_documento')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_generados');
    }
};
