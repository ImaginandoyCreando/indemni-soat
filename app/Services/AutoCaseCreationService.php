<?php

namespace App\Services;

use App\Models\Caso;
use App\Models\Bitacora;
use App\Models\EmailLog;
use Carbon\Carbon;

class AutoCaseCreationService
{
    private $patterns = [
        // Patrones para detectar negocios nuevos
        'nuevo_negocio' => [
            'nuevo negocio soat',
            'solicitud de indemnización',
            'accidente de tránsito',
            'reclamación soat',
            'caso nuevo',
            'cliente solicita',
            'nuevo caso',
            'asesoría soat',
            'trámite soat'
        ],
        
        // Patrones para extraer información
        'nombre_cliente' => [
            '/cliente[:\s]+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i',
            '/nombre[:\s]+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i',
            '/señor[a]?[:\s]+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i',
            '/paciente[:\s]+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i'
        ],
        
        'fecha_accidente' => [
            '/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/',
            '/(\d{1,2})\s+de\s+(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+de\s+(\d{4})/i',
            '/fecha[:\s]+(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/i'
        ],
        
        'aseguradora' => [
            '/sura|sura\s+seguros/i',
            '/mapfre|mapfre\s+seguros/i',
            '/hdi|hdi\s+seguros/i',
            '/axa|axa\s+seguros/i',
            '/liberty|liberty\s+seguros/i',
            '/bolívar|bolivar\s+seguros/i',
            '/estado solidario|solidaria/i',
            '/allianz|allianz\s+seguros/i',
            '/zurich|zurich\s+seguros/i'
        ],
        
        'monto' => [
            '/\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/',
            '/valor[:\s]+\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/i',
            '/monto[:\s]+\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/i',
            '/(\d+)\s*(?:smmlv|smmv|salarios?)/i'
        ],
        
        'contacto' => [
            '/(\d{3}[-\s]?\d{3}[-\s]?\d{4})/',
            '/(\d{7,})/',
            '/telefono[:\s]+(\d{7,})/i',
            '/celular[:\s]+(\d{7,})/i'
        ]
    ];

    public function processEmailForNewCase($email, $accountName)
    {
        $subject = strtolower($email['subject']);
        $body = strtolower($email['body']);
        $fullText = $subject . ' ' . $body;
        
        // Verificar si es un negocio nuevo
        if (!$this->isNewBusiness($fullText)) {
            return null;
        }
        
        // Extraer información
        $caseData = $this->extractCaseInformation($email);
        
        // Verificar que tengamos información mínima
        if (!$this->hasMinimumRequiredInfo($caseData)) {
            return null;
        }
        
        // Crear el caso
        return $this->createAutoCase($caseData, $email, $accountName);
    }

    private function isNewBusiness($text)
    {
        foreach ($this->patterns['nuevo_negocio'] as $pattern) {
            if (strpos($text, $pattern) !== false) {
                return true;
            }
        }
        
        // También detectar correos que no mencionan casos existentes
        if (!preg_match('/caso[:\s#]+([A-Z0-9\-]+)/i', $text) && 
            !preg_match('/expediente[:\s#]+([A-Z0-9\-]+)/i', $text) &&
            (strpos($text, 'soat') !== false || strpos($text, 'accidente') !== false)) {
            return true;
        }
        
        return false;
    }

    private function extractCaseInformation($email)
    {
        $data = [
            'nombre_cliente' => $this->extractPattern($email['subject'] . ' ' . $email['body'], 'nombre_cliente'),
            'fecha_accidente' => $this->extractPattern($email['subject'] . ' ' . $email['body'], 'fecha_accidente'),
            'aseguradora' => $this->extractPattern($email['subject'] . ' ' . $email['body'], 'aseguradora'),
            'monto' => $this->extractPattern($email['subject'] . ' ' . $email['body'], 'monto'),
            'contacto' => $this->extractPattern($email['subject'] . ' ' . $email['body'], 'contacto'),
            'email_origen' => $email['from_email'],
            'asunto' => $email['subject'],
            'cuerpo' => substr($email['body'], 0, 1000) // Primeros 1000 caracteres
        ];
        
        return $data;
    }

    private function extractPattern($text, $patternType)
    {
        if ($patternType === 'aseguradora') {
            foreach ($this->patterns['aseguradora'] as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    return $this->normalizeInsuranceName($matches[0]);
                }
            }
        } else {
            foreach ($this->patterns[$patternType] as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    return $matches[1] ?? $matches[0];
                }
            }
        }
        
        return null;
    }

    private function normalizeInsuranceName($insurance)
    {
        $insurance = strtolower($insurance);
        
        if (strpos($insurance, 'sura') !== false) return 'SURA';
        if (strpos($insurance, 'mapfre') !== false) return 'MAPFRE';
        if (strpos($insurance, 'hdi') !== false) return 'HDI';
        if (strpos($insurance, 'axa') !== false) return 'AXA';
        if (strpos($insurance, 'liberty') !== false) return 'LIBERTY';
        if (strpos($insurance, 'bolívar') !== false || strpos($insurance, 'bolivar') !== false) return 'BOLÍVAR';
        if (strpos($insurance, 'estado solidario') !== false || strpos($insurance, 'solidaria') !== false) return 'ESTADO SOLIDARIO';
        if (strpos($insurance, 'allianz') !== false) return 'ALLIANZ';
        if (strpos($insurance, 'zurich') !== false) return 'ZURICH';
        
        return 'POR IDENTIFICAR';
    }

    private function hasMinimumRequiredInfo($data)
    {
        // Necesitamos al menos nombre del cliente O fecha del accidente
        return !empty($data['nombre_cliente']) || !empty($data['fecha_accidente']);
    }

    private function createAutoCase($data, $email, $accountName)
    {
        try {
            // Generar número de caso único
            $numeroCaso = 'SOAT-' . date('Y') . '-' . str_pad(Caso::count() + 1, 4, '0', STR_PAD_LEFT);
            
            // Determinar etapa inicial según contenido
            $etapaInicial = $this->determineInitialStage($email['subject'] . ' ' . $email['body']);
            
            // Crear el caso
            $caso = Caso::create([
                'numero_caso' => $numeroCaso,
                'nombre_cliente' => $data['nombre_cliente'] ?? 'Por identificar',
                'fecha_accidente' => $data['fecha_accidente'] ? $this->parseDate($data['fecha_accidente']) : now(),
                'aseguradora' => $data['aseguradora'] ?? 'Por identificar',
                'estado' => $etapaInicial['estado'],
                'etapa_actual' => $etapaInicial['etapa'],
                'monto_reclamado' => $data['monto'] ? $this->parseAmount($data['monto']) : null,
                'telefono' => $data['contacto'],
                'email' => $data['email_origen'],
                'descripcion' => "Caso creado automáticamente desde correo:\n\n" .
                                "Asunto: " . $data['asunto'] . "\n" .
                                "De: " . $data['email_origen'] . "\n" .
                                "Cuenta: " . $accountName . "\n\n" .
                                "Contenido:\n" . $data['cuerpo'],
                'created_by' => 1, // Usuario sistema
                'auto_created' => true,
                'auto_created_from' => $accountName,
                'auto_created_date' => now()
            ]);
            
            // Crear bitácora inicial
            Bitacora::create([
                'caso_id' => $caso->id,
                'titulo' => 'Caso creado automáticamente',
                'descripcion' => "Caso creado desde correo de {$accountName}: {$data['asunto']}",
                'fecha_evento' => now()
            ]);
            
            // Marcar correo como procesado
            EmailLog::create([
                'caso_id' => $caso->id,
                'email_id' => $email['message_id'] ?? $email['id'],
                'subject' => $email['subject'],
                'body' => $email['body'],
                'from_email' => $email['from_email'],
                'from_name' => $email['from_name'],
                'email_date' => $email['date'],
                'detected_insurance' => $data['aseguradora'],
                'email_type' => 'nuevo_negocio_auto',
                'extracted_data' => json_encode($data),
                'processed' => true,
            ]);
            
            return [
                'success' => true,
                'caso' => $caso,
                'message' => "Caso {$numeroCaso} creado automáticamente"
            ];
            
        } catch (\Exception $e) {
            \Log::error("Error creando caso automático: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error creando caso: ' . $e->getMessage()
            ];
        }
    }

    private function determineInitialStage($text)
    {
        $text = strtolower($text);
        
        if (strpos($text, 'solicitud') !== false || strpos($text, 'consulta') !== false) {
            return ['estado' => 'Solicitud inicial', 'etapa' => 'solicitud_inicial'];
        }
        
        if (strpos($text, 'documentación') !== false || strpos($text, 'documentos') !== false) {
            return ['estado' => 'Recopilando documentos', 'etapa' => 'recopilacion_documentos'];
        }
        
        if (strpos($text, 'reclamación') !== false || strpos($text, 'reclamo') !== false) {
            return ['estado' => 'Reclamación presentada', 'etapa' => 'reclamacion_presentada'];
        }
        
        if (strpos($text, 'negociación') !== false || strpos($text, 'negociar') !== false) {
            return ['estado' => 'En negociación', 'etapa' => 'negociacion'];
        }
        
        // Por defecto
        return ['estado' => 'Nuevo caso', 'etapa' => 'nuevo'];
    }

    private function parseDate($dateString)
    {
        try {
            // Intentar diferentes formatos
            $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];
            
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date) {
                    return $date;
                }
            }
            
            // Si no funciona, intentar con strtotime
            return new \DateTime($dateString);
            
        } catch (\Exception $e) {
            return now();
        }
    }

    private function parseAmount($amountString)
    {
        // Limpiar el string
        $amount = preg_replace('/[^0-9,]/', '', $amountString);
        $amount = str_replace(',', '.', $amount);
        
        return is_numeric($amount) ? floatval($amount) : null;
    }
}
