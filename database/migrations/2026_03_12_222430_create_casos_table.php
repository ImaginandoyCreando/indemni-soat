<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_caso')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('cedula', 30)->index();
            $table->string('telefono', 30)->nullable();
            $table->string('correo')->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->date('fecha_accidente')->nullable();
            $table->string('aseguradora', 150)->index();
            $table->string('junta_asignada', 150)->nullable();
            $table->string('estado', 100)->default('Nuevo');
            $table->date('fecha_solicitud_aseguradora')->nullable();
            $table->date('fecha_respuesta_aseguradora')->nullable();
            $table->date('fecha_apelacion')->nullable();
            $table->date('fecha_tutela')->nullable();
            $table->date('fecha_pago_honorarios')->nullable();
            $table->date('fecha_envio_junta')->nullable();
            $table->date('fecha_dictamen_junta')->nullable();
            $table->date('fecha_reclamacion_final')->nullable();
            $table->date('fecha_pago_final')->nullable();
            $table->decimal('porcentaje_pcl', 5, 2)->nullable();
            $table->decimal('valor_reclamado', 15, 2)->nullable();
            $table->decimal('valor_pagado', 15, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casos');
    }
};