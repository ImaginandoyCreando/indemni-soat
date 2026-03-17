<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Caso - INDEMNI SOAT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{
    font-family:Arial,Helvetica,sans-serif;
    background:#f4f6f9;
    padding:30px;
    margin:0;
    color:#111827;
}
.container{
    max-width:1200px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
}
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}
input,select,textarea{
    width:100%;
    padding:10px;
    margin-top:5px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:6px;
    box-sizing:border-box;
    font-family:inherit;
    font-size:14px;
}
textarea{
    min-height:120px;
}
button{
    background:#0d6efd;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:6px;
    cursor:pointer;
    font-size:14px;
}
a{
    text-decoration:none;
}
.full{
    grid-column:1 / -1;
}
h2,h3{
    margin-top:0;
}
.section{
    background:#f8fafc;
    padding:16px;
    border-radius:8px;
    border:1px solid #e2e8f0;
}
.finanzas{
    background:#f8fafc;
    padding:16px;
    border-radius:8px;
    border:1px solid #e2e8f0;
}
.finanzas div{
    margin-bottom:10px;
}
.resultado{
    font-weight:bold;
    color:#198754;
}
.helper{
    display:block;
    margin-top:-8px;
    margin-bottom:12px;
    font-size:12px;
    color:#64748b;
    line-height:1.4;
}
.header-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    flex-wrap:wrap;
    margin-bottom:20px;
}
.badge{
    display:inline-block;
    padding:8px 12px;
    border-radius:999px;
    background:#e9ecef;
    color:#374151;
    font-size:13px;
}
.actions{
    margin-top:20px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.btn-secondary{
    background:#6c757d;
    color:#fff;
    padding:12px 20px;
    border-radius:6px;
    display:inline-block;
}
.alert-error{
    background:#f8d7da;
    color:#842029;
    border:1px solid #f5c2c7;
    padding:12px 14px;
    border-radius:8px;
    margin-bottom:18px;
}
.alert-error ul{
    margin:0;
    padding-left:18px;
}
.readonly-box{
    background:#eef2f7;
}

/* ── Estilos para bloques condicionales del flujo jurídico ── */
.flujo-bloque{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:8px;
    padding:14px 16px;
    margin-bottom:4px;
}
.flujo-bloque.bloque-warn{
    border-left:4px solid #f59e0b;
    background:#fffbeb;
}
.flujo-bloque.bloque-info{
    border-left:4px solid #3b82f6;
    background:#eff6ff;
}
.flujo-bloque.bloque-danger{
    border-left:4px solid #ef4444;
    background:#fef2f2;
}
.flujo-bloque.bloque-success{
    border-left:4px solid #10b981;
    background:#f0fdf4;
}
.flujo-bloque label{
    font-weight:600;
    font-size:13px;
    display:block;
    margin-bottom:4px;
    color:#374151;
}
.flujo-bloque select,
.flujo-bloque input{
    margin-bottom:0;
}
.flujo-titulo{
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:#6b7280;
    margin-bottom:12px;
    margin-top:0;
}
.campo-bloqueado{
    opacity:.5;
    pointer-events:none;
}

@media (max-width:900px){
    .grid{
        grid-template-columns:1fr;
    }
    body{
        padding:18px;
    }
}
</style>
</head>
<body>
<div class="container">
    <div class="header-top">
        <div>
            <h2>Editar Caso</h2>
            <div class="badge">{{ $caso->numero_caso }}</div>
        </div>
        <div>
            <strong>Estado actual:</strong> {{ $caso->estado ?: 'N/A' }}<br>
            <strong>Avance:</strong> {{ $caso->porcentaje_avance ?? 0 }}%
        </div>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <strong>Corrige los siguientes errores:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('casos.update', $caso) }}">
        @csrf
        @method('PUT')

        <div class="grid">

            {{-- ================================================================ --}}
            {{-- 1. INFORMACIÓN GENERAL                                           --}}
            {{-- ================================================================ --}}
            <div class="full section">
                <h3>1. Información general de la víctima</h3>
            </div>

            <div>
                <label>Nombres</label>
                <input type="text" name="nombres" value="{{ old('nombres', $caso->nombres) }}" required>
            </div>

            <div>
                <label>Apellidos</label>
                <input type="text" name="apellidos" value="{{ old('apellidos', $caso->apellidos) }}" required>
            </div>

            <div>
                <label>Cédula</label>
                <input type="text" name="cedula" value="{{ old('cedula', $caso->cedula) }}" required>
            </div>

            <div>
                <label>Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $caso->telefono) }}">
            </div>

            <div>
                <label>Correo</label>
                <input type="email" name="correo" value="{{ old('correo', $caso->correo) }}">
            </div>

            <div>
                <label>Departamento</label>
                <input type="text" name="departamento" value="{{ old('departamento', $caso->departamento) }}">
            </div>

            <div>
                <label>Ciudad</label>
                <input type="text" name="ciudad" value="{{ old('ciudad', $caso->ciudad) }}">
            </div>

            <div>
                <label>Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion', $caso->direccion) }}">
            </div>

            <div>
                <label>Fecha del accidente</label>
                <input type="date" name="fecha_accidente"
                    value="{{ old('fecha_accidente', $caso->fecha_accidente ? \Carbon\Carbon::parse($caso->fecha_accidente)->format('Y-m-d') : '') }}">
            </div>

            <div>
                <label>Aseguradora</label>
                <select name="aseguradora" required>
                    @foreach($aseguradoras as $aseguradora)
                        <option value="{{ $aseguradora }}"
                            {{ old('aseguradora', $caso->aseguradora) == $aseguradora ? 'selected' : '' }}>
                            {{ $aseguradora }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Junta asignada</label>
                <select name="junta_asignada">
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
                <label>Estado</label>
                <select name="estado" required>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}"
                            {{ old('estado', $caso->estado) == $estado ? 'selected' : '' }}>
                            {{ $estado }}
                        </option>
                    @endforeach
                </select>
                <span class="helper">El sistema puede reajustar este estado según las fechas diligenciadas.</span>
            </div>

            {{-- ================================================================ --}}
            {{-- 2. DOCUMENTACIÓN INICIAL                                         --}}
            {{-- ================================================================ --}}
            <div class="full section">
                <h3>2. Documentación inicial del negocio</h3>
            </div>

            <div>
                <label>¿Tiene poder firmado?</label>
                <select name="tiene_poder">
                    <option value="0" {{ old('tiene_poder', $caso->tiene_poder ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('tiene_poder', $caso->tiene_poder ?? 0) == 1 ? 'selected' : '' }}>Sí</option>
                </select>
                <span class="helper">Marca si la víctima ya entregó el poder firmado ante notaría.</span>
            </div>

            <div>
                <label>Fecha entrega de poder</label>
                <input type="date" name="fecha_entrega_poder"
                    value="{{ old('fecha_entrega_poder', !empty($caso->fecha_entrega_poder) ? \Carbon\Carbon::parse($caso->fecha_entrega_poder)->format('Y-m-d') : '') }}">
            </div>

            <div>
                <label>Fecha poder firmado</label>
                <input type="date" name="fecha_poder_firmado"
                    value="{{ old('fecha_poder_firmado', !empty($caso->fecha_poder_firmado) ? \Carbon\Carbon::parse($caso->fecha_poder_firmado)->format('Y-m-d') : '') }}">
            </div>

            <div>
                <label>¿Tiene contrato firmado?</label>
                <select name="tiene_contrato">
                    <option value="0" {{ old('tiene_contrato', $caso->tiene_contrato ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('tiene_contrato', $caso->tiene_contrato ?? 0) == 1 ? 'selected' : '' }}>Sí</option>
                </select>
                <span class="helper">Marca si la víctima ya entregó el contrato firmado.</span>
            </div>

            <div>
                <label>Fecha entrega de contrato</label>
                <input type="date" name="fecha_entrega_contrato"
                    value="{{ old('fecha_entrega_contrato', !empty($caso->fecha_entrega_contrato) ? \Carbon\Carbon::parse($caso->fecha_entrega_contrato)->format('Y-m-d') : '') }}">
            </div>

            <div>
                <label>Fecha contrato firmado</label>
                <input type="date" name="fecha_contrato_firmado"
                    value="{{ old('fecha_contrato_firmado', !empty($caso->fecha_contrato_firmado) ? \Carbon\Carbon::parse($caso->fecha_contrato_firmado)->format('Y-m-d') : '') }}">
            </div>

            {{-- ================================================================ --}}
            {{-- 3. REQUISITOS MÉDICOS                                            --}}
            {{-- ================================================================ --}}
            <div class="full section">
                <h3>3. Requisitos médicos y soportes previos</h3>
            </div>

            <div>
                <label>¿Tiene alta por ortopedia?</label>
                <select name="alta_ortopedia">
                    <option value="0" {{ old('alta_ortopedia', $caso->alta_ortopedia ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('alta_ortopedia', $caso->alta_ortopedia ?? 0) == 1 ? 'selected' : '' }}>Sí</option>
                </select>
                <span class="helper">Esto es clave antes de enviar la solicitud a junta.</span>
            </div>

            <div>
                <label>Fecha alta por ortopedia</label>
                <input type="date" name="fecha_alta_ortopedia"
                    value="{{ old('fecha_alta_ortopedia', !empty($caso->fecha_alta_ortopedia) ? \Carbon\Carbon::parse($caso->fecha_alta_ortopedia)->format('Y-m-d') : '') }}">
            </div>

            <div class="full">
                <label>Observación alta por ortopedia</label>
                <textarea name="observacion_alta_ortopedia">{{ old('observacion_alta_ortopedia', $caso->observacion_alta_ortopedia ?? '') }}</textarea>
            </div>

            <div>
                <label>¿FURPEN completo?</label>
                <select name="furpen_completo">
                    <option value="0" {{ old('furpen_completo', $caso->furpen_completo ?? 0) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('furpen_completo', $caso->furpen_completo ?? 0) == 1 ? 'selected' : '' }}>Sí</option>
                </select>
                <span class="helper">Marca si la víctima ya entregó toda la información del FURPEN.</span>
            </div>

            <div>
                <label>Fecha FURPEN recibido</label>
                <input type="date" name="fecha_furpen_recibido"
                    value="{{ old('fecha_furpen_recibido', !empty($caso->fecha_furpen_recibido) ? \Carbon\Carbon::parse($caso->fecha_furpen_recibido)->format('Y-m-d') : '') }}">
            </div>

            <div class="full">
                <label>Observación FURPEN</label>
                <textarea name="observacion_furpen">{{ old('observacion_furpen', $caso->observacion_furpen ?? '') }}</textarea>
            </div>

            {{-- ================================================================ --}}
            {{-- 4. FLUJO JURÍDICO COMPLETO                                       --}}
            {{-- ================================================================ --}}
            <div class="full section">
                <h3>4. Flujo jurídico del caso</h3>
            </div>

            {{-- ── PASO 1: Solicitud ─────────────────────────────────────────── --}}
            <div class="full flujo-bloque bloque-info">
                <p class="flujo-titulo">Paso 1 — Solicitud a aseguradora</p>
                <div class="grid" style="gap:12px">
                    <div>
                        <label>Fecha solicitud de calificación</label>
                        <input type="date" name="fecha_solicitud_aseguradora"
                            value="{{ old('fecha_solicitud_aseguradora', $caso->fecha_solicitud_aseguradora ? \Carbon\Carbon::parse($caso->fecha_solicitud_aseguradora)->format('Y-m-d') : '') }}">
                        <span class="helper">Primer paso: cuando se radica la solicitud ante la aseguradora.</span>
                    </div>
                </div>
            </div>

            {{-- ── PASO 2: Respuesta aseguradora — CAMPO NUEVO ──────────────── --}}
            <div class="full flujo-bloque bloque-info">
                <p class="flujo-titulo">Paso 2 — Respuesta de la aseguradora</p>
                <div class="grid" style="gap:12px">
                    <div>
                        <label>Tipo de respuesta <span style="color:#ef4444">*</span></label>
                        <select name="tipo_respuesta_aseguradora" id="tipo_respuesta_aseguradora"
                            onchange="mostrarCamposRespuesta()">
                            <option value="">— Seleccionar —</option>
                            <option value="emitio_dictamen"
                                {{ old('tipo_respuesta_aseguradora', $caso->tipo_respuesta_aseguradora ?? '') == 'emitio_dictamen' ? 'selected' : '' }}>
                                Emitió dictamen (calificó)
                            </option>
                            <option value="nego"
                                {{ old('tipo_respuesta_aseguradora', $caso->tipo_respuesta_aseguradora ?? '') == 'nego' ? 'selected' : '' }}>
                                Negó la solicitud
                            </option>
                            <option value="no_respondio"
                                {{ old('tipo_respuesta_aseguradora', $caso->tipo_respuesta_aseguradora ?? '') == 'no_respondio' ? 'selected' : '' }}>
                                No respondió (pasó 1 mes)
                            </option>
                        </select>
                        <span class="helper">
                            <strong>Emitió dictamen</strong> → flujo de apelación.<br>
                            <strong>Negó / No respondió</strong> → flujo de tutela para calificación.
                        </span>
                    </div>

                    {{-- Fecha solo aplica si emitió dictamen o negó --}}
                    <div id="bloque_fecha_respuesta">
                        <label>Fecha respuesta / dictamen</label>
                        <input type="date" name="fecha_respuesta_aseguradora"
                            value="{{ old('fecha_respuesta_aseguradora', $caso->fecha_respuesta_aseguradora ? \Carbon\Carbon::parse($caso->fecha_respuesta_aseguradora)->format('Y-m-d') : '') }}">
                        <span class="helper">Dejar vacío si la respuesta fue "no respondió".</span>
                    </div>
                </div>
            </div>

            {{-- ── PASO 3: Apelación — solo aplica si emitió dictamen ───────── --}}
            <div class="full flujo-bloque" id="bloque_apelacion">
                <p class="flujo-titulo">Paso 3 — Apelación del dictamen (solo si emitió dictamen)</p>
                <div>
                    <label>Fecha apelación del dictamen</label>
                    <input type="date" name="fecha_apelacion"
                        value="{{ old('fecha_apelacion', $caso->fecha_apelacion ? \Carbon\Carbon::parse($caso->fecha_apelacion)->format('Y-m-d') : '') }}">
                    <span class="helper">Aplica cuando la aseguradora sí calificó y se decide apelar su dictamen.</span>
                </div>
            </div>

            {{-- ── PASO 4: Tutela — CAMPO NUEVO tipo_tutela ────────────────── --}}
            <div class="full flujo-bloque bloque-warn">
                <p class="flujo-titulo">Paso 4 — Tutela</p>
                <div class="grid" style="gap:12px">
                    <div>
                        <label>Fecha tutela</label>
                        <input type="date" name="fecha_tutela"
                            value="{{ old('fecha_tutela', $caso->fecha_tutela ? \Carbon\Carbon::parse($caso->fecha_tutela)->format('Y-m-d') : '') }}">
                        <span class="helper">Registrar cuando se presente la tutela.</span>
                    </div>
                    <div>
                        <label>Tipo de tutela <span style="color:#ef4444">*</span></label>
                        <select name="tipo_tutela">
                            <option value="">— Seleccionar —</option>
                            <option value="tutela_calificacion"
                                {{ old('tipo_tutela', $caso->tipo_tutela ?? '') == 'tutela_calificacion' ? 'selected' : '' }}>
                                Tutela para calificación (aseguradora negó o no respondió)
                            </option>
                            <option value="tutela_debido_proceso"
                                {{ old('tipo_tutela', $caso->tipo_tutela ?? '') == 'tutela_debido_proceso' ? 'selected' : '' }}>
                                Tutela por debido proceso (no pagan honorarios tras apelación)
                            </option>
                        </select>
                        <span class="helper">
                            <strong>Calificación</strong>: la aseguradora negó o no respondió.<br>
                            <strong>Debido proceso</strong>: apelaron pero no pagan honorarios después de 1 mes.
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── PASO 5: Fallo de tutela ──────────────────────────────────── --}}
            <div class="full flujo-bloque bloque-warn">
                <p class="flujo-titulo">Paso 5 — Fallo de tutela</p>
                <div class="grid" style="gap:12px">
                    <div>
                        <label>Fecha fallo de tutela</label>
                        <input type="date" name="fecha_fallo_tutela"
                            value="{{ old('fecha_fallo_tutela', !empty($caso->fecha_fallo_tutela) ? \Carbon\Carbon::parse($caso->fecha_fallo_tutela)->format('Y-m-d') : '') }}">
                    </div>
                    <div>
                        <label>Resultado del fallo</label>
                        <select name="resultado_fallo_tutela" id="resultado_fallo_tutela"
                            onchange="mostrarCamposFallo()">
                            <option value="">— Seleccionar —</option>
                            <option value="concedido"
                                {{ old('resultado_fallo_tutela', $caso->resultado_fallo_tutela ?? '') == 'concedido' ? 'selected' : '' }}>
                                Concedido
                            </option>
                            <option value="negado"
                                {{ old('resultado_fallo_tutela', $caso->resultado_fallo_tutela ?? '') == 'negado' ? 'selected' : '' }}>
                                Negado
                            </option>
                            <option value="parcial"
                                {{ old('resultado_fallo_tutela', $caso->resultado_fallo_tutela ?? '') == 'parcial' ? 'selected' : '' }}>
                                Parcial
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── PASO 5A: Si fallo CONCEDIDO → Cumplimiento — CAMPO NUEVO ── --}}
            <div class="full flujo-bloque bloque-success" id="bloque_cumplimiento_tutela">
                <p class="flujo-titulo">Paso 5A — Cumplimiento del fallo concedido</p>
                <div class="grid" style="gap:12px">
                    <div>
                        <label>Fecha cumplimiento</label>
                        <input type="date" name="fecha_cumplimiento_tutela"
                            value="{{ old('fecha_cumplimiento_tutela', !empty($caso->fecha_cumplimiento_tutela) ? \Carbon\Carbon::parse($caso->fecha_cumplimiento_tutela)->format('Y-m-d') : '') }}">
                        <span class="helper">Fecha en que la aseguradora acató el fallo.</span>
                    </div>
                    <div>
                        <label>Tipo de cumplimiento</label>
                        <select name="tipo_cumplimiento_tutela">
                            <option value="">— Seleccionar —</option>
                            <option value="voluntario"
                                {{ old('tipo_cumplimiento_tutela', $caso->tipo_cumplimiento_tutela ?? '') == 'voluntario' ? 'selected' : '' }}>
                                Voluntario (cumplió dentro de las 2 semanas)
                            </option>
                            <option value="desacato"
                                {{ old('tipo_cumplimiento_tutela', $caso->tipo_cumplimiento_tutela ?? '') == 'desacato' ? 'selected' : '' }}>
                                Tras incidente de desacato
                            </option>
                        </select>
                        <span class="helper">
                            Luego de registrar el cumplimiento, registra el pago de honorarios
                            (si tutela fue por debido proceso) o el dictamen de la aseguradora
                            (si tutela fue para calificación).
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── PASO 5B: Si fallo CONCEDIDO pero NO cumple → Desacato ───── --}}
            <div class="full flujo-bloque bloque-danger" id="bloque_desacato">
                <p class="flujo-titulo">Paso 5B — Incidente de desacato (no cumplieron en 14 días)</p>
                <div>
                    <label>Fecha incidente de desacato</label>
                    <input type="date" name="fecha_incidente_desacato"
                        value="{{ old('fecha_incidente_desacato', !empty($caso->fecha_incidente_desacato) ? \Carbon\Carbon::parse($caso->fecha_incidente_desacato)->format('Y-m-d') : '') }}">
                    <span class="helper">Se usa cuando existe fallo favorable pero no cumplen en el plazo de 14 días.</span>
                </div>
            </div>

            {{-- ── PASO 5C: Si fallo NEGADO → Impugnación ─────────────────── --}}
            <div class="full flujo-bloque bloque-danger" id="bloque_impugnacion">
                <p class="flujo-titulo">Paso 5C — Impugnación (fallo negado)</p>
                <div>
                    <label>Fecha impugnación</label>
                    <input type="date" name="fecha_impugnacion"
                        value="{{ old('fecha_impugnacion', !empty($caso->fecha_impugnacion) ? \Carbon\Carbon::parse($caso->fecha_impugnacion)->format('Y-m-d') : '') }}">
                    <span class="helper">Se usa cuando el fallo de tutela es desfavorable y se impugna ante segunda instancia.</span>
                </div>
            </div>

            {{-- ── PASO 5D: Segunda instancia — CAMPO NUEVO ────────────────── --}}
            <div class="full flujo-bloque bloque-danger" id="bloque_segunda_instancia">
                <p class="flujo-titulo">Paso 5D — Fallo de segunda instancia (tras impugnación)</p>
                <div class="grid" style="gap:12px">
                    <div>
                        <label>Fecha fallo segunda instancia</label>
                        <input type="date" name="fecha_fallo_segunda_instancia"
                            value="{{ old('fecha_fallo_segunda_instancia', !empty($caso->fecha_fallo_segunda_instancia) ? \Carbon\Carbon::parse($caso->fecha_fallo_segunda_instancia)->format('Y-m-d') : '') }}">
                    </div>
                    <div>
                        <label>Resultado segunda instancia</label>
                        <select name="resultado_fallo_segunda_instancia"
                            id="resultado_segunda_instancia"
                            onchange="mostrarResultadoSegundaInstancia()">
                            <option value="">— Seleccionar —</option>
                            <option value="confirma"
                                {{ old('resultado_fallo_segunda_instancia', $caso->resultado_fallo_segunda_instancia ?? '') == 'confirma' ? 'selected' : '' }}>
                                Confirma — el caso se pierde
                            </option>
                            <option value="revoca"
                                {{ old('resultado_fallo_segunda_instancia', $caso->resultado_fallo_segunda_instancia ?? '') == 'revoca' ? 'selected' : '' }}>
                                Revoca — la aseguradora debe cumplir
                            </option>
                        </select>
                        <span class="helper" id="helper_segunda_instancia">
                            @if(($caso->resultado_fallo_segunda_instancia ?? '') == 'confirma')
                                <strong style="color:#ef4444">Caso cerrado desfavorablemente.</strong>
                            @elseif(($caso->resultado_fallo_segunda_instancia ?? '') == 'revoca')
                                <strong style="color:#10b981">La aseguradora debe cumplir — registrar cumplimiento y continuar flujo.</strong>
                            @else
                                Confirma: el caso queda cerrado. Revoca: la aseguradora debe calificar o pagar honorarios.
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── PASO 6: Pago de honorarios ───────────────────────────────── --}}
            <div class="full flujo-bloque bloque-info">
                <p class="flujo-titulo">Paso 6 — Pago de honorarios a junta</p>
                <div>
                    <label>Fecha pago de honorarios</label>
                    <input type="date" name="fecha_pago_honorarios"
                        value="{{ old('fecha_pago_honorarios', $caso->fecha_pago_honorarios ? \Carbon\Carbon::parse($caso->fecha_pago_honorarios)->format('Y-m-d') : '') }}">
                    <span class="helper">
                        Se registra cuando la aseguradora paga los honorarios para acudir a la junta.<br>
                        <strong>Nota:</strong> si la tutela fue por debido proceso, este campo se activa
                        luego del cumplimiento del fallo.
                    </span>
                </div>
            </div>

            {{-- ── PASO 7-12: Flujo post-honorarios ────────────────────────── --}}
            <div class="full flujo-bloque bloque-success">
                <p class="flujo-titulo">Pasos 7-12 — Flujo post-honorarios (junta → pago final)</p>
                <div class="grid" style="gap:12px">
                    <div>
                        <label>Fecha solicitud / envío a junta</label>
                        <input type="date" name="fecha_envio_junta"
                            value="{{ old('fecha_envio_junta', $caso->fecha_envio_junta ? \Carbon\Carbon::parse($caso->fecha_envio_junta)->format('Y-m-d') : '') }}">
                        <span class="helper">Solo con honorarios pagados y alta ortopedia confirmada.</span>
                    </div>

                    <div>
                        <label>Fecha dictamen de junta</label>
                        <input type="date" name="fecha_dictamen_junta"
                            value="{{ old('fecha_dictamen_junta', $caso->fecha_dictamen_junta ? \Carbon\Carbon::parse($caso->fecha_dictamen_junta)->format('Y-m-d') : '') }}">
                        <span class="helper">Cuando la junta emita el dictamen definitivo.</span>
                    </div>

                    <div>
                        <label>Porcentaje PCL</label>
                        <input type="number" step="0.01" min="0" name="porcentaje_pcl"
                            value="{{ old('porcentaje_pcl', $caso->porcentaje_pcl) }}">
                    </div>

                    <div>
                        <label>Valor reclamado</label>
                        <input type="number" step="0.01" min="0" name="valor_reclamado"
                            value="{{ old('valor_reclamado', $caso->valor_reclamado) }}">
                    </div>

                    <div>
                        <label>Fecha cobro a aseguradora</label>
                        <input type="date" name="fecha_reclamacion_final"
                            value="{{ old('fecha_reclamacion_final', $caso->fecha_reclamacion_final ? \Carbon\Carbon::parse($caso->fecha_reclamacion_final)->format('Y-m-d') : '') }}">
                        <span class="helper">Envío del cobro con el dictamen de junta.</span>
                    </div>

                    <div>
                        <label>Fecha pago final</label>
                        <input type="date" name="fecha_pago_final"
                            value="{{ old('fecha_pago_final', $caso->fecha_pago_final ? \Carbon\Carbon::parse($caso->fecha_pago_final)->format('Y-m-d') : '') }}">
                        <span class="helper">Cuando la aseguradora efectivamente pague la indemnización.</span>
                    </div>
                </div>
            </div>

            {{-- ================================================================ --}}
            {{-- 5. DATOS FINANCIEROS                                             --}}
            {{-- ================================================================ --}}
            <div class="full section">
                <h3>5. Datos financieros</h3>
            </div>

            <div>
                <label>Valor estimado</label>
                <input type="text" class="readonly-box"
                    value="{{ $caso->valor_estimado ? '$' . number_format($caso->valor_estimado, 0, ',', '.') : 'No calculado' }}"
                    readonly>
            </div>

            <div>
                <label>SMLDV aplicados</label>
                <input type="text" class="readonly-box" value="{{ $caso->smldv_aplicados ?? 'N/A' }}" readonly>
            </div>

            <div>
                <label>Valor pagado</label>
                <input type="number" step="0.01" min="0" id="valor_pagado" name="valor_pagado"
                    value="{{ old('valor_pagado', $caso->valor_pagado) }}">
            </div>

            <div>
                <label>Porcentaje honorarios</label>
                <select name="porcentaje_honorarios" id="porcentaje_honorarios">
                    <option value="">Seleccionar</option>
                    <option value="40"
                        {{ in_array(old('porcentaje_honorarios', (string)$caso->porcentaje_honorarios), ['40','40.00']) ? 'selected' : '' }}>
                        40%
                    </option>
                    <option value="50"
                        {{ in_array(old('porcentaje_honorarios', (string)$caso->porcentaje_honorarios), ['50','50.00']) ? 'selected' : '' }}>
                        50%
                    </option>
                </select>
            </div>

            <div class="full finanzas">
                <h3>Resumen financiero</h3>
                <div>
                    Ganancia equipo jurídico:
                    <span class="resultado" id="ganancia_equipo">
                        ${{ number_format($caso->ganancia_equipo ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div>
                    Valor neto cliente:
                    <span class="resultado" id="valor_cliente">
                        ${{ number_format($caso->valor_neto_cliente ?? 0, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="full">
                <label>Observaciones</label>
                <textarea name="observaciones">{{ old('observaciones', $caso->observaciones) }}</textarea>
            </div>

        </div>{{-- /grid --}}

        <div class="actions">
            <button type="submit">Actualizar Caso</button>
            <a href="{{ route('casos.index') }}" class="btn-secondary">← Volver</a>
        </div>
    </form>
</div>

<script>
// ─── Finanzas ────────────────────────────────────────────────────────────────
function calcularFinanzas() {
    const valor      = parseFloat(document.getElementById('valor_pagado').value) || 0;
    const porcentaje = parseFloat(document.getElementById('porcentaje_honorarios').value) || 0;
    const ganancia   = (valor * porcentaje) / 100;
    const cliente    = valor - ganancia;
    document.getElementById('ganancia_equipo').innerText = '$' + Math.round(ganancia).toLocaleString('es-CO');
    document.getElementById('valor_cliente').innerText   = '$' + Math.round(cliente).toLocaleString('es-CO');
}
document.getElementById('valor_pagado').addEventListener('input', calcularFinanzas);
document.getElementById('porcentaje_honorarios').addEventListener('change', calcularFinanzas);

// ─── Visibilidad de bloques según tipo de respuesta ──────────────────────────
function mostrarCamposRespuesta() {
    const tipo          = document.getElementById('tipo_respuesta_aseguradora').value;
    const bloqueApel    = document.getElementById('bloque_apelacion');
    const bloqueFecha   = document.getElementById('bloque_fecha_respuesta');

    // Apelación solo aplica cuando emitió dictamen
    bloqueApel.style.display = (tipo === 'emitio_dictamen') ? '' : 'none';

    // Fecha de respuesta no aplica si no respondió
    bloqueFecha.style.display = (tipo === 'no_respondio') ? 'none' : '';
}

// ─── Visibilidad de bloques según resultado del fallo de tutela ──────────────
function mostrarCamposFallo() {
    const resultado              = document.getElementById('resultado_fallo_tutela').value;
    const bloqueCumplimiento     = document.getElementById('bloque_cumplimiento_tutela');
    const bloqueDesacato         = document.getElementById('bloque_desacato');
    const bloqueImpugnacion      = document.getElementById('bloque_impugnacion');
    const bloqueSegundaInstancia = document.getElementById('bloque_segunda_instancia');

    // Concedido → cumplimiento + desacato (si no cumple)
    bloqueCumplimiento.style.display     = (resultado === 'concedido') ? '' : 'none';
    bloqueDesacato.style.display         = (resultado === 'concedido') ? '' : 'none';

    // Negado/parcial → impugnación + segunda instancia
    bloqueImpugnacion.style.display      = (resultado === 'negado' || resultado === 'parcial') ? '' : 'none';
    bloqueSegundaInstancia.style.display = (resultado === 'negado' || resultado === 'parcial') ? '' : 'none';
}

// ─── Mensaje dinámico segunda instancia ──────────────────────────────────────
function mostrarResultadoSegundaInstancia() {
    const resultado = document.getElementById('resultado_segunda_instancia').value;
    const helper    = document.getElementById('helper_segunda_instancia');
    if (resultado === 'confirma') {
        helper.innerHTML = '<strong style="color:#ef4444">Caso cerrado desfavorablemente.</strong>';
    } else if (resultado === 'revoca') {
        helper.innerHTML = '<strong style="color:#10b981">La aseguradora debe cumplir — registrar cumplimiento y continuar flujo.</strong>';
    } else {
        helper.innerHTML = 'Confirma: el caso queda cerrado. Revoca: la aseguradora debe calificar o pagar honorarios.';
    }
}

// ─── Init: aplicar visibilidad según valores actuales al cargar la página ────
document.addEventListener('DOMContentLoaded', function () {
    mostrarCamposRespuesta();
    mostrarCamposFallo();
    mostrarResultadoSegundaInstancia();
});
</script>
</body>
</html>