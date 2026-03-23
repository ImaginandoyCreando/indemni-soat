@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
/* ── Cards del dashboard ── */
.is-dash-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px 20px;
    transition: all .2s, background .3s;
    cursor: default;
    position: relative;
    overflow: hidden;
}
.is-dash-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: var(--dc-accent, #1B4FFF);
    opacity: .85;
}
.is-dash-card:hover {
    border-color: var(--border-2);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}
.dc-label {
    font-size: 10px; font-weight: 700;
    color: var(--text-3); text-transform: uppercase;
    letter-spacing: .6px; margin-bottom: 9px;
}
.dc-value {
    font-family: 'Playfair Display', serif;
    font-size: 26px; font-weight: 700;
    color: var(--text-1); line-height: 1;
    transition: color .3s;
}
.dc-sub {
    font-size: 11px; color: var(--text-3);
    margin-top: 6px; line-height: 1.4;
}

/* ── Sección panel ── */
.is-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px 22px;
    margin-bottom: 18px;
    transition: background .3s, border-color .3s;
}
.is-panel-title {
    font-family: 'Playfair Display', serif;
    font-size: 16px; font-weight: 700;
    color: var(--text-1); margin-bottom: 4px;
    transition: color .3s;
}
.is-panel-sub {
    font-size: 12px; color: var(--text-3);
    margin-bottom: 16px; line-height: 1.4;
}

/* ── Alert boxes ── */
.is-alert-box {
    background: var(--bg-card);
    border: 1px solid var(--border-2);
    border-radius: 10px;
    padding: 14px 16px;
    transition: background .3s;
}
.is-alert-box.ab-warn   {
    background: rgba(245,158,11,0.06);
    border-color: rgba(245,158,11,0.22);
}
.is-alert-box.ab-red    {
    background: rgba(229,57,53,0.06);
    border-color: rgba(229,57,53,0.22);
}
.is-alert-box.ab-blue   {
    background: rgba(8,145,178,0.06);
    border-color: rgba(8,145,178,0.22);
}
.is-alert-box.ab-green  {
    background: rgba(5,150,105,0.06);
    border-color: rgba(5,150,105,0.22);
}
.is-alert-box.ab-purple {
    background: rgba(124,58,237,0.06);
    border-color: rgba(124,58,237,0.22);
}
.is-alert-box h4 {
    margin: 0 0 7px 0;
    font-size: 12px; font-weight: 700;
    color: var(--text-1);
    transition: color .3s;
}
.is-alert-count {
    font-family: 'Playfair Display', serif;
    font-size: 28px; font-weight: 700;
    line-height: 1;
}
.is-alert-small {
    font-size: 11px; color: var(--text-3);
    margin-top: 5px; line-height: 1.4;
}

/* ── Vencimiento chips ── */
.vc-red    { background: rgba(229,57,53,.12); color: #F26F6F; }
.vc-orange { background: rgba(245,158,11,.12); color: #F5B942; }
.vc-blue   { background: rgba(8,145,178,.12);  color: #22B8D4; }

/* ── Metric chip ── */
.mc {
    display: inline-block; padding: 3px 8px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
    background: var(--cobalt-glow, rgba(27,79,255,.12));
    color: #4B78FF;
}

/* ── Mini chips ── */
.mchip { display: inline-block; padding: 3px 7px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.mc-ok     { background: rgba(5,150,105,.12); color: #1DBD7F; }
.mc-warn   { background: rgba(245,158,11,.12); color: #F5B942; }
.mc-danger { background: rgba(229,57,53,.12);  color: #F26F6F; }
.mc-info   { background: rgba(8,145,178,.12);  color: #22B8D4; }
.mc-purple { background: rgba(124,58,237,.12); color: #A78BFA; }

/* ── Tabla link ── */
.is-tbl-link { color: #4B78FF; text-decoration: none; font-weight: 600; }
.is-tbl-link:hover { text-decoration: underline; }

/* ── Empty state ── */
.is-empty {
    padding: 18px; font-size: 13px; color: var(--text-3);
    background: var(--bg-input); border: 1px dashed var(--border-2);
    border-radius: 8px; text-align: center;
}

/* ── Alert group heading ── */
.is-alert-group-label {
    font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1px;
    color: var(--text-3); margin-bottom: 10px; margin-top: 18px;
}
.is-alert-group-label:first-child { margin-top: 0; }

/* ── Chart box ── */
.is-chart-box { height: 320px; position: relative; }
</style>

{{-- ── Cabecera ── --}}
<div class="is-animate-rise"
     style="display:flex;justify-content:space-between;align-items:flex-start;
            margin-bottom:24px;gap:14px;flex-wrap:wrap;">
    <div>
        <div class="is-page-title">Dashboard</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">
            Resumen jurídico, operativo y financiero del sistema
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('casos.index') }}" class="is-btn-ghost">
            Ver casos
        </a>
        @if(auth()->user()->puedeEditar())
            <a href="{{ route('dashboard.exportarExcel') }}" class="is-btn-ghost">
                ↓ Excel
            </a>
            <a href="{{ route('dashboard.exportarPdf') }}" class="is-btn-ghost">
                ↓ PDF
            </a>
        @endif
    </div>
</div>

{{-- ════════════════════════════════════════════
     TARJETAS FINANCIERAS — Fila 1
════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px;"
     class="is-animate-rise is-stagger-1">
    <div class="is-dash-card" style="--dc-accent:#1DBD7F;">
        <div class="dc-label">Total recuperado</div>
        <div class="dc-value" style="color:#1DBD7F;">
            ${{ number_format($totalRecuperado ?? 0,0,',','.') }}
        </div>
        <div class="dc-sub">Suma de todos los pagos finales</div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#4B78FF;">
        <div class="dc-label">Ganancia total equipo</div>
        <div class="dc-value" style="color:#4B78FF;">
            ${{ number_format($totalGananciaEquipo ?? 0,0,',','.') }}
        </div>
        <div class="dc-sub">Honorarios acumulados</div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#D4AA48;">
        <div class="dc-label">Neto total clientes</div>
        <div class="dc-value" style="color:#D4AA48;">
            ${{ number_format($totalNetoClientes ?? 0,0,',','.') }}
        </div>
        <div class="dc-sub">Valor neto entregado a víctimas</div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#F59E0B;">
        <div class="dc-label">Promedio ganancia / caso pagado</div>
        <div class="dc-value" style="color:#F5B942;">
            ${{ number_format($promedioGananciaEquipo ?? 0,0,',','.') }}
        </div>
        <div class="dc-sub">Promedio sobre casos pagados</div>
    </div>
</div>

{{-- Fila 2 --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px;"
     class="is-animate-rise is-stagger-1">
    <div class="is-dash-card" style="--dc-accent:#4B78FF;">
        <div class="dc-label">Total casos</div>
        <div class="dc-value">{{ $totalCasos ?? 0 }}</div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#1DBD7F;">
        <div class="dc-label">Casos pagados</div>
        <div class="dc-value" style="color:#1DBD7F;">{{ $casosPagados ?? 0 }}</div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#F59E0B;">
        <div class="dc-label">Casos activos</div>
        <div class="dc-value" style="color:#F5B942;">{{ $casosActivos ?? 0 }}</div>
        <div class="dc-sub">Sin pago final</div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#A78BFA;">
        <div class="dc-label">Casos con tutela</div>
        <div class="dc-value" style="color:#A78BFA;">{{ $casosTutela ?? 0 }}</div>
    </div>
</div>

{{-- Fila 3 --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;"
     class="is-animate-rise is-stagger-2">
    <div class="is-dash-card" style="--dc-accent:#F59E0B;">
        <div class="dc-label">Listos para cobrar</div>
        <div class="dc-value" style="color:#F5B942;">{{ $casosListosReclamar ?? 0 }}</div>
        <div class="dc-sub">Con dictamen y sin reclamación final</div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#4B78FF;">
        <div class="dc-label">Valor estimado total</div>
        <div class="dc-value" style="font-size:20px;color:#4B78FF;">
            ${{ number_format($valorEstimadoTotal ?? 0,0,',','.') }}
        </div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#1DBD7F;">
        <div class="dc-label">Valor reclamado total</div>
        <div class="dc-value" style="font-size:20px;color:#1DBD7F;">
            ${{ number_format($valorReclamadoTotal ?? 0,0,',','.') }}
        </div>
    </div>
    <div class="is-dash-card" style="--dc-accent:#E53935;">
        <div class="dc-label">Saldo pendiente estimado</div>
        <div class="dc-value" style="font-size:20px;color:#F26F6F;">
            ${{ number_format($saldoPendiente ?? 0,0,',','.') }}
        </div>
        <div class="dc-sub">Diferencia estimado — pagado</div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     PANEL DE VENCIMIENTOS
════════════════════════════════════════════ --}}
<div class="is-panel is-animate-rise is-stagger-2">
    <div class="is-panel-title">Panel de vencimientos jurídicos</div>
    <div class="is-panel-sub"></div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px;">
        <div class="is-dash-card" style="--dc-accent:#E53935;">
            <div class="dc-label">Casos críticos</div>
            <div class="dc-value" style="color:#F26F6F;">
                {{ $casosCriticos ?? 0 }}
            </div>
            <div class="dc-sub">Mayor urgencia legal o financiera</div>
        </div>
        <div class="is-dash-card" style="--dc-accent:#F59E0B;">
            <div class="dc-label">Casos urgentes</div>
            <div class="dc-value" style="color:#F5B942;">
                {{ $casosUrgentes ?? 0 }}
            </div>
            <div class="dc-sub">Requieren actuación pronta</div>
        </div>
        <div class="is-dash-card" style="--dc-accent:#E53935;">
            <div class="dc-label">Pagos atrasados</div>
            <div class="dc-value" style="color:#F26F6F;">
                {{ $pagosAtrasados ?? 0 }}
            </div>
            <div class="dc-sub">Reclamados y sin pago final</div>
        </div>
        <div class="is-dash-card" style="--dc-accent:#F59E0B;">
            <div class="dc-label">Tutelas en seguimiento</div>
            <div class="dc-value" style="color:#F5B942;">
                {{ $tutelasPendientesSeguimiento ?? 0 }}
            </div>
            <div class="dc-sub">Requieren revisión o impulso</div>
        </div>
    </div>

    @if(($vencimientos ?? collect())->count())
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Prioridad</th><th>Caso</th><th>Víctima</th>
                        <th>Aseguradora</th><th>Evento</th>
                        <th>Fecha base</th><th>Días</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vencimientos->take(25) as $item)
                        <tr>
                            <td>
                                <span class="is-badge vc-{{ $item['color'] }}"
                                      style="font-size:10px;padding:3px 9px;
                                             border-radius:20px;font-weight:700;">
                                    {{ $item['prioridad'] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('casos.show', $item['caso_id']) }}"
                                   class="is-tbl-link">
                                    {{ $item['numero_caso'] }}
                                </a>
                            </td>
                            <td>{{ $item['victima'] }}</td>
                            <td>{{ $item['aseguradora'] ?: '—' }}</td>
                            <td style="font-size:12px;">{{ $item['evento'] }}</td>
                            <td style="font-size:12px;color:var(--text-2);">
                                {{ $item['fecha_base'] ?: '—' }}
                            </td>
                            <td>
                                <strong style="font-family:'Playfair Display',serif;">
                                    {{ $item['dias'] }}
                                </strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="is-empty">No hay vencimientos jurídicos detectados.</div>
    @endif
</div>

{{-- ════════════════════════════════════════════
     ALERTAS AUTOMÁTICAS
════════════════════════════════════════════ --}}
<div class="is-panel is-animate-rise is-stagger-3">
    <div class="is-panel-title">Alertas automáticas del sistema</div>
    <div class="is-panel-sub">
        Acciones pendientes detectadas por el motor jurídico.
    </div>

    <div class="is-alert-group-label">Solicitud y respuesta</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:6px;">
        <div class="is-alert-box ab-warn">
            <h4>Sin respuesta de aseguradora</h4>
            <div class="is-alert-count" style="color:#F5B942;">
                {{ ($alertasSinRespuesta ?? collect())->count() }}
            </div>
            <div class="is-alert-small">30+ días sin respuesta registrada.</div>
        </div>
        <div class="is-alert-box ab-warn">
            <h4>Apelar dictamen</h4>
            <div class="is-alert-count" style="color:#F5B942;">
                {{ ($alertasApelarDictamen ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Aseguradora emitió dictamen sin apelar.</div>
        </div>
        <div class="is-alert-box ab-red">
            <h4>Presentar tutela</h4>
            <div class="is-alert-count" style="color:#F26F6F;">
                {{ ($alertasTutela ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Procede tutela para calificación o por debido proceso.</div>
        </div>
        <div class="is-alert-box">
            <h4>Pagar honorarios junta</h4>
            <div class="is-alert-count" style="color:var(--text-1);">
                {{ ($alertasHonorariosJunta ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Apelación registrada, pendiente honorarios.</div>
        </div>
    </div>

    <div class="is-alert-group-label">Tutela, fallo y cumplimiento</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:6px;">
        <div class="is-alert-box ab-blue">
            <h4>Esperando fallo tutela</h4>
            <div class="is-alert-count" style="color:#22B8D4;">
                {{ ($alertasEsperandoFalloTutela ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Tutela presentada, en tiempo normal de espera.</div>
        </div>
        <div class="is-alert-box ab-warn">
            <h4>Revisar fallo tutela</h4>
            <div class="is-alert-count" style="color:#F5B942;">
                {{ ($alertasSeguimientoTutela ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Pasó 1 mes — revisar fallo o impulsar.</div>
        </div>
        <div class="is-alert-box ab-green">
            <h4>Esperando cumplimiento</h4>
            <div class="is-alert-count" style="color:#1DBD7F;">
                {{ $casosCumplimientoTutela ?? 0 }}
            </div>
            <div class="is-alert-small">Fallo concedido — 14 días para cumplir.</div>
        </div>
        <div class="is-alert-box ab-red">
            <h4>Incidente de desacato</h4>
            <div class="is-alert-count" style="color:#F26F6F;">
                {{ $casosDesacatoPendiente ?? 0 }}
            </div>
            <div class="is-alert-small">Pasaron 14 días y no han cumplido.</div>
        </div>
    </div>

    <div class="is-alert-group-label">Impugnación y segunda instancia</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:6px;">
        <div class="is-alert-box ab-red">
            <h4>Impugnación pendiente</h4>
            <div class="is-alert-count" style="color:#F26F6F;">
                {{ ($alertasImpugnacion ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Fallo negado o parcial — pendiente impugnar.</div>
        </div>
        <div class="is-alert-box ab-purple">
            <h4>Pendiente segunda instancia</h4>
            <div class="is-alert-count" style="color:#A78BFA;">
                {{ $casosSegundaInstancia ?? 0 }}
            </div>
            <div class="is-alert-small">Impugnado, esperando fallo segunda instancia.</div>
        </div>
        <div class="is-alert-box ab-red">
            <h4>2ª instancia revocó — cumplir</h4>
            <div class="is-alert-count" style="color:#F26F6F;">
                {{ $casosCumplimientoSegunda ?? 0 }}
            </div>
            <div class="is-alert-small">Segunda instancia revocó, aseguradora no ha cumplido.</div>
        </div>
        <div class="is-alert-box">
            <h4>Casos cerrados (2ª inst.)</h4>
            <div class="is-alert-count" style="color:var(--text-2);">
                {{ $casosCerradosSegundaInstancia ?? 0 }}
            </div>
            <div class="is-alert-small">Confirmó fallo negado — sin más acciones.</div>
        </div>
    </div>

    <div class="is-alert-group-label">Junta, cobro y pago final</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
        <div class="is-alert-box ab-blue">
            <h4>Solicitar a junta</h4>
            <div class="is-alert-count" style="color:#22B8D4;">
                {{ ($alertasSolicitudJunta ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Honorarios pagados y sin solicitud a junta.</div>
        </div>
        <div class="is-alert-box ab-blue">
            <h4>Cobrar a aseguradora</h4>
            <div class="is-alert-count" style="color:#22B8D4;">
                {{ ($alertasReclamacion ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Dictamen listo y sin reclamación final.</div>
        </div>
        <div class="is-alert-box ab-warn">
            <h4>Pago final pendiente</h4>
            <div class="is-alert-count" style="color:#F5B942;">
                {{ ($alertasPagoPendiente ?? collect())->count() }}
            </div>
            <div class="is-alert-small">Reclamados pero sin pago final.</div>
        </div>
        <div class="is-alert-box ab-red">
            <h4>Hacer queja</h4>
            <div class="is-alert-count" style="color:#F26F6F;">
                {{ ($alertasQuejaNoPago ?? collect())->count() }}
            </div>
            <div class="is-alert-small">+1 mes desde reclamación y no han pagado.</div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     ANÁLISIS ESTRATÉGICO
════════════════════════════════════════════ --}}
<div class="is-panel is-animate-rise is-stagger-3">
    <div class="is-panel-title">Análisis estratégico de aseguradoras</div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);
                gap:12px;margin-bottom:18px;">
        <div class="is-dash-card" style="--dc-accent:#1DBD7F;">
            <div class="dc-label">Aseguradora mayor pago</div>
            <div class="dc-value" style="font-size:18px;color:#1DBD7F;">
                {{ $topAseguradoraPagos['aseguradora'] ?? 'N/A' }}
            </div>
            <div class="dc-sub">
                Total:
                <strong style="color:#D4AA48;">
                    ${{ number_format($topAseguradoraPagos['total_pagado'] ?? 0,0,',','.') }}
                </strong>
            </div>
        </div>
        <div class="is-dash-card" style="--dc-accent:#F59E0B;">
            <div class="dc-label">Mayor tasa de tutela</div>
            <div class="dc-value" style="font-size:18px;color:#F5B942;">
                {{ $aseguradoraMayorTutela['aseguradora'] ?? 'N/A' }}
            </div>
            <div class="dc-sub">
                Tasa:
                <strong>
                    {{ number_format($aseguradoraMayorTutela['tasa_tutela'] ?? 0,1,',','.') }}%
                </strong>
            </div>
        </div>
        <div class="is-dash-card" style="--dc-accent:#E53935;">
            <div class="dc-label">Más lenta pagando</div>
            <div class="dc-value" style="font-size:18px;color:#F26F6F;">
                {{ $aseguradoraMasLentaPago['aseguradora'] ?? 'N/A' }}
            </div>
            <div class="dc-sub">
                Promedio:
                <strong>
                    {{ number_format($aseguradoraMasLentaPago['tiempo_promedio_pago_dias'] ?? 0,1,',','.') }} días
                </strong>
            </div>
        </div>
        <div class="is-dash-card" style="--dc-accent:#A78BFA;">
            <div class="dc-label">Más negaciones</div>
            <div class="dc-value" style="font-size:18px;color:#A78BFA;">
                {{ $aseguradoraMasNegaciones['aseguradora'] ?? 'N/A' }}
            </div>
            <div class="dc-sub">
                Negaciones:
                <strong>{{ $aseguradoraMasNegaciones['casos_nego'] ?? 0 }}</strong>
            </div>
        </div>
    </div>

    @if(($aseguradorasEstrategicas ?? collect())->count())
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Aseguradora</th><th>Casos</th><th>Pagados</th>
                        <th>Tasa pago</th><th>Tasa tutela</th>
                        <th>Tasa apelación</th><th>Dictamen</th>
                        <th>Negó</th><th>No resp.</th>
                        <th>2ª revoca</th><th>2ª confirma</th>
                        <th>T. resp.</th><th>T. pago</th>
                        <th>Total pagado</th><th>Prom. caso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aseguradorasEstrategicas as $item)
                        <tr>
                            <td><strong style="color:var(--text-1);">{{ $item['aseguradora'] }}</strong></td>
                            <td>{{ $item['total_casos'] }}</td>
                            <td>{{ $item['casos_pagados'] }}</td>
                            <td><span class="mc">{{ number_format($item['tasa_pago'],1,',','.') }}%</span></td>
                            <td><span class="mc">{{ number_format($item['tasa_tutela'],1,',','.') }}%</span></td>
                            <td><span class="mc">{{ number_format($item['tasa_apelacion'],1,',','.') }}%</span></td>
                            <td><span class="mchip mc-ok">{{ $item['casos_emitio_dictamen'] ?? 0 }}</span></td>
                            <td><span class="mchip mc-danger">{{ $item['casos_nego'] ?? 0 }}</span></td>
                            <td><span class="mchip mc-warn">{{ $item['casos_no_respondio'] ?? 0 }}</span></td>
                            <td><span class="mchip mc-ok">{{ $item['casos_segunda_revoca'] ?? 0 }}</span></td>
                            <td><span class="mchip mc-danger">{{ $item['casos_segunda_confirma'] ?? 0 }}</span></td>
                            <td style="font-size:12px;color:var(--text-2);">{{ number_format($item['tiempo_promedio_respuesta_dias'],1,',','.') }}d</td>
                            <td style="font-size:12px;color:var(--text-2);">{{ number_format($item['tiempo_promedio_pago_dias'],1,',','.') }}d</td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#D4AA48;">
                                ${{ number_format($item['total_pagado'],0,',','.') }}
                            </td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#4B78FF;">
                                ${{ number_format($item['promedio_pagado_por_caso'],0,',','.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="is-empty">No hay suficiente información para generar análisis estratégico.</div>
    @endif
</div>

{{-- ════════════════════════════════════════════
     GRÁFICAS
════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-panel" style="margin-bottom:0;">
        <div class="is-panel-title" style="margin-bottom:14px;">
            Pagado vs ganancia por aseguradora
        </div>
        <div class="is-chart-box">
            <canvas id="chartAseguradoras"></canvas>
        </div>
    </div>
    <div class="is-panel" style="margin-bottom:0;">
        <div class="is-panel-title" style="margin-bottom:14px;">Casos por estado</div>
        <div class="is-chart-box">
            <canvas id="chartEstados"></canvas>
        </div>
    </div>
    <div class="is-panel" style="margin-bottom:0;">
        <div class="is-panel-title" style="margin-bottom:14px;">Pagos y ganancia por mes</div>
        <div class="is-chart-box">
            <canvas id="chartMensual"></canvas>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-panel" style="margin-bottom:0;">
        <div class="is-panel-title" style="margin-bottom:14px;">
            Distribución de respuestas de aseguradora
        </div>
        <div class="is-chart-box">
            <canvas id="chartRespuestas"></canvas>
        </div>
    </div>
    <div class="is-panel" style="margin-bottom:0;">
        <div class="is-panel-title" style="margin-bottom:14px;">
            Tipos de tutela presentadas
        </div>
        <div class="is-chart-box">
            <canvas id="chartTutelas"></canvas>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     TABLAS RESUMENES
════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:16px;margin-bottom:16px;"
     class="is-animate-rise is-stagger-3">
    <div class="is-panel" style="margin-bottom:0;">
        <div class="is-panel-title" style="margin-bottom:14px;">
            Resumen financiero por aseguradora
        </div>
        @if(($porAseguradora ?? collect())->count())
            <table>
                <thead>
                    <tr>
                        <th>Aseguradora</th><th>Casos</th>
                        <th>Total pagado</th><th>Ganancia equipo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($porAseguradora as $item)
                        <tr>
                            <td>{{ $item->aseguradora ?: '—' }}</td>
                            <td>{{ $item->total_casos }}</td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#D4AA48;">
                                ${{ number_format($item->total_pagado,0,',','.') }}
                            </td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#4B78FF;">
                                ${{ number_format($item->total_equipo,0,',','.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="is-empty">No hay información disponible.</div>
        @endif
    </div>

    <div class="is-panel" style="margin-bottom:0;">
        <div class="is-panel-title" style="margin-bottom:14px;">Últimos casos pagados</div>
        @if(($ultimosPagados ?? collect())->count())
            <table>
                <thead>
                    <tr><th>Caso</th><th>Víctima</th><th>Pago</th><th>Honor.</th></tr>
                </thead>
                <tbody>
                    @foreach($ultimosPagados as $caso)
                        <tr>
                            <td>
                                <a href="{{ route('casos.show', $caso) }}"
                                   class="is-tbl-link">
                                    {{ $caso->numero_caso }}
                                </a>
                            </td>
                            <td style="font-size:12px;">
                                {{ $caso->nombres }} {{ $caso->apellidos }}
                            </td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#D4AA48;">
                                ${{ number_format($caso->valor_pagado ?? 0,0,',','.') }}
                            </td>
                            <td>
                                <span class="is-badge is-badge-cobalt" style="font-size:10px;">
                                    {{ $caso->porcentaje_honorarios
                                        ? number_format($caso->porcentaje_honorarios,0,',','.').'%'
                                        : 'N/A' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="is-empty">No hay pagos registrados.</div>
        @endif
    </div>
</div>

<div class="is-panel is-animate-rise is-stagger-3">
    <div class="is-panel-title" style="margin-bottom:14px;">Casos por aseguradora</div>
    @if(($casosPorAseguradora ?? collect())->count())
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Aseguradora</th><th>Casos</th>
                        <th>V. Estimado total</th><th>V. Pagado total</th>
                        <th>Ganancia equipo</th><th>Neto clientes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($casosPorAseguradora as $item)
                        <tr>
                            <td style="font-weight:600;color:var(--text-1);">
                                {{ $item->aseguradora ?: '—' }}
                            </td>
                            <td>{{ $item->total }}</td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#4B78FF;">
                                ${{ number_format($item->valor_estimado_total,0,',','.') }}
                            </td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#D4AA48;">
                                ${{ number_format($item->valor_pagado_total,0,',','.') }}
                            </td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#4B78FF;">
                                ${{ number_format($item->ganancia_equipo_total,0,',','.') }}
                            </td>
                            <td style="font-family:'Playfair Display',serif;font-weight:700;color:#1DBD7F;">
                                ${{ number_format($item->valor_neto_cliente_total,0,',','.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="is-empty">No hay casos agrupados por aseguradora.</div>
    @endif
</div>

<div class="is-panel is-animate-rise is-stagger-3">
    <div class="is-panel-title" style="margin-bottom:14px;">Casos por estado</div>
    @if(($casosPorEstado ?? collect())->count())
        <table>
            <thead>
                <tr><th>Estado</th><th>Total casos</th></tr>
            </thead>
            <tbody>
                @foreach($casosPorEstado as $item)
                    <tr>
                        <td>{{ $item->estado ?: 'Sin estado' }}</td>
                        <td>
                            <span class="is-badge is-badge-cobalt" style="font-size:11px;">
                                {{ $item->total }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="is-empty">No hay estados registrados.</div>
    @endif
</div>

<div class="is-panel is-animate-rise is-stagger-3">
    <div class="is-panel-title" style="margin-bottom:14px;">
        Últimos movimientos de bitácora
    </div>
    @if(($ultimosMovimientos ?? collect())->count())
        <table>
            <thead>
                <tr><th>Fecha</th><th>Caso</th><th>Movimiento</th><th>Descripción</th></tr>
            </thead>
            <tbody>
                @foreach($ultimosMovimientos as $movimiento)
                    <tr>
                        <td style="font-size:12px;color:var(--text-2);white-space:nowrap;">
                            {{ $movimiento->fecha_evento
                                ?: optional($movimiento->created_at)->format('Y-m-d') }}
                        </td>
                        <td>
                            @if($movimiento->caso)
                                <a href="{{ route('casos.show', $movimiento->caso) }}"
                                   class="is-tbl-link">
                                    {{ $movimiento->caso->numero_caso }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td style="font-size:13px;font-weight:600;color:var(--text-1);">
                            {{ $movimiento->titulo }}
                        </td>
                        <td style="font-size:12px;color:var(--text-2);">
                            {{ $movimiento->descripcion }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="is-empty">No hay movimientos registrados.</div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labelsAseguradoras     = @json($labelsAseguradoras ?? []);
const dataPagadoAseguradoras = @json($dataPagadoAseguradoras ?? []);
const dataEquipoAseguradoras = @json($dataEquipoAseguradoras ?? []);
const labelsEstados          = @json($labelsEstados ?? []);
const dataEstados            = @json($dataEstados ?? []);
const labelsPagosMensuales   = @json($labelsPagosMensuales ?? []);
const dataPagosMensuales     = @json($dataPagosMensuales ?? []);
const dataEquipoMensual      = @json($dataEquipoMensual ?? []);
const distribucionRespuestas = @json($distribucionRespuestas ?? []);
const distribucionTutelas    = @json($distribucionTutelas ?? []);

// Detectar tema para Chart.js
const isDark = document.documentElement.getAttribute('data-theme') !== 'light';
const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
const tickColor  = isDark ? '#4E6A8A' : '#8FA5C0';
const legendColor= isDark ? '#8EA9CC' : '#445C7A';

const baseOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: { color: legendColor, font: { family: "'DM Sans',sans-serif", size: 11 } }
        }
    },
    scales: {
        x: { ticks: { color: tickColor, font: { size: 11 } }, grid: { color: gridColor } },
        y: { ticks: { color: tickColor, font: { size: 11 } }, grid: { color: gridColor } }
    }
};

if (document.getElementById('chartAseguradoras')) {
    new Chart(document.getElementById('chartAseguradoras'), {
        type: 'bar',
        data: {
            labels: labelsAseguradoras,
            datasets: [
                {
                    label: 'Total pagado',
                    data: dataPagadoAseguradoras,
                    backgroundColor: 'rgba(29,189,127,.7)',
                    borderColor: '#1DBD7F', borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Ganancia equipo',
                    data: dataEquipoAseguradoras,
                    backgroundColor: 'rgba(75,120,255,.7)',
                    borderColor: '#4B78FF', borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: baseOpts
    });
}

if (document.getElementById('chartEstados')) {
    new Chart(document.getElementById('chartEstados'), {
        type: 'doughnut',
        data: {
            labels: labelsEstados,
            datasets: [{
                data: dataEstados,
                backgroundColor: [
                    '#1B4FFF','#1DBD7F','#F59E0B','#E53935',
                    '#4E6A8A','#0891B2','#7C3AED','#059669',
                    '#F97316','#84CC16','#E11D48','#A855F7'
                ],
                borderWidth: 2,
                borderColor: isDark ? '#0F1E35' : '#fff'
            }]
        },
        options: {
            ...baseOpts,
            scales: {},
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: legendColor,
                        font: { family: "'DM Sans',sans-serif", size: 11 },
                        padding: 12, boxWidth: 12
                    }
                }
            }
        }
    });
}

if (document.getElementById('chartMensual')) {
    new Chart(document.getElementById('chartMensual'), {
        type: 'line',
        data: {
            labels: labelsPagosMensuales,
            datasets: [
                {
                    label: 'Pagado mensual',
                    data: dataPagosMensuales,
                    borderColor: '#1DBD7F',
                    backgroundColor: 'rgba(29,189,127,.1)',
                    tension: .3, fill: true,
                    pointBackgroundColor: '#1DBD7F', pointRadius: 3
                },
                {
                    label: 'Ganancia equipo',
                    data: dataEquipoMensual,
                    borderColor: '#4B78FF',
                    backgroundColor: 'rgba(75,120,255,.08)',
                    tension: .3, fill: true,
                    pointBackgroundColor: '#4B78FF', pointRadius: 3
                }
            ]
        },
        options: baseOpts
    });
}

if (document.getElementById('chartRespuestas')) {
    new Chart(document.getElementById('chartRespuestas'), {
        type: 'doughnut',
        data: {
            labels: ['Emitió dictamen','Negó la solicitud','No respondió','Sin respuesta aún'],
            datasets: [{
                data: [
                    distribucionRespuestas.emitio_dictamen ?? 0,
                    distribucionRespuestas.nego            ?? 0,
                    distribucionRespuestas.no_respondio    ?? 0,
                    distribucionRespuestas.sin_respuesta   ?? 0
                ],
                backgroundColor: ['#1DBD7F','#F26F6F','#F5B942','#4E6A8A'],
                borderWidth: 2,
                borderColor: isDark ? '#0F1E35' : '#fff'
            }]
        },
        options: {
            ...baseOpts, scales: {},
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: legendColor, font: { family: "'DM Sans',sans-serif", size: 11 }, padding: 10, boxWidth: 12 }
                }
            }
        }
    });
}

if (document.getElementById('chartTutelas')) {
    new Chart(document.getElementById('chartTutelas'), {
        type: 'bar',
        data: {
            labels: ['Para calificación','Por debido proceso'],
            datasets: [{
                label: 'Tutelas',
                data: [
                    distribucionTutelas.calificacion   ?? 0,
                    distribucionTutelas.debido_proceso ?? 0
                ],
                backgroundColor: ['rgba(8,145,178,.75)','rgba(124,58,237,.75)'],
                borderColor:     ['#0891B2','#7C3AED'],
                borderWidth: 1, borderRadius: 4
            }]
        },
        options: {
            ...baseOpts,
            indexAxis: 'y',
            plugins: { legend: { display: false } }
        }
    });
}
</script>
@endpush
