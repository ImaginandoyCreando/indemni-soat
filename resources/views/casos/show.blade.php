<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle del Caso - INDEMNI SOAT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{
    font-family:Arial,Helvetica,sans-serif;
    background:#f4f6f9;
    margin:0;
    color:#111827;
}
.layout{display:flex;min-height:100vh}
.sidebar{
    width:260px;
    background:linear-gradient(180deg,#1f2937 0%,#172033 100%);
    color:#fff;
    padding:25px 18px;
    display:flex;
    flex-direction:column;
    flex-shrink:0;
}
.brand{font-size:28px;font-weight:bold;margin-bottom:30px;line-height:1.2}
.menu a{display:block;padding:12px 14px;margin-bottom:10px;text-decoration:none;color:#fff;background:#374151;border-radius:10px;transition:.2s ease}
.menu a:hover{background:#2563eb}
.menu a.active{background:#2563eb}
.user-box{margin-top:auto;padding-top:16px;border-top:1px solid #374151}
.user-name{font-size:13px;font-weight:bold;color:#fff;margin-bottom:2px}
.user-role{font-size:11px;color:#9ca3af;margin-bottom:10px}
.logout-btn{width:100%;padding:8px;background:#374151;color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer;font-family:inherit;transition:.2s}
.logout-btn:hover{background:#dc3545}
.main-content{flex:1;padding:30px}
.container{
    max-width:1250px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
}
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}
.card{
    background:#f8f9fa;
    border:1px solid #ddd;
    border-radius:8px;
    padding:15px;
}
h1,h2,h3{margin-top:0}
.label{font-weight:bold;font-size:13px;color:#6b7280;margin-bottom:4px}
.value{margin-top:4px;font-size:15px}
.actions{
    margin-top:20px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.btn{
    display:inline-block;
    padding:10px 16px;
    background:#0d6efd;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
}
.btn-warning{background:#ffc107;color:#000}
.btn-secondary{background:#6c757d}
.full{grid-column:1 / -1}

.timeline{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    margin-bottom:25px;
}
.step{
    border-radius:10px;
    padding:14px;
    border:1px solid #dcdcdc;
    background:#f1f3f5;
    position:relative;
}
.step.done{background:#d1e7dd;border-color:#a3cfbb}
.step.warn{background:#fff3cd;border-color:#ffc107}
.step.danger{background:#f8d7da;border-color:#f5c6cb}
.step.closed{background:#e2e3e5;border-color:#c8c9ca}
.step-title{font-weight:bold;margin-bottom:6px;font-size:14px}
.step-date{font-size:13px;color:#374151}
.step-badge{
    display:inline-block;
    margin-top:6px;
    padding:3px 8px;
    border-radius:999px;
    font-size:11px;
    font-weight:bold;
}
.badge-ok{background:#a3cfbb;color:#0f5132}
.badge-warn{background:#ffc107;color:#664d03}
.badge-danger{background:#f5c6cb;color:#842029}
.badge-info{background:#b6effb;color:#055160}
.badge-purple{background:#ede9fe;color:#5b21b6}
.badge-gray{background:#dee2e6;color:#41464b}

.header-box{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
    flex-wrap:wrap;
    margin-bottom:20px;
}
.badge-num{
    display:inline-block;
    padding:8px 12px;
    border-radius:999px;
    background:#0d6efd;
    color:#fff;
    font-size:14px;
}
.money{font-weight:bold;color:#198754}
.finanzas{background:#f8fafc;border:1px solid #dbe4f0}
.helper{
    display:block;margin-top:6px;
    font-size:12px;color:#64748b;line-height:1.4;
}
.alert-chip{
    display:inline-block;
    padding:8px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:bold;
}
.alert-red{background:#f8d7da;color:#842029}
.alert-orange{background:#fff3cd;color:#997404}
.alert-blue{background:#cff4fc;color:#055160}
.alert-green{background:#d1e7dd;color:#0f5132}
.alert-gray{background:#e2e3e5;color:#41464b}
.section-title{margin-bottom:14px;margin-top:25px}

.flujo-resumen{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:10px;
    padding:16px 20px;
    margin-bottom:24px;
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    align-items:center;
}
.flujo-item{
    display:flex;
    align-items:center;
    gap:6px;
    font-size:13px;
}
.flujo-dot{
    width:10px;height:10px;
    border-radius:50%;
    flex-shrink:0;
}
.dot-done{background:#198754}
.dot-pending{background:#dee2e6}
.dot-warn{background:#ffc107}
.dot-danger{background:#dc3545}

@media (max-width:900px){
    .layout{flex-direction:column}
    .sidebar{width:100%}
    .grid{grid-template-columns:1fr}
    .timeline{grid-template-columns:1fr}
    .main-content{padding:18px}
}
</style>
</head>
<body>
<div class="layout">

    <aside class="sidebar">
        <div class="brand">INDEMNI<br>SOAT</div>
        <nav class="menu">
            <a href="{{ route('casos.index') }}" class="{{ request()->routeIs('casos.*') ? 'active' : '' }}">Casos</a>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            @if(auth()->user()->puedeGestionarUsuarios())
                <a href="{{ route('users.index') }}" style="background:#4b5563" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Usuarios</a>
            @endif
        </nav>
        <div style="flex:1"></div>
        <div class="user-box">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role">{{ auth()->user()->textoRol() }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <div class="main-content">
    <div class="container">

    @php
        $alertClass = match($caso->color_alerta) {
            'red'         => 'alert-red',
            'orange'      => 'alert-orange',
            'cyan','blue' => 'alert-blue',
            'green'       => 'alert-green',
            default       => 'alert-gray',
        };

        $fmt = fn($f) => $f ? \Carbon\Carbon::parse($f)->format('d/m/Y') : null;

        $fechaAccidente        = $fmt($caso->fecha_accidente)              ?? 'No registrada';
        $fechaSolicitud        = $fmt($caso->fecha_solicitud_aseguradora)  ?? 'Pendiente';
        $fechaRespuesta        = $fmt($caso->fecha_respuesta_aseguradora)  ?? 'Pendiente';
        $fechaTutela           = $fmt($caso->fecha_tutela)                 ?? 'Pendiente';
        $fechaFalloTutela      = $fmt($caso->fecha_fallo_tutela)           ?? 'Pendiente';
        $fechaCumplimiento     = $fmt($caso->fecha_cumplimiento_tutela)    ?? 'Pendiente';
        $fechaDesacato         = $fmt($caso->fecha_incidente_desacato)     ?? 'Pendiente';
        $fechaImpugnacion      = $fmt($caso->fecha_impugnacion)            ?? 'Pendiente';
        $fechaSegunda          = $fmt($caso->fecha_fallo_segunda_instancia)?? 'Pendiente';
        $fechaApelacion        = $fmt($caso->fecha_apelacion)              ?? 'Pendiente';
        $fechaPagoHonorarios   = $fmt($caso->fecha_pago_honorarios)        ?? 'Pendiente';
        $fechaAltaOrtopedia    = $fmt($caso->fecha_alta_ortopedia)         ?? 'Pendiente';
        $fechaEnvioJunta       = $fmt($caso->fecha_envio_junta)            ?? 'Pendiente';
        $fechaDictamenJunta    = $fmt($caso->fecha_dictamen_junta)         ?? 'Pendiente';
        $fechaFurpen           = $fmt($caso->fecha_furpen_recibido)        ?? 'Pendiente';
        $fechaReclamacion      = $fmt($caso->fecha_reclamacion_final)      ?? 'Pendiente';
        $fechaPagoFinal        = $fmt($caso->fecha_pago_final)             ?? 'Pendiente';
        $fechaPrescripcion     = $fmt($caso->fecha_prescripcion)           ?? 'No calculada';

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
            'desacato'   => 'Tras incidente de desacato',
            default      => 'Pendiente',
        };

        $textoResultadoSegunda = match($caso->resultado_fallo_segunda_instancia ?? '') {
            'confirma' => 'Confirma — caso cerrado',
            'revoca'   => 'Revoca — aseguradora debe cumplir',
            default    => 'Pendiente',
        };
    @endphp

    <div class="header-box">
        <div>
            <h1>Detalle del Caso</h1>
            <div class="badge-num">{{ $caso->numero_caso }}</div>
        </div>
        <div>
            <strong>Estado actual:</strong> {{ $caso->estado ?: 'N/A' }}<br>
            <strong>Avance del caso:</strong> {{ $caso->porcentaje_avance ?? 0 }}%<br><br>
            <span class="alert-chip {{ $alertClass }}">{{ $caso->texto_alerta }}</span>
        </div>
    </div>

    <div style="margin-bottom:25px;">
        <div style="font-weight:bold;margin-bottom:8px;">Progreso general</div>
        <div style="width:100%;background:#e9ecef;border-radius:999px;height:26px;overflow:hidden;">
            <div style="width:{{ $caso->porcentaje_avance ?? 0 }}%;background:#198754;height:26px;color:#fff;text-align:center;line-height:26px;font-weight:bold;">
                {{ $caso->porcentaje_avance ?? 0 }}%
            </div>
        </div>
    </div>

    <div class="flujo-resumen">
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_solicitud_aseguradora ? 'dot-done' : 'dot-pending' }}"></div>Solicitud</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->tipo_respuesta_aseguradora ? 'dot-done' : 'dot-pending' }}"></div>Respuesta</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_apelacion ? 'dot-done' : 'dot-pending' }}"></div>Apelación</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_tutela ? 'dot-done' : 'dot-pending' }}"></div>Tutela</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_fallo_tutela ? 'dot-done' : 'dot-pending' }}"></div>Fallo tutela</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_cumplimiento_tutela ? 'dot-done' : 'dot-pending' }}"></div>Cumplimiento</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_incidente_desacato ? 'dot-warn' : 'dot-pending' }}"></div>Desacato</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_impugnacion ? 'dot-warn' : 'dot-pending' }}"></div>Impugnación</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_fallo_segunda_instancia ? ($caso->resultado_fallo_segunda_instancia === 'confirma' ? 'dot-danger' : 'dot-done') : 'dot-pending' }}"></div>2ª instancia</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_pago_honorarios ? 'dot-done' : 'dot-pending' }}"></div>Honorarios</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->alta_ortopedia ? 'dot-done' : 'dot-pending' }}"></div>Alta ortopedia</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_envio_junta ? 'dot-done' : 'dot-pending' }}"></div>Junta</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_dictamen_junta ? 'dot-done' : 'dot-pending' }}"></div>Dictamen junta</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_reclamacion_final ? 'dot-done' : 'dot-pending' }}"></div>Reclamación</div>
        <div class="flujo-item"><div class="flujo-dot {{ $caso->fecha_pago_final ? 'dot-done' : 'dot-pending' }}"></div>Pago final</div>
    </div>

    <h2 class="section-title">Flujo jurídico del caso</h2>

    <div class="timeline">
        <div class="step {{ $caso->fecha_solicitud_aseguradora ? 'done' : '' }}">
            <div class="step-title">1. Solicitud a aseguradora</div>
            <div class="step-date">{{ $fechaSolicitud }}</div>
        </div>
        <div class="step {{ $caso->tipo_respuesta_aseguradora ? 'done' : '' }}">
            <div class="step-title">2. Respuesta de aseguradora</div>
            <div class="step-date">{{ $fechaRespuesta }}</div>
            @if($caso->tipo_respuesta_aseguradora)
                @php $badgeResp = match($caso->tipo_respuesta_aseguradora){'emitio_dictamen'=>'badge-ok','nego'=>'badge-danger','no_respondio'=>'badge-warn',default=>'badge-gray'}; @endphp
                <span class="step-badge {{ $badgeResp }}">{{ $textoTipoRespuesta }}</span>
            @endif
        </div>
        <div class="step {{ $caso->fecha_apelacion ? 'done' : '' }}">
            <div class="step-title">3. Apelación del dictamen</div>
            <div class="step-date">{{ $fechaApelacion }}</div>
            <span class="helper">Solo aplica cuando la aseguradora emitió dictamen.</span>
        </div>
        <div class="step {{ $caso->fecha_tutela ? 'done' : '' }}">
            <div class="step-title">4. Tutela</div>
            <div class="step-date">{{ $fechaTutela }}</div>
            @if($caso->tipo_tutela)
                @php $badgeTutela = $caso->tipo_tutela === 'tutela_calificacion' ? 'badge-info' : 'badge-purple'; @endphp
                <span class="step-badge {{ $badgeTutela }}">{{ $textoTipoTutela }}</span>
            @endif
        </div>
        <div class="step {{ $caso->fecha_fallo_tutela ? ($caso->resultado_fallo_tutela === 'negado' ? 'danger' : 'done') : '' }}">
            <div class="step-title">5. Fallo de tutela</div>
            <div class="step-date">{{ $fechaFalloTutela }}</div>
            @if($caso->resultado_fallo_tutela)
                @php $badgeFallo = match($caso->resultado_fallo_tutela){'concedido'=>'badge-ok','negado'=>'badge-danger','parcial'=>'badge-warn',default=>'badge-gray'}; @endphp
                <span class="step-badge {{ $badgeFallo }}">{{ $textoResultadoFallo }}</span>
            @endif
        </div>
        <div class="step {{ $caso->fecha_cumplimiento_tutela ? 'done' : '' }}">
            <div class="step-title">5A. Cumplimiento del fallo</div>
            <div class="step-date">{{ $fechaCumplimiento }}</div>
            @if($caso->tipo_cumplimiento_tutela)
                @php $badgeCumpl = $caso->tipo_cumplimiento_tutela === 'voluntario' ? 'badge-ok' : 'badge-warn'; @endphp
                <span class="step-badge {{ $badgeCumpl }}">{{ $textoTipoCumplimiento }}</span>
            @endif
        </div>
        <div class="step {{ $caso->fecha_incidente_desacato ? 'warn' : '' }}">
            <div class="step-title">5B. Incidente de desacato</div>
            <div class="step-date">{{ $fechaDesacato }}</div>
            <span class="helper">Cuando el fallo fue concedido pero no cumplieron en 14 días.</span>
        </div>
        <div class="step {{ $caso->fecha_impugnacion ? 'warn' : '' }}">
            <div class="step-title">5C. Impugnación</div>
            <div class="step-date">{{ $fechaImpugnacion }}</div>
            <span class="helper">Cuando el fallo de tutela fue negado o parcial.</span>
        </div>
        <div class="step {{ $caso->fecha_fallo_segunda_instancia ? ($caso->resultado_fallo_segunda_instancia === 'confirma' ? 'closed' : 'done') : '' }}">
            <div class="step-title">5D. Segunda instancia</div>
            <div class="step-date">{{ $fechaSegunda }}</div>
            @if($caso->resultado_fallo_segunda_instancia)
                @php $badgeSegunda = $caso->resultado_fallo_segunda_instancia === 'revoca' ? 'badge-ok' : 'badge-danger'; @endphp
                <span class="step-badge {{ $badgeSegunda }}">{{ $textoResultadoSegunda }}</span>
            @endif
        </div>
        <div class="step {{ $caso->fecha_pago_honorarios ? 'done' : '' }}">
            <div class="step-title">6. Pago de honorarios a junta</div>
            <div class="step-date">{{ $fechaPagoHonorarios }}</div>
        </div>
        <div class="step {{ $caso->alta_ortopedia ? 'done' : '' }}">
            <div class="step-title">7. Alta por ortopedia</div>
            <div class="step-date">{{ $fechaAltaOrtopedia }}</div>
            <span class="helper">{{ $caso->alta_ortopedia ? 'Confirmada' : 'Pendiente' }}</span>
        </div>
        <div class="step {{ $caso->fecha_envio_junta ? 'done' : '' }}">
            <div class="step-title">8. Solicitud / envío a junta</div>
            <div class="step-date">{{ $fechaEnvioJunta }}</div>
            <span class="helper">Requiere honorarios pagados + alta ortopedia.</span>
        </div>
        <div class="step {{ $caso->fecha_dictamen_junta ? 'done' : '' }}">
            <div class="step-title">9. Dictamen de junta</div>
            <div class="step-date">{{ $fechaDictamenJunta }}</div>
        </div>
        <div class="step {{ $caso->furpen_completo ? 'done' : '' }}">
            <div class="step-title">10. FURPEN</div>
            <div class="step-date">{{ $fechaFurpen }}</div>
            <span class="helper">{{ $caso->furpen_completo ? 'Completo' : 'Pendiente' }}</span>
        </div>
        <div class="step {{ $caso->fecha_reclamacion_final ? 'done' : '' }}">
            <div class="step-title">11. Cobro a aseguradora</div>
            <div class="step-date">{{ $fechaReclamacion }}</div>
        </div>
        <div class="step {{ $caso->fecha_pago_final ? 'done' : '' }}">
            <div class="step-title">12. Pago final</div>
            <div class="step-date">{{ $fechaPagoFinal }}</div>
        </div>
    </div>

    <h2 class="section-title">Detalle del flujo jurídico</h2>
    <div class="grid">
        <div class="card"><div class="label">Tipo de respuesta aseguradora</div><div class="value">{{ $textoTipoRespuesta }}</div></div>
        <div class="card"><div class="label">Fecha respuesta / dictamen aseguradora</div><div class="value">{{ $fechaRespuesta }}</div></div>
        <div class="card"><div class="label">Tipo de tutela presentada</div><div class="value">{{ $textoTipoTutela }}</div></div>
        <div class="card"><div class="label">Fecha tutela</div><div class="value">{{ $fechaTutela }}</div></div>
        <div class="card"><div class="label">Resultado fallo tutela</div><div class="value">{{ $textoResultadoFallo }}</div></div>
        <div class="card"><div class="label">Fecha fallo tutela</div><div class="value">{{ $fechaFalloTutela }}</div></div>
        <div class="card"><div class="label">Cumplimiento del fallo</div><div class="value">{{ $textoTipoCumplimiento }}</div></div>
        <div class="card"><div class="label">Fecha cumplimiento tutela</div><div class="value">{{ $fechaCumplimiento }}</div></div>
        <div class="card"><div class="label">Resultado segunda instancia</div><div class="value">{{ $textoResultadoSegunda }}</div></div>
        <div class="card"><div class="label">Fecha fallo segunda instancia</div><div class="value">{{ $fechaSegunda }}</div></div>
        <div class="card"><div class="label">Fecha incidente de desacato</div><div class="value">{{ $fechaDesacato }}</div></div>
        <div class="card"><div class="label">Fecha impugnación</div><div class="value">{{ $fechaImpugnacion }}</div></div>
        <div class="card"><div class="label">Observación pago</div><div class="value">{{ $caso->observacion_pago ?: 'Sin observación' }}</div></div>
        <div class="card"><div class="label">Observación reclamación</div><div class="value">{{ $caso->observacion_reclamacion ?: 'Sin observación' }}</div></div>
    </div>

    <h2 class="section-title">Información general</h2>
    <div class="grid">
        <div class="card"><div class="label">Víctima</div><div class="value">{{ $caso->nombres }} {{ $caso->apellidos }}</div></div>
        <div class="card"><div class="label">Cédula</div><div class="value">{{ $caso->cedula }}</div></div>
        <div class="card"><div class="label">Teléfono</div><div class="value">{{ $caso->telefono ?: 'No registrado' }}</div></div>
        <div class="card"><div class="label">Correo</div><div class="value">{{ $caso->correo ?: 'No registrado' }}</div></div>
        <div class="card"><div class="label">Departamento</div><div class="value">{{ $caso->departamento ?: 'No registrado' }}</div></div>
        <div class="card"><div class="label">Ciudad</div><div class="value">{{ $caso->ciudad ?: 'No registrada' }}</div></div>
        <div class="card"><div class="label">Dirección</div><div class="value">{{ $caso->direccion ?: 'No registrada' }}</div></div>
        <div class="card"><div class="label">Fecha del accidente</div><div class="value">{{ $fechaAccidente }}</div></div>
        <div class="card"><div class="label">Aseguradora</div><div class="value">{{ $caso->aseguradora ?: 'No registrada' }}</div></div>
        <div class="card"><div class="label">Junta asignada</div><div class="value">{{ $caso->junta_asignada ?: 'No registrada' }}</div></div>
        <div class="card"><div class="label">Porcentaje PCL</div><div class="value">{{ $caso->porcentaje_pcl ?: 'No registrado' }}</div></div>
        <div class="card"><div class="label">Estado</div><div class="value">{{ $caso->estado ?: 'No registrado' }}</div></div>
    </div>

    <h2 class="section-title">Documentación inicial</h2>
    <div class="grid">
        <div class="card"><div class="label">Poder firmado</div><div class="value">{{ $caso->tiene_poder ? 'Sí' : 'No' }}</div></div>
        <div class="card"><div class="label">Fecha entrega poder</div><div class="value">{{ $fmt($caso->fecha_entrega_poder) ?? 'No registrada' }}</div></div>
        <div class="card"><div class="label">Fecha poder firmado</div><div class="value">{{ $fmt($caso->fecha_poder_firmado) ?? 'Pendiente' }}</div></div>
        <div class="card"><div class="label">Contrato firmado</div><div class="value">{{ $caso->tiene_contrato ? 'Sí' : 'No' }}</div></div>
        <div class="card"><div class="label">Fecha entrega contrato</div><div class="value">{{ $fmt($caso->fecha_entrega_contrato) ?? 'No registrada' }}</div></div>
        <div class="card"><div class="label">Fecha contrato firmado</div><div class="value">{{ $fmt($caso->fecha_contrato_firmado) ?? 'Pendiente' }}</div></div>
    </div>

    <h2 class="section-title">Requisitos médicos y soportes</h2>
    <div class="grid">
        <div class="card"><div class="label">Alta por ortopedia</div><div class="value">{{ $caso->alta_ortopedia ? 'Sí' : 'No' }}</div></div>
        <div class="card"><div class="label">Fecha alta ortopedia</div><div class="value">{{ $fechaAltaOrtopedia }}</div></div>
        <div class="card full"><div class="label">Observación alta ortopedia</div><div class="value">{{ $caso->observacion_alta_ortopedia ?: 'Sin observación' }}</div></div>
        <div class="card"><div class="label">FURPEN completo</div><div class="value">{{ $caso->furpen_completo ? 'Sí' : 'No' }}</div></div>
        <div class="card"><div class="label">Fecha FURPEN recibido</div><div class="value">{{ $fechaFurpen }}</div></div>
        <div class="card full"><div class="label">Observación FURPEN</div><div class="value">{{ $caso->observacion_furpen ?: 'Sin observación' }}</div></div>
    </div>

    <h2 class="section-title">Resumen económico</h2>
    <div class="grid">
        <div class="card finanzas"><div class="label">Valor estimado</div><div class="value money">{{ $caso->valor_estimado ? '$'.number_format($caso->valor_estimado,0,',','.') : 'No registrado' }}</div></div>
        <div class="card finanzas"><div class="label">Valor reclamado</div><div class="value money">{{ $caso->valor_reclamado ? '$'.number_format($caso->valor_reclamado,0,',','.') : 'No registrado' }}</div></div>
        <div class="card finanzas"><div class="label">Valor pagado</div><div class="value money">{{ $caso->valor_pagado ? '$'.number_format($caso->valor_pagado,0,',','.') : 'No registrado' }}</div></div>
        <div class="card finanzas"><div class="label">Porcentaje honorarios</div><div class="value">{{ $caso->porcentaje_honorarios ? number_format($caso->porcentaje_honorarios,0,',','.').'%' : 'No registrado' }}</div></div>
        <div class="card finanzas"><div class="label">Ganancia equipo jurídico</div><div class="value money">{{ $caso->ganancia_equipo ? '$'.number_format($caso->ganancia_equipo,0,',','.') : 'No registrado' }}</div></div>
        <div class="card finanzas"><div class="label">Valor neto cliente</div><div class="value money">{{ $caso->valor_neto_cliente ? '$'.number_format($caso->valor_neto_cliente,0,',','.') : 'No registrado' }}</div></div>
        <div class="card full"><div class="label">Observaciones</div><div class="value">{{ $caso->observaciones ?: 'Sin observaciones' }}</div></div>
    </div>

    <h2 class="section-title">Control legal</h2>
    <div class="grid">
        <div class="card"><div class="label">Fecha prescripción</div><div class="value">{{ $fechaPrescripcion }}</div></div>
        <div class="card">
            <div class="label">Días para prescripción</div>
            <div class="value">
                @if($diasPrescripcion !== null)
                    @if($diasPrescripcion < 0)
                        <span style="color:#dc3545;font-weight:bold;">Prescrito hace {{ abs($diasPrescripcion) }} día(s)</span>
                    @elseif($diasPrescripcion <= 90)
                        <span style="color:#dc3545;font-weight:bold;">{{ $diasPrescripcion }} día(s) — ⚠️ Crítico</span>
                    @else
                        {{ $diasPrescripcion }} día(s)
                    @endif
                @else
                    N/A
                @endif
            </div>
        </div>
    </div>

    <div class="actions">
        @if(auth()->user()->puedeEditar())
            <a href="{{ route('casos.edit', $caso) }}" class="btn btn-warning">Editar</a>
        @endif
        <a href="{{ route('casos.documentos.index', $caso) }}" class="btn">Expediente</a>
        <a href="{{ route('casos.bitacoras.index', $caso) }}" class="btn btn-secondary">Bitácora</a>
        <a href="{{ route('casos.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    </div>
    </div>
</div>
</body>
</html>