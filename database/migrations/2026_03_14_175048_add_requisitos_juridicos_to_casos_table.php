<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casos', function (Blueprint $table) {

            // PODER Y CONTRATO
            $table->boolean('tiene_poder')->default(false);
            $table->date('fecha_entrega_poder')->nullable();
            $table->date('fecha_poder_firmado')->nullable();

            $table->boolean('tiene_contrato')->default(false);
            $table->date('fecha_entrega_contrato')->nullable();
            $table->date('fecha_contrato_firmado')->nullable();

            // ALTA POR ORTOPEDIA
            $table->boolean('alta_ortopedia')->default(false);
            $table->date('fecha_alta_ortopedia')->nullable();
            $table->text('observacion_alta_ortopedia')->nullable();

            // FURPEN
            $table->boolean('furpen_completo')->default(false);
            $table->date('fecha_furpen_recibido')->nullable();
            $table->text('observacion_furpen')->nullable();

            // CONTROL JUDICIAL
            $table->date('fecha_fallo_tutela')->nullable();
            $table->string('resultado_fallo_tutela')->nullable(); 
            $table->date('fecha_incidente_desacato')->nullable();
            $table->date('fecha_impugnacion')->nullable();

            // PRESCRIPCION
            $table->date('fecha_prescripcion')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('casos', function (Blueprint $table) {

            $table->dropColumn([
                'tiene_poder',
                'fecha_entrega_poder',
                'fecha_poder_firmado',

                'tiene_contrato',
                'fecha_entrega_contrato',
                'fecha_contrato_firmado',

                'alta_ortopedia',
                'fecha_alta_ortopedia',
                'observacion_alta_ortopedia',

                'furpen_completo',
                'fecha_furpen_recibido',
                'observacion_furpen',

                'fecha_fallo_tutela',
                'resultado_fallo_tutela',
                'fecha_incidente_desacato',
                'fecha_impugnacion',

                'fecha_prescripcion'
            ]);

        });
    }
};