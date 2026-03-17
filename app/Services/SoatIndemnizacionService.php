<?php

namespace App\Services;

use App\Models\SalarioMinimo;
use Carbon\Carbon;

class SoatIndemnizacionService
{
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

        $salario = SalarioMinimo::where('anio', $anio)->first();

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