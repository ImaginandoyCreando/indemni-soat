<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\Bitacora;
use App\Models\EmailLog;
use Carbon\Carbon;

class AutoCaseCreationService
{
    private $patterns = [
        // ── Patrones para detectar negocios nuevos / eventos relevantes ──────────
        'nuevo_negocio' => [
            // Frases explícitas de negocio nuevo
            'nuevo negocio soat',
            'solicitud de indemnización',
            'accidente de tránsito',
            'reclamación soat',
            'caso nuevo',
            'cliente solicita',
            'nuevo caso',
            'asesoría soat',
            'trámite soat',
            'requiere asesoría',
            'necesita ayuda',
            'consulta legal',
            'accidente laboral',
            'incidente vial',
            'colisión vehicular',
            'atropellamiento',
            'lesión corporal',
            'perjuicios económicos',
            'daño moral',
            'pérdida capacidad',
            'calificación pcl',
            'dictamen pericial'
        ],

        // ── Patrones jurídicos colombianos (NUEVOS) ───────────────────────────
        'evento_juridico' => [
            'acción de tutela',
            'accion de tutela',
            'tutela',
            'fallo de tutela',
            'impugnación',
            'impugnacion',
            'dictamen',
            'calificación de invalidez',
            'calificacion de invalidez',
            'junta de calificación',
            'pérdida de capacidad laboral',
            'perdida de capacidad laboral',
            'pago de honorarios',
            'honorarios',
            'indemnización soat',
            'indemnizacion soat',
            'reclamación soat',
            'reclamacion soat',
            'segunda instancia',
            'fallo de segunda instancia',
            'cumplimiento de fallo',
            'incidente de desacato',
            'radicación de tutela',
            'radicacion de tutela',
            'notificación judicial',
            'notificacion judicial',
        ],

        // ── Extracción de nombre del cliente ─────────────────────────────────
        'nombre_cliente' => [
            // Formato juzgados colombianos: "seguida por NOMBRE APELLIDO RAD:"
            '/seguida\s+por\s+([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{5,60}?)(?:\s+RAD|\s+contra|\s+vs\.?|\s+C\.C|\.|,|$)/i',
            // "interpuesta por / presentada por NOMBRE"
            '/(?:interpuesta|presentada|instaurada)\s+por\s+([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]{5,60}?)(?:\s+contra|\s+RAD|,|\.)/i',
            // "Señor(a)(es): NOMBRE" en cuerpo
            '/se[ñn]or[a]?\(?es?\)?[:\s]+([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑA-Za-záéíóúñ\s]{5,60}?)(?:\n|$)/i',
            // "paciente: NOMBRE"
            '/paciente[:\s]+([A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑA-Za-záéíóúñ\s]{5,60}?)(?:\n|$)/i',
            // "cliente: NOMBRE"
            '/cliente[:\s]+([A-Z][a-záéíóúñ]+(?:\s+[A-Z][a-záéíóúñ]+)*)/i',
            // "nombre: NOMBRE"
            '/nombre[:\s]+([A-Z][a-záéíóúñ]+(?:\s+[A-Z][a-záéíóúñ]+)*)/i',
            // Nombre completo en mayúsculas en asunto (≥ 2 palabras, ≥ 4 chars cada una)
            '/\b([A-ZÁÉÍÓÚÑ]{3,}\s+[A-ZÁÉÍÓÚÑ]{3,}(?:\s+[A-ZÁÉÍÓÚÑ]{3,})*)\b/',
        ],

        // ── Fechas ────────────────────────────────────────────────────────────
        'fecha_accidente' => [
            '/(\d{1,2})\s+de\s+(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+de\s+(\d{4})/i',
            '/fecha\s*(?:de\s*accidente)?[:\s]+(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/i',
            '/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/',
        ],

        // ── Radicado judicial ─────────────────────────────────────────────────
        'radicado' => [
            '/RAD[:\.\s]+([0-9]{4}[\.\-][0-9]+[\.\-]?[0-9]*)/i',
            '/radicado[:\s]+([0-9\-\.]{8,})/i',
            '/No\.\s*([0-9]{4}[\.\-][0-9]+[\.\-]?[0-9]*)/i',
        ],

        // ── Aseguradoras ──────────────────────────────────────────────────────
        'aseguradora' => [
            '/previsora|la\s+previsora/i',
            '/sura|seguros\s+sura/i',
            '/mapfre/i',
            '/hdi\s+seguros|hdi/i',
            '/axa\s+seguros|axa/i',
            '/liberty\s+seguros|liberty/i',
            '/bol[ií]var\s+seguros|bol[ií]var/i',
            '/estado\s+solidario|solidaria/i',
            '/allianz/i',
            '/zurich/i',
            '/mundial\s+seguros|mundial/i',
            '/cardif/i',
            '/colm[eé]dica/i',
            '/positiva/i',
        ],

        // ── Montos ────────────────────────────────────────────────────────────
        'monto' => [
            '/valor[:\s]+\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/i',
            '/monto[:\s]+\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/i',
            '/(\d+)\s*(?:smmlv|smmv|salarios?)/i',
            '/\$\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/',
        ],

        // ── Contacto ──────────────────────────────────────────────────────────
        'contacto' => [
            '/(?:tel[eé]fono|celular|cel)[:\s]+(\d[\d\s\-]{6,})/i',
            '/(\d{3}[-\s]?\d{3}[-\s]?\d{4})/',
            '/(\b3\d{9}\b)/',   // Celular colombiano
        ],
    ];

    // ── Palabras reservadas que NO son nombres de personas ────────────────────
    private $excludeFromNames = [
        'ACCION', 'ACCIÓN', 'TUTELA', 'SOAT', 'SEGURO', 'SEGUROS', 'RAD',
        'OFICIO', 'JUZGADO', 'CIVIL', 'PENAL', 'TRIBUNAL', 'CORTE',
        'MINISTERIO', 'COLOMBIA', 'PROVIDENCIA', 'NOTIFICACION', 'NOTIFICACIÓN',
        'ESTE', 'CORREO', 'SOLO', 'PARA', 'EFECTOS', 'SEÑOR', 'SEÑORA',
        'RESPETADO', 'MEDIANTE', 'PRESENTE', 'NOTIFICO', 'ADJUNTO',
        'COMPAÑIA', 'COMPAÑÍA', 'EMPRESA', 'JUNTA', 'REGIONAL', 'NACIONAL',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    //  PUNTO DE ENTRADA PRINCIPAL
    // ─────────────────────────────────────────────────────────────────────────
    public function processEmailForNewCase($email, $accountName)
    {
        $subject  = $email['subject'] ?? '';
        $body     = $email['body'] ?? '';
        $fullText = $subject . ' ' . $body;

        // 1. ¿Es un correo relevante para crear/actualizar un caso?
        if (!$this->isRelevantEmail($fullText)) {
            return null;
        }

        // 2. Extraer toda la información posible
        $caseData = $this->extractCaseInformation($email);

        // 3. Verificar información mínima requerida
        if (!$this->hasMinimumRequiredInfo($caseData)) {
            \Log::info("AutoCase: correo descartado por falta de info mínima. Asunto: {$subject}");
            return null;
        }

        // 4. Crear el caso
        return $this->createAutoCase($caseData, $email, $accountName);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DETECCIÓN DE RELEVANCIA
    // ─────────────────────────────────────────────────────────────────────────
    private function isRelevantEmail($text)
    {
        $lower = strtolower($text);

        // Palabras clave de negocio nuevo
        foreach ($this->patterns['nuevo_negocio'] as $keyword) {
            if (strpos($lower, $keyword) !== false) {
                return true;
            }
        }

        // Eventos jurídicos colombianos
        foreach ($this->patterns['evento_juridico'] as $keyword) {
            if (strpos($lower, $keyword) !== false) {
                return true;
            }
        }

        // Correos con radicado judicial
        foreach ($this->patterns['radicado'] as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        // Correos que mencionan SOAT o accidente sin número de caso existente
        if (!preg_match('/caso[:\s#]+([A-Z0-9\-]+)/i', $text) &&
            !preg_match('/expediente[:\s#]+([A-Z0-9\-]+)/i', $text) &&
            (strpos($lower, 'soat') !== false || strpos($lower, 'accidente') !== false)) {
            return true;
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXTRACCIÓN DE INFORMACIÓN
    // ─────────────────────────────────────────────────────────────────────────
    private function extractCaseInformation($email)
    {
        $fullText = ($email['subject'] ?? '') . ' ' . ($email['body'] ?? '');

        return [
            'nombre_cliente'  => $this->extractNombreCliente($email['subject'] ?? '', $email['body'] ?? ''),
            'fecha_accidente' => $this->extractFecha($fullText),
            'aseguradora'     => $this->extractAseguradora($fullText),
            'radicado'        => $this->extractRadicado($fullText),
            'monto'           => $this->extractMonto($fullText),
            'contacto'        => $this->extractContacto($fullText),
            'tipo_evento'     => $this->detectTipoEvento($email['subject'] ?? '', $email['body'] ?? ''),
            'email_origen'    => $email['from_email'] ?? '',
            'asunto'          => $email['subject'] ?? '',
            'cuerpo'          => substr($email['body'] ?? '', 0, 1000),
        ];
    }

    // ── Nombre del cliente ────────────────────────────────────────────────────
    private function extractNombreCliente($subject, $body)
    {
        $fullText = $subject . ' ' . $body;

        foreach ($this->patterns['nombre_cliente'] as $index => $pattern) {
            if (preg_match($pattern, $fullText, $matches)) {
                $candidate = trim($matches[1]);

                // Para el patrón genérico de mayúsculas, validar que no sea palabra reservada
                if ($index === count($this->patterns['nombre_cliente']) - 1) {
                    if ($this->isExcludedWord($candidate)) continue;
                    if (str_word_count($candidate) < 2) continue;
                }

                // Limpiar y validar longitud mínima
                $candidate = preg_replace('/\s+/', ' ', $candidate);
                if (strlen($candidate) >= 5) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function isExcludedWord($text)
    {
        $words = explode(' ', strtoupper($text));
        foreach ($words as $word) {
            if (in_array(trim($word), $this->excludeFromNames)) {
                return true;
            }
        }
        return false;
    }

    // ── Fecha ─────────────────────────────────────────────────────────────────
    private function extractFecha($text)
    {
        $meses = [
            'enero'=>1,'febrero'=>2,'marzo'=>3,'abril'=>4,'mayo'=>5,'junio'=>6,
            'julio'=>7,'agosto'=>8,'septiembre'=>9,'octubre'=>10,'noviembre'=>11,'diciembre'=>12,
        ];

        // "13 de abril de 2026"
        if (preg_match(
            '/(\d{1,2})\s+de\s+(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+de\s+(\d{4})/i',
            $text, $m
        )) {
            try {
                return Carbon::createFromDate((int)$m[3], $meses[strtolower($m[2])], (int)$m[1]);
            } catch (\Exception $e) {}
        }

        // "fecha: dd/mm/yyyy" o "fecha: dd-mm-yyyy"
        if (preg_match('/fecha\s*(?:de\s*accidente)?[:\s]+(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/i', $text, $m)) {
            return $this->parseDate("{$m[1]}/{$m[2]}/{$m[3]}");
        }

        // "dd/mm/yyyy" o "dd-mm-yyyy"
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $text, $m)) {
            return $this->parseDate("{$m[1]}/{$m[2]}/{$m[3]}");
        }

        return null;
    }

    // ── Aseguradora ───────────────────────────────────────────────────────────
    private function extractAseguradora($text)
    {
        $map = [
            '/previsora|la\s+previsora/i'         => 'LA PREVISORA',
            '/seguros\s+sura|sura/i'              => 'SURA',
            '/mapfre/i'                           => 'MAPFRE',
            '/hdi\s+seguros|hdi/i'               => 'HDI',
            '/axa\s+seguros|axa/i'               => 'AXA',
            '/liberty\s+seguros|liberty/i'        => 'LIBERTY',
            '/bol[ií]var\s+seguros|bol[ií]var/i' => 'BOLÍVAR',
            '/estado\s+solidario|solidaria/i'     => 'ESTADO SOLIDARIO',
            '/allianz/i'                          => 'ALLIANZ',
            '/zurich/i'                           => 'ZURICH',
            '/mundial\s+seguros|mundial/i'        => 'MUNDIAL',
            '/cardif/i'                           => 'CARDIF',
            '/colm[eé]dica/i'                    => 'COLMÉDICA',
            '/positiva\s+compañ/i'               => 'POSITIVA',
        ];

        foreach ($map as $pattern => $nombre) {
            if (preg_match($pattern, $text)) {
                return $nombre;
            }
        }

        return null;
    }

    // ── Radicado ──────────────────────────────────────────────────────────────
    private function extractRadicado($text)
    {
        foreach ($this->patterns['radicado'] as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return trim($m[1]);
            }
        }
        return null;
    }

    // ── Monto ─────────────────────────────────────────────────────────────────
    private function extractMonto($text)
    {
        foreach ($this->patterns['monto'] as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return $m[1] ?? $m[0];
            }
        }
        return null;
    }

    // ── Contacto ──────────────────────────────────────────────────────────────
    private function extractContacto($text)
    {
        foreach ($this->patterns['contacto'] as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return preg_replace('/[\s\-]/', '', $m[1]);
            }
        }
        return null;
    }

    // ── Tipo de evento jurídico ───────────────────────────────────────────────
    private function detectTipoEvento($subject, $body)
    {
        $text = strtolower($subject . ' ' . $body);

        if (preg_match('/fallo.*segunda\s+instancia|segunda\s+instancia.*fallo/i', $text)) return 'fallo_segunda_instancia';
        if (preg_match('/cumplimiento.*fallo|fallo.*cumplimiento/i', $text))                return 'cumplimiento_fallo';
        if (preg_match('/incidente\s+de\s+desacato|desacato/i', $text))                    return 'incidente_desacato';
        if (preg_match('/impugnaci[oó]n/i', $text))                                        return 'impugnacion';
        if (preg_match('/fallo.*tutela|tutela.*fallo/i', $text))                           return 'fallo_tutela';
        if (preg_match('/acci[oó]n\s+de\s+tutela|tutela/i', $subject))                    return 'tutela';
        if (preg_match('/dictamen|calificaci[oó]n\s+de\s+invalidez/i', $text))            return 'dictamen';
        if (preg_match('/p[eé]rdida\s+de\s+capacidad\s+laboral/i', $text))                return 'dictamen';
        if (preg_match('/pago\s+de\s+honorarios|honorarios/i', $text))                    return 'honorarios';
        if (preg_match('/indemnizaci[oó]n/i', $text))                                     return 'indemnizacion';
        if (preg_match('/reclamaci[oó]n/i', $text))                                       return 'reclamacion';
        if (preg_match('/solicitud.*soat|soat.*solicitud/i', $text))                      return 'solicitud_soat';
        if (preg_match('/accidente\s+de\s+tr[aá]nsito/i', $text))                        return 'accidente';

        return 'nuevo';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  VALIDACIÓN MÍNIMA
    // ─────────────────────────────────────────────────────────────────────────
    private function hasMinimumRequiredInfo($data)
    {
        // Necesitamos al menos nombre del cliente O (radicado + aseguradora)
        return !empty($data['nombre_cliente']) ||
               (!empty($data['radicado']) && !empty($data['aseguradora']));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  MAPEO TIPO → ESTADO
    // ─────────────────────────────────────────────────────────────────────────
    private function estadoDesde($tipo)
    {
        $estados = [
            'tutela'                 => 'Acción de tutela recibida',
            'fallo_tutela'           => 'Fallo de tutela recibido',
            'fallo_segunda_instancia'=> 'Fallo de segunda instancia recibido',
            'cumplimiento_fallo'     => 'En cumplimiento de fallo',
            'incidente_desacato'     => 'Incidente de desacato',
            'impugnacion'            => 'Impugnación presentada',
            'dictamen'               => 'Dictamen de calificación recibido',
            'honorarios'             => 'Honorarios pagados',
            'indemnizacion'          => 'Indemnización recibida',
            'reclamacion'            => 'Reclamación presentada',
            'solicitud_soat'         => 'Solicitud SOAT',
            'accidente'              => 'Nuevo caso - Accidente',
            'nuevo'                  => 'Nuevo caso',
        ];

        return $estados[$tipo] ?? 'Nuevo caso';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CREACIÓN DEL CASO
    // ─────────────────────────────────────────────────────────────────────────
    private function createAutoCase($data, $email, $accountName)
    {
        try {
            // Número de caso: usa radicado si existe, sino genera uno secuencial
            $numeroCaso = $data['radicado']
                ? 'RAD-' . $data['radicado']
                : 'SOAT-' . date('Y') . '-' . str_pad(Caso::count() + 1, 4, '0', STR_PAD_LEFT);

            // Determinar etapa inicial
            $etapaInicial = $this->determineInitialStage($data['tipo_evento']);

            // Crear el caso
            $caso = Caso::create([
                'numero_caso'    => $numeroCaso,
                'nombre_cliente' => $data['nombre_cliente'] ?? 'Por identificar',
                'fecha_accidente'=> $data['fecha_accidente'] ?? now(),
                'aseguradora'    => $data['aseguradora'] ?? 'Por identificar',
                'estado'         => $etapaInicial['estado'],
                'etapa_actual'   => $etapaInicial['etapa'],
                'monto_reclamado'=> $data['monto'] ? $this->parseAmount($data['monto']) : null,
                'telefono'       => $data['contacto'],
                'email'          => $data['email_origen'],
                'descripcion'    => "Caso creado automáticamente desde correo:\n\n" .
                                    "Asunto: " . $data['asunto'] . "\n" .
                                    "De: " . $data['email_origen'] . "\n" .
                                    "Cuenta: " . $accountName . "\n" .
                                    "Tipo detectado: " . $data['tipo_evento'] . "\n\n" .
                                    "Contenido:\n" . $data['cuerpo'],
                'created_by'     => 1,
                'auto_created'   => true,
            ]);

            // Bitácora inicial
            Bitacora::create([
                'caso_id'     => $caso->id,
                'titulo'      => 'Caso creado automáticamente desde correo',
                'descripcion' => "Tipo: {$data['tipo_evento']} | Cuenta: {$accountName} | Asunto: {$data['asunto']}",
                'fecha_evento'=> now(),
            ]);

            // Registrar email log
            EmailLog::create([
                'caso_id'            => $caso->id,
                'email_id'           => $email['message_id'] ?? ($email['id'] ?? null),
                'subject'            => $email['subject'] ?? '',
                'body'               => $email['body'] ?? '',
                'from_email'         => $email['from_email'] ?? '',
                'from_name'          => $email['from_name'] ?? '',
                'email_date'         => $email['date'] ?? now(),
                'detected_insurance' => $data['aseguradora'],
                'email_type'         => $data['tipo_evento'],
                'extracted_data'     => json_encode($data),
                'processed'          => true,
            ]);

            \Log::info("AutoCase: caso {$numeroCaso} creado para '{$data['nombre_cliente']}' | Tipo: {$data['tipo_evento']}");

            return [
                'success' => true,
                'caso'    => $caso,
                'message' => "Caso {$numeroCaso} creado automáticamente",
            ];

        } catch (\Exception $e) {
            \Log::error("AutoCase ERROR al crear caso: " . $e->getMessage() . " | Asunto: " . ($data['asunto'] ?? ''));
            return [
                'success' => false,
                'message' => 'Error creando caso: ' . $e->getMessage(),
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ETAPA INICIAL SEGÚN TIPO
    // ─────────────────────────────────────────────────────────────────────────
    private function determineInitialStage($tipoEvento)
    {
        $etapas = [
            'tutela'                  => ['estado' => 'Acción de tutela recibida',         'etapa' => 'tutela'],
            'fallo_tutela'            => ['estado' => 'Fallo de tutela recibido',           'etapa' => 'fallo_tutela'],
            'fallo_segunda_instancia' => ['estado' => 'Fallo de segunda instancia',         'etapa' => 'fallo_segunda_instancia'],
            'cumplimiento_fallo'      => ['estado' => 'En cumplimiento de fallo',           'etapa' => 'cumplimiento_tutela'],
            'incidente_desacato'      => ['estado' => 'Incidente de desacato',              'etapa' => 'incidente_desacato'],
            'impugnacion'             => ['estado' => 'Impugnación presentada',             'etapa' => 'impugnacion'],
            'dictamen'                => ['estado' => 'Dictamen de calificación recibido',  'etapa' => 'dictamen_junta'],
            'honorarios'              => ['estado' => 'Honorarios pagados',                 'etapa' => 'pago_honorarios'],
            'indemnizacion'           => ['estado' => 'Indemnización recibida',             'etapa' => 'pago'],
            'reclamacion'             => ['estado' => 'Reclamación presentada',             'etapa' => 'reclamacion_presentada'],
            'solicitud_soat'          => ['estado' => 'Solicitud inicial',                  'etapa' => 'solicitud_inicial'],
            'accidente'               => ['estado' => 'Nuevo caso - Accidente de tránsito', 'etapa' => 'nuevo'],
            'nuevo'                   => ['estado' => 'Nuevo caso',                         'etapa' => 'nuevo'],
        ];

        return $etapas[$tipoEvento] ?? ['estado' => 'Nuevo caso', 'etapa' => 'nuevo'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UTILIDADES
    // ─────────────────────────────────────────────────────────────────────────
    private function parseDate($dateString)
    {
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date && $date->format($format) === $dateString) {
                return Carbon::instance($date);
            }
        }

        try {
            return new Carbon($dateString);
        } catch (\Exception $e) {
            return now();
        }
    }

    private function parseAmount($amountString)
    {
        // Quitar todo excepto dígitos y coma decimal
        $amount = preg_replace('/[^0-9,]/', '', $amountString);
        $amount = str_replace(',', '.', $amount);

        return is_numeric($amount) ? floatval($amount) : null;
    }
}
