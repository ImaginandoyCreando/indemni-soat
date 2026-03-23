@extends('layouts.app')

@section('title', 'Editar Caso')

@section('content')

<style>
/* ── Sección activa al foco ── */
.is-form-section { transition: box-shadow .2s; }
.is-form-section:focus-within {
    box-shadow: 0 0 0 2px rgba(27,79,255,0.15);
    border-color: rgba(27,79,255,0.25) !important;
}

/* ── Bloques de flujo jurídico ── */
.flujo-bloque {
    border-radius: 10px;
    padding: 16px 18px;
    margin-bottom: 4px;
    border: 1px solid var(--border-2);
    background: var(--bg-card);
    transition: background .3s, border-color .3s;
}
.flujo-bloque.fl-info    {
    border-left: 3px solid #1B4FFF;
    background: rgba(27,79,255,0.04);
}
.flujo-bloque.fl-warn    {
    border-left: 3px solid #F59E0B;
    background: rgba(245,158,11,0.04);
}
.flujo-bloque.fl-danger  {
    border-left: 3px solid #E53935;
    background: rgba(229,57,53,0.04);
}
.flujo-bloque.fl-success {
    border-left: 3px solid #059669;
    background: rgba(5,150,105,0.04);
}
.flujo-titulo {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-3);
    margin: 0 0 14px 0;
}

/* ── Campo solo lectura ── */
.is-input-readonly {
    width: 100%;
    padding: 11px 13px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-3);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    cursor: default;
}

/* ── Resumen financiero ── */
.is-fin-card {
    background: var(--bg-card-alt, rgba(27,79,255,0.04));
    border: 1px solid var(--border-2);
    border-radius: 10px;
    padding: 18px 20px;
    grid-column: 1 / -1;
    transition: background .3s;
}
.is-fin-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}
.is-fin-row:last-child { border-bottom: none; }
.is-fin-val {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 15px;
    color: #1DBD7F;
}
</style>

{{-- ── Cabecera ── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:center;gap:14px;margin-bottom:28px;
            flex-wrap:wrap;">
    <a href="{{ route('casos.index') }}"
       style="width:38px;height:38px;border-radius:8px;
              border:1px solid var(--border-2);background:var(--bg-input);
              display:flex;align-items:center;justify-content:center;
              color:var(--text-2);font-size:18px;text-decoration:none;
              transition:all .2s;flex-shrink:0;"
       onmouseover="this.style.background='var(--bg-hover)';this.style.color='var(--text-1)'"
       onmouseout="this.style.background='var(--bg-input)';this.style.color='var(--text-2)'">
        ←
    </a>
    <div style="flex:1;">
        <div class="is-page-title">Editar Caso</div>
        <div style="display:flex;align-items:center;gap:10px;margin-top:5px;flex-wrap:wrap;">
            <span class="is-badge is-badge-cobalt" style="font-size:12px;">
                {{ $caso->numero_caso }}
            </span>
            <span style="font-size:12px;color:var(--text-2);">
                Estado:
                <strong style="color:var(--text-1);">
                    {{ $caso->estado ?: 'N/A' }}
                </strong>
            </span>
            <span style="font-size:12px;color:var(--text-2);">
                Avance:
                <strong style="color:var(--text-1);">
                    {{ $caso->porcentaje_avance ?? 0 }}%
                </strong>
            </span>
        </div>
    </div>
</div>

{{-- ── Errores ── --}}
@if($errors->any())
    <div class="is-animate-rise"
         style="background:rgba(229,57,53,0.08);
                border:1px solid rgba(229,57,53,0.22);
                border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:700;
                    color:#F26F6F;margin-bottom:8px;">
            Corrige los siguientes errores:
        </div>
        <ul style="margin:0;padding-left:18px;
                   font-size:13px;color:#F26F6F;line-height:1.7;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('casos.update', $caso) }}">
@csrf
@method('PUT')

{{-- ════════════════════════════════════════════
     SECCIÓN 1 — Información general
════════════════════════════════════════════ --}}
<div class="is-form-section is-animate-rise is-stagger-1">
    <div class="is-form-section-header">
        <div class="is-section-num">1</div>
        <div class="is-section-title">Información general de la víctima</div>
    </div>
    <div class="is-form-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            <div>
                <label class="is-form-label">Nombres *</label>
                <input type="text" name="nombres" class="is-input"
                       value="{{ old('nombres', $caso->nombres) }}"
                       required placeholder="Ej. Carlos Andrés">
            </div>

            <div>
                <label class="is-form-label">Apellidos *</label>
                <input type="text" name="apellidos" class="is-input"
                       value="{{ old('apellidos', $caso->apellidos) }}"
                       required placeholder="Ej. Pérez García">
            </div>

            <div>
                <label class="is-form-label">Cédula *</label>
                <input type="text" name="cedula" class="is-input"
                       value="{{ old('cedula', $caso->cedula) }}"
                       required>
            </div>

            <div>
                <label class="is-form-label">Teléfono / Celular</label>
                <input type="text" name="telefono" class="is-input"
                       value="{{ old('telefono', $caso->telefono) }}"
                       placeholder="300 000 0000">
            </div>

            <div>
                <label class="is-form-label">Correo electrónico</label>
                <input type="email" name="correo" class="is-input"
                       value="{{ old('correo', $caso->correo) }}">
            </div>

            <div>
                <label class="is-form-label">Departamento</label>
                <input type="text" name="departamento" class="is-input"
                       value="{{ old('departamento', $caso->departamento) }}">
            </div>

            <div>
                <label class="is-form-label">Ciudad / Municipio</label>
                <input type="text" name="ciudad" class="is-input"
                       value="{{ old('ciudad', $caso->ciudad) }}">
            </div>

            <div>
                <label class="is-form-label">Dirección</label>
                <input type="text" name="direccion" class="is-input"
                       value="{{ old('direccion', $caso->direccion) }}">
            </div>

            <div>
                <label class="is-form-label">Fecha del accidente</label>
                <input type="date" name="fecha_accidente" class="is-input"
                       value="{{ old('fecha_accidente',
                           $caso->fecha_accidente
                               ? \Carbon\Carbon::parse($caso->fecha_accidente)->format('Y-m-d')
                               : '') }}">
            </div>

            <div>
                <label class="is-form-label">Aseguradora *</label>
                <select name="aseguradora" class="is-select" required>
                    @foreach($aseguradoras as $aseguradora)
                        <option value="{{ $aseguradora }}"
                            {{ old('aseguradora', $caso->aseguradora) == $aseguradora ? 'selected' : '' }}>
                            {{ $aseguradora }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="is-form-label">Junta asignada</label>
                <select name="junta_asignada" class="is-select">
                    <option value="">Seleccionar</option>
                    @foreach($juntas as $junta)
                        <option value="{{ $junta }}"
                            {{ old('junta_asignada', $caso->junta_asignada) == $junta ? 'selected' : '' }}>
                            {{ $junta }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="is-form-label">Estado *</label>
                <select name="estado" class="is-select" required>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}"
                            {{ old('estado', $caso->estado) == $estado ? 'selected' : '' }}>
                            {{ $estado }}
                        </option>
                    @endforeach
                </select>
                <div class="is-field-hint">
                    El sistema puede reajustar este estado según las fechas
                    diligenciadas.
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECCIÓN 2 — Documentación inicial
════════════════════════════════════════════ --}}
<div class="is-form-section is-animate-rise is-stagger-2">
    <div class="is-form-section-header">
        <div class="is-section-num">2</div>
        <div class="is-section-title">Documentación inicial del negocio</div>
    </div>
    <div class="is-form-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            <div>
                <label class="is-form-label">¿Tiene poder firmado?</label>
                <select name="tiene_poder" class="is-select">
                    <option value="0"
                        {{ old('tiene_poder', $caso->tiene_poder ?? 0) == 0 ? 'selected' : '' }}>
                        No
                    </option>
                    <option value="1"
                        {{ old('tiene_poder', $caso->tiene_poder ?? 0) == 1 ? 'selected' : '' }}>
                        Sí
                    </option>
                </select>
                <div class="is-field-hint">
                    Marca si la víctima ya entregó el poder firmado ante notaría.
                </div>
            </div>

            <div>
                <label class="is-form-label">Fecha entrega del poder</label>
                <input type="date" name="fecha_entrega_poder" class="is-input"
                       value="{{ old('fecha_entrega_poder',
                           !empty($caso->fecha_entrega_poder)
                               ? \Carbon\Carbon::parse($caso->fecha_entrega_poder)->format('Y-m-d')
                               : '') }}">
            </div>

            <div>
                <label class="is-form-label">Fecha poder firmado (real)</label>
                <input type="date" name="fecha_poder_firmado" class="is-input"
                       value="{{ old('fecha_poder_firmado',
                           !empty($caso->fecha_poder_firmado)
                               ? \Carbon\Carbon::parse($caso->fecha_poder_firmado)->format('Y-m-d')
                               : '') }}">
            </div>

            <div>
                <label class="is-form-label">¿Tiene contrato firmado?</label>
                <select name="tiene_contrato" class="is-select">
                    <option value="0"
                        {{ old('tiene_contrato', $caso->tiene_contrato ?? 0) == 0 ? 'selected' : '' }}>
                        No
                    </option>
                    <option value="1"
                        {{ old('tiene_contrato', $caso->tiene_contrato ?? 0) == 1 ? 'selected' : '' }}>
                        Sí
                    </option>
                </select>
                <div class="is-field-hint">
                    Marca si la víctima ya entregó el contrato firmado.
                </div>
            </div>

            <div>
                <label class="is-form-label">Fecha entrega del contrato</label>
                <input type="date" name="fecha_entrega_contrato" class="is-input"
                       value="{{ old('fecha_entrega_contrato',
                           !empty($caso->fecha_entrega_contrato)
                               ? \Carbon\Carbon::parse($caso->fecha_entrega_contrato)->format('Y-m-d')
                               : '') }}">
            </div>

            <div>
                <label class="is-form-label">Fecha contrato firmado (real)</label>
                <input type="date" name="fecha_contrato_firmado" class="is-input"
                       value="{{ old('fecha_contrato_firmado',
                           !empty($caso->fecha_contrato_firmado)
                               ? \Carbon\Carbon::parse($caso->fecha_contrato_firmado)->format('Y-m-d')
                               : '') }}">
            </div>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECCIÓN 3 — Requisitos médicos
════════════════════════════════════════════ --}}
<div class="is-form-section is-animate-rise is-stagger-2">
    <div class="is-form-section-header">
        <div class="is-section-num">3</div>
        <div class="is-section-title">Requisitos médicos y soportes previos</div>
    </div>
    <div class="is-form-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            <div>
                <label class="is-form-label">¿Tiene alta por ortopedia?</label>
                <select name="alta_ortopedia" class="is-select">
                    <option value="0"
                        {{ old('alta_ortopedia', $caso->alta_ortopedia ?? 0) == 0 ? 'selected' : '' }}>
                        No
                    </option>
                    <option value="1"
                        {{ old('alta_ortopedia', $caso->alta_ortopedia ?? 0) == 1 ? 'selected' : '' }}>
                        Sí
                    </option>
                </select>
                <div class="is-field-hint">
                    Clave antes de enviar la solicitud a junta.
                </div>
            </div>

            <div>
                <label class="is-form-label">Fecha alta por ortopedia</label>
                <input type="date" name="fecha_alta_ortopedia" class="is-input"
                       value="{{ old('fecha_alta_ortopedia',
                           !empty($caso->fecha_alta_ortopedia)
                               ? \Carbon\Carbon::parse($caso->fecha_alta_ortopedia)->format('Y-m-d')
                               : '') }}">
            </div>

            <div style="grid-column:1/-1;">
                <label class="is-form-label">Observación alta por ortopedia</label>
                <textarea name="observacion_alta_ortopedia" class="is-textarea">{{ old('observacion_alta_ortopedia', $caso->observacion_alta_ortopedia ?? '') }}</textarea>
            </div>

            <div>
                <label class="is-form-label">¿FURPEN completo?</label>
                <select name="furpen_completo" class="is-select">
                    <option value="0"
                        {{ old('furpen_completo', $caso->furpen_completo ?? 0) == 0 ? 'selected' : '' }}>
                        No
                    </option>
                    <option value="1"
                        {{ old('furpen_completo', $caso->furpen_completo ?? 0) == 1 ? 'selected' : '' }}>
                        Sí
                    </option>
                </select>
                <div class="is-field-hint">
                    Marca si la víctima ya entregó toda la información del FURPEN.
                </div>
            </div>

            <div>
                <label class="is-form-label">Fecha FURPEN recibido</label>
                <input type="date" name="fecha_furpen_recibido" class="is-input"
                       value="{{ old('fecha_furpen_recibido',
                           !empty($caso->fecha_furpen_recibido)
                               ? \Carbon\Carbon::parse($caso->fecha_furpen_recibido)->format('Y-m-d')
                               : '') }}">
            </div>

            <div style="grid-column:1/-1;">
                <label class="is-form-label">Observación FURPEN</label>
                <textarea name="observacion_furpen" class="is-textarea">{{ old('observacion_furpen', $caso->observacion_furpen ?? '') }}</textarea>
            </div>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECCIÓN 4 — Flujo jurídico
════════════════════════════════════════════ --}}
<div class="is-form-section is-animate-rise is-stagger-3">
    <div class="is-form-section-header">
        <div class="is-section-num">4</div>
        <div class="is-section-title">Flujo jurídico completo del caso</div>
    </div>
    <div class="is-form-body">
        <div style="display:flex;flex-direction:column;gap:10px;">

            {{-- Paso 1: Solicitud --}}
            <div class="flujo-bloque fl-info">
                <p class="flujo-titulo">Paso 1 — Solicitud a aseguradora</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">
                            Fecha solicitud de calificación
                        </label>
                        <input type="date" name="fecha_solicitud_aseguradora"
                               class="is-input"
                               value="{{ old('fecha_solicitud_aseguradora',
                                   $caso->fecha_solicitud_aseguradora
                                       ? \Carbon\Carbon::parse($caso->fecha_solicitud_aseguradora)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Primer paso: cuando se radica la solicitud ante la
                            aseguradora.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 2: Respuesta aseguradora --}}
            <div class="flujo-bloque fl-info">
                <p class="flujo-titulo">Paso 2 — Respuesta de la aseguradora</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Tipo de respuesta</label>
                        <select name="tipo_respuesta_aseguradora"
                                id="tipo_respuesta_aseguradora"
                                class="is-select"
                                onchange="mostrarCamposRespuesta()">
                            <option value="">— Seleccionar —</option>
                            <option value="emitio_dictamen"
                                {{ old('tipo_respuesta_aseguradora',
                                    $caso->tipo_respuesta_aseguradora ?? '') == 'emitio_dictamen'
                                    ? 'selected' : '' }}>
                                Emitió dictamen (calificó)
                            </option>
                            <option value="nego"
                                {{ old('tipo_respuesta_aseguradora',
                                    $caso->tipo_respuesta_aseguradora ?? '') == 'nego'
                                    ? 'selected' : '' }}>
                                Negó la solicitud
                            </option>
                            <option value="no_respondio"
                                {{ old('tipo_respuesta_aseguradora',
                                    $caso->tipo_respuesta_aseguradora ?? '') == 'no_respondio'
                                    ? 'selected' : '' }}>
                                No respondió (pasó 1 mes)
                            </option>
                        </select>
                        <div class="is-field-hint">
                            <strong style="color:var(--text-1)">Emitió dictamen</strong>
                            → flujo de apelación.<br>
                            <strong style="color:var(--text-1)">Negó / No respondió</strong>
                            → tutela para calificación.
                        </div>
                    </div>
                    <div id="bloque_fecha_respuesta">
                        <label class="is-form-label">
                            Fecha respuesta / dictamen
                        </label>
                        <input type="date" name="fecha_respuesta_aseguradora"
                               class="is-input"
                               value="{{ old('fecha_respuesta_aseguradora',
                                   $caso->fecha_respuesta_aseguradora
                                       ? \Carbon\Carbon::parse($caso->fecha_respuesta_aseguradora)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Dejar vacío si la respuesta fue "no respondió".
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 3: Apelación --}}
            <div class="flujo-bloque fl-info" id="bloque_apelacion">
                <p class="flujo-titulo">
                    Paso 3 — Apelación del dictamen
                    <span style="color:var(--text-3);font-weight:400;
                                 text-transform:none;letter-spacing:0;">
                        (solo si emitió dictamen)
                    </span>
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Fecha apelación</label>
                        <input type="date" name="fecha_apelacion"
                               class="is-input"
                               value="{{ old('fecha_apelacion',
                                   $caso->fecha_apelacion
                                       ? \Carbon\Carbon::parse($caso->fecha_apelacion)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Aplica cuando la aseguradora calificó y se decide
                            apelar su dictamen.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 4: Tutela --}}
            <div class="flujo-bloque fl-warn">
                <p class="flujo-titulo">Paso 4 — Tutela</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Fecha tutela</label>
                        <input type="date" name="fecha_tutela"
                               class="is-input"
                               value="{{ old('fecha_tutela',
                                   $caso->fecha_tutela
                                       ? \Carbon\Carbon::parse($caso->fecha_tutela)->format('Y-m-d')
                                       : '') }}">
                    </div>
                    <div>
                        <label class="is-form-label">Tipo de tutela</label>
                        <select name="tipo_tutela" class="is-select">
                            <option value="">— Seleccionar —</option>
                            <option value="tutela_calificacion"
                                {{ old('tipo_tutela', $caso->tipo_tutela ?? '') == 'tutela_calificacion'
                                    ? 'selected' : '' }}>
                                Para calificación (negó o no respondió)
                            </option>
                            <option value="tutela_debido_proceso"
                                {{ old('tipo_tutela', $caso->tipo_tutela ?? '') == 'tutela_debido_proceso'
                                    ? 'selected' : '' }}>
                                Por debido proceso (no pagan honorarios)
                            </option>
                        </select>
                        <div class="is-field-hint">
                            <strong style="color:var(--text-1)">Calificación</strong>:
                            aseguradora negó o no respondió.<br>
                            <strong style="color:var(--text-1)">Debido proceso</strong>:
                            apelaron pero no pagan honorarios.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 5: Fallo de tutela --}}
            <div class="flujo-bloque fl-warn">
                <p class="flujo-titulo">Paso 5 — Fallo de tutela</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Fecha fallo</label>
                        <input type="date" name="fecha_fallo_tutela"
                               class="is-input"
                               value="{{ old('fecha_fallo_tutela',
                                   !empty($caso->fecha_fallo_tutela)
                                       ? \Carbon\Carbon::parse($caso->fecha_fallo_tutela)->format('Y-m-d')
                                       : '') }}">
                    </div>
                    <div>
                        <label class="is-form-label">Resultado del fallo</label>
                        <select name="resultado_fallo_tutela"
                                id="resultado_fallo_tutela"
                                class="is-select"
                                onchange="mostrarCamposFallo()">
                            <option value="">— Seleccionar —</option>
                            <option value="concedido"
                                {{ old('resultado_fallo_tutela',
                                    $caso->resultado_fallo_tutela ?? '') == 'concedido'
                                    ? 'selected' : '' }}>
                                Concedido
                            </option>
                            <option value="negado"
                                {{ old('resultado_fallo_tutela',
                                    $caso->resultado_fallo_tutela ?? '') == 'negado'
                                    ? 'selected' : '' }}>
                                Negado
                            </option>
                            <option value="parcial"
                                {{ old('resultado_fallo_tutela',
                                    $caso->resultado_fallo_tutela ?? '') == 'parcial'
                                    ? 'selected' : '' }}>
                                Parcial
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Paso 5A: Cumplimiento --}}
            <div class="flujo-bloque fl-success" id="bloque_cumplimiento_tutela">
                <p class="flujo-titulo">Paso 5A — Cumplimiento del fallo concedido</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Fecha cumplimiento</label>
                        <input type="date" name="fecha_cumplimiento_tutela"
                               class="is-input"
                               value="{{ old('fecha_cumplimiento_tutela',
                                   !empty($caso->fecha_cumplimiento_tutela)
                                       ? \Carbon\Carbon::parse($caso->fecha_cumplimiento_tutela)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Fecha en que la aseguradora acató el fallo.
                        </div>
                    </div>
                    <div>
                        <label class="is-form-label">Tipo de cumplimiento</label>
                        <select name="tipo_cumplimiento_tutela" class="is-select">
                            <option value="">— Seleccionar —</option>
                            <option value="voluntario"
                                {{ old('tipo_cumplimiento_tutela',
                                    $caso->tipo_cumplimiento_tutela ?? '') == 'voluntario'
                                    ? 'selected' : '' }}>
                                Voluntario (dentro de 2 semanas)
                            </option>
                            <option value="desacato"
                                {{ old('tipo_cumplimiento_tutela',
                                    $caso->tipo_cumplimiento_tutela ?? '') == 'desacato'
                                    ? 'selected' : '' }}>
                                Tras incidente de desacato
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Paso 5B: Desacato --}}
            <div class="flujo-bloque fl-danger" id="bloque_desacato">
                <p class="flujo-titulo">
                    Paso 5B — Incidente de desacato
                    <span style="color:var(--text-3);font-weight:400;
                                 text-transform:none;letter-spacing:0;">
                        (no cumplieron en 14 días)
                    </span>
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Fecha desacato</label>
                        <input type="date" name="fecha_incidente_desacato"
                               class="is-input"
                               value="{{ old('fecha_incidente_desacato',
                                   !empty($caso->fecha_incidente_desacato)
                                       ? \Carbon\Carbon::parse($caso->fecha_incidente_desacato)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Fallo favorable pero no cumple en el plazo de 14 días.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 5C: Impugnación --}}
            <div class="flujo-bloque fl-danger" id="bloque_impugnacion">
                <p class="flujo-titulo">
                    Paso 5C — Impugnación
                    <span style="color:var(--text-3);font-weight:400;
                                 text-transform:none;letter-spacing:0;">
                        (fallo negado)
                    </span>
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Fecha impugnación</label>
                        <input type="date" name="fecha_impugnacion"
                               class="is-input"
                               value="{{ old('fecha_impugnacion',
                                   !empty($caso->fecha_impugnacion)
                                       ? \Carbon\Carbon::parse($caso->fecha_impugnacion)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Fallo desfavorable → se impugna ante segunda instancia.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 5D: Segunda instancia --}}
            <div class="flujo-bloque fl-danger" id="bloque_segunda_instancia">
                <p class="flujo-titulo">Paso 5D — Fallo de segunda instancia</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Fecha fallo 2ª instancia</label>
                        <input type="date" name="fecha_fallo_segunda_instancia"
                               class="is-input"
                               value="{{ old('fecha_fallo_segunda_instancia',
                                   !empty($caso->fecha_fallo_segunda_instancia)
                                       ? \Carbon\Carbon::parse($caso->fecha_fallo_segunda_instancia)->format('Y-m-d')
                                       : '') }}">
                    </div>
                    <div>
                        <label class="is-form-label">Resultado</label>
                        <select name="resultado_fallo_segunda_instancia"
                                id="resultado_segunda_instancia"
                                class="is-select"
                                onchange="mostrarResultadoSegundaInstancia()">
                            <option value="">— Seleccionar —</option>
                            <option value="confirma"
                                {{ old('resultado_fallo_segunda_instancia',
                                    $caso->resultado_fallo_segunda_instancia ?? '') == 'confirma'
                                    ? 'selected' : '' }}>
                                Confirma — el caso se pierde
                            </option>
                            <option value="revoca"
                                {{ old('resultado_fallo_segunda_instancia',
                                    $caso->resultado_fallo_segunda_instancia ?? '') == 'revoca'
                                    ? 'selected' : '' }}>
                                Revoca — aseguradora debe cumplir
                            </option>
                        </select>
                        <div class="is-field-hint" id="helper_segunda_instancia">
                            @if(($caso->resultado_fallo_segunda_instancia ?? '') == 'confirma')
                                <strong style="color:#F26F6F">
                                    Caso cerrado desfavorablemente.
                                </strong>
                            @elseif(($caso->resultado_fallo_segunda_instancia ?? '') == 'revoca')
                                <strong style="color:#1DBD7F">
                                    La aseguradora debe cumplir — continuar flujo.
                                </strong>
                            @else
                                Confirma: caso cerrado.
                                Revoca: aseguradora debe calificar o pagar honorarios.
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paso 6: Honorarios --}}
            <div class="flujo-bloque fl-info">
                <p class="flujo-titulo">Paso 6 — Pago de honorarios a junta</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="is-form-label">Fecha pago honorarios</label>
                        <input type="date" name="fecha_pago_honorarios"
                               class="is-input"
                               value="{{ old('fecha_pago_honorarios',
                                   $caso->fecha_pago_honorarios
                                       ? \Carbon\Carbon::parse($caso->fecha_pago_honorarios)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Cuando la aseguradora paga los honorarios para acudir
                            a la junta.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pasos 7-12 --}}
            <div class="flujo-bloque fl-success">
                <p class="flujo-titulo">
                    Pasos 7–12 — Junta → Cobro → Pago final
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                    <div>
                        <label class="is-form-label">Fecha envío a junta</label>
                        <input type="date" name="fecha_envio_junta"
                               class="is-input"
                               value="{{ old('fecha_envio_junta',
                                   $caso->fecha_envio_junta
                                       ? \Carbon\Carbon::parse($caso->fecha_envio_junta)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Solo con honorarios pagados y alta ortopedia confirmada.
                        </div>
                    </div>

                    <div>
                        <label class="is-form-label">Fecha dictamen de junta</label>
                        <input type="date" name="fecha_dictamen_junta"
                               class="is-input"
                               value="{{ old('fecha_dictamen_junta',
                                   $caso->fecha_dictamen_junta
                                       ? \Carbon\Carbon::parse($caso->fecha_dictamen_junta)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Cuando la junta emita el dictamen definitivo.
                        </div>
                    </div>

                    <div>
                        <label class="is-form-label">Porcentaje PCL</label>
                        <input type="number" step="0.01" min="0"
                               name="porcentaje_pcl" class="is-input"
                               value="{{ old('porcentaje_pcl', $caso->porcentaje_pcl) }}"
                               placeholder="Ej. 35">
                    </div>

                    <div>
                        <label class="is-form-label">Valor reclamado</label>
                        <input type="number" step="0.01" min="0"
                               name="valor_reclamado" class="is-input"
                               value="{{ old('valor_reclamado', $caso->valor_reclamado) }}">
                    </div>

                    <div>
                        <label class="is-form-label">Fecha cobro a aseguradora</label>
                        <input type="date" name="fecha_reclamacion_final"
                               class="is-input"
                               value="{{ old('fecha_reclamacion_final',
                                   $caso->fecha_reclamacion_final
                                       ? \Carbon\Carbon::parse($caso->fecha_reclamacion_final)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Envío del cobro con el dictamen de junta.
                        </div>
                    </div>

                    <div>
                        <label class="is-form-label">Fecha pago final</label>
                        <input type="date" name="fecha_pago_final"
                               class="is-input"
                               value="{{ old('fecha_pago_final',
                                   $caso->fecha_pago_final
                                       ? \Carbon\Carbon::parse($caso->fecha_pago_final)->format('Y-m-d')
                                       : '') }}">
                        <div class="is-field-hint">
                            Cuando la aseguradora pague la indemnización.
                        </div>
                    </div>

                </div>
            </div>

        </div>{{-- fin columna flujo --}}
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECCIÓN 5 — Datos financieros
════════════════════════════════════════════ --}}
<div class="is-form-section is-animate-rise is-stagger-3">
    <div class="is-form-section-header">
        <div class="is-section-num">5</div>
        <div class="is-section-title">Datos financieros</div>
    </div>
    <div class="is-form-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            <div>
                <label class="is-form-label">Valor estimado (calculado)</label>
                <div class="is-input-readonly">
                    {{ $caso->valor_estimado
                        ? '$' . number_format($caso->valor_estimado, 0, ',', '.')
                        : 'No calculado' }}
                </div>
            </div>

            <div>
                <label class="is-form-label">SMLDV aplicados</label>
                <div class="is-input-readonly">
                    {{ $caso->smldv_aplicados ?? 'N/A' }}
                </div>
            </div>

            <div>
                <label class="is-form-label">Valor pagado</label>
                <input type="number" step="0.01" min="0"
                       id="valor_pagado" name="valor_pagado"
                       class="is-input"
                       value="{{ old('valor_pagado', $caso->valor_pagado) }}"
                       placeholder="0">
            </div>

            <div>
                <label class="is-form-label">% Honorarios</label>
                <select name="porcentaje_honorarios"
                        id="porcentaje_honorarios"
                        class="is-select">
                    <option value="">Seleccionar</option>
                    <option value="40"
                        {{ in_array(old('porcentaje_honorarios',
                            (string)$caso->porcentaje_honorarios),
                            ['40','40.00']) ? 'selected' : '' }}>
                        40%
                    </option>
                    <option value="50"
                        {{ in_array(old('porcentaje_honorarios',
                            (string)$caso->porcentaje_honorarios),
                            ['50','50.00']) ? 'selected' : '' }}>
                        50%
                    </option>
                </select>
            </div>

            {{-- Resumen financiero --}}
            <div class="is-fin-card">
                <div style="font-size:11px;font-weight:700;color:var(--text-3);
                            letter-spacing:.8px;text-transform:uppercase;
                            margin-bottom:12px;">
                    Resumen financiero
                </div>
                <div class="is-fin-row">
                    <span style="font-size:13px;color:var(--text-2);">
                        Ganancia equipo jurídico
                    </span>
                    <span class="is-fin-val" id="ganancia_equipo">
                        ${{ number_format($caso->ganancia_equipo ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="is-fin-row">
                    <span style="font-size:13px;color:var(--text-2);">
                        Valor neto cliente
                    </span>
                    <span class="is-fin-val" id="valor_cliente">
                        ${{ number_format($caso->valor_neto_cliente ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div style="grid-column:1/-1;">
                <label class="is-form-label">Observaciones</label>
                <textarea name="observaciones" class="is-textarea"
                          style="min-height:100px;"
                          placeholder="Notas generales del caso...">{{ old('observaciones', $caso->observaciones) }}</textarea>
            </div>

        </div>

        {{-- Acciones --}}
        <div style="display:flex;justify-content:flex-end;
                    gap:10px;margin-top:24px;flex-wrap:wrap;">
            <a href="{{ route('casos.index') }}" class="is-btn-ghost">
                Cancelar
            </a>
            <button type="submit" class="is-btn-gold">
                ✓ Actualizar Caso
            </button>
        </div>

    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
// ── Cálculo financiero en tiempo real ─────────────────────────────────────
function calcularFinanzas() {
    const valor      = parseFloat(document.getElementById('valor_pagado').value) || 0;
    const porcentaje = parseFloat(document.getElementById('porcentaje_honorarios').value) || 0;
    const ganancia   = (valor * porcentaje) / 100;
    const cliente    = valor - ganancia;
    document.getElementById('ganancia_equipo').textContent =
        '$' + Math.round(ganancia).toLocaleString('es-CO');
    document.getElementById('valor_cliente').textContent =
        '$' + Math.round(cliente).toLocaleString('es-CO');
}
document.getElementById('valor_pagado')
    .addEventListener('input', calcularFinanzas);
document.getElementById('porcentaje_honorarios')
    .addEventListener('change', calcularFinanzas);

// ── Visibilidad según tipo de respuesta ───────────────────────────────────
function mostrarCamposRespuesta() {
    const tipo        = document.getElementById('tipo_respuesta_aseguradora').value;
    const bloqueApel  = document.getElementById('bloque_apelacion');
    const bloqueFecha = document.getElementById('bloque_fecha_respuesta');

    bloqueApel.style.display  = (tipo === 'emitio_dictamen') ? '' : 'none';
    bloqueFecha.style.display = (tipo === 'no_respondio')    ? 'none' : '';
}

// ── Visibilidad según resultado del fallo ─────────────────────────────────
function mostrarCamposFallo() {
    const resultado              = document.getElementById('resultado_fallo_tutela').value;
    const bloqueCumplimiento     = document.getElementById('bloque_cumplimiento_tutela');
    const bloqueDesacato         = document.getElementById('bloque_desacato');
    const bloqueImpugnacion      = document.getElementById('bloque_impugnacion');
    const bloqueSegundaInstancia = document.getElementById('bloque_segunda_instancia');

    bloqueCumplimiento.style.display     = (resultado === 'concedido') ? '' : 'none';
    bloqueDesacato.style.display         = (resultado === 'concedido') ? '' : 'none';
    bloqueImpugnacion.style.display      = (['negado','parcial'].includes(resultado)) ? '' : 'none';
    bloqueSegundaInstancia.style.display = (['negado','parcial'].includes(resultado)) ? '' : 'none';
}

// ── Mensaje dinámico segunda instancia ───────────────────────────────────
function mostrarResultadoSegundaInstancia() {
    const val    = document.getElementById('resultado_segunda_instancia').value;
    const helper = document.getElementById('helper_segunda_instancia');
    if (val === 'confirma') {
        helper.innerHTML =
            '<strong style="color:#F26F6F">Caso cerrado desfavorablemente — no hay más acciones jurídicas.</strong>';
    } else if (val === 'revoca') {
        helper.innerHTML =
            '<strong style="color:#1DBD7F">La aseguradora debe cumplir — registrar cumplimiento y continuar flujo.</strong>';
    } else {
        helper.innerHTML =
            'Confirma: caso cerrado. Revoca: aseguradora debe calificar o pagar honorarios.';
    }
}

// ── Init al cargar ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    mostrarCamposRespuesta();
    mostrarCamposFallo();
    mostrarResultadoSegundaInstancia();
});
</script>
@endpush
