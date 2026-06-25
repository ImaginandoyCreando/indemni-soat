<?php

namespace App\Services;

use App\Models\Caso;
use ZipArchive;

class PlantillaAnalizadorService
{
    private array $mappeo = [
        'nombre_victima'         => 'nombre_completo',
        'nombre_completo'        => 'nombre_completo',
        'nombres'                => 'nombres',
        'apellidos'              => 'apellidos',
        'cedula'                 => 'cedula',
        'numero_cedula'          => 'cedula',
        'telefono'               => 'telefono',
        'correo'                 => 'correo',
        'aseguradora'            => 'aseguradora',
        'nombre_aseguradora'     => 'aseguradora',
        'numero_caso'            => 'numero_caso',
        'expediente'             => 'numero_caso',
        'ciudad'                 => 'ciudad',
        'departamento'           => 'departamento',
        'direccion'              => 'direccion',
        'porcentaje_pcl'         => 'porcentaje_pcl',
        'pcl'                    => 'porcentaje_pcl',
        'junta_asignada'         => 'junta_asignada',
        'junta'                  => 'junta_asignada',
        'etapa_actual'           => 'etapa_actual',
        'estado'                 => 'estado',
        'fecha_accidente'        => '_fecha_accidente',
        'fecha_solicitud'        => '_fecha_solicitud_aseguradora',
        'fecha_tutela'           => '_fecha_tutela',
        'fecha_fallo_tutela'     => '_fecha_fallo_tutela',
        'fecha_impugnacion'      => '_fecha_impugnacion',
        'fecha_envio_junta'      => '_fecha_envio_junta',
        'fecha_dictamen_junta'   => '_fecha_dictamen_junta',
        'fecha_prescripcion'     => '_fecha_prescripcion',
    ];

    public function detectarVariables(string $rutaAbsoluta, string $extension): array
    {
        $texto = match (strtolower($extension)) {
            'docx'  => $this->extraerTextoDocx($rutaAbsoluta),
            'xlsx'  => $this->extraerTextoXlsx($rutaAbsoluta),
            default => $this->extraerTextoPdf($rutaAbsoluta),
        };

        preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $texto, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function preLlenarDesde(Caso $caso, array $variables): array
    {
        $resultado = [];

        foreach ($variables as $var) {
            $campo = $this->mappeo[$var] ?? null;

            if ($campo === null) {
                $resultado[$var] = '';
                continue;
            }

            if (str_starts_with($campo, '_')) {
                $nombreFecha = ltrim($campo, '_');
                $fecha = $caso->{$nombreFecha};
                $resultado[$var] = $fecha
                    ? \Carbon\Carbon::parse($fecha)->format('d/m/Y')
                    : '';
            } else {
                $resultado[$var] = (string) ($caso->{$campo} ?? '');
            }
        }

        return $resultado;
    }

    public function generarDocx(string $rutaPlantilla, array $valores): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'docgen_') . '.docx';
        copy($rutaPlantilla, $tmpPath);

        $zip = new ZipArchive();
        if ($zip->open($tmpPath) !== true) {
            throw new \RuntimeException('No se pudo abrir la plantilla DOCX para generación.');
        }

        $archivosXml = ['word/document.xml', 'word/header1.xml', 'word/footer1.xml'];

        foreach ($archivosXml as $xmlFile) {
            $contenido = $zip->getFromName($xmlFile);
            if ($contenido === false) continue;

            $contenido = $this->limpiarTagsFragmentados($contenido);

            foreach ($valores as $clave => $valor) {
                $contenido = str_replace('{{'.$clave.'}}', htmlspecialchars($valor ?? ''), $contenido);
            }

            $zip->addFromString($xmlFile, $contenido);
        }

        $zip->close();

        return $tmpPath;
    }

    private function extraerTextoDocx(string $ruta): string
    {
        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) return '';

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        return strip_tags($xml);
    }

    private function extraerTextoXlsx(string $ruta): string
    {
        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) return '';

        $texto = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nombre = $zip->getNameIndex($i);
            if (str_contains($nombre, 'xl/worksheets/') || $nombre === 'xl/sharedStrings.xml') {
                $texto .= $zip->getFromName($nombre) ?: '';
            }
        }
        $zip->close();

        return strip_tags($texto);
    }

    private function extraerTextoPdf(string $ruta): string
    {
        $contenido = file_get_contents($ruta);
        if ($contenido === false) return '';

        preg_match_all('/BT(.+?)ET/s', $contenido, $matches);
        $texto = implode(' ', $matches[1] ?? []);

        preg_match_all('/\(([^)]+)\)/', $texto, $cadenas);
        return implode(' ', $cadenas[1] ?? []);
    }

    private function limpiarTagsFragmentados(string $xml): string
    {
        $xml = preg_replace_callback(
            '/\{\{[^}]*\}\}|(\{\{[^}]*?)(<[^>]+>)([^}]*?\}\})/',
            function ($m) {
                if (isset($m[1]) && $m[1] !== '') {
                    return $m[1] . $m[3];
                }
                return $m[0];
            },
            $xml
        ) ?? $xml;

        return $xml;
    }
}
