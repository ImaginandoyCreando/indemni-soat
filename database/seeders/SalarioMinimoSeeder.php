<?php

namespace Database\Seeders;

use App\Models\SalarioMinimo;
use Illuminate\Database\Seeder;

class SalarioMinimoSeeder extends Seeder
{
    public function run(): void
    {
        $datos = [
            ['anio' => 2016, 'smmlv' => 689455.00],
            ['anio' => 2017, 'smmlv' => 737717.00],
            ['anio' => 2018, 'smmlv' => 781242.00],
            ['anio' => 2019, 'smmlv' => 828116.00],
            ['anio' => 2020, 'smmlv' => 877803.00],
            ['anio' => 2021, 'smmlv' => 908526.00],
            ['anio' => 2022, 'smmlv' => 1000000.00],
            ['anio' => 2023, 'smmlv' => 1160000.00],
            ['anio' => 2024, 'smmlv' => 1300000.00],
            ['anio' => 2025, 'smmlv' => 1423500.00],
        ];

        foreach ($datos as $item) {
            SalarioMinimo::updateOrCreate(
                ['anio' => $item['anio']],
                [
                    'smmlv' => $item['smmlv'],
                    'smldv' => round($item['smmlv'] / 30, 2),
                ]
            );
        }
    }
}