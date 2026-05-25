<?php

namespace Tests\Feature;

use App\Models\SalarioMinimo;
use App\Services\SoatIndemnizacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoatIndemnizacionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SoatIndemnizacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SoatIndemnizacionService();

        // SMLDV 2024 = 1.300.000 / 30 ≈ 43.333,33
        SalarioMinimo::create([
            'anio'  => 2024,
            'smmlv' => 1300000,
            'smldv' => round(1300000 / 30, 2),
        ]);
    }

    public function test_devuelve_error_sin_fecha_o_pcl(): void
    {
        $r = $this->service->calcular(null, 10);
        $this->assertNull($r['valor_estimado']);
        $this->assertNotNull($r['mensaje']);

        $r = $this->service->calcular('2024-01-15', null);
        $this->assertNull($r['valor_estimado']);
    }

    public function test_pcl_cero_o_negativo_no_calcula(): void
    {
        $r = $this->service->calcular('2024-06-01', 0);
        $this->assertNull($r['smldv_aplicados']);
        $this->assertSame('El porcentaje PCL no es válido para cálculo.', $r['mensaje']);
    }

    public function test_pcl_hasta_5_aplica_14_smldv_base(): void
    {
        $r = $this->service->calcular('2024-06-01', 5);
        $this->assertSame(14.0, $r['smldv_aplicados']);
        $this->assertEquals(round(14 * 43333.33, 2), $r['valor_estimado']);
    }

    public function test_pcl_6_suma_un_bloque_de_3_5(): void
    {
        // 14 + ceil(6-5)*3.5 = 17.5
        $r = $this->service->calcular('2024-06-01', 6);
        $this->assertSame(17.5, $r['smldv_aplicados']);
    }

    public function test_pcl_10_suma_5_bloques(): void
    {
        // 14 + ceil(10-5)*3.5 = 14 + 17.5 = 31.5
        $r = $this->service->calcular('2024-06-01', 10);
        $this->assertSame(31.5, $r['smldv_aplicados']);
    }

    public function test_pcl_50_es_el_maximo_antes_del_tope(): void
    {
        // 14 + ceil(50-5)*3.5 = 14 + 157.5 = 171.5
        $r = $this->service->calcular('2024-06-01', 50);
        $this->assertSame(171.5, $r['smldv_aplicados']);
    }

    public function test_pcl_mayor_a_50_se_topea_en_180(): void
    {
        $r = $this->service->calcular('2024-06-01', 75);
        $this->assertSame(180.0, $r['smldv_aplicados']);

        $r = $this->service->calcular('2024-06-01', 100);
        $this->assertSame(180.0, $r['smldv_aplicados']);
    }

    public function test_pcl_decimal_se_redondea_hacia_arriba_en_bloques(): void
    {
        // ceil(7.2-5) = ceil(2.2) = 3 → 14 + 3*3.5 = 24.5
        $r = $this->service->calcular('2024-06-01', 7.2);
        $this->assertSame(24.5, $r['smldv_aplicados']);
    }

    public function test_anio_sin_salario_cargado_avisa(): void
    {
        $r = $this->service->calcular('1999-01-01', 10);
        $this->assertSame(1999, $r['anio']);
        $this->assertNull($r['valor_estimado']);
        $this->assertStringContainsString('No existe salario mínimo', $r['mensaje']);
    }

    public function test_usa_smldv_del_anio_correcto(): void
    {
        SalarioMinimo::create([
            'anio'  => 2020,
            'smmlv' => 877803,
            'smldv' => 29260.10,
        ]);

        $r2020 = $this->service->calcular('2020-03-01', 5);
        $r2024 = $this->service->calcular('2024-03-01', 5);

        $this->assertSame(29260.10, (float) $r2020['smldv_anio_accidente']);
        $this->assertSame(43333.33, (float) $r2024['smldv_anio_accidente']);
        $this->assertNotEquals($r2020['valor_estimado'], $r2024['valor_estimado']);
    }
}
