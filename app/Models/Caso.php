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

    // No incluir accessors en $appends para evitar calculos automaticos en serialización
    protected $appends = [];

    // -------------------------------------------------------------------------
    // CACHE INTERNO POR INSTANCIA (memoización)
    // Evita recalcular el mismo valor varias veces por fila en Blade
    // -------------------------------------------------------------------------
    private string  $_alertaValor       = '';
    private ?bool   $_pagado            = null;
    private ?bool   $_prescrito         = null;
    private ?bool   $_prescripcionCrit  = null;
    private ?int    $_diasPrescripcion  = null;
    private array   $_limiteCache       = [];

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
     * Devuelve el codigo de alerta de mayor prioridad para este caso.
     *
     * OPTIMIZACION POSTGRESQL:
     * Cuando se llama desde la vista de lista (index), CasoController inyecta
     * el valor calculado en SQL como columna "alerta_codigo". En ese caso se
     * devuelve directamente sin ningun calculo PHP — esto hace que la lista
     * escale a miles de casos sin degradacion.
     *
     * Cuando se llama desde show/edit (un solo caso), se calcula en PHP como
     * siempre. Tambien se aplica memoizacion para que color_alerta y
     * texto_alerta no recalculen el mismo valor.
     */
    public function getAlertaValorAttribute(): string
    {
        // ── Camino rapido: valor precomputado por PostgreSQL en la query ──
        // CasoController::index() agrega "alerta_codigo" via selectRaw().
        // Para la lista de casos este es el unico camino que se ejecuta.
        if ($this->_alertaValor === ''
            && array_key_exists('alerta_codigo', $this->attributes)
            && $this->attributes['alerta_codigo'] !== null
            && $this->attributes['alerta_codigo'] !== '') {
            $this->_alertaValor = (string) $this->attributes['alerta_codigo'];
            return $this->_alertaValor;
        }

        // ── Memoizacion PHP: evita recalcular en color_alerta / texto_alerta ──
        if ($this->_alertaValor !== '') return $this->_alertaValor;

        // ── Calculo PHP (fallback para show, edit, notificaciones, etc.) ──
        if ($this->estaPagado())            return $this->_alertaValor = 'pagado';
        if ($this->estaPrescrito())         return $this->_alertaValor = 'prescrito';
        if ($this->prescripcionCritica())   return $this->_alertaValor = 'prescripcion_critica';
        if ($this->requierePoderContrato()) return $this->_alertaValor = 'documentacion_inicial';
        if ($this->requiereFirmaPoder())    return $this->_alertaValor = 'poder_pendiente';
        if ($this->requiereFirmaContrato()) return $this->_alertaValor = 'contrato_pendiente';

        if ($this->casoCerradoSegundaInstancia())           return $this->_alertaValor = 'caso_cerrado';
        if ($this->requiereCumplimientoSegundaInstancia())  return $this->_alertaValor = 'cumplimiento_segunda_instancia';
        if ($this->requiereIncidenteDesacato())             return $this->_alertaValor = 'desacato';
        if ($this->requiereCumplimientoTutela())            return $this->_alertaValor = 'cumplimiento_tutela';
        if ($this->requiereImpugnacion())                   return $this->_alertaValor = 'impugnacion';
        if ($this->requiereSegundaInstancia())              return $this->_alertaValor = 'segunda_instancia';
        if ($this->requiereQuejaNoPago())                   return $this->_alertaValor = 'queja';
        if ($this->requiereSeguimientoTutela())             return $this->_alertaValor = 'seguimiento_tutela';
        if ($this->requiereTutela())                        return $this->_alertaValor = 'tutela';
        if ($this->requierePagoPendiente())                 return $this->_alertaValor = 'pago_pendiente';
        if ($this->requiereFurpen())                        return $this->_alertaValor = 'furpen_pendiente';
        if ($this->requiereCobroAseguradora())              return $this->_alertaValor = 'reclamacion';
        if ($this->requiereAltaOrtopedia())                 return $this->_alertaValor = 'alta_ortopedia_pendiente';
        if ($this->requiereSolicitudJunta())                return $this->_alertaValor = 'solicitud_junta';
        if ($this->requierePagoHonorariosJunta())           return $this->_alertaValor = 'honorarios_junta';
        if ($this->requiereApelacion())                     return $this->_alertaValor = 'apelar_dictamen';
        if ($this->requiereRespuestaAseguradora())          return $this->_alertaValor = 'sin_respuesta';

        return $this->_alertaValor = 'normal';
    }

    public function getTextoAlertaAttribute()
    {
        return match ($this->alerta_valor) {
            'pagado'                        => 'Pagado',
            'prescrito'                     => 'Caso prescrito',
            'prescripcion_critica'          => 'Prescripcion proxima',
            'documentacion_inicial'         => 'Falta poder / contrato',
            'poder_pendiente'               => 'Poder pendiente',
            'contrato_pendiente'            => 'Contrato pendiente',
            'caso_cerrado'                  => 'Caso cerrado - segunda instancia',
            'cumplimiento_segunda_instancia'=> 'Cumplimiento segunda instancia',
            'desacato'                      => 'Incidente de desacato',
            'cumplimiento_tutela'           => 'Esperando cumplimiento tutela',
            'impugnacion'                   => 'Impugnacion pendiente',
            'segunda_instancia'             => 'Pendiente segunda instancia',
            'queja'                         => 'Queja por no pago',
            'seguimiento_tutela'            => 'Seguimiento tutela',
            'tutela'                        => 'Critica / tutela',
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
        if ($this->_pagado !== null) return $this->_pagado;
        return $this->_pagado = (($this->estado ?? '') === 'Pagado' || !empty($this->fecha_pago_final));
    }

    public function estaPrescrito(): bool
    {
        if ($this->_prescrito !== null) return $this->_prescrito;
        if (empty($this->fecha_prescripcion)) return $this->_prescrito = false;
        return $this->_prescrito = Carbon::today()->gt(Carbon::parse($this->fecha_prescripcion));
    }

    public function diasParaPrescripcion(): ?int
    {
        if ($this->_diasPrescripcion !== null) return $this->_diasPrescripcion;
        if (empty($this->fecha_prescripcion)) return $this->_diasPrescripcion = 0;
        return $this->_diasPrescripcion = Carbon::today()->diffInDays(
            Carbon::parse($this->fecha_prescripcion), false
        );
    }

    public function prescripcionCritica(): bool
    {
        if ($this->_prescripcionCrit !== null) return $this->_prescripcionCrit;
        if ($this->estaPagado() || $this->estaPrescrito() || empty($this->fecha_prescripcion)) {
            return $this->_prescripcionCrit = false;
        }
        $dias = $this->diasParaPrescripcion();
        return $this->_prescripcionCrit = ($dias !== null && $dias <= 90);
    }

    // -------------------------------------------------------------------------
    // METODOS REQUIERE* — logica de alertas
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
        $limite = $this->_getLimite('respuesta_aseguradora', $this->fecha_solicitud_aseguradora, 30);
        return $limite ? Carbon::today()->gt($limite) : false;
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
            $limite = $this->_getLimite('tutela_apelacion', $this->fecha_apelacion, 30);
            return Carbon::today()->gt($limite);
        }

        return false;
    }

    public function requiereSeguimientoTutela(): bool
    {
        if ($this->estaPagado() || empty($this->fecha_tutela) || !empty($this->fecha_fallo_tutela)) return false;
        $limite = $this->_getLimite('seguimiento_tutela', $this->fecha_tutela, 30);
        return $limite ? Carbon::today()->gt($limite) : false;
    }

    public function requiereCumplimientoTutela(): bool
    {
        if ($this->estaPagado()) return false;
        if (empty($this->fecha_fallo_tutela) || $this->resultado_fallo_tutela !== 'concedido') return false;
        if (!empty($this->fecha_cumplimiento_tutela) || !empty($this->fecha_incidente_desacato)) return false;
        if ($this->cumplioFalloTutela()) return false;
        $limite = $this->_getLimite('cumplimiento_fallo', $this->fecha_fallo_tutela, 14);
        return $limite ? !Carbon::today()->gt($limite) : true;
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
        return !empty($this->fecha_impugnacion) && empty($this->fecha_fallo_segunda_instancia);
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
        if (!empty($this->fecha_incidente_desacato) || $this->cumplioFalloTutela()) return false;
        if (!empty($this->fecha_fallo_segunda_instancia)
            && $this->resultado_fallo_segunda_instancia === 'revoca'
            && !$this->cumplioFalloTutela()) {
            return true;
        }
        $limite = $this->_getLimite('cumplimiento_fallo', $this->fecha_fallo_tutela, 14);
        return $limite ? Carbon::today()->gt($limite) : false;
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
        return !empty($this->fecha_pago_honorarios) && !$this->alta_ortopedia && empty($this->fecha_envio_junta);
    }

    public function requiereSolicitudJunta(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_pago_honorarios) && $this->alta_ortopedia && empty($this->fecha_envio_junta);
    }

    public function requiereCobroAseguradora(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_dictamen_junta) && $this->furpen_completo && empty($this->fecha_reclamacion_final);
    }

    public function requiereFurpen(): bool
    {
        if ($this->estaPagado()) return false;
        return !empty($this->fecha_dictamen_junta) && !$this->furpen_completo && empty($this->fecha_reclamacion_final);
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
        $limite = $this->_getLimite('pago_final', $this->fecha_reclamacion_final, 30);
        return $limite ? Carbon::today()->gt($limite) : false;
    }

    // -------------------------------------------------------------------------
    // HELPER PRIVADO: cache de fechas limite para evitar Carbon::parse repetidos
    // -------------------------------------------------------------------------

    private function _getLimite(string $key, $fecha, int $dias): ?Carbon
    {
        if (array_key_exists($key, $this->_limiteCache)) return $this->_limiteCache[$key];
        $this->_limiteCache[$key] = empty($fecha)
            ? null
            : Carbon::parse($fecha)->copy()->addDays($dias);
        return $this->_limiteCache[$key];
    }

    // -------------------------------------------------------------------------
    // FECHA LIMITE ATTRIBUTES (compatibilidad con vistas existentes)
    // -------------------------------------------------------------------------

    public function getFechaLimiteRespuestaAseguradoraAttribute()
    {
        return $this->_getLimite('respuesta_aseguradora', $this->fecha_solicitud_aseguradora, 30);
    }

    public function getFechaLimitePagoFinalAttribute()
    {
        return $this->_getLimite('pago_final', $this->fecha_reclamacion_final, 30);
    }

    public function getFechaLimiteSeguimientoTutelaAttribute()
    {
        return $this->_getLimite('seguimiento_tutela', $this->fecha_tutela, 30);
    }

    public function getFechaLimiteCumplimientoFalloAttribute()
    {
        return $this->_getLimite('cumplimiento_fallo', $this->fecha_fallo_tutela, 14);
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

            'sin_respuesta' => $query
                ->whereNotNull('fecha_solicitud_aseguradora')
                ->whereNull('tipo_respuesta_aseguradora')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            'aseguradora_nego' => $query
                ->where('tipo_respuesta_aseguradora', 'nego')
                ->whereNull('fecha_tutela')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            'aseguradora_no_respondio' => $query
                ->where('tipo_respuesta_aseguradora', 'no_respondio')
                ->whereNull('fecha_tutela')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            'dictamen_aseguradora' => $query
                ->where('tipo_respuesta_aseguradora', 'emitio_dictamen')
                ->whereNull('fecha_apelacion')
                ->whereNull('fecha_tutela')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            'apelar_dictamen' => $query
                ->where('tipo_respuesta_aseguradora', 'emitio_dictamen')
                ->whereNotNull('fecha_respuesta_aseguradora')
                ->whereNull('fecha_apelacion')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'Pagado');
                }),

            'tutela' => $query
                ->whereNotNull('fecha_tutela')
                ->whereNull('fecha_fallo_tutela')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            'seguimiento_tutela' => $query
                ->whereNotNull('fecha_tutela')
                ->whereNull('fecha_fallo_tutela')
                ->whereDate('fecha_tutela', '<', $fechaLimite30Dias),

            'impugnar_fallo' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where('resultado_fallo_tutela', 'negado')
                ->whereNull('fecha_impugnacion')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            'cumplimiento_tutela' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where('resultado_fallo_tutela', 'concedido')
                ->whereNull('fecha_cumplimiento_tutela')
                ->whereNull('fecha_incidente_desacato')
                ->whereDate('fecha_fallo_tutela', '>=', $fechaLimite14Dias),

            'desacato' => $query
                ->whereNotNull('fecha_fallo_tutela')
                ->where('resultado_fallo_tutela', 'concedido')
                ->whereNull('fecha_incidente_desacato')
                ->whereNull('fecha_cumplimiento_tutela')
                ->whereDate('fecha_fallo_tutela', '<', $fechaLimite14Dias),

            'prescripcion_critica' => $query
                ->whereNotNull('fecha_prescripcion')
                ->whereDate('fecha_prescripcion', '>=', $hoy)
                ->whereDate('fecha_prescripcion', '<=', $fechaPrescripcionMax)
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhereNotIn('estado', ['Pagado', 'Cerrado']);
                }),

            'prescrito' => $query
                ->whereNotNull('fecha_prescripcion')
                ->whereDate('fecha_prescripcion', '<', $hoy),

            'pago_pendiente' => $query
                ->whereNotNull('fecha_reclamacion_final')
                ->whereNull('fecha_pago_final')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'Pagado');
                }),

            'queja' => $query
                ->whereNotNull('fecha_reclamacion_final')
                ->whereNull('fecha_pago_final')
                ->whereDate('fecha_reclamacion_final', '<', $fechaLimite30Dias),

            'reclamacion' => $query
                ->whereNotNull('fecha_dictamen_junta')
                ->where('furpen_completo', true)
                ->whereNull('fecha_reclamacion_final'),

            'solicitud_junta' => $query
                ->whereNotNull('fecha_pago_honorarios')
                ->where('alta_ortopedia', true)
                ->whereNull('fecha_envio_junta'),

            'honorarios_junta' => $query
                ->whereNotNull('fecha_apelacion')
                ->whereNull('fecha_pago_honorarios'),

            'pagado' => $query
                ->where(function ($q) {
                    $q->where('estado', 'Pagado')->orWhereNotNull('fecha_pago_final');
                }),

            'fallo_segunda_instancia' => $query
                ->whereNotNull('fecha_fallo_segunda_instancia'),

            'seguimiento_tutela' => $query
                ->whereNotNull('fecha_tutela')
                ->whereNull('fecha_fallo_tutela')
                ->whereDate('fecha_tutela', '<', $fechaLimite30Dias),

            'fallo_segunda_instancia' => $query
                ->whereNotNull('fecha_fallo_segunda_instancia'),

            default => $query,
        };
    }

    // -------------------------------------------------------------------------
    // BOOTED (porcentaje_avance calculado al guardar)
    // -------------------------------------------------------------------------

    protected static function booted()
    {
        static::saving(function ($caso) {
            $campos = [
                'fecha_accidente', 'fecha_solicitud_aseguradora', 'fecha_respuesta_aseguradora',
                'fecha_apelacion', 'fecha_tutela', 'fecha_pago_honorarios', 'fecha_envio_junta',
                'fecha_dictamen_junta', 'fecha_reclamacion_final', 'fecha_pago_final',
                'tiene_poder', 'tiene_contrato', 'alta_ortopedia', 'furpen_completo',
                'fecha_fallo_tutela', 'fecha_cumplimiento_tutela', 'fecha_incidente_desacato',
                'fecha_impugnacion', 'fecha_fallo_segunda_instancia',
            ];

            $total    = count($campos);
            $rellenos = 0;

            foreach ($campos as $campo) {
                $valor = $caso->$campo ?? null;
                if ($valor !== null && $valor !== '' && $valor !== false) {
                    $rellenos++;
                }
            }

            $caso->porcentaje_avance = $total > 0 ? (int) round(($rellenos / $total) * 100) : 0;
        });
    }
}
