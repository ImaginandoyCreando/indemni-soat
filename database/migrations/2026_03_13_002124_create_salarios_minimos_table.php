<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salarios_minimos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('anio')->unique();
            $table->decimal('smmlv', 15, 2);
            $table->decimal('smldv', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salarios_minimos');
    }
};