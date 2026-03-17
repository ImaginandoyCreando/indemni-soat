<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            // Tipo de respuesta de la aseguradora: emitio_dictamen | nego | no_respondio
            if (!Schema::hasColumn('casos', 'tipo_respuesta_aseguradora')) {
                $table->string('tipo_respuesta_aseguradora')->nullable()->after('fecha_respuesta_aseguradora');
            }

            // Tipo de tutela: tutela_calificacion | tutela_debido_proceso
            if (!Schema::hasColumn('casos', 'tipo_tutela')) {
                $table->string('tipo_tutela')->nullable()->after('fecha_tutela');
            }

            // Cumplimiento del fallo de tutela concedido
            if (!Schema::hasColumn('casos', 'fecha_cumplimiento_tutela')) {
                $table->date('fecha_cumplimiento_tutela')->nullable()->after('fecha_incidente_desacato');
            }
            if (!Schema::hasColumn('casos', 'tipo_cumplimiento_tutela')) {
                $table->string('tipo_cumplimiento_tutela')->nullable()->after('fecha_cumplimiento_tutela');
            }

            // Segunda instancia (después de impugnación)
            if (!Schema::hasColumn('casos', 'fecha_fallo_segunda_instancia')) {
                $table->date('fecha_fallo_segunda_instancia')->nullable()->after('fecha_impugnacion');
            }
            if (!Schema::hasColumn('casos', 'resultado_fallo_segunda_instancia')) {
                $table->string('resultado_fallo_segunda_instancia')->nullable()->after('fecha_fallo_segunda_instancia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('casos', function (Blueprint $table) {
            $columns = [
                'tipo_respuesta_aseguradora',
                'tipo_tutela',
                'fecha_cumplimiento_tutela',
                'tipo_cumplimiento_tutela',
                'fecha_fallo_segunda_instancia',
                'resultado_fallo_segunda_instancia',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('casos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};