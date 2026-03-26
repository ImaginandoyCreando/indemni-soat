@extends('layouts.app')

@section('title', 'Detalle del Caso')

@section('content')

<style>
/* ── Cards de detalle ── */
.is-detail-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 16px;
    transition: background .3s, border-color .3s;
}
.is-detail-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--text-3);
    letter-spacing: .7px;
    text-transform: uppercase;
    margin-bottom: 5px;
}
.is-detail-value {
    font-size: 14px;
    color: var(--text-1);
    line-height: 1.4;
    transition: color .3s;
}
.is-detail-value.money {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    color: #D4AA48;
    font-size: 15px;
}
.is-detail-value.money-green {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    color: #1DBD7F;
    font-size: 15px;
}
.is-detail-value.money-blue {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    color: #4B78FF;
    font-size: 15px;
}

/* ── Sección title ── */
.is-section-heading {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 700;
    color: var(--text-1);
    margin: 28px 0 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
    transition: color .3s;
}

/* ── Progreso ── */
.is-progress-track {
    width: 100%;
    height: 10px;
    background: var(--border-2);
    border-radius: 999px;
    overflow: hidden;
    margin-top: 6px;
}
.is-progress-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #059669, #1DBD7F);
    transition: width .6s ease;
}

/* ── Timeline ── */
.is-timeline {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 8px;
}
.is-tl-step {
    border-radius: 8px;
    padding: 13px 14px;
    border: 1px solid var(--border-2);
    background: var(--bg-card);
    position: relative;
    transition: background .3s, border-color .3s;
}
.is-tl-step.tl-done    {
    background: rgba(5,150,105,0.07);
    border-color: rgba(5,150,105,0.22);
}
.is-tl-step.tl-warn    {
    background: rgba(245,158,11,0.07);
    border-color: rgba(245,158,11,0.22);
}
.is-tl-step.tl-danger  {
    background: rgba(229,57,53,0.07);
    border-color: rgba(229,57,53,0.22);
}
.is-tl-step.tl-closed  {
    background: var(--bg-input);
    border-color: var(--border);
    opacity: .75;
}
.is-tl-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-1);
    margin-bottom: 5px;
    transition: color .3s;
}
.is-tl-date {
    font-size: 11px;
    color: var(--text-2);
}
.is-tl-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 6px;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
}

/* ── Flujo resumen dots ── */
.is-flujo-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    padding: 14px 18px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 8px;
    margin-bottom: 20px;
    transition: background .3s;
}
.is-flujo-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 500;
    color: var(--text-2);
    white-space: nowrap;
}
.is-flujo-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.fd-done    { background: #059669; box-shadow: 0 0 5px rgba(5,150,105,.5); }
.fd-pending { background: var(--border-3); }
.fd-warn    { background: #F59E0B; }
.fd-danger  { background: #E53935; }
</style>

@php
    $alertBadgeClass = match($caso->color_alerta) {
        'red'         => 'is-badge is-badge-red',
        'orange'      => 'is-badge is-badge-amber',
        'cyan','blue' => 'is-badge is-badge-teal',
        'green'       => 'is-badge is-badge-green',
        default       => 'is-badge is-badge-gray',
    };

    $fmt = fn($f) => $f ? \Carbon\Carbon::parse($f)->format('d/m/Y') : null;

    $fechaAccidente      = $fmt($caso->fecha_accidente)             ?? 'No registrada';
    $fechaSolicitud      = $fmt($caso->fecha_solicitud_aseguradora) ?? 'Pendiente';
    $fechaRespuesta      = $fmt($caso->fecha_respuesta_aseguradora) ?? 'Pendiente';
    $fechaTutela         = $fmt($caso->fecha_tutela)                ?? 'Pendiente';
    $fechaFalloTutela    = $fmt($caso->fecha_fallo_tutela)          ?? 'Pendiente';
    $fechaCumplimiento   = $fmt($caso->fecha_cumplimiento_tutela)   ?? 'Pendiente';
    $fechaDesacato       = $fmt($caso->fecha_incidente_desacato)    ?? 'Pendiente';
    $fechaImpugnacion    = $fmt($caso->fecha_impugnacion)           ?? 'Pendiente';
    $fechaSegunda        = $fmt($caso->fecha_fallo_segunda_instancia)?? 'Pendiente';
    $fechaApelacion      = $fmt($caso->fecha_apelacion)             ?? 'Pendiente';
    $fechaPagoHonorarios = $fmt($caso->fecha_pago_honorarios)       ?? 'Pendiente';
    $fechaAltaOrtopedia  = $fmt($caso->fecha_alta_ortopedia)        ?? 'Pendiente';
    $fechaEnvioJunta     = $fmt($caso->fecha_envio_junta)           ?? 'Pendiente';
    $fechaDictamenJunta  = $fmt($caso->fecha_dictamen_junta)        ?? 'Pendiente';
    $fechaFurpen         = $fmt($caso->fecha_furpen_recibido)       ?? 'Pendiente';
    $fechaReclamacion    = $fmt($caso->fecha_reclamacion_final)     ?? 'Pendiente';
    $fechaPagoFinal      = $fmt($caso->fecha_pago_final)            ?? 'Pendiente';
    $fechaPrescripcion   = $fmt($caso->fecha_prescripcion)          ?? 'No calculada';

    $diasPrescripcion = $caso->diasParaPrescripcion();

    $textoTipoRespuesta = match($caso->tipo_respuesta_aseguradora ?? '') {
        'emitio_dictamen' => 'Emitió dictamen',
        'nego'            => 'Negó la solicitud',
        'no_respondio'    => 'No respondió',
        default           => 'Pendiente',
    };
    $textoTipoTutela = match($caso->tipo_tutela ?? '') {
        'tutela_calificacion'   => 'Para calificación',
        'tutela_debido_proceso' => 'Por debido proceso',
        default                 => 'Pendiente',
    };
    $textoResultadoFallo = match($caso->resultado_fallo_tutela ?? '') {
        'concedido' => 'Concedido',
        'negado'    => 'Negado',
        'parcial'   => 'Parcial',
        default     => 'Pendiente',
    };
    $textoTipoCumplimiento = match($caso->tipo_cumplimiento_tutela ?? '') {
        'voluntario' => 'Voluntario',
        'desacato'   => 'Tras desacato',
        default      => 'Pendiente',
    };
    $textoResultadoSegunda = match($caso->resultado_fallo_segunda_instancia ?? '') {
        'confirma' => 'Confirma — caso cerrado',
        'revoca'   => 'Revoca — aseguradora debe cumplir',
        default    => 'Pendiente',
    };
@endphp

{{-- ── Cabecera ── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:flex-start;justify-content:space-between;
            margin-bottom:24px;gap:16px;flex-wrap:wrap;">
    <div>
        <div class="is-page-title">Detalle del Caso</div>
        <div style="display:flex;align-items:center;gap:10px;margin-top:6px;flex-wrap:wrap;">
            <span style="font-family:'Playfair Display',serif;font-size:16px;
                         font-weight:700;color:#4B78FF;">
                {{ $caso->numero_caso }}
            </span>
            <span class="{{ $alertBadgeClass }}" style="font-size:11px;">
                {{ $caso->texto_alerta }}
            </span>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @if(auth()->user()->puedeEditar())
            <a href="{{ route('casos.edit', $caso) }}" class="is-btn-primary">
                ✏️ Editar
            </a>
        @endif
        <a href="{{ route('casos.documentos.index', $caso) }}" class="is-btn-ghost">
            Expediente
        </a>
        <a href="{{ route('casos.bitacoras.index', $caso) }}" class="is-btn-ghost">
            Bitácora
        </a>
        <a href="{{ route('casos.index') }}" class="is-btn-ghost">
            ← Volver
        </a>
    </div>
</div>

{{-- ── Barra de estado + progreso ── --}}
<div class="is-animate-rise is-stagger-1"
     style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;
            gap:12px;margin-bottom:20px;">
    <div class="is-stat-card" style="--stat-accent:#1B4FFF;">
        <div class="is-stat-label">Estado actual</div>
        <div style="font-size:14px;font-weight:600;color:var(--text-1);margin-top:4px;">
            {{ $caso->estado ?: 'N/A' }}
        </div>
    </div>
    <div class="is-stat-card" style="--stat-accent:#059669;">
        <div class="is-stat-label">Avance del caso</div>
        <div class="is-stat-value" style="color:#1DBD7F;">
            {{ $caso->porcentaje_avance ?? 0 }}%
        </div>
        <div class="is-progress-track" style="margin-top:8px;">
            <div class="is-progress-fill"
                 style="width:{{ $caso->porcentaje_avance ?? 0 }}%;">
            </div>
        </div>
    </div>
    <div class="is-stat-card" style="--stat-accent:#D4AA48;">
        <div class="is-stat-label">Valor estimado</div>
        <div class="is-stat-value"
             style="font-size:18px;color:#D4AA48;">
            {{ $caso->valor_estimado
                ? '$'.number_format($caso->valor_estimado,0,',','.')
                : '—' }}
        </div>
    </div>
    <div class="is-stat-card"
         style="--stat-accent:{{ $diasPrescripcion !== null && $diasPrescripcion <= 90 ? '#E53935' : '#4B78FF' }};">
        <div class="is-stat-label">Días para prescripción</div>
        <div class="is-stat-value"
             style="color:{{ $diasPrescripcion !== null && $diasPrescripcion <= 90 ? '#F26F6F' : 'var(--text-1)' }};font-size:18px;">
            @if($diasPrescripcion !== null)
                @if($diasPrescripcion < 0)
                    <span style="color:#F26F6F;">
                        Prescrito hace {{ abs($diasPrescripcion) }}d
                    </span>
                @elseif($diasPrescripcion <= 90)
                    {{ $diasPrescripcion }}d ⚠️
                @else
                    {{ $diasPrescripcion }}d
                @endif
            @else
                N/A
            @endif
        </div>
    </div>
</div>

{{-- ── Barra de flujo (dots) ── --}}
<div class="is-flujo-bar is-animate-rise is-stagger-1">
    @php
        $flujoItems = [
            ['label' => 'Solicitud',   'val' => $caso->fecha_solicitud_aseguradora,    'type' => 'done'],
            ['label' => 'Respuesta',   'val' => $caso->tipo_respuesta_aseguradora,     'type' => 'done'],
            ['label' => 'Apelación',   'val' => $caso->fecha_apelacion,               'type' => 'done'],
            ['label' => 'Tutela',      'val' => $caso->fecha_tutela,                  'type' => 'done'],
            ['label' => 'Fallo',       'val' => $caso->fecha_fallo_tutela,            'type' => 'done'],
            ['label' => 'Cumplimiento','val' => $caso->fecha_cumplimiento_tutela,     'type' => 'done'],
            ['label' => 'Desacato',    'val' => $caso->fecha_incidente_desacato,      'type' => 'warn'],
            ['label' => 'Impugnación', 'val' => $caso->fecha_impugnacion,             'type' => 'warn'],
            ['label' => '2ª Inst.',    'val' => $caso->fecha_fallo_segunda_instancia,
                'type' => ($caso->resultado_fallo_segunda_instancia === 'confirma' ? 'danger' : 'done')],
            ['label' => 'Honorarios',  'val' => $caso->fecha_pago_honorarios,         'type' => 'done'],
            ['label' => 'Alta ortop.', 'val' => $caso->alta_ortopedia,               'type' => 'done'],
            ['label' => 'Junta',       'val' => $caso->fecha_envio_junta,             'type' => 'done'],
            ['label' => 'Dictamen',    'val' => $caso->fecha_dictamen_junta,          'type' => 'done'],
            ['label' => 'FURPEN',      'val' => $caso->furpen_completo,              'type' => 'done'],
            ['label' => 'Reclamación', 'val' => $caso->fecha_reclamacion_final,       'type' => 'done'],
            ['label' => 'Pago final',  'val' => $caso->fecha_pago_final,             'type' => 'done'],
        ];
    @endphp
    @foreach($flujoItems as $fi)
        <div class="is-flujo-item">
            <div class="is-flujo-dot {{ $fi['val'] ? 'fd-'.$fi['type'] : 'fd-pending' }}"></div>
            {{ $fi['label'] }}
        </div>
    @endforeach
</div>

{{-- ════════════════════════════════════════════
     TIMELINE — Flujo jurídico
════════════════════════════════════════════ --}}
<div class="is-section-heading is-animate-rise is-stagger-2">
    Flujo jurídico del caso
</div>

<div class="is-timeline is-animate-rise is-stagger-2">

    <div class="is-tl-step {{ $caso->fecha_solicitud_aseguradora ? 'tl-done' : '' }}">
        <div class="is-tl-title">1. Solicitud a aseguradora</div>
        <div class="is-tl-date">{{ $fechaSolicitud }}</div>
    </div>

    <div class="is-tl-step {{ $caso->tipo_respuesta_aseguradora ? 'tl-done' : '' }}">
        <div class="is-tl-title">2. Respuesta aseguradora</div>
        <div class="is-tl-date">{{ $fechaRespuesta }}</div>
        @if($caso->tipo_respuesta_aseguradora)
            @php
                $respClass = match($caso->tipo_respuesta_aseguradora) {
                    'emitio_dictamen' => 'is-badge-green',
                    'nego'            => 'is-badge-red',
                    'no_respondio'    => 'is-badge-amber',
                    default           => 'is-badge-gray',
                };
            @endphp
            <span class="is-tl-badge is-badge {{ $respClass }}">
                {{ $textoTipoRespuesta }}
            </span>
        @endif
    </div>

    <div class="is-tl-step {{ $caso->fecha_apelacion ? 'tl-done' : '' }}">
        <div class="is-tl-title">3. Apelación del dictamen</div>
        <div class="is-tl-date">{{ $fechaApelacion }}</div>
        <div class="is-field-hint" style="margin-top:5px;">
            Solo si la aseguradora emitió dictamen.
        </div>
    </div>

    <div class="is-tl-step {{ $caso->fecha_tutela ? 'tl-done' : '' }}">
        <div class="is-tl-title">4. Tutela</div>
        <div class="is-tl-date">{{ $fechaTutela }}</div>
        @if($caso->tipo_tutela)
            @php
                $tutelaClass = $caso->tipo_tutela === 'tutela_calificacion'
                    ? 'is-badge-teal' : 'is-badge-cobalt';
            @endphp
            <span class="is-tl-badge is-badge {{ $tutelaClass }}">
                {{ $textoTipoTutela }}
            </span>
        @endif
    </div>

    <div class="is-tl-step {{ $caso->fecha_fallo_tutela
        ? ($caso->resultado_fallo_tutela === 'negado' ? 'tl-danger' : 'tl-done')
        : '' }}">
        <div class="is-tl-title">5. Fallo de tutela</div>
        <div class="is-tl-date">{{ $fechaFalloTutela }}</div>
        @if($caso->resultado_fallo_tutela)
            @php
                $falloClass = match($caso->resultado_fallo_tutela) {
                    'concedido' => 'is-badge-green',
                    'negado'    => 'is-badge-red',
                    'parcial'   => 'is-badge-amber',
                    default     => 'is-badge-gray',
                };
            @endphp
            <span class="is-tl-badge is-badge {{ $falloClass }}">
                {{ $textoResultadoFallo }}
            </span>
        @endif
    </div>

    <div class="is-tl-step {{ $caso->fecha_cumplimiento_tutela ? 'tl-done' : '' }}">
        <div class="is-tl-title">5A. Cumplimiento del fallo</div>
        <div class="is-tl-date">{{ $fechaCumplimiento }}</div>
        @if($caso->tipo_cumplimiento_tutela)
            @php
                $cumplClass = $caso->tipo_cumplimiento_tutela === 'voluntario'
                    ? 'is-badge-green' : 'is-badge-amber';
            @endphp
            <span class="is-tl-badge is-badge {{ $cumplClass }}">
                {{ $textoTipoCumplimiento }}
            </span>
        @endif
    </div>

    <div class="is-tl-step {{ $caso->fecha_incidente_desacato ? 'tl-warn' : '' }}">
        <div class="is-tl-title">5B. Incidente de desacato</div>
        <div class="is-tl-date">{{ $fechaDesacato }}</div>
        <div class="is-field-hint" style="margin-top:5px;">
            Fallo favorable pero no cumplió en 14 días.
        </div>
    </div>

    <div class="is-tl-step {{ $caso->fecha_impugnacion ? 'tl-warn' : '' }}">
        <div class="is-tl-title">5C. Impugnación</div>
        <div class="is-tl-date">{{ $fechaImpugnacion }}</div>
        <div class="is-field-hint" style="margin-top:5px;">
            Cuando el fallo fue negado o parcial.
        </div>
    </div>

    <div class="is-tl-step {{ $caso->fecha_fallo_segunda_instancia
        ? ($caso->resultado_fallo_segunda_instancia === 'confirma'
            ? 'tl-closed' : 'tl-done')
        : '' }}">
        <div class="is-tl-title">5D. Segunda instancia</div>
        <div class="is-tl-date">{{ $fechaSegunda }}</div>
        @if($caso->resultado_fallo_segunda_instancia)
            @php
                $segundaClass = $caso->resultado_fallo_segunda_instancia === 'revoca'
                    ? 'is-badge-green' : 'is-badge-red';
            @endphp
            <span class="is-tl-badge is-badge {{ $segundaClass }}">
                {{ $textoResultadoSegunda }}
            </span>
        @endif
    </div>

    <div class="is-tl-step {{ $caso->fecha_pago_honorarios ? 'tl-done' : '' }}">
        <div class="is-tl-title">6. Pago honorarios junta</div>
        <div class="is-tl-date">{{ $fechaPagoHonorarios }}</div>
    </div>

    <div class="is-tl-step {{ $caso->alta_ortopedia ? 'tl-done' : '' }}">
        <div class="is-tl-title">7. Alta por ortopedia</div>
        <div class="is-tl-date">{{ $fechaAltaOrtopedia }}</div>
        <span class="is-tl-badge is-badge {{ $caso->alta_ortopedia
            ? 'is-badge-green' : 'is-badge-gray' }}">
            {{ $caso->alta_ortopedia ? 'Confirmada' : 'Pendiente' }}
        </span>
    </div>

    <div class="is-tl-step {{ $caso->fecha_envio_junta ? 'tl-done' : '' }}">
        <div class="is-tl-title">8. Solicitud a junta</div>
        <div class="is-tl-date">{{ $fechaEnvioJunta }}</div>
        <div class="is-field-hint" style="margin-top:5px;">
            Requiere honorarios + alta ortopedia.
        </div>
    </div>

    <div class="is-tl-step {{ $caso->fecha_dictamen_junta ? 'tl-done' : '' }}">
        <div class="is-tl-title">9. Dictamen de junta</div>
        <div class="is-tl-date">{{ $fechaDictamenJunta }}</div>
    </div>

    <div class="is-tl-step {{ $caso->furpen_completo ? 'tl-done' : '' }}">
        <div class="is-tl-title">10. FURPEN</div>
        <div class="is-tl-date">{{ $fechaFurpen }}</div>
        <span class="is-tl-badge is-badge {{ $caso->furpen_completo
            ? 'is-badge-green' : 'is-badge-gray' }}">
            {{ $caso->furpen_completo ? 'Completo' : 'Pendiente' }}
        </span>
    </div>

    <div class="is-tl-step {{ $caso->fecha_reclamacion_final ? 'tl-done' : '' }}">
        <div class="is-tl-title">11. Cobro a aseguradora</div>
        <div class="is-tl-date">{{ $fechaReclamacion }}</div>
    </div>

    <div class="is-tl-step {{ $caso->fecha_pago_final ? 'tl-done' : '' }}">
        <div class="is-tl-title">12. Pago final</div>
        <div class="is-tl-date">{{ $fechaPagoFinal }}</div>
        @if($caso->fecha_pago_final)
            <span class="is-tl-badge is-badge is-badge-green">✓ Pagado</span>
        @endif
    </div>

</div>

{{-- ════════════════════════════════════════════
     INFORMACIÓN GENERAL
════════════════════════════════════════════ --}}
<div class="is-section-heading is-animate-rise is-stagger-3">
    Información general de la víctima
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-detail-card">
        <div class="is-detail-label">Víctima</div>
        <div class="is-detail-value"
             style="font-size:16px;font-weight:600;">
            {{ $caso->nombres }} {{ $caso->apellidos }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Cédula</div>
        <div class="is-detail-value">{{ $caso->cedula }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Teléfono</div>
        <div class="is-detail-value">
            {{ $caso->telefono ?: '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Correo electrónico</div>
        <div class="is-detail-value">
            {{ $caso->correo ?: '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Departamento</div>
        <div class="is-detail-value">
            {{ $caso->departamento ?: '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Ciudad / Municipio</div>
        <div class="is-detail-value">
            {{ $caso->ciudad ?: '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Dirección</div>
        <div class="is-detail-value">
            {{ $caso->direccion ?: '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha del accidente</div>
        <div class="is-detail-value">{{ $fechaAccidente }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Aseguradora</div>
        <div class="is-detail-value"
             style="font-weight:600;">
            {{ $caso->aseguradora ?: '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Junta asignada</div>
        <div class="is-detail-value">
            {{ $caso->junta_asignada ?: '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Porcentaje PCL</div>
        <div class="is-detail-value">
            @if($caso->porcentaje_pcl)
                <div class="is-pcl-wrap">
                    {{ $caso->porcentaje_pcl }}%
                    <div class="is-pcl-track" style="width:80px;">
                        <div class="is-pcl-fill"
                             style="width:{{ min($caso->porcentaje_pcl,100) }}%;
                                    background:#1B4FFF;">
                        </div>
                    </div>
                </div>
            @else
                —
            @endif
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Estado</div>
        <div class="is-detail-value">
            <span class="is-badge is-badge-cobalt">
                {{ $caso->estado ?: 'N/A' }}
            </span>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     DOCUMENTACIÓN INICIAL
════════════════════════════════════════════ --}}
<div class="is-section-heading is-animate-rise is-stagger-3">
    Documentación inicial
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-detail-card">
        <div class="is-detail-label">Poder firmado</div>
        <div class="is-detail-value">
            <span class="is-badge {{ $caso->tiene_poder ? 'is-badge-green' : 'is-badge-amber' }}">
                {{ $caso->tiene_poder ? 'Sí ✓' : 'Pendiente' }}
            </span>
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha entrega poder</div>
        <div class="is-detail-value">
            {{ $fmt($caso->fecha_entrega_poder) ?? '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha poder firmado</div>
        <div class="is-detail-value">
            {{ $fmt($caso->fecha_poder_firmado) ?? 'Pendiente' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Contrato firmado</div>
        <div class="is-detail-value">
            <span class="is-badge {{ $caso->tiene_contrato ? 'is-badge-green' : 'is-badge-amber' }}">
                {{ $caso->tiene_contrato ? 'Sí ✓' : 'Pendiente' }}
            </span>
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha entrega contrato</div>
        <div class="is-detail-value">
            {{ $fmt($caso->fecha_entrega_contrato) ?? '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha contrato firmado</div>
        <div class="is-detail-value">
            {{ $fmt($caso->fecha_contrato_firmado) ?? 'Pendiente' }}
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     REQUISITOS MÉDICOS
════════════════════════════════════════════ --}}
<div class="is-section-heading is-animate-rise is-stagger-3">
    Requisitos médicos y soportes
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-detail-card">
        <div class="is-detail-label">Alta por ortopedia</div>
        <div class="is-detail-value">
            <span class="is-badge {{ $caso->alta_ortopedia
                ? 'is-badge-green' : 'is-badge-gray' }}">
                {{ $caso->alta_ortopedia ? 'Confirmada ✓' : 'Pendiente' }}
            </span>
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha alta ortopedia</div>
        <div class="is-detail-value">{{ $fechaAltaOrtopedia }}</div>
    </div>
    <div class="is-detail-card"
         style="grid-column:1/-1;">
        <div class="is-detail-label">Observación alta ortopedia</div>
        <div class="is-detail-value">
            {{ $caso->observacion_alta_ortopedia ?: 'Sin observación' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">FURPEN completo</div>
        <div class="is-detail-value">
            <span class="is-badge {{ $caso->furpen_completo
                ? 'is-badge-green' : 'is-badge-gray' }}">
                {{ $caso->furpen_completo ? 'Completo ✓' : 'Pendiente' }}
            </span>
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha FURPEN recibido</div>
        <div class="is-detail-value">{{ $fechaFurpen }}</div>
    </div>
    <div class="is-detail-card"
         style="grid-column:1/-1;">
        <div class="is-detail-label">Observación FURPEN</div>
        <div class="is-detail-value">
            {{ $caso->observacion_furpen ?: 'Sin observación' }}
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     DETALLE FLUJO JURÍDICO
════════════════════════════════════════════ --}}
<div class="is-section-heading is-animate-rise is-stagger-3">
    Detalle del flujo jurídico
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-detail-card">
        <div class="is-detail-label">Tipo respuesta aseguradora</div>
        <div class="is-detail-value">{{ $textoTipoRespuesta }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha respuesta / dictamen</div>
        <div class="is-detail-value">{{ $fechaRespuesta }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Tipo de tutela presentada</div>
        <div class="is-detail-value">{{ $textoTipoTutela }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha tutela</div>
        <div class="is-detail-value">{{ $fechaTutela }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Resultado fallo tutela</div>
        <div class="is-detail-value">{{ $textoResultadoFallo }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha fallo tutela</div>
        <div class="is-detail-value">{{ $fechaFalloTutela }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Cumplimiento del fallo</div>
        <div class="is-detail-value">{{ $textoTipoCumplimiento }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha cumplimiento tutela</div>
        <div class="is-detail-value">{{ $fechaCumplimiento }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Resultado segunda instancia</div>
        <div class="is-detail-value">{{ $textoResultadoSegunda }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha segunda instancia</div>
        <div class="is-detail-value">{{ $fechaSegunda }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha desacato</div>
        <div class="is-detail-value">{{ $fechaDesacato }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha impugnación</div>
        <div class="is-detail-value">{{ $fechaImpugnacion }}</div>
    </div>
    @if($caso->observacion_pago)
    <div class="is-detail-card">
        <div class="is-detail-label">Observación pago</div>
        <div class="is-detail-value">{{ $caso->observacion_pago }}</div>
    </div>
    @endif
    @if($caso->observacion_reclamacion)
    <div class="is-detail-card">
        <div class="is-detail-label">Observación reclamación</div>
        <div class="is-detail-value">{{ $caso->observacion_reclamacion }}</div>
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════
     RESUMEN ECONÓMICO
════════════════════════════════════════════ --}}
<div class="is-section-heading is-animate-rise is-stagger-3">
    Resumen económico
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-detail-card">
        <div class="is-detail-label">Valor estimado</div>
        <div class="is-detail-value money">
            {{ $caso->valor_estimado
                ? '$'.number_format($caso->valor_estimado,0,',','.')
                : '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Valor reclamado</div>
        <div class="is-detail-value money">
            {{ $caso->valor_reclamado
                ? '$'.number_format($caso->valor_reclamado,0,',','.')
                : '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Valor pagado</div>
        <div class="is-detail-value money-green">
            {{ $caso->valor_pagado
                ? '$'.number_format($caso->valor_pagado,0,',','.')
                : '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">% Honorarios</div>
        <div class="is-detail-value">
            {{ $caso->porcentaje_honorarios
                ? number_format($caso->porcentaje_honorarios,0,',','.').'%'
                : '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Ganancia equipo jurídico</div>
        <div class="is-detail-value money-blue">
            {{ $caso->ganancia_equipo
                ? '$'.number_format($caso->ganancia_equipo,0,',','.')
                : '—' }}
        </div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Valor neto cliente</div>
        <div class="is-detail-value money">
            {{ $caso->valor_neto_cliente
                ? '$'.number_format($caso->valor_neto_cliente,0,',','.')
                : '—' }}
        </div>
    </div>
    <div class="is-detail-card" style="grid-column:1/-1;">
        <div class="is-detail-label">Observaciones generales</div>
        <div class="is-detail-value">
            {{ $caso->observaciones ?: 'Sin observaciones' }}
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     CONTROL LEGAL
════════════════════════════════════════════ --}}
<div class="is-section-heading is-animate-rise is-stagger-3">
    Control legal
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:8px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-detail-card">
        <div class="is-detail-label">Fecha de prescripción</div>
        <div class="is-detail-value">{{ $fechaPrescripcion }}</div>
    </div>
    <div class="is-detail-card">
        <div class="is-detail-label">Días para prescripción</div>
        <div class="is-detail-value">
            @if($diasPrescripcion !== null)
                @if($diasPrescripcion < 0)
                    <span style="color:#F26F6F;font-weight:700;">
                        Prescrito hace {{ abs($diasPrescripcion) }} día(s)
                    </span>
                @elseif($diasPrescripcion <= 90)
                    <span style="color:#F26F6F;font-weight:700;">
                        {{ $diasPrescripcion }} día(s) — ⚠️ Crítico
                    </span>
                @else
                    {{ $diasPrescripcion }} día(s)
                @endif
            @else
                N/A
            @endif
        </div>
    </div>
</div>

@endsection
