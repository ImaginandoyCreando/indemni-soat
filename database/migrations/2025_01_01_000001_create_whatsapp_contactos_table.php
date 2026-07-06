<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_contactos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('numero', 30)->unique(); // guardado normalizado sin +, espacios ni guiones
            $table->string('rol', 80)->default('general'); // ej: admin, abogado, general
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_contactos');
    }
};
