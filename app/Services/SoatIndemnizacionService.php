<?php

namespace App\Services;

use App\Models\SalarioMinimo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SoatIndemnizacionService
{
    /**
     * Los salarios mínimos cambian una vez al año. Cachearlos evita ir a la BD
     * en cada cálculo de indemnización (que se ejecuta en cada save de Caso).
     */
    private const CACHE_TTL_SECONDS = 86400; // 24 h

    public function calcular(?string $fechaAccidente, $porcentajePcl): array
    {
        if (empty($fechaAccidente) || $porcentajePcl === null || $porcentajePcl === '') {
            return [
                'anio' => null,
                'smldv_aplicados' => null,
                'smldv_anio_accidente' => null,
                'valor_estimado' => null,
                'mensaje' => 'Falta fecha del accidente o porcentaje PCL.',
            ];
        }

        $anio = Carbon::parse($fechaAccidente)->year;
        $porcentaje = (float) $porcentajePcl;

        $salario = $this->salarioDelAnio($anio);

        if (!$salario) {
            return [
                'anio' => $anio,
                'smldv_aplicados' => null,
                'smldv_anio_accidente' => null,
                'valor_estimado' => null,
                'mensaje' => 'No existe salario mínimo cargado para el año del accidente.',
            ];
        }

        $smldvAplicados = $this->obtenerSmldvPorPcl($porcentaje);

        if ($smldvAplicados === null) {
            return [
                'anio' => $anio,
                'smldv_aplicados' => null,
                'smldv_anio_accidente' => $salario->smldv,
                'valor_estimado' => null,
                'mensaje' => 'El porcentaje PCL no es válido para cálculo.',
            ];
        }

        $valorEstimado = round($smldvAplicados * $salario->smldv, 2);

        return [
            'anio' => $anio,
            'smldv_aplicados' => $smldvAplicados,
            'smldv_anio_accidente' => $salario->smldv,
            'valor_estimado' => $valorEstimado,
            'mensaje' => null,
        ];
    }

    private function salarioDelAnio(int $anio): ?SalarioMinimo
    {
        return Cache::remember(
            "salario_minimo_{$anio}",
            self::CACHE_TTL_SECONDS,
            fn () => SalarioMinimo::where('anio', $anio)->first()
        );
    }

    private function obtenerSmldvPorPcl(float $pcl): ?float
    {
        if ($pcl <= 0) {
            return null;
        }

        if ($pcl <= 5) {
            return 14.0;
        }

        if ($pcl > 50) {
            return 180.0;
        }

        $bloques = ceil($pcl - 5);

        return 14.0 + ($bloques * 3.5);
    }
}