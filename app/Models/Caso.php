<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Caso extends Model
{
    use HasFactory;

    protected $table = 'casos';

    protected $fillable = [
        'numero_caso',
        'nombres',
        'apellidos',
        'cedula',
        'telefono',
        'correo',
        'departamento',
        'ciudad',
        'direccion',
        'aseguradora',
        'estado',
        'porcentaje_pcl',
        'valor_estimado',
        'valor_reclamado',
        'valor_pagado',
        'porcentaje_honorarios',
        'ganancia_equipo',
        'valor_neto_cliente',
        'porcentaje_avance',
        'junta_asignada',
        'fecha_accidente',
        'fecha_solicitud_aseguradora',
        'fecha_respuesta_aseguradora',
        'tipo_respuesta_aseguradora',       // NUEVO: emitio_dictamen | nego | no_respondio
        'fecha_apelacion',
        'fecha_tutela',
        'tipo_tutela',                      // NUEVO: tutela_calificacion | tutela_debido_proceso
        'fecha_pago_honorarios',
        'fecha_envio_junta',
        'fecha_dictamen_junta',
        'fecha_reclamacion_final',
        'fecha_pago_final',
        'observacion_pago',
        'observacion_reclamacion',
        'observaciones',
        'smldv_aplicados',
        'smldv_anio_accidente',
        'user_id',
        'auto_created',
        'auto_created_from',
        'auto_created_date',
        'tiene_poder',
        'fecha_entrega_poder',
        'fecha_poder_firmado',
        'tiene_contrato',
        'fecha_entrega_contrato',
        'fecha_contrato_firmado',
        'alta_ortopedia',
        'fecha_alta_ortopedia',
        'observacion_alta_ortopedia',
        'furpen_completo',
        'fecha_furpen_recibido',
        'observacion_furpen',
        'fecha_fallo_tutela',
        'resultado_fallo_tutela',
        'fecha_incidente_desacato',
        'fecha_cumplimiento_tutela',        // NUEVO: fecha en que la aseguradora cumplió el fallo
        'tipo_cumplimiento_tutela',         // NUEVO: voluntario | desacato
        'fecha_impugnacion',
        'fecha_fallo_segunda_instancia',    // NUEVO: fallo de segunda instancia tras impugnación
        'resultado_fallo_segunda_instancia',// NUEVO: confirma | revoca
        'fecha_prescripcion',
    ];

    protected $casts = [
        'fecha_accidente'                   => 'date',
        'fecha_solicitud_aseguradora'       => 'date',
        'fecha_respuesta_aseguradora'       => 'date',
        'fecha_apelacion'                   => 'date',
        'fecha_tutela'                      => 'date',
        'fecha_pago_honorarios'             => 'date',
        'fecha_envio_junta'                 => 'date',
        'fecha_dictamen_junta'              => 'date',
        'fecha_reclamacion_final'           => 'date',
        'fecha_pago_final'                  => 'date',
        'fecha_entrega_poder'               => 'date',
        'fecha_poder_firmado'               => 'date',
        'fecha_entrega_contrato'            => 'date',
        'fecha_contrato_firmado'            => 'date',
        'fecha_alta_ortopedia'              => 'date',
        'fecha_furpen_recibido'             => 'date',
        'fecha_fallo_tutela'                => 'date',
        'fecha_incidente_desacato'          => 'date',
        'fecha_cumplimiento_tutela'         => 'date',  // NUEVO
        'fecha_impugnacion'                 => 'date',
        'fecha_fallo_segunda_instancia'     => 'date',  // NUEVO
        'fecha_prescripcion'                => 'date',
        'tiene_poder'                       => 'boolean',
        'tiene_contrato'                    => 'boolean',
        'alta_ortopedia'                    => 'boolean',
        'furpen_completo'                   => 'boolean',
        'porcentaje_pcl'                    => 'decimal:2',
        'valor_estimado'                    => 'decimal:2',
        'valor_reclamado'                   => 'decimal:2',
        'valor_pagado'                      => 'decimal:2',
        'porcentaje_honorarios'             => 'decimal:2',
        'ganancia_equipo'                   => 'decimal:2',
        'valor_neto_cliente'                => 'decimal:2',
        'smldv_aplicados'                   => 'decimal:2',
        'smldv_anio_accidente'              => 'decimal:2',
        'porcentaje_avance'                 => 'integer',
    ];

    protected $appends = [
        'nombre_completo',
        'texto_alerta',
        'color_alerta',
        'alerta_valor',
    ];

    // -------------------------------------------------------------------------
    // RELACIONES
    // -------------------------------------------------------------------------

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'caso_id');
    }

    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'caso_id');
    }

    // -------------------------------------------------------------------------
    // ACCESSORS
    // -------------------------------------------------------------------------

    public function getNombreCompletoAttribute()
    {
        return trim(($this->nombres ?? '') . ' ' . ($this->apellidos ?? ''));
    }

    /**
     * Devuelve el código de alerta de mayor prioridad para este caso.
     * El orden define la prioridad: el primero que aplique gana.
     */
    public function getAlertaValorAttribute()
    {
        if ($this->estaPagado())           return 'pagado';
        if ($this->estaPrescrito())        return 'prescrito';
        if ($this->prescripcionCritica())  return 'prescripcion_critica';
        if ($this->requierePoderContrato()) return 'documentacion_inicial';
        if ($this->requiereFirmaPoder())   return 'poder_pendiente';
        if ($this->requiereFirmaContrato()) return 'contrato_pendiente';

        // Caso cerrado en segunda instancia (confirma) - no se puede hacer nada
        if ($this->casoCerradoSegundaInstancia()) return 'caso_cerrado';

        // Segunda instancia revocó → aseguradora debe cumplir
        if ($this->requiereCumplimientoSegundaInstancia()) return 'cumplimiento_segunda_instancia';

        // Desacato (prioridad alta: ya pasaron 14 días sin cumplir fallo concedido)
        if ($this->requiereIncidenteDesacato()) return 'desacato';

        // Cumplimiento pendiente (dentro de las 2 semanas tras fallo concedido)
        if ($this->requiereCumplimientoTutela()) return 'cumplimiento_tutela';

        // Impugnación
        if ($this->requiereImpugnacion()) return 'impugnacion';

        // Segunda instancia pendiente (esperando fallo)
        if ($this->requiereSegundaInstancia()) return 'segunda_instancia';

        if ($this->requiereQuejaNoPago())         return 'queja';
        if ($this->requiereSeguimientoTutela())   return 'seguimiento_tutela';
        if ($this->requiereTutela())              return 'tutela';
        if ($this->requierePagoPendiente())       return 'pago_pendiente';
        if ($this->requiereFurpen())              return 'furpen_pendiente';
        if ($this->requiereCobroAseguradora())    return 'reclamacion';
        if ($this->requiereAltaOrtopedia())       return 'alta_ortopedia_pendiente';
        if ($this->requiereSolicitudJunta())      return 'solicitud_junta';
        if ($this->requierePagoHonorariosJunta()) return 'honorarios_junta';
        if ($this->requiereApelacion())           return 'apelar_dictamen';
        if ($this->requiereRespuestaAseguradora()) return 'sin_respuesta';

        return 'normal';
    }

    public function getTextoAlertaAttribute()
    {
        return match ($this->alerta_valor) {
            'pagado'                        => 'Pagado',
            'prescrito'                     => 'Caso prescrito',
            'prescripcion_critica'          => 'Prescripción próxima',
            'documentacion_inicial'         => 'Falta poder / contrato',
            'poder_pendiente'               => 'Poder pendiente',
            'contrato_pendiente'            => 'Contrato pendiente',
            'caso_cerrado'                  => 'Caso cerrado - segunda instancia',
            'cumplimiento_segunda_instancia'=> 'Cumplimiento segunda instancia',
            'desacato'                      => 'Incidente de desacato',
            'cumplimiento_tutela'           => 'Esperando cumplimiento tutela',
            'impugnacion'                   => 'Impugnación pendiente',
            'segunda_instancia'             => 'Pendiente segunda instancia',
            'queja'                         => 'Queja por no pago',
            'seguimiento_tutela'            => 'Seguimiento tutela',
            'tutela'                        => 'Crítica / tutela',
            'pago_pendiente'                => 'Pago pendiente',
            'furpen_pendiente'              => 'FURPEN pendiente',
            'reclamacion'                   => 'Seguimiento / cobro',
            'alta_ortopedia_pendiente'      => 'Alta ortopedia pendiente',
            'solicitud_junta'               => 'Solicitud a junta',
            'honorarios_junta'              => 'Pagar honorarios junta',
            'apelar_dictamen'               => 'Apelar dictamen',
            'sin_respuesta'                 => 'Sin respuesta aseguradora',
            default                         => 'Normal',
        };
    }

    public function getColorAlertaAttribute()
    {
        return match ($this->alerta_valor) {
            'pagado'                                                         => 'green',
            'prescrito', 'caso_cerrado', 'desacato', 'queja',
            'seguimiento_tutela', 'tutela', 'sin_respuesta',
            'prescripcion_critica'                                           => 'red',
            'documentacion_inicial', 'poder_pendiente', 'contrato_pendiente',
            'impugnacion', 'segunda_instancia', 'apelar_dictamen',
            'honorarios_junta', 'alta_ortopedia_pendiente',
            'furpen_pendiente', 'cumplimiento_tutela',
            'cumplimiento_segunda_instancia'                                 => 'orange',
            'reclamacion', 'pago_pendiente', 'solicitud_junta'              => 'blue',
            default                                                          => 'gray',
        };
    }

    // -------------------------------------------------------------------------
    // ESTADO
    // -------------------------------------------------------------------------

    public function estaPagado(): bool
    {
        return ($this->estado ?? '') === 'Pagado' || !empty($this->fecha_pago_final);
    }

    public function estaPrescrito(): bool
    {
        if (empty($this->fecha_prescripcion)) return false;
        return Carbon::today()->gt(Carbon::parse($this->fecha_prescripcion));
    }

    public function diasParaPrescripcion(): ?int
    {
        if (empty($this->fecha_prescripcion)) return null;
        return Carbon::today()->diffInDays(Carbon::parse($this->fecha_prescripcion), false);
    }

    public function prescripcionCritica(): bool
    {
        if ($this->estaPagado() || $this->estaPrescrito() || empty($this->fecha_prescripcion)) {
            return false;
        }
        $dias = $this->diasParaPrescripcion();
        return $dias !== null && $dias <= 90;
    }

    // -------------------------------------------------------------------------
    // MÉTODOS REQUIERE* — lógica de alertas
    // -------------------------------------------------------------------------

    public function requierePoderContrato(): bool
    {
        if ($this->estaPagado()) return false;
        return !$this->tiene_poder || !$this->tiene_contrato;
    }

    public function requiereFirmaPoder(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_entrega_poder) && empty($this->fecha_poder_firmado);
    }

    public function requiereFirmaContrato(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_entrega_contrato) && empty($this->fecha_contrato_firmado);
    }

    public function requiereRespuestaAseguradora(): bool
    {
        if ($this->estaPagado() || $this->requierePoderContrato()) return false;
        if (empty($this->fecha_solicitud_aseguradora) || !empty($this->tipo_respuesta_aseguradora)) return false;
        return $this->fecha_limite_respuesta_aseguradora
            ? Carbon::today()->gt($this->fecha_limite_respuesta_aseguradora)
            : false;
    }

    public function requiereApelacion(): bool
    {
        if ($this->estaPagado()) return false;
        // Solo aplica si la aseguradora emitió dictamen directamente
        if ($this->tipo_respuesta_aseguradora !== 'emitio_dictamen') return false;
        return !empty($this->fecha_respuesta_aseguradora) && empty($this->fecha_apelacion);
    }

    /**
     * Tutela de calificación: cuando la aseguradora negó o no respondió.
     * Tutela de debido proceso: cuando apelaron pero no pagaron honorarios.
     */
    public function requiereTutela(): bool
    {
        if ($this->estaPagado() || !empty($this->fecha_tutela)) return false;

        // Sin respuesta o negó → tutela para calificación
        if ($this->requiereRespuestaAseguradora()) return true;
        if (in_array($this->tipo_respuesta_aseguradora, ['nego', 'no_respondio']) && empty($this->fecha_tutela)) {
            return true;
        }

        // Apelaron y pasó un mes sin pagar honorarios → tutela por debido proceso
        if (!empty($this->fecha_apelacion) && empty($this->fecha_pago_honorarios)) {
            $limite = Carbon::parse($this->fecha_apelacion)->addDays(30);
            return Carbon::today()->gt($limite);
        }

        return false;
    }

    public function requiereSeguimientoTutela(): bool
    {
        if ($this->estaPagado() || empty($this->fecha_tutela) || !empty($this->fecha_fallo_tutela)) return false;
        return $this->fecha_limite_seguimiento_tutela
            ? Carbon::today()->gt($this->fecha_limite_seguimiento_tutela)
            : false;
    }

    /**
     * Cumplimiento pendiente: fallo concedido, dentro de las 2 semanas,
     * sin que la aseguradora haya cumplido todavía.
     */
    public function requiereCumplimientoTutela(): bool
    {
        if ($this->estaPagado()) return false;
        if (empty($this->fecha_fallo_tutela) || $this->resultado_fallo_tutela !== 'concedido') return false;
        if (!empty($this->fecha_cumplimiento_tutela)) return false;
        if (!empty($this->fecha_incidente_desacato)) return false;
        if ($this->cumplioFalloTutela()) return false;

        // Está dentro del plazo de 2 semanas (aún no es desacato)
        return $this->fecha_limite_cumplimiento_fallo
            ? !Carbon::today()->gt($this->fecha_limite_cumplimiento_fallo)
            : true;
    }

    public function requiereImpugnacion(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_fallo_tutela)
            && in_array($this->resultado_fallo_tutela, ['negado', 'parcial'])
            && empty($this->fecha_impugnacion);
    }

    /**
     * Esperando fallo de segunda instancia (después de impugnación).
     */
    public function requiereSegundaInstancia(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_impugnacion)
            && empty($this->fecha_fallo_segunda_instancia);
    }

    /**
     * Segunda instancia revocó → aseguradora debe cumplir lo ordenado.
     */
    public function requiereCumplimientoSegundaInstancia(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_fallo_segunda_instancia)
            && $this->resultado_fallo_segunda_instancia === 'revoca'
            && empty($this->fecha_cumplimiento_tutela)
            && !$this->cumplioFalloTutela();
    }

    /**
     * Caso cerrado porque segunda instancia confirmó el fallo negado.
     */
    public function casoCerradoSegundaInstancia(): bool
    {
        return !empty($this->fecha_fallo_segunda_instancia)
            && $this->resultado_fallo_segunda_instancia === 'confirma';
    }

    public function requiereIncidenteDesacato(): bool
    {
        if ($this->estaPagado()) return false;
        if (empty($this->fecha_fallo_tutela) || $this->resultado_fallo_tutela !== 'concedido') return false;
        if (!empty($this->fecha_incidente_desacato)) return false;
        if ($this->cumplioFalloTutela()) return false;
        // Aplica también si segunda instancia revocó y no cumple
        if (!empty($this->fecha_fallo_segunda_instancia) &&
            $this->resultado_fallo_segunda_instancia === 'revoca' &&
            !$this->cumplioFalloTutela()) {
            return true;
        }
        return $this->fecha_limite_cumplimiento_fallo
            ? Carbon::today()->gt($this->fecha_limite_cumplimiento_fallo)
            : false;
    }

    public function cumplioFalloTutela(): bool
    {
        // Cumplió si pagó honorarios (tutela debido proceso) o si emitió dictamen (tutela calificación)
        if (!empty($this->fecha_cumplimiento_tutela)) return true;
        if (!empty($this->fecha_pago_honorarios)) return true;
        if ($this->tipo_tutela === 'tutela_calificacion' && !empty($this->fecha_respuesta_aseguradora)) return true;
        return false;
    }

    public function requierePagoHonorariosJunta(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_apelacion) && empty($this->fecha_pago_honorarios);
    }

    public function requiereAltaOrtopedia(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_pago_honorarios)
            && !$this->alta_ortopedia
            && empty($this->fecha_envio_junta);
    }

    public function requiereSolicitudJunta(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_pago_honorarios)
            && $this->alta_ortopedia
            && empty($this->fecha_envio_junta);
    }

    public function requiereCobroAseguradora(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_dictamen_junta)
            && $this->furpen_completo
            && empty($this->fecha_reclamacion_final);
    }

    public function requiereFurpen(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_dictamen_junta)
            && !$this->furpen_completo
            && empty($this->fecha_reclamacion_final);
    }

    public function requierePagoPendiente(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_reclamacion_final) && empty($this->fecha_pago_final);
    }

    public function requiereQuejaNoPago(): bool
    {
        if ($this->estaPagado()) return false;
        if (empty($this->fecha_reclamacion_final) || !empty($this->fecha_pago_final)) return false;
        return $this->fecha_limite_pago_final
            ? Carbon::today()->gt($this->fecha_limite_pago_final)
            : false;
    }

    // -------------------------------------------------------------------------
    // FECHA LÍMITE ATTRIBUTES
    // -------------------------------------------------------------------------

    public function getFechaLimiteRespuestaAseguradoraAttribute()
    {
        return !empty($this->fecha_solicitud_aseguradora)
            ? Carbon::parse($this->fecha_solicitud_aseguradora)->copy()->addDays(30)
            : null;
    }

    public function getFechaLimitePagoFinalAttribute()
    {
        return !empty($this->fecha_reclamacion_final)
            ? Carbon::parse($this->fecha_reclamacion_final)->copy()->addDays(30)
            : null;
    }

    public function getFechaLimiteSeguimientoTutelaAttribute()
    {
        return !empty($this->fecha_tutela)
            ? Carbon::parse($this->fecha_tutela)->copy()->addDays(30)
            : null;
    }

    public function getFechaLimiteCumplimientoFalloAttribute()
    {
        return !empty($this->fecha_fallo_tutela)
            ? Carbon::parse($this->fecha_fallo_tutela)->copy()->addDays(14)
            : null;
    }

    // -------------------------------------------------------------------------
    // SCOPE FILTRAR ALERTA
    // -------------------------------------------------------------------------

    public function scopeFiltrarAlerta($query, $alerta)
    {
        if (empty($alerta)) return $query;

        $fechaLimite30Dias = Carbon::today()->subDays(30)->toDateString();
        $fechaLimite14Dias = Carbon::today()->subDays(14)->toDateString();
        $fechaPrescripcionCritica = Carbon::today()->addDays(90)->toDateString();

        return match ($alerta) {
            'documentacion_inicial' => $query->where(function ($q) {
                $q->where('tiene_poder', false)->orWhere('tiene_contrato', false);
            }),

            'poder_pendiente' => $query
                ->whereNotNull('fecha_entrega_poder')
                ->whereNull('fecha_poder_firmado'),

            'contrato_pendiente' => $query
                ->whereNotNull('fecha_entrega_contrato')
                ->whereNull('fecha_contrato_firmado'),

            'sin_respuesta' => $query
                ->whereNotNull('fecha_solicitud_aseguradora')
                ->whereNull('tipo_respuesta_aseguradora')
                ->whereDate('fecha_solicitud_aseguradora', '<', $fechaLimite30Dias)
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'Pagado');
                }),

            'apelar_dictamen' => $query
                ->where('tipo_respuesta_aseguradora', 'emitio_dictamen')
                ->whereNotNull('fecha_respuesta_aseguradora')
                ->whereNull('fecha_apelacion')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'Pagado');
                }),

            'tutela' => $query
                ->whereNull('fecha_tutela')
                ->where(function ($q) use ($fechaLimite30Dias) {
                    $q->where(function ($sub) use ($fechaLimite30Dias) {
                        $sub->whereNotNull('fecha_solicitud_aseguradora')
                            ->whereNull('tipo_respuesta_aseguradora')
                            ->whereDate('fecha_solicitud_aseguradora', '<', $fechaLimite30Dias);
                    })->orWhere(function ($sub) {
                        $sub->whereIn('tipo_respuesta_aseguradora', ['nego', 'no_respondio']);
                    })->orWhere(function ($sub) use ($fechaLimite30Dias) {
                        $sub->whereNotNull('fecha_apelacion')
                            ->whereNull('fecha_pago_honorarios')
                            ->whereDate('fecha_apelacion', '<', $fechaLimite30Dias);
                    });
                }),

            'seguimiento_tutela' => $query
                ->whereNotNull('fecha_tutela')
                ->whereNull('fecha_fallo_tutela')
                ->whereDate('fecha_tutela', '<', $fechaLimite30Dias),

            'cumplimiento_tutela' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where('resultado_fallo_tutela', 'concedido')
                ->whereNull('fecha_cumplimiento_tutela')
                ->whereNull('fecha_incidente_desacato')
                ->whereNull('fecha_pago_honorarios')
                ->whereDate('fecha_fallo_tutela', '>=', $fechaLimite14Dias),

            'desacato' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where('resultado_fallo_tutela', 'concedido')
                ->whereNull('fecha_incidente_desacato')
                ->whereNull('fecha_pago_honorarios')
                ->whereNull('fecha_cumplimiento_tutela')
                ->whereDate('fecha_fallo_tutela', '<', $fechaLimite14Dias),

            'impugnacion' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->whereIn('resultado_fallo_tutela', ['negado', 'parcial'])
                ->whereNull('fecha_impugnacion'),

            'segunda_instancia' => $query
                ->whereNotNull('fecha_impugnacion')
                ->whereNull('fecha_fallo_segunda_instancia'),

            'caso_cerrado' => $query
                ->whereNotNull('fecha_fallo_segunda_instancia')
                ->where('resultado_fallo_segunda_instancia', 'confirma'),

            'cumplimiento_segunda_instancia' => $query
                ->whereNotNull('fecha_fallo_segunda_instancia')
                ->where('resultado_fallo_segunda_instancia', 'revoca')
                ->whereNull('fecha_cumplimiento_tutela')
                ->whereNull('fecha_pago_honorarios'),

            'honorarios_junta' => $query
                ->whereNotNull('fecha_apelacion')
                ->whereNull('fecha_pago_honorarios'),

            'alta_ortopedia_pendiente' => $query
                ->whereNotNull('fecha_pago_honorarios')
                ->where('alta_ortopedia', false)
                ->whereNull('fecha_envio_junta'),

            'solicitud_junta' => $query
                ->whereNotNull('fecha_pago_honorarios')
                ->where('alta_ortopedia', true)
                ->whereNull('fecha_envio_junta'),

            'furpen_pendiente' => $query
                ->whereNotNull('fecha_dictamen_junta')
                ->where('furpen_completo', false)
                ->whereNull('fecha_reclamacion_final'),

            'reclamacion' => $query
                ->whereNotNull('fecha_dictamen_junta')
                ->where('furpen_completo', true)
                ->whereNull('fecha_reclamacion_final'),

            'pago_pendiente' => $query
                ->whereNotNull('fecha_reclamacion_final')
                ->whereNull('fecha_pago_final'),

            'queja' => $query
                ->whereNotNull('fecha_reclamacion_final')
                ->whereNull('fecha_pago_final')
                ->whereDate('fecha_reclamacion_final', '<', $fechaLimite30Dias),

            'prescripcion_critica' => $query
                ->whereNotNull('fecha_prescripcion')
                ->whereDate('fecha_prescripcion', '<=', $fechaPrescripcionCritica)
                ->whereDate('fecha_prescripcion', '>=', Carbon::today()->toDateString()),

            'prescrito' => $query
                ->whereNotNull('fecha_prescripcion')
                ->whereDate('fecha_prescripcion', '<', Carbon::today()->toDateString()),

            'pagado' => $query->where(function ($q) {
                $q->where('estado', 'Pagado')->orWhereNotNull('fecha_pago_final');
            }),

            'normal' => $query
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'Pagado');
                })
                ->where(function ($q) {
                    $q->where('tiene_poder', true)->where('tiene_contrato', true);
                })
                ->whereNull('fecha_tutela')
                ->whereNull('fecha_incidente_desacato')
                ->whereNull('fecha_impugnacion')
                ->whereNull('fecha_fallo_segunda_instancia'),

            default => $query,
        };
    }

    // -------------------------------------------------------------------------
    // BOOTED / CÁLCULOS AUTOMÁTICOS
    // -------------------------------------------------------------------------

    protected static function booted()
    {
        static::saving(function ($caso) {
            $valorPagado = is_numeric($caso->valor_pagado) ? (float) $caso->valor_pagado : 0;
            $honorarios  = is_numeric($caso->porcentaje_honorarios) ? (float) $caso->porcentaje_honorarios : 0;

            if ($valorPagado > 0 && $honorarios > 0) {
                $caso->ganancia_equipo    = round($valorPagado * ($honorarios / 100), 2);
                $caso->valor_neto_cliente = round($valorPagado - $caso->ganancia_equipo, 2);
            } else {
                $caso->ganancia_equipo    = 0;
                $caso->valor_neto_cliente = $valorPagado > 0 ? $valorPagado : 0;
            }

            if (!empty($caso->fecha_accidente)) {
                $caso->fecha_prescripcion = Carbon::parse($caso->fecha_accidente)->copy()->addMonths(18);
            }

            $caso->porcentaje_avance = self::calcularAvance($caso);

            if (!empty($caso->fecha_pago_final)) {
                $caso->estado = 'Pagado';
            }
        });
    }

    public static function calcularAvance($caso): int
    {
        $pasos = [
            (bool) $caso->tiene_poder,
            (bool) $caso->tiene_contrato,
            !empty($caso->fecha_solicitud_aseguradora),
            !empty($caso->tipo_respuesta_aseguradora),          // antes: fecha_respuesta
            !empty($caso->fecha_apelacion) || !empty($caso->fecha_tutela),
            !empty($caso->fecha_fallo_tutela),
            !empty($caso->fecha_pago_honorarios),
            (bool) $caso->alta_ortopedia,
            !empty($caso->fecha_envio_junta),
            !empty($caso->fecha_dictamen_junta),
            (bool) $caso->furpen_completo,
            !empty($caso->fecha_reclamacion_final),
            !empty($caso->fecha_pago_final),
        ];

        $completados = count(array_filter($pasos));
        $total       = count($pasos);

        return $total > 0 ? (int) round(($completados / $total) * 100) : 0;
    }
}