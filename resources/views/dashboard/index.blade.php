<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard - INDEMNI SOAT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;margin:0;color:#111827}
.layout{display:flex;min-height:100vh}
.sidebar{width:260px;background:linear-gradient(180deg,#1f2937 0%,#172033 100%);color:#fff;padding:25px 18px;display:flex;flex-direction:column;flex-shrink:0}
.brand{font-size:28px;font-weight:bold;margin-bottom:30px;line-height:1.2}
.menu a{display:block;padding:12px 14px;margin-bottom:10px;text-decoration:none;color:#fff;background:#374151;border-radius:8px;transition:.2s ease}
.menu a:hover{background:#2563eb}
.menu a.active{background:#2563eb}
.user-box{margin-top:auto;padding-top:16px;border-top:1px solid #374151}
.user-name{font-size:13px;font-weight:bold;color:#fff;margin-bottom:2px}
.user-role{font-size:11px;color:#9ca3af;margin-bottom:10px}
.logout-btn{width:100%;padding:8px;background:#374151;color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer;font-family:inherit;transition:.2s}
.logout-btn:hover{background:#dc3545}
.content{flex:1;padding:30px}
.container{max-width:1650px;margin:auto}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:24px}
.btn{display:inline-block;padding:10px 14px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;border:none;font-size:13px}
.section{background:#fff;padding:20px;border-radius:12px;border:1px solid #ddd;margin-bottom:20px;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.section h2,.section h3{margin-top:0;margin-bottom:16px}
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px}
.cards-3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px}
.cards-2{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:22px}
.card{background:#fff;padding:18px;border-radius:12px;border:1px solid #ddd;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.card-title{font-size:13px;color:#6b7280;margin-bottom:8px}
.card-value{font-size:28px;font-weight:bold}
.card-sub{margin-top:8px;color:#6b7280;font-size:12px;line-height:1.4}
.money{color:#198754}
.money-blue{color:#1d4ed8}
.money-orange{color:#c2410c}
.gray{color:#374151}
table{width:100%;border-collapse:collapse}
th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
th{background:#f8f9fa;white-space:nowrap;font-size:13px}
.alert-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.alert-box{background:#fff8e1;border:1px solid #ffe08a;border-radius:12px;padding:16px}
.alert-box.alert-red{background:#fff5f5;border-color:#f5c2c7}
.alert-box.alert-blue{background:#f3fbff;border-color:#b6effb}
.alert-box.alert-green{background:#f3fff7;border-color:#badbcc}
.alert-box.alert-purple{background:#faf5ff;border-color:#e9d5ff}
.alert-box h4{margin:0 0 8px 0;font-size:14px}
.alert-count{font-size:30px;font-weight:bold;color:#b26a00}
.alert-count.red{color:#842029}
.alert-count.blue{color:#055160}
.alert-count.green{color:#0f5132}
.alert-count.purple{color:#5b21b6}
.small{color:#666;font-size:12px;line-height:1.4}
.card-red{border-color:#f5c2c7;background:#fff5f5}
.card-orange{border-color:#ffe69c;background:#fffaf0}
.card-blue{border-color:#b6effb;background:#f3fbff}
.card-green{border-color:#badbcc;background:#f3fff7}
.card-purple{border-color:#e9d5ff;background:#faf5ff}
.pill{display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;background:#e5e7eb;color:#374151}
.metric-chip{display:inline-block;padding:4px 8px;border-radius:999px;font-size:12px;font-weight:bold;background:#eef2ff;color:#3730a3}
.vencimiento-chip{display:inline-block;padding:5px 9px;border-radius:999px;font-size:12px;font-weight:bold}
.vencimiento-red{background:#f8d7da;color:#842029}
.vencimiento-orange{background:#fff3cd;color:#997404}
.vencimiento-blue{background:#cff4fc;color:#055160}
.mini-chip{display:inline-block;padding:3px 7px;border-radius:999px;font-size:11px;font-weight:bold}
.chip-ok{background:#d1e7dd;color:#0f5132}
.chip-warn{background:#fff3cd;color:#997404}
.chip-danger{background:#f8d7da;color:#842029}
.chip-purple{background:#ede9fe;color:#5b21b6}
.chip-info{background:#cff4fc;color:#055160}
.grid-2{display:grid;grid-template-columns:1.2fr 1fr;gap:20px}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px}
.chart-box{height:340px}
.empty-state{padding:18px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b}
.table-link{color:#2563eb;text-decoration:none;font-weight:600}
.table-link:hover{text-decoration:underline}
.section-sub{font-size:12px;color:#6b7280;margin-top:-10px;margin-bottom:14px}
@media (max-width:1400px){.alert-grid{grid-template-columns:repeat(3,1fr)}}
@media (max-width:1200px){.cards{grid-template-columns:repeat(2,1fr)}.cards-3{grid-template-columns:repeat(2,1fr)}.grid-2{grid-template-columns:1fr}.grid-3{grid-template-columns:1fr}.alert-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:900px){.layout{flex-direction:column}.sidebar{width:100%}.cards{grid-template-columns:1fr}.cards-3{grid-template-columns:1fr}.cards-2{grid-template-columns:1fr}.alert-grid{grid-template-columns:1fr}.content{padding:18px}}
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

    <main class="content">
        <div class="container">

            <div class="topbar">
                <div>
                    <h1 style="margin:0;">Dashboard INDEMNI SOAT</h1>
                    <div class="small" style="margin-top:6px;color:#6b7280;">Resumen jurídico, operativo y financiero del sistema</div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="{{ route('casos.index') }}" class="btn">Ver casos</a>
                    @if(auth()->user()->puedeEditar())
                        <a href="{{ route('dashboard.exportarExcel') }}" class="btn">Exportar Excel</a>
                        <a href="{{ route('dashboard.exportarPdf') }}" class="btn">Exportar PDF</a>
                    @endif
                </div>
            </div>

            {{-- TARJETAS FINANCIERAS --}}
            <div class="cards">
                <div class="card">
                    <div class="card-title">Total recuperado</div>
                    <div class="card-value money">${{ number_format($totalRecuperado ?? 0,0,',','.') }}</div>
                    <div class="card-sub">Suma de todos los pagos finales registrados</div>
                </div>
                <div class="card">
                    <div class="card-title">Ganancia total equipo</div>
                    <div class="card-value money-blue">${{ number_format($totalGananciaEquipo ?? 0,0,',','.') }}</div>
                    <div class="card-sub">Honorarios acumulados del equipo jurídico</div>
                </div>
                <div class="card">
                    <div class="card-title">Neto total clientes</div>
                    <div class="card-value money">${{ number_format($totalNetoClientes ?? 0,0,',','.') }}</div>
                    <div class="card-sub">Valor neto entregado a víctimas</div>
                </div>
                <div class="card">
                    <div class="card-title">Promedio ganancia por caso pagado</div>
                    <div class="card-value money-orange">${{ number_format($promedioGananciaEquipo ?? 0,0,',','.') }}</div>
                    <div class="card-sub">Promedio sobre casos pagados</div>
                </div>
            </div>

            <div class="cards">
                <div class="card"><div class="card-title">Total casos</div><div class="card-value gray">{{ $totalCasos ?? 0 }}</div></div>
                <div class="card"><div class="card-title">Casos pagados</div><div class="card-value money">{{ $casosPagados ?? 0 }}</div></div>
                <div class="card"><div class="card-title">Casos activos</div><div class="card-value money-orange">{{ $casosActivos ?? 0 }}</div><div class="card-sub">Casos sin pago final</div></div>
                <div class="card"><div class="card-title">Casos con tutela</div><div class="card-value money-blue">{{ $casosTutela ?? 0 }}</div></div>
            </div>

            <div class="cards">
                <div class="card"><div class="card-title">Casos listos para cobrar</div><div class="card-value money-orange">{{ $casosListosReclamar ?? 0 }}</div><div class="card-sub">Con dictamen de junta y sin reclamación final</div></div>
                <div class="card"><div class="card-title">Valor estimado total</div><div class="card-value money-blue">${{ number_format($valorEstimadoTotal ?? 0,0,',','.') }}</div></div>
                <div class="card"><div class="card-title">Valor reclamado total</div><div class="card-value money">${{ number_format($valorReclamadoTotal ?? 0,0,',','.') }}</div></div>
                <div class="card"><div class="card-title">Saldo pendiente estimado</div><div class="card-value money-orange">${{ number_format($saldoPendiente ?? 0,0,',','.') }}</div><div class="card-sub">Diferencia entre valor estimado y pagado</div></div>
            </div>

            {{-- PANEL DE VENCIMIENTOS --}}
            <div class="section">
                <h3>Panel de vencimientos jurídicos</h3>
                <div class="cards">
                    <div class="card card-red"><div class="card-title">Casos críticos</div><div class="card-value" style="color:#842029">{{ $casosCriticos ?? 0 }}</div><div class="card-sub">Mayor urgencia legal o financiera.</div></div>
                    <div class="card card-orange"><div class="card-title">Casos urgentes</div><div class="card-value" style="color:#997404">{{ $casosUrgentes ?? 0 }}</div><div class="card-sub">Requieren actuación pronta.</div></div>
                    <div class="card card-red"><div class="card-title">Pagos atrasados</div><div class="card-value" style="color:#842029">{{ $pagosAtrasados ?? 0 }}</div><div class="card-sub">Reclamados y aún sin pago final.</div></div>
                    <div class="card card-orange"><div class="card-title">Tutelas en seguimiento</div><div class="card-value" style="color:#997404">{{ $tutelasPendientesSeguimiento ?? 0 }}</div><div class="card-sub">Requieren revisión o impulso.</div></div>
                </div>
                @if(($vencimientos ?? collect())->count())
                    <table>
                        <tr><th>Prioridad</th><th>Caso</th><th>Víctima</th><th>Aseguradora</th><th>Evento</th><th>Fecha base</th><th>Días</th></tr>
                        @foreach($vencimientos->take(25) as $item)
                            <tr>
                                <td><span class="vencimiento-chip vencimiento-{{ $item['color'] }}">{{ $item['prioridad'] }}</span></td>
                                <td><a href="{{ route('casos.show', $item['caso_id']) }}" class="table-link">{{ $item['numero_caso'] }}</a></td>
                                <td>{{ $item['victima'] }}</td>
                                <td>{{ $item['aseguradora'] ?: 'N/A' }}</td>
                                <td>{{ $item['evento'] }}</td>
                                <td>{{ $item['fecha_base'] ?: 'N/A' }}</td>
                                <td><strong>{{ $item['dias'] }}</strong></td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <div class="empty-state">No hay vencimientos jurídicos detectados.</div>
                @endif
            </div>

            {{-- ALERTAS --}}
            <div class="section">
                <h3>Alertas automáticas del sistema</h3>
                <p class="section-sub">Acciones pendientes detectadas automáticamente por el motor jurídico.</p>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:10px;">Solicitud y respuesta</div>
                <div class="alert-grid" style="margin-bottom:20px;">
                    <div class="alert-box"><h4>Sin respuesta de aseguradora</h4><div class="alert-count">{{ ($alertasSinRespuesta ?? collect())->count() }}</div><div class="small">Casos con 30 días o más sin respuesta registrada.</div></div>
                    <div class="alert-box"><h4>Apelar dictamen</h4><div class="alert-count">{{ ($alertasApelarDictamen ?? collect())->count() }}</div><div class="small">Aseguradora emitió dictamen y no se ha apelado.</div></div>
                    <div class="alert-box"><h4>Presentar tutela</h4><div class="alert-count">{{ ($alertasTutela ?? collect())->count() }}</div><div class="small">Procede tutela para calificación o por debido proceso.</div></div>
                    <div class="alert-box"><h4>Pagar honorarios junta</h4><div class="alert-count">{{ ($alertasHonorariosJunta ?? collect())->count() }}</div><div class="small">Apelación registrada, pendiente pago de honorarios.</div></div>
                </div>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:10px;">Tutela, fallo y cumplimiento</div>
                <div class="alert-grid" style="margin-bottom:20px;">
                    <div class="alert-box"><h4>Esperando fallo tutela</h4><div class="alert-count blue">{{ ($alertasEsperandoFalloTutela ?? collect())->count() }}</div><div class="small">Tutela presentada, dentro del tiempo normal de espera.</div></div>
                    <div class="alert-box"><h4>Revisar fallo tutela</h4><div class="alert-count" style="color:#b26a00">{{ ($alertasSeguimientoTutela ?? collect())->count() }}</div><div class="small">Pasó 1 mes desde la tutela — revisar fallo o impulsar.</div></div>
                    <div class="alert-box alert-green"><h4>Esperando cumplimiento fallo</h4><div class="alert-count green">{{ $casosCumplimientoTutela ?? 0 }}</div><div class="small">Fallo concedido — aseguradora tiene 14 días para cumplir.</div></div>
                    <div class="alert-box alert-red"><h4>Incidente de desacato</h4><div class="alert-count red">{{ $casosDesacatoPendiente ?? 0 }}</div><div class="small">Fallo concedido, pasaron 14 días y no han cumplido.</div></div>
                </div>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:10px;">Impugnación y segunda instancia</div>
                <div class="alert-grid" style="margin-bottom:20px;">
                    <div class="alert-box alert-red"><h4>Impugnación pendiente</h4><div class="alert-count red">{{ ($alertasImpugnacion ?? collect())->count() }}</div><div class="small">Fallo negado o parcial — pendiente de impugnar.</div></div>
                    <div class="alert-box alert-purple"><h4>Pendiente segunda instancia</h4><div class="alert-count purple">{{ $casosSegundaInstancia ?? 0 }}</div><div class="small">Se impugnó y se espera fallo de segunda instancia.</div></div>
                    <div class="alert-box alert-red"><h4>2ª instancia revocó — cumplir</h4><div class="alert-count red">{{ $casosCumplimientoSegunda ?? 0 }}</div><div class="small">Segunda instancia revocó y la aseguradora aún no ha cumplido.</div></div>
                    <div class="alert-box"><h4>Casos cerrados (2ª instancia)</h4><div class="alert-count" style="color:#41464b">{{ $casosCerradosSegundaInstancia ?? 0 }}</div><div class="small">Segunda instancia confirmó el fallo negado — sin más acciones.</div></div>
                </div>

                <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:10px;">Junta, cobro y pago final</div>
                <div class="alert-grid">
                    <div class="alert-box alert-blue"><h4>Solicitar a junta</h4><div class="alert-count blue">{{ ($alertasSolicitudJunta ?? collect())->count() }}</div><div class="small">Honorarios pagados y sin solicitud a junta.</div></div>
                    <div class="alert-box alert-blue"><h4>Cobrar a aseguradora</h4><div class="alert-count blue">{{ ($alertasReclamacion ?? collect())->count() }}</div><div class="small">Dictamen de junta listo y sin reclamación final.</div></div>
                    <div class="alert-box"><h4>Pago final pendiente</h4><div class="alert-count" style="color:#b26a00">{{ ($alertasPagoPendiente ?? collect())->count() }}</div><div class="small">Reclamados pero aún sin pago final.</div></div>
                    <div class="alert-box alert-red"><h4>Hacer queja</h4><div class="alert-count red">{{ ($alertasQuejaNoPago ?? collect())->count() }}</div><div class="small">Pasó 1 mes desde la reclamación y no han pagado.</div></div>
                </div>
            </div>

            {{-- ANÁLISIS ESTRATÉGICO --}}
            <div class="section">
                <h3>Análisis estratégico de aseguradoras</h3>
                <div class="cards">
                    <div class="card card-green">
                        <div class="card-title">Aseguradora con mayor pago</div>
                        <div class="card-value money">{{ $topAseguradoraPagos['aseguradora'] ?? 'N/A' }}</div>
                        <div class="card-sub">Total pagado: <strong>${{ number_format($topAseguradoraPagos['total_pagado'] ?? 0,0,',','.') }}</strong></div>
                    </div>
                    <div class="card card-orange">
                        <div class="card-title">Aseguradora con más tutela</div>
                        <div class="card-value money-orange">{{ $aseguradoraMayorTutela['aseguradora'] ?? 'N/A' }}</div>
                        <div class="card-sub">Tasa de tutela: <strong>{{ number_format($aseguradoraMayorTutela['tasa_tutela'] ?? 0,1,',','.') }}%</strong></div>
                    </div>
                    <div class="card card-red">
                        <div class="card-title">Aseguradora más lenta pagando</div>
                        <div class="card-value" style="color:#842029">{{ $aseguradoraMasLentaPago['aseguradora'] ?? 'N/A' }}</div>
                        <div class="card-sub">Promedio: <strong>{{ number_format($aseguradoraMasLentaPago['tiempo_promedio_pago_dias'] ?? 0,1,',','.') }} días</strong></div>
                    </div>
                    <div class="card card-purple">
                        <div class="card-title">Aseguradora con más negaciones</div>
                        <div class="card-value" style="color:#5b21b6">{{ $aseguradoraMasNegaciones['aseguradora'] ?? 'N/A' }}</div>
                        <div class="card-sub">Negaciones registradas: <strong>{{ $aseguradoraMasNegaciones['casos_nego'] ?? 0 }}</strong></div>
                    </div>
                </div>

                @if(($aseguradorasEstrategicas ?? collect())->count())
                    <div style="overflow-x:auto">
                        <table>
                            <tr>
                                <th>Aseguradora</th><th>Casos</th><th>Pagados</th><th>Tasa pago</th><th>Tasa tutela</th><th>Tasa apelación</th>
                                <th>Emitió dictamen</th><th>Negó</th><th>No respondió</th><th>2ª revoca</th><th>2ª confirma</th>
                                <th>T. respuesta</th><th>T. pago</th><th>Total pagado</th><th>Prom. por caso</th>
                            </tr>
                            @foreach($aseguradorasEstrategicas as $item)
                                <tr>
                                    <td><strong>{{ $item['aseguradora'] }}</strong></td>
                                    <td>{{ $item['total_casos'] }}</td>
                                    <td>{{ $item['casos_pagados'] }}</td>
                                    <td><span class="metric-chip">{{ number_format($item['tasa_pago'],1,',','.') }}%</span></td>
                                    <td><span class="metric-chip">{{ number_format($item['tasa_tutela'],1,',','.') }}%</span></td>
                                    <td><span class="metric-chip">{{ number_format($item['tasa_apelacion'],1,',','.') }}%</span></td>
                                    <td><span class="mini-chip chip-ok">{{ $item['casos_emitio_dictamen'] ?? 0 }}</span></td>
                                    <td><span class="mini-chip chip-danger">{{ $item['casos_nego'] ?? 0 }}</span></td>
                                    <td><span class="mini-chip chip-warn">{{ $item['casos_no_respondio'] ?? 0 }}</span></td>
                                    <td><span class="mini-chip chip-ok">{{ $item['casos_segunda_revoca'] ?? 0 }}</span></td>
                                    <td><span class="mini-chip chip-danger">{{ $item['casos_segunda_confirma'] ?? 0 }}</span></td>
                                    <td>{{ number_format($item['tiempo_promedio_respuesta_dias'],1,',','.') }} días</td>
                                    <td>{{ number_format($item['tiempo_promedio_pago_dias'],1,',','.') }} días</td>
                                    <td class="money">${{ number_format($item['total_pagado'],0,',','.') }}</td>
                                    <td class="money-blue">${{ number_format($item['promedio_pagado_por_caso'],0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @else
                    <div class="empty-state">No hay suficiente información para generar análisis estratégico.</div>
                @endif
            </div>

            {{-- GRÁFICAS --}}
            <div class="grid-3">
                <div class="section">
                    <h3>Pagado vs ganancia por aseguradora</h3>
                    <div class="chart-box"><canvas id="chartAseguradoras"></canvas></div>
                </div>
                <div class="section">
                    <h3>Casos por estado</h3>
                    <div class="chart-box"><canvas id="chartEstados"></canvas></div>
                </div>
                <div class="section">
                    <h3>Pagos y ganancia por mes</h3>
                    <div class="chart-box"><canvas id="chartMensual"></canvas></div>
                </div>
            </div>

            <div class="cards-2" style="margin-bottom:20px;">
                <div class="section" style="margin-bottom:0">
                    <h3>Distribución de respuestas de aseguradora</h3>
                    <div class="chart-box"><canvas id="chartRespuestas"></canvas></div>
                </div>
                <div class="section" style="margin-bottom:0">
                    <h3>Tipos de tutela presentadas</h3>
                    <div class="chart-box"><canvas id="chartTutelas"></canvas></div>
                </div>
            </div>

            {{-- TABLAS --}}
            <div class="grid-2">
                <div class="section">
                    <h3>Resumen financiero por aseguradora</h3>
                    @if(($porAseguradora ?? collect())->count())
                        <table>
                            <tr><th>Aseguradora</th><th>Total casos</th><th>Total pagado</th><th>Ganancia equipo</th></tr>
                            @foreach($porAseguradora as $item)
                                <tr>
                                    <td>{{ $item->aseguradora ?: 'N/A' }}</td>
                                    <td>{{ $item->total_casos }}</td>
                                    <td class="money">${{ number_format($item->total_pagado,0,',','.') }}</td>
                                    <td class="money-blue">${{ number_format($item->total_equipo,0,',','.') }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="empty-state">No hay información disponible.</div>
                    @endif
                </div>

                <div class="section">
                    <h3>Últimos casos pagados</h3>
                    @if(($ultimosPagados ?? collect())->count())
                        <table>
                            <tr><th>Caso</th><th>Víctima</th><th>Pago</th><th>Honor.</th></tr>
                            @foreach($ultimosPagados as $caso)
                                <tr>
                                    <td><a href="{{ route('casos.show', $caso) }}" class="table-link">{{ $caso->numero_caso }}</a></td>
                                    <td>{{ $caso->nombres }} {{ $caso->apellidos }}</td>
                                    <td class="money">${{ number_format($caso->valor_pagado ?? 0,0,',','.') }}</td>
                                    <td><span class="pill">{{ $caso->porcentaje_honorarios ? number_format($caso->porcentaje_honorarios,0,',','.').'%' : 'N/A' }}</span></td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="empty-state">No hay pagos registrados.</div>
                    @endif
                </div>
            </div>

            <div class="section">
                <h3>Casos por aseguradora</h3>
                @if(($casosPorAseguradora ?? collect())->count())
                    <table>
                        <tr><th>Aseguradora</th><th>Total casos</th><th>Valor estimado total</th><th>Valor pagado total</th><th>Ganancia equipo</th><th>Neto clientes</th></tr>
                        @foreach($casosPorAseguradora as $item)
                            <tr>
                                <td>{{ $item->aseguradora ?: 'N/A' }}</td>
                                <td>{{ $item->total }}</td>
                                <td class="money-blue">${{ number_format($item->valor_estimado_total,0,',','.') }}</td>
                                <td class="money">${{ number_format($item->valor_pagado_total,0,',','.') }}</td>
                                <td class="money-blue">${{ number_format($item->ganancia_equipo_total,0,',','.') }}</td>
                                <td class="money">${{ number_format($item->valor_neto_cliente_total,0,',','.') }}</td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <div class="empty-state">No hay casos agrupados por aseguradora.</div>
                @endif
            </div>

            <div class="section">
                <h3>Casos por estado</h3>
                @if(($casosPorEstado ?? collect())->count())
                    <table>
                        <tr><th>Estado</th><th>Total casos</th></tr>
                        @foreach($casosPorEstado as $item)
                            <tr><td>{{ $item->estado ?: 'Sin estado' }}</td><td>{{ $item->total }}</td></tr>
                        @endforeach
                    </table>
                @else
                    <div class="empty-state">No hay estados registrados.</div>
                @endif
            </div>

            <div class="section">
                <h3>Últimos movimientos de bitácora</h3>
                @if(($ultimosMovimientos ?? collect())->count())
                    <table>
                        <tr><th>Fecha</th><th>Caso</th><th>Movimiento</th><th>Descripción</th></tr>
                        @foreach($ultimosMovimientos as $movimiento)
                            <tr>
                                <td>{{ $movimiento->fecha_evento ?: optional($movimiento->created_at)->format('Y-m-d') }}</td>
                                <td>
                                    @if($movimiento->caso)
                                        <a href="{{ route('casos.show', $movimiento->caso) }}" class="table-link">{{ $movimiento->caso->numero_caso }}</a>
                                    @else N/A @endif
                                </td>
                                <td>{{ $movimiento->titulo }}</td>
                                <td>{{ $movimiento->descripcion }}</td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <div class="empty-state">No hay movimientos registrados.</div>
                @endif
            </div>

        </div>
    </main>
</div>

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

const chartDefaults = { responsive: true, maintainAspectRatio: false };

if (document.getElementById('chartAseguradoras')) {
    new Chart(document.getElementById('chartAseguradoras'), {
        type: 'bar',
        data: {
            labels: labelsAseguradoras,
            datasets: [
                { label: 'Total pagado',    data: dataPagadoAseguradoras, backgroundColor: 'rgba(25,135,84,.7)',  borderColor: 'rgba(25,135,84,1)',  borderWidth: 1 },
                { label: 'Ganancia equipo', data: dataEquipoAseguradoras, backgroundColor: 'rgba(29,78,216,.7)',  borderColor: 'rgba(29,78,216,1)',  borderWidth: 1 }
            ]
        },
        options: chartDefaults
    });
}

if (document.getElementById('chartEstados')) {
    new Chart(document.getElementById('chartEstados'), {
        type: 'doughnut',
        data: {
            labels: labelsEstados,
            datasets: [{
                data: dataEstados,
                backgroundColor: ['#2563eb','#198754','#f59e0b','#dc3545','#6b7280','#0ea5e9','#8b5cf6','#14b8a6','#f97316','#84cc16','#e11d48','#a855f7']
            }]
        },
        options: chartDefaults
    });
}

if (document.getElementById('chartMensual')) {
    new Chart(document.getElementById('chartMensual'), {
        type: 'line',
        data: {
            labels: labelsPagosMensuales,
            datasets: [
                { label: 'Pagado mensual',          data: dataPagosMensuales, borderColor: 'rgba(25,135,84,1)',  backgroundColor: 'rgba(25,135,84,.12)', tension: .25, fill: true },
                { label: 'Ganancia equipo mensual', data: dataEquipoMensual,  borderColor: 'rgba(29,78,216,1)',  backgroundColor: 'rgba(29,78,216,.10)', tension: .25, fill: true }
            ]
        },
        options: chartDefaults
    });
}

if (document.getElementById('chartRespuestas')) {
    new Chart(document.getElementById('chartRespuestas'), {
        type: 'doughnut',
        data: {
            labels: ['Emitió dictamen', 'Negó la solicitud', 'No respondió', 'Sin respuesta aún'],
            datasets: [{
                data: [
                    distribucionRespuestas.emitio_dictamen ?? 0,
                    distribucionRespuestas.nego            ?? 0,
                    distribucionRespuestas.no_respondio    ?? 0,
                    distribucionRespuestas.sin_respuesta   ?? 0
                ],
                backgroundColor: ['#198754','#dc3545','#f59e0b','#9ca3af']
            }]
        },
        options: { ...chartDefaults, plugins: { legend: { position: 'bottom' } } }
    });
}

if (document.getElementById('chartTutelas')) {
    new Chart(document.getElementById('chartTutelas'), {
        type: 'bar',
        data: {
            labels: ['Para calificación', 'Por debido proceso'],
            datasets: [{
                label: 'Tutelas',
                data: [
                    distribucionTutelas.calificacion   ?? 0,
                    distribucionTutelas.debido_proceso ?? 0
                ],
                backgroundColor: ['rgba(14,165,233,.75)', 'rgba(124,58,237,.75)'],
                borderColor:     ['rgba(14,165,233,1)',    'rgba(124,58,237,1)'],
                borderWidth: 1
            }]
        },
        options: { ...chartDefaults, indexAxis: 'y', plugins: { legend: { display: false } } }
    });
}
</script>

</body>
</html>