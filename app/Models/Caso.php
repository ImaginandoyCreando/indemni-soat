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
        'etapa_actual',
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
        'tipo_respuesta_aseguradora',
        'fecha_apelacion',
        'fecha_tutela',
        'tipo_tutela',
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
        'fecha_cumplimiento_tutela',
        'tipo_cumplimiento_tutela',
        'fecha_impugnacion',
        'fecha_fallo_segunda_instancia',
        'resultado_fallo_segunda_instancia',
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
        'fecha_cumplimiento_tutela'         => 'date',
        'fecha_impugnacion'                 => 'date',
        'fecha_fallo_segunda_instancia'     => 'date',
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
     */
    public function getAlertaValorAttribute()
    {
        if ($this->estaPagado())            return 'pagado';
        if ($this->estaPrescrito())         return 'prescrito';
        if ($this->prescripcionCritica())   return 'prescripcion_critica';
        if ($this->requierePoderContrato()) return 'documentacion_inicial';
        if ($this->requiereFirmaPoder())    return 'poder_pendiente';
        if ($this->requiereFirmaContrato()) return 'contrato_pendiente';

        if ($this->casoCerradoSegundaInstancia()) return 'caso_cerrado';

        if ($this->requiereCumplimientoSegundaInstancia()) return 'cumplimiento_segunda_instancia';

        if ($this->requiereIncidenteDesacato()) return 'desacato';

        if ($this->requiereCumplimientoTutela()) return 'cumplimiento_tutela';

        if ($this->requiereImpugnacion()) return 'impugnacion';

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
        if ($this->tipo_respuesta_aseguradora !== 'emitio_dictamen') return false;
        return !empty($this->fecha_respuesta_aseguradora) && empty($this->fecha_apelacion);
    }

    public function requiereTutela(): bool
    {
        if ($this->estaPagado() || !empty($this->fecha_tutela)) return false;

        if ($this->requiereRespuestaAseguradora()) return true;
        if (in_array($this->tipo_respuesta_aseguradora, ['nego', 'no_respondio']) && empty($this->fecha_tutela)) {
            return true;
        }

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

    public function requiereCumplimientoTutela(): bool
    {
        if ($this->estaPagado()) return false;
        if (empty($this->fecha_fallo_tutela) || $this->resultado_fallo_tutela !== 'concedido') return false;
        if (!empty($this->fecha_cumplimiento_tutela)) return false;
        if (!empty($this->fecha_incidente_desacato)) return false;
        if ($this->cumplioFalloTutela()) return false;

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

    public function requiereSegundaInstancia(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_impugnacion)
            && empty($this->fecha_fallo_segunda_instancia);
    }

    public function requiereCumplimientoSegundaInstancia(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_fallo_segunda_instancia)
            && $this->resultado_fallo_segunda_instancia === 'revoca'
            && empty($this->fecha_cumplimiento_tutela)
            && !$this->cumplioFalloTutela();
    }

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

        $hoy                  = Carbon::today()->toDateString();
        $fechaLimite30Dias    = Carbon::today()->subDays(30)->toDateString();
        $fechaLimite14Dias    = Carbon::today()->subDays(14)->toDateString();
        $fechaPrescripcionMax = Carbon::today()->addDays(90)->toDateString();

        return match ($alerta) {

            // ── DOCUMENTACIÓN ──────────────────────────────────────────────────
            'documentacion_inicial' => $query->where(function ($q) {
                $q->where('tiene_poder', false)->orWhere('tiene_contrato', false);
            }),

            'poder_pendiente' => $query
                ->whereNotNull('fecha_entrega_poder')
                ->whereNull('fecha_poder_firmado'),

            'contrato_pendiente' => $query
                ->whereNotNull('fecha_entrega_contrato')
                ->whereNull('fecha_contrato_firmado'),

            'furpen_pendiente' => $query
                ->whereNotNull('fecha_dictamen_junta')
                ->where('furpen_completo', false)
                ->whereNull('fecha_reclamacion_final'),

            // ── ASEGURADORA ────────────────────────────────────────────────────

            // Solicitud enviada y NO ha respondido aún (sin importar el tiempo)
            'sin_respuesta' => $query
                ->whereNotNull('fecha_solicitud_aseguradora')
                ->whereNull('tipo_respuesta_aseguradora')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            // Aseguradora NEGÓ → acción: presentar tutela para calificación
            'aseguradora_nego' => $query
                ->where('tipo_respuesta_aseguradora', 'nego')
                ->whereNull('fecha_tutela')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            // Aseguradora NO RESPONDIÓ confirmado → acción: presentar tutela
            'aseguradora_no_respondio' => $query
                ->where('tipo_respuesta_aseguradora', 'no_respondio')
                ->whereNull('fecha_tutela')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            // Aseguradora emitió dictamen → acción: manifestar inconformidad o apelar
            'dictamen_aseguradora' => $query
                ->where('tipo_respuesta_aseguradora', 'emitio_dictamen')
                ->whereNull('fecha_apelacion')
                ->whereNull('fecha_tutela')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            // Apelación presentada → esperando respuesta del ente calificador
            'apelar_dictamen' => $query
                ->where('tipo_respuesta_aseguradora', 'emitio_dictamen')
                ->whereNotNull('fecha_respuesta_aseguradora')
                ->whereNull('fecha_apelacion')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'Pagado');
                }),

            // ── TUTELA ─────────────────────────────────────────────────────────

            // Tutela presentada (cualquier tipo) → esperando fallo del juez
            'tutela' => $query
                ->whereNotNull('fecha_tutela')
                ->whereNull('fecha_fallo_tutela')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            // Seguimiento tutela (mismo que tutela — alias para compatibilidad)
            'seguimiento_tutela' => $query
                ->whereNotNull('fecha_tutela')
                ->whereNull('fecha_fallo_tutela')
                ->whereDate('fecha_tutela', '<', $fechaLimite30Dias),

            // Fallo negado → IMPUGNAR en 3 días hábiles (urgente)
            'impugnar_fallo' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where('resultado_fallo_tutela', 'negado')
                ->whereNull('fecha_impugnacion')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            // Fallo concedido → aseguradora debe cumplir (dentro de 14 días)
            'cumplimiento_tutela' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where('resultado_fallo_tutela', 'concedido')
                ->whereNull('fecha_cumplimiento_tutela')
                ->whereNull('fecha_incidente_desacato')
                ->whereNull('fecha_pago_honorarios')
                ->whereDate('fecha_fallo_tutela', '>=', $fechaLimite14Dias),

            // Fallo registrado sin definir resultado (estado intermedio)
            'fallo_tutela_registrado' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where(function ($q) {
                    $q->whereNull('resultado_fallo_tutela')
                      ->orWhere('resultado_fallo_tutela', '');
                })
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            // Tutela cumplida → pendiente dictamen aseguradora o pago de honorarios
            'cumplimiento_segunda_instancia' => $query->where(function ($q) {
                $q->where(function ($sub) {
                    // Tutela calificación cumplida: pendiente dictamen
                    $sub->whereNotNull('fecha_cumplimiento_tutela')
                        ->where('tipo_tutela', 'tutela_calificacion')
                        ->whereNull('fecha_pago_honorarios');
                })->orWhere(function ($sub) {
                    // Tutela debido proceso cumplida: pendiente honorarios
                    $sub->whereNotNull('fecha_cumplimiento_tutela')
                        ->where('tipo_tutela', 'tutela_debido_proceso')
                        ->whereNull('fecha_pago_honorarios');
                })->orWhere(function ($sub) {
                    // Segunda instancia revocó → aseguradora debe cumplir
                    $sub->whereNotNull('fecha_fallo_segunda_instancia')
                        ->where('resultado_fallo_segunda_instancia', 'revoca')
                        ->whereNull('fecha_cumplimiento_tutela')
                        ->whereNull('fecha_pago_honorarios');
                });
            }),

            // ── DESACATO ───────────────────────────────────────────────────────

            // Fallo concedido hace más de 14 días sin cumplimiento → presentar desacato
            'desacato' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where('resultado_fallo_tutela', 'concedido')
                ->whereNull('fecha_incidente_desacato')
                ->whereNull('fecha_pago_honorarios')
                ->whereNull('fecha_cumplimiento_tutela')
                ->whereDate('fecha_fallo_tutela', '<', $fechaLimite14Dias),

            // ── IMPUGNACIÓN ────────────────────────────────────────────────────

            // Fallo negado o parcial → pendiente impugnar
            'impugnacion' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->whereIn('resultado_fallo_tutela', ['negado', 'parcial'])
                ->whereNull('fecha_impugnacion'),

            // ── SEGUNDA INSTANCIA ──────────────────────────────────────────────

            // Impugnación presentada → esperando fallo de segunda instancia
            'segunda_instancia' => $query
                ->whereNotNull('fecha_impugnacion')
                ->whereNull('fecha_fallo_segunda_instancia'),

            // Segunda instancia registrada → actualizar resultado
            'fallo_segunda_instancia' => $query
                ->whereNotNull('fecha_fallo_segunda_instancia')
                ->where(function ($q) {
                    $q->whereNull('resultado_fallo_segunda_instancia')
                      ->orWhere('resultado_fallo_segunda_instancia', '');
                }),

            // Segunda instancia revoca → aseguradora debe CALIFICAR (tutela calificación)
            'segunda_revoca_calificar' => $query
                ->whereNotNull('fecha_fallo_segunda_instancia')
                ->where('resultado_fallo_segunda_instancia', 'revoca')
                ->where('tipo_tutela', 'tutela_calificacion')
                ->whereNull('fecha_pago_honorarios'),

            // Segunda instancia revoca → aseguradora debe PAGAR HONORARIOS (tutela debido proceso)
            'segunda_revoca_honorarios' => $query
                ->whereNotNull('fecha_fallo_segunda_instancia')
                ->where('resultado_fallo_segunda_instancia', 'revoca')
                ->where('tipo_tutela', 'tutela_debido_proceso')
                ->whereNull('fecha_pago_honorarios'),

            // Segunda instancia confirmó → caso cerrado
            'caso_cerrado' => $query
                ->whereNotNull('fecha_fallo_segunda_instancia')
                ->where('resultado_fallo_segunda_instancia', 'confirma'),

            // ── ORTOPEDIA Y JUNTA ──────────────────────────────────────────────

            // Pendiente alta por ortopedia
            'alta_ortopedia_pendiente' => $query
                ->whereNotNull('fecha_pago_honorarios')
                ->where('alta_ortopedia', false)
                ->whereNull('fecha_envio_junta'),

            // Pago de honorarios a junta pendiente (apelaron pero no pagaron honorarios)
            'honorarios_junta' => $query
                ->whereNotNull('fecha_apelacion')
                ->whereNull('fecha_pago_honorarios'),

            // Listo para junta o solicitud ya enviada → seguimiento
            'solicitud_junta' => $query->where(function ($q) {
                $q->where(function ($sub) {
                    // Listo para solicitar (alta ortopedia OK pero no enviado)
                    $sub->whereNotNull('fecha_pago_honorarios')
                        ->where('alta_ortopedia', true)
                        ->whereNull('fecha_envio_junta');
                })->orWhere(function ($sub) {
                    // Ya enviado, esperando dictamen
                    $sub->whereNotNull('fecha_envio_junta')
                        ->whereNull('fecha_dictamen_junta');
                });
            }),

            // Dictamen de junta recibido → pendiente FURPEN para cobrar
            'dictamen_junta' => $query
                ->whereNotNull('fecha_dictamen_junta')
                ->whereNull('fecha_reclamacion_final'),

            // ── COBRO Y PAGO ───────────────────────────────────────────────────

            // Listo para cobrar (dictamen + FURPEN completo) o cobro ya enviado
            'reclamacion' => $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('fecha_dictamen_junta')
                        ->where('furpen_completo', true)
                        ->whereNull('fecha_reclamacion_final');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('fecha_reclamacion_final')
                        ->whereNull('fecha_pago_final');
                });
            }),

            // Cobro enviado → sin pago aún
            'pago_pendiente' => $query
                ->whereNotNull('fecha_reclamacion_final')
                ->whereNull('fecha_pago_final'),

            // Cobro enviado hace más de 30 días sin pago → queja
            'queja' => $query
                ->whereNotNull('fecha_reclamacion_final')
                ->whereNull('fecha_pago_final')
                ->whereDate('fecha_reclamacion_final', '<', $fechaLimite30Dias),

            // ── PRESCRIPCIÓN ───────────────────────────────────────────────────

            // Prescripción en los próximos 90 días
            'prescripcion_critica' => $query
                ->whereNotNull('fecha_prescripcion')
                ->whereDate('fecha_prescripcion', '<=', $fechaPrescripcionMax)
                ->whereDate('fecha_prescripcion', '>=', $hoy),

            // Ya prescrito
            'prescrito' => $query
                ->whereNotNull('fecha_prescripcion')
                ->whereDate('fecha_prescripcion', '<', $hoy),

            // ── ESTADOS FINALES ────────────────────────────────────────────────

            'pagado' => $query->where(function ($q) {
                $q->where('estado', 'Pagado')->orWhereNotNull('fecha_pago_final');
            }),

            'normal' => $query
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'Pagado');
                })
                ->where('tiene_poder', true)
                ->where('tiene_contrato', true)
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
            !empty($caso->tipo_respuesta_aseguradora),
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
