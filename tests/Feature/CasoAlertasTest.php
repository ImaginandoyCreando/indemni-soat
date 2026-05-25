<?php

namespace Tests\Feature;

use App\Models\Caso;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasoAlertasTest extends TestCase
{
    use RefreshDatabase;

    private function caso(array $atributos = []): Caso
    {
        return Caso::create(array_merge([
            'numero_caso' => 'SOAT-TEST-' . uniqid(),
            'nombres'     => 'Juan',
            'apellidos'   => 'Pérez',
            'cedula'      => '1234567890',
            'aseguradora' => 'SURA',
            'tiene_poder' => true,
            'tiene_contrato' => true,
        ], $atributos));
    }

    public function test_caso_pagado_no_tiene_alertas_pendientes(): void
    {
        $caso = $this->caso(['fecha_pago_final' => Carbon::yesterday()]);

        $this->assertTrue($caso->estaPagado());
        $this->assertSame('pagado', $caso->alerta_valor);
        $this->assertFalse($caso->requierePoderContrato());
    }

    public function test_caso_sin_poder_o_contrato_alerta_documentacion(): void
    {
        $caso = $this->caso(['tiene_poder' => false]);
        $this->assertSame('documentacion_inicial', $caso->alerta_valor);
    }

    public function test_poder_entregado_pero_no_firmado_alerta(): void
    {
        $caso = $this->caso([
            'fecha_entrega_poder' => Carbon::today()->subDays(5),
            'fecha_poder_firmado' => null,
        ]);
        $this->assertTrue($caso->requiereFirmaPoder());
    }

    public function test_prescripcion_se_calcula_automaticamente_a_18_meses(): void
    {
        $accidente = Carbon::parse('2024-01-15');
        $caso = $this->caso(['fecha_accidente' => $accidente]);

        $this->assertEquals(
            $accidente->copy()->addMonths(18)->toDateString(),
            $caso->fecha_prescripcion->toDateString()
        );
    }

    public function test_caso_prescrito_se_marca_correctamente(): void
    {
        $caso = $this->caso(['fecha_accidente' => Carbon::today()->subMonths(20)]);
        $this->assertTrue($caso->estaPrescrito());
    }

    public function test_prescripcion_critica_dentro_de_90_dias(): void
    {
        // 17 meses atrás → prescripción en ~1 mes (≤90 días)
        $caso = $this->caso(['fecha_accidente' => Carbon::today()->subMonths(17)]);

        $this->assertFalse($caso->estaPrescrito());
        $this->assertTrue($caso->prescripcionCritica());
    }

    public function test_avance_se_recalcula_al_guardar(): void
    {
        $caso = $this->caso();
        // 2 pasos completados (poder + contrato) de 13 = 15%
        $this->assertSame(15, $caso->porcentaje_avance);

        $caso->update([
            'fecha_solicitud_aseguradora' => Carbon::today()->subDays(5),
            'tipo_respuesta_aseguradora'  => 'emitio_dictamen',
        ]);

        // 4 de 13 ≈ 31%
        $this->assertSame(31, $caso->porcentaje_avance);
    }

    public function test_honorarios_y_ganancia_se_calculan_al_guardar(): void
    {
        $caso = $this->caso([
            'valor_pagado'          => 10000000,
            'porcentaje_honorarios' => 20,
        ]);

        $this->assertEquals(2000000, (float) $caso->ganancia_equipo);
        $this->assertEquals(8000000, (float) $caso->valor_neto_cliente);
    }

    public function test_apelacion_solo_aplica_si_aseguradora_emitio_dictamen(): void
    {
        $base = [
            'fecha_solicitud_aseguradora' => Carbon::today()->subDays(40),
            'fecha_respuesta_aseguradora' => Carbon::today()->subDays(5),
        ];

        $caso = $this->caso(array_merge($base, ['tipo_respuesta_aseguradora' => 'emitio_dictamen']));
        $this->assertTrue($caso->requiereApelacion());

        $caso2 = $this->caso(array_merge($base, ['tipo_respuesta_aseguradora' => 'nego']));
        $this->assertFalse($caso2->requiereApelacion());
    }

    public function test_tutela_calificacion_aplica_si_aseguradora_nego(): void
    {
        $caso = $this->caso([
            'fecha_solicitud_aseguradora' => Carbon::today()->subDays(40),
            'fecha_respuesta_aseguradora' => Carbon::today()->subDays(5),
            'tipo_respuesta_aseguradora'  => 'nego',
        ]);
        $this->assertTrue($caso->requiereTutela());
    }

    public function test_desacato_aplica_pasados_14_dias_del_fallo_concedido(): void
    {
        $caso = $this->caso([
            'fecha_tutela'             => Carbon::today()->subDays(30),
            'fecha_fallo_tutela'       => Carbon::today()->subDays(20),
            'resultado_fallo_tutela'   => 'concedido',
        ]);

        $this->assertTrue($caso->requiereIncidenteDesacato());
        $this->assertSame('desacato', $caso->alerta_valor);
    }

    public function test_dentro_de_14_dias_es_cumplimiento_no_desacato(): void
    {
        $caso = $this->caso([
            'fecha_tutela'             => Carbon::today()->subDays(10),
            'fecha_fallo_tutela'       => Carbon::today()->subDays(5),
            'resultado_fallo_tutela'   => 'concedido',
        ]);

        $this->assertTrue($caso->requiereCumplimientoTutela());
        $this->assertFalse($caso->requiereIncidenteDesacato());
    }

    public function test_caso_cerrado_si_segunda_instancia_confirma(): void
    {
        $caso = $this->caso([
            'fecha_tutela'                       => Carbon::today()->subDays(60),
            'fecha_fallo_tutela'                 => Carbon::today()->subDays(45),
            'resultado_fallo_tutela'             => 'negado',
            'fecha_impugnacion'                  => Carbon::today()->subDays(30),
            'fecha_fallo_segunda_instancia'      => Carbon::today()->subDays(5),
            'resultado_fallo_segunda_instancia'  => 'confirma',
        ]);

        $this->assertTrue($caso->casoCerradoSegundaInstancia());
        $this->assertSame('caso_cerrado', $caso->alerta_valor);
    }

    public function test_scope_filtrar_alerta_pagado_devuelve_solo_pagados(): void
    {
        $this->caso(['fecha_pago_final' => Carbon::yesterday()]);
        $this->caso();

        $this->assertSame(1, Caso::filtrarAlerta('pagado')->count());
    }

    public function test_scope_filtrar_alerta_desacato(): void
    {
        // No desacato (dentro de 14d)
        $this->caso([
            'fecha_fallo_tutela'     => Carbon::today()->subDays(5),
            'resultado_fallo_tutela' => 'concedido',
        ]);
        // Desacato (más de 14d)
        $this->caso([
            'fecha_fallo_tutela'     => Carbon::today()->subDays(20),
            'resultado_fallo_tutela' => 'concedido',
        ]);

        $this->assertSame(1, Caso::filtrarAlerta('desacato')->count());
    }
}
