<?php

namespace App\Services;

use App\Models\Caso;
use ZipArchive;

class PlantillaAnalizadorService
{
    private array $mappeo = [
        // Variables genéricas existentes
        'nombre_victima'             => 'nombre_completo',
        'nombre_completo'            => 'nombre_completo',
        'nombres'                    => 'nombres',
        'apellidos'                  => 'apellidos',
        'cedula'                     => 'cedula',
        'numero_cedula'              => 'cedula',
        'telefono'                   => 'telefono',
        'correo'                     => 'correo',
        'aseguradora'                => 'aseguradora',
        'nombre_aseguradora'         => 'aseguradora',
        'numero_caso'                => 'numero_caso',
        'expediente'                 => 'numero_caso',
        'ciudad'                     => 'ciudad',
        'departamento'               => 'departamento',
        'direccion'                  => 'direccion',
        'porcentaje_pcl'             => 'porcentaje_pcl',
        'pcl'                        => 'porcentaje_pcl',
        'junta_asignada'             => 'junta_asignada',
        'junta'                      => 'junta_asignada',
        'etapa_actual'               => 'etapa_actual',
        'estado'                     => 'estado',
        'numero_dictamen'            => 'numero_dictamen',
        'fecha_notificacion_dictamen'=> '_fecha_notificacion_dictamen',
        'fecha_accidente'            => '_fecha_accidente',
        'fecha_solicitud'            => '_fecha_solicitud_aseguradora',
        'fecha_tutela'               => '_fecha_tutela',
        'fecha_fallo_tutela'         => '_fecha_fallo_tutela',
        'fecha_impugnacion'          => '_fecha_impugnacion',
        'fecha_envio_junta'          => '_fecha_envio_junta',
        'fecha_dictamen_junta'       => '_fecha_dictamen_junta',
        'fecha_prescripcion'         => '_fecha_prescripcion',

        // FURPEN — Beneficiario (datos del reclamante, tomados del caso)
        'benef_primer_apellido'      => '_primer_apellido',
        'benef_segundo_apellido'     => '_segundo_apellido',
        'benef_primer_nombre'        => '_primer_nombre',
        'benef_segundo_nombre'       => '_segundo_nombre',
        'benef_numero_documento'     => 'cedula',
        'benef_direccion'            => 'direccion',
        'benef_correo'               => 'correo',
        'benef_departamento'         => 'departamento',
        'benef_municipio'            => 'ciudad',
        'benef_telefono'             => 'telefono',

        // FURPEN — Víctima (mismos datos del caso)
        'vic_primer_apellido'        => '_primer_apellido',
        'vic_segundo_apellido'       => '_segundo_apellido',
        'vic_primer_nombre'          => '_primer_nombre',
        'vic_segundo_nombre'         => '_segundo_nombre',
        'vic_numero_documento'       => 'cedula',
        'vic_direccion'              => 'direccion',
        'vic_departamento'           => 'departamento',
        'vic_municipio'              => 'ciudad',
        'vic_telefono'               => 'telefono',
        'evento_fecha'               => '_fecha_accidente',

        // FURPEN — Vehículo
        'vehic_aseguradora'          => 'aseguradora',
    ];

    /**
     * Variables que deben mostrarse como lista desplegable en el formulario.
     * Clave = nombre de variable, Valor = opciones disponibles.
     */
    public static array $opcionesSelect = [
        // Beneficiario
        'benef_tipo_documento'           => ['CC', 'CE', 'PA', 'PE', 'PT'],
        'benef_tipo_cuenta'              => ['Ahorros', 'Corriente'],
        'benef_parentesco'               => [
            'Padres', 'Cónyuge', 'Compañero permanente',
            'Hijos', 'Hermanos', 'Representante Legal',
        ],

        // Apoderado
        'apo_tipo_documento'             => ['CC', 'CE'],

        // Víctima
        'vic_tipo_documento'             => ['CC', 'CE', 'CD', 'PA', 'SC', 'PE', 'RC', 'TI', 'CN', 'DE', 'PT'],
        'vic_sexo'                       => ['M', 'F', 'O'],
        'vic_zona'                       => ['U - Urbana', 'R - Rural'],
        'vic_condicion'                  => ['Conductor', 'Peatón', 'Ocupante', 'Ciclista'],

        // Evento
        'naturaleza_evento'              => [
            'Accidente de tránsito',
            'Sismo', 'Maremoto', 'Erupciones volcánicas', 'Huracán',
            'Inundaciones', 'Tornado', 'Avalancha', 'Deslizamiento de tierra',
            'Incendio natural', 'Rayo', 'Vendabal',
            'Explosión', 'Masacre', 'Mina antipersonal', 'Incendio',
            'Ataque a municipios', 'Combate', 'Otro',
        ],

        // Vehículo
        'vehic_estado_aseguramiento'     => [
            'Asegurado', 'No asegurado', 'Vehículo fantasma',
            'Póliza falsa', 'No asegurado sin placa',
            'No asegurado - propietario indeterminado',
        ],
        'vehic_tipo_servicio'            => [
            'Automóvil', 'Bus', 'Buseta', 'Camión', 'Camioneta',
            'Campero', 'Microbus', 'Tractocamión', 'Motocicleta',
            'Motocarro', 'Mototriciclo', 'Cuatrimoto',
            'Moto Extranjera', 'Vehículo Extranjero', 'Volqueta',
        ],
        'vehic_intervencion_autoridad'   => ['SI', 'NO'],

        // Propietario vehículo
        'prop_tipo_documento'            => ['CC', 'CE', 'PA', 'NIT', 'TI', 'PE', 'PT', 'CD', 'DE'],

        // Conductor
        'cond_tipo_documento'            => ['CC', 'CE', 'PA', 'RC', 'TI', 'PE', 'PT', 'SC', 'CD', 'DE', 'SI', 'AS', 'MS'],

        // Amparos
        'amparo_reclamado'               => [
            'Indemnización por muerte y Gastos Funerarios',
            'Incapacidad permanente',
        ],
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

            // Campos especiales calculados con guion bajo doble
            if (str_starts_with($campo, '__')) {
                $resultado[$var] = $this->calcularCampoEspecial($caso, ltrim($campo, '_'));
                continue;
            }

            if (str_starts_with($campo, '_')) {
                $nombreFecha = ltrim($campo, '_');

                // Campos especiales de nombre/apellido
                if (in_array($nombreFecha, ['primer_apellido', 'segundo_apellido', 'primer_nombre', 'segundo_nombre'])) {
                    $resultado[$var] = $this->extraerParteNombre($caso, $nombreFecha);
                    continue;
                }

                $fecha = $caso->{$nombreFecha} ?? null;
                $resultado[$var] = $fecha
                    ? \Carbon\Carbon::parse($fecha)->format('d/m/Y')
                    : '';
            } else {
                $resultado[$var] = (string) ($caso->{$campo} ?? '');
            }
        }

        return $resultado;
    }

    /**
     * Extrae partes individuales del nombre completo o apellidos del caso.
     */
    private function extraerParteNombre(Caso $caso, string $parte): string
    {
        $nombres   = explode(' ', trim($caso->nombres ?? ''));
        $apellidos = explode(' ', trim($caso->apellidos ?? ''));

        return match ($parte) {
            'primer_apellido'   => $apellidos[0] ?? '',
            'segundo_apellido'  => $apellidos[1] ?? '',
            'primer_nombre'     => $nombres[0] ?? '',
            'segundo_nombre'    => $nombres[1] ?? '',
            default             => '',
        };
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
            '/\{\{[^}]*(?:<[^>]+>[^}]*)*\}\}/',
            function ($m) {
                return preg_replace('/<[^>]+>/', '', $m[0]) ?? $m[0];
            },
            $xml
        ) ?? $xml;

        return $xml;
    }
}
