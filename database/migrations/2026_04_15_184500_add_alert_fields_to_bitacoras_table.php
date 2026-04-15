<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bitacoras', function (Blueprint $table) {
            $table->string('tipo_alerta')->nullable()->after('descripcion')->comment('Tipo de alerta: alerta_no_respuesta, alerta_proximo_vencer, pago_detectado, fallo_tutela, respuesta_positiva');
            $table->boolean('auto_generada')->default(false)->after('tipo_alerta')->comment('Si la bitácora fue generada automáticamente');
            $table->string('prioridad')->default('media')->after('auto_generada')->comment('Prioridad: baja, media, alta');
        });
    }

    public function down()
    {
        Schema::table('bitacoras', function (Blueprint $table) {
            $table->dropColumn(['tipo_alerta', 'auto_generada', 'prioridad']);
        });
    }
};
