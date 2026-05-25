<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── email_logs_new: agregar caso_id que faltaba y unicidad en email_id ──
        Schema::table('email_logs_new', function (Blueprint $table) {
            if (!Schema::hasColumn('email_logs_new', 'caso_id')) {
                $table->foreignId('caso_id')->nullable()->after('id')->constrained('casos')->nullOnDelete();
            }
        });

        // Limpiar duplicados antes de aplicar índice único en email_id.
        // Conserva el registro más reciente por cada email_id repetido.
        if (Schema::hasColumn('email_logs_new', 'email_id')) {
            $duplicates = DB::table('email_logs_new')
                ->select('email_id', DB::raw('MAX(id) as keep_id'))
                ->whereNotNull('email_id')
                ->groupBy('email_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $dup) {
                DB::table('email_logs_new')
                    ->where('email_id', $dup->email_id)
                    ->where('id', '!=', $dup->keep_id)
                    ->delete();
            }

            Schema::table('email_logs_new', function (Blueprint $table) {
                $table->unique('email_id', 'email_logs_new_email_id_unique');
                $table->index('email_type', 'email_logs_new_email_type_index');
                $table->index('created_at', 'email_logs_new_created_at_index');
                $table->index('processed', 'email_logs_new_processed_index');
            });
        }

        // ── casos: índices en columnas usadas por scopeFiltrarAlerta y búsquedas ──
        Schema::table('casos', function (Blueprint $table) {
            $indexes = [
                'fecha_solicitud_aseguradora'    => 'casos_fecha_solicitud_aseg_idx',
                'fecha_respuesta_aseguradora'    => 'casos_fecha_respuesta_aseg_idx',
                'fecha_tutela'                   => 'casos_fecha_tutela_idx',
                'fecha_fallo_tutela'             => 'casos_fecha_fallo_tutela_idx',
                'fecha_impugnacion'              => 'casos_fecha_impugnacion_idx',
                'fecha_fallo_segunda_instancia'  => 'casos_fecha_fallo_2da_idx',
                'fecha_pago_final'               => 'casos_fecha_pago_final_idx',
                'fecha_prescripcion'             => 'casos_fecha_prescripcion_idx',
                'fecha_reclamacion_final'        => 'casos_fecha_reclam_final_idx',
                'estado'                         => 'casos_estado_idx',
                'tipo_respuesta_aseguradora'     => 'casos_tipo_resp_aseg_idx',
                'auto_created'                   => 'casos_auto_created_idx',
            ];

            foreach ($indexes as $column => $name) {
                if (Schema::hasColumn('casos', $column)) {
                    $table->index($column, $name);
                }
            }
        });

        // ── bitacoras: índice en caso_id+fecha (queries comunes de timeline) ──
        Schema::table('bitacoras', function (Blueprint $table) {
            $table->index(['caso_id', 'fecha_evento'], 'bitacoras_caso_fecha_idx');
            if (Schema::hasColumn('bitacoras', 'tipo_alerta')) {
                $table->index('tipo_alerta', 'bitacoras_tipo_alerta_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_logs_new', function (Blueprint $table) {
            $table->dropUnique('email_logs_new_email_id_unique');
            $table->dropIndex('email_logs_new_email_type_index');
            $table->dropIndex('email_logs_new_created_at_index');
            $table->dropIndex('email_logs_new_processed_index');

            if (Schema::hasColumn('email_logs_new', 'caso_id')) {
                $table->dropConstrainedForeignId('caso_id');
            }
        });

        Schema::table('casos', function (Blueprint $table) {
            $names = [
                'casos_fecha_solicitud_aseg_idx',
                'casos_fecha_respuesta_aseg_idx',
                'casos_fecha_tutela_idx',
                'casos_fecha_fallo_tutela_idx',
                'casos_fecha_impugnacion_idx',
                'casos_fecha_fallo_2da_idx',
                'casos_fecha_pago_final_idx',
                'casos_fecha_prescripcion_idx',
                'casos_fecha_reclam_final_idx',
                'casos_estado_idx',
                'casos_tipo_resp_aseg_idx',
                'casos_auto_created_idx',
            ];
            foreach ($names as $name) {
                try { $table->dropIndex($name); } catch (\Throwable $e) {}
            }
        });

        Schema::table('bitacoras', function (Blueprint $table) {
            try { $table->dropIndex('bitacoras_caso_fecha_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('bitacoras_tipo_alerta_idx'); } catch (\Throwable $e) {}
        });
    }
};
