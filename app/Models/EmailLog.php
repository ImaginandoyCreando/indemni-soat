<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'caso_id',
        'email_id',
        'subject',
        'body',
        'from_email',
        'from_name',
        'email_date',
        'detected_insurance',
        'email_type',
        'extracted_data',
        'processed',
    ];

    protected $casts = [
        'email_date' => 'datetime',
        'extracted_data' => 'array',
        'processed' => 'boolean',
    ];

    public function caso()
    {
        return $this->belongsTo(Caso::class);
    }

    // Detectar automáticamente la aseguradora
    public static function detectInsurance($email, $subject, $body)
    {
        $patterns = [
            'SURA' => ['sura.co', 'segurossura.com', 'seguros sura'],
            'MAPFRE' => ['mapfre.com.co', 'mapfre colombia'],
            'HDI' => ['hdi.com.co', 'hdi seguros'],
            'Aseguradora Solidaria' => ['solidaria.com.co', 'aseguradora solidaria'],
            'Bolívar' => ['segurosbolivar.com', 'bolivar seguros'],
            'Estado' => ['laestada.com', 'aseguradora estado'],
            'Liberty' => ['liberty.com.co', 'liberty seguros'],
            'AXA' => ['axa.com.co', 'axa seguros'],
        ];

        $text = strtolower($email . ' ' . $subject . ' ' . $body);

        foreach ($patterns as $insurance => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, strtolower($keyword)) !== false) {
                    return $insurance;
                }
            }
        }

        return null;
    }

    // Clasificar tipo de correo
    public static function classifyEmail($subject, $body)
    {
        $text = strtolower($subject . ' ' . $body);

        // Solicitud enviada
        if (preg_match('/(solicitud|radicaci[oó]n|env[ií]amos|presentamos).*calificaci[oó]n/', $text)) {
            return 'solicitud_enviada';
        }

        // Respuesta positiva
        if (preg_match('/(aprobad[ao]|aceptad[ao]|procede|favorable|concedid[ao]|reconocido)/', $text)) {
            return 'respuesta_positiva';
        }

        // Respuesta negativa
        if (preg_match('/(niega|rechazad[ao]|negad[ao]|desestimad[ao]|improcedente)/', $text)) {
            return 'respuesta_negativa';
        }

        // En proceso
        if (preg_match('/(estudiando|an[aá]lisis|tramitando|proceso|revisi[oó]n)/', $text)) {
            return 'en_proceso';
        }

        // Requisitos
        if (preg_match('/(requiere|solicita|necesita|falta|documentos)/', $text)) {
            return 'requiere_documentos';
        }

        // Citación
        if (preg_match('/(citaci[oó]n|audiencia|conciliaci[oó]n|comparecer)/', $text)) {
            return 'citacion';
        }

        return 'otro';
    }
}
