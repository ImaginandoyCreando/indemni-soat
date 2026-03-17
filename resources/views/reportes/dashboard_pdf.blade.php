<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Dashboard INDEMNI SOAT</title>
    <style>
        body{font-family:DejaVu Sans,sans-serif;font-size:10px;color:#111827;margin:0;padding:0}
        .page{padding:18px 22px}
        h1,h2,h3,h4{margin:0}
        .header{border-bottom:2px solid #1f2937;padding-bottom:10px;margin-bottom:16px}
        .header-title{font-size:20px;font-weight:bold;color:#111827}
        .header-sub{font-size:10px;color:#6b7280;margin-top:5px}
        .section{margin-top:16px}
        .section-title{font-size:13px;font-weight:bold;margin-bottom:8px;color:#111827;border-left:4px solid #2563eb;padding-left:7px}
        .section-sub{font-size:9px;color:#6b7280;margin-left:11px;margin-bottom:8px;margin-top:-5px}
        table{width:100%;border-collapse:collapse}
        th,td{border:1px solid #d1d5db;padding:5px 6px;text-align:left;vertical-align:top;word-break:break-word}
        th{background:#f3f4f6;font-size:9px;font-weight:bold}
        .cards{width:100%;margin-bottom:12px}
        .cards td{width:25%;border:1px solid #d1d5db;padding:9px;vertical-align:top}
        .label{font-size:9px;color:#6b7280;margin-bottom:4px}
        .value{font-size:16px;font-weight:bold;line-height:1.2}
        .sub{font-size:9px;color:#6b7280;margin-top:5px;line-height:1.3}
        .green{color:#198754}
        .blue{color:#1d4ed8}
        .orange{color:#c2410c}
        .red{color:#842029}
        .purple{color:#5b21b6}
        .gray{color:#374151}
        .box-red{background:#fff5f5;border:1px solid #f5c2c7}
        .box-orange{background:#fffaf0;border:1px solid #ffe69c}
        .box-blue{background:#f3fbff;border:1px solid #b6effb}
        .box-green{background:#f3fff7;border:1px solid #badbcc}
        .box-purple{background:#faf5ff;border:1px solid #e9d5ff}
        .pill{display:inline-block;padding:2px 6px;border-radius:999px;font-size:8px;font-weight:bold;background:#e5e7eb;color:#374151}
        .pill-ok{background:#d1e7dd;color:#0f5132}
        .pill-warn{background:#fff3cd;color:#997404}
        .pill-danger{background:#f8d7da;color:#842029}
        .pill-purple{background:#ede9fe;color:#5b21b6}
        .pill-info{background:#cff4fc;color:#055160}
        .footer{margin-top:16px;font-size:9px;color:#6b7280;text-align:right;border-top:1px solid #e5e7eb;padding-top:8px}
        .mb-8{margin-bottom:8px}
        .separator{height:1px;background:#e5e7eb;margin:14px 0}
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="header-title">Dashboard INDEMNI SOAT</div>
        <div class="header-sub">Reporte jurídico, operativo y financiero · Generado el {{ now()->format('Y-m-d H:i') }}</div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- RESUMEN GENERAL                                             --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Resumen general</div>
        <table class="cards">
            <tr>
                <td><div class="label">Total recuperado</div><div class="value green">${{ number_format($totalRecuperado??0,0,',','.') }}</div><div class="sub">Suma de pagos finales</div></td>
                <td><div class="label">Ganancia total equipo</div><div class="value blue">${{ number_format($totalGananciaEquipo??0,0,',','.') }}</div><div class="sub">Honorarios acumulados</div></td>
                <td><div class="label">Neto total clientes</div><div class="value green">${{ number_format($totalNetoClientes??0,0,',','.') }}</div><div class="sub">Entregado a víctimas</div></td>
                <td><div class="label">Promedio ganancia por caso</div><div class="value orange">${{ number_format($promedioGananciaEquipo??0,0,',','.') }}</div><div class="sub">Sobre casos pagados</div></td>
            </tr>
            <tr>
                <td><div class="label">Total casos</div><div class="value gray">{{ $totalCasos??0 }}</div></td>
                <td><div class="label">Casos pagados</div><div class="value green">{{ $casosPagados??0 }}</div></td>
                <td><div class="label">Casos activos</div><div class="value orange">{{ $casosActivos??0 }}</div></td>
                <td><div class="label">Casos con tutela</div><div class="value blue">{{ $casosTutela??0 }}</div></td>
            </tr>
            <tr>
                <td><div class="label">Listos para cobrar</div><div class="value orange">{{ $casosListosReclamar??0 }}</div></td>
                <td><div class="label">Valor estimado total</div><div class="value blue">${{ number_format($totalEstimado??0,0,',','.') }}</div></td>
                <td><div class="label">Valor reclamado total</div><div class="value green">${{ number_format($totalReclamado??0,0,',','.') }}</div></td>
                <td><div class="label">Saldo pendiente estimado</div><div class="value orange">${{ number_format($saldoPendiente??0,0,',','.') }}</div></td>
            </tr>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- PANEL DE VENCIMIENTOS                                       --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Panel de vencimientos jurídicos</div>
        <table class="cards">
            <tr>
                <td class="box-red"><div class="label">Casos críticos</div><div class="value red">{{ $casosCriticos??0 }}</div><div class="sub">Mayor urgencia legal o financiera</div></td>
                <td class="box-orange"><div class="label">Casos urgentes</div><div class="value orange">{{ $casosUrgentes??0 }}</div><div class="sub">Requieren actuación pronta</div></td>
                <td class="box-red"><div class="label">Pagos atrasados</div><div class="value red">{{ $pagosAtrasados??0 }}</div><div class="sub">Reclamados sin pago final</div></td>
                <td class="box-orange"><div class="label">Tutelas en seguimiento</div><div class="value orange">{{ $tutelasPendientesSeguimiento??0 }}</div><div class="sub">Requieren revisión o impulso</div></td>
            </tr>
        </table>

        @if(($vencimientos??collect())->count())
            <table>
                <thead>
                    <tr><th>Prioridad</th><th>Caso</th><th>Víctima</th><th>Aseguradora</th><th>Evento</th><th>Fecha base</th><th>Días</th></tr>
                </thead>
                <tbody>
                    @foreach($vencimientos->take(15) as $item)
                        <tr>
                            <td>{{ $item['prioridad'] }}</td>
                            <td>{{ $item['numero_caso'] }}</td>
                            <td>{{ $item['victima'] }}</td>
                            <td>{{ $item['aseguradora']?:'N/A' }}</td>
                            <td>{{ $item['evento'] }}</td>
                            <td>{{ $item['fecha_base']?:'N/A' }}</td>
                            <td><strong>{{ $item['dias'] }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table><tr><td>No hay vencimientos detectados.</td></tr></table>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ALERTAS DEL SISTEMA — ORGANIZADAS POR BLOQUES (NUEVO)      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Alertas automáticas del sistema</div>

        {{-- Bloque 1: Solicitud y respuesta --}}
        <div style="font-size:8px;font-weight:bold;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Solicitud y respuesta</div>
        <table class="cards" style="margin-bottom:8px;">
            <tr>
                <td><div class="label">Sin respuesta aseguradora</div><div class="value orange">{{ ($alertasSinRespuesta??collect())->count() }}</div></td>
                <td><div class="label">Apelar dictamen</div><div class="value orange">{{ ($alertasApelarDictamen??collect())->count() }}</div></td>
                <td><div class="label">Presentar tutela</div><div class="value orange">{{ ($alertasTutela??collect())->count() }}</div></td>
                <td><div class="label">Pagar honorarios junta</div><div class="value orange">{{ ($alertasHonorariosJunta??collect())->count() }}</div></td>
            </tr>
        </table>

        {{-- Bloque 2: Tutela y cumplimiento (NUEVOS) --}}
        <div style="font-size:8px;font-weight:bold;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Tutela, fallo y cumplimiento</div>
        <table class="cards" style="margin-bottom:8px;">
            <tr>
                <td><div class="label">Esperando fallo tutela</div><div class="value blue">{{ ($alertasEsperandoFalloTutela??collect())->count() }}</div></td>
                <td><div class="label">Revisar fallo tutela</div><div class="value orange">{{ ($alertasSeguimientoTutela??collect())->count() }}</div></td>
                <td class="box-green"><div class="label">Esperando cumplimiento fallo</div><div class="value green">{{ $casosCumplimientoTutela??0 }}</div><div class="sub">Fallo concedido, 14 días para cumplir</div></td>
                <td class="box-red"><div class="label">Incidente de desacato</div><div class="value red">{{ $casosDesacatoPendiente??0 }}</div><div class="sub">14 días cumplidos, no han cumplido</div></td>
            </tr>
        </table>

        {{-- Bloque 3: Impugnación y segunda instancia (NUEVOS) --}}
        <div style="font-size:8px;font-weight:bold;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Impugnación y segunda instancia</div>
        <table class="cards" style="margin-bottom:8px;">
            <tr>
                <td class="box-red"><div class="label">Impugnación pendiente</div><div class="value red">{{ ($alertasImpugnacion??collect())->count() }}</div><div class="sub">Fallo negado sin impugnar</div></td>
                <td class="box-purple"><div class="label">Pendiente segunda instancia</div><div class="value purple">{{ $casosSegundaInstancia??0 }}</div><div class="sub">Esperando fallo 2ª instancia</div></td>
                <td class="box-red"><div class="label">2ª revocó — sin cumplir</div><div class="value red">{{ $casosCumplimientoSegunda??0 }}</div><div class="sub">Aseguradora debe cumplir</div></td>
                <td><div class="label">Cerrados (2ª instancia confirma)</div><div class="value gray">{{ $casosCerradosSegunda??0 }}</div><div class="sub">Sin más acciones jurídicas</div></td>
            </tr>
        </table>

        {{-- Bloque 4: Junta, cobro y pago --}}
        <div style="font-size:8px;font-weight:bold;color:#9ca3af;text-transform:uppercase;margin-bottom:4px;">Junta, cobro y pago final</div>
        <table class="cards">
            <tr>
                <td class="box-blue"><div class="label">Solicitar a junta</div><div class="value blue">{{ ($alertasSolicitudJunta??collect())->count() }}</div></td>
                <td class="box-blue"><div class="label">Cobrar a aseguradora</div><div class="value blue">{{ ($alertasReclamacion??collect())->count() }}</div></td>
                <td><div class="label">Pago final pendiente</div><div class="value orange">{{ ($alertasPagoPendiente??collect())->count() }}</div></td>
                <td class="box-red"><div class="label">Hacer queja</div><div class="value red">{{ ($alertasQuejaNoPago??collect())->count() }}</div></td>
            </tr>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- DISTRIBUCIONES (NUEVO)                                      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Distribución del flujo jurídico</div>
        <table class="cards">
            <tr>
                <td>
                    <div class="label" style="margin-bottom:6px;font-size:10px;font-weight:bold;">Respuestas de aseguradora</div>
                    <table style="width:100%;border:none">
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-ok">Emitió dictamen</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $distribucionRespuestas['emitio_dictamen']??0 }}</strong></td></tr>
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-danger">Negó solicitud</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $distribucionRespuestas['nego']??0 }}</strong></td></tr>
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-warn">No respondió</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $distribucionRespuestas['no_respondio']??0 }}</strong></td></tr>
                        <tr><td style="border:none;padding:2px 0"><span class="pill">Sin respuesta aún</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $distribucionRespuestas['sin_respuesta']??0 }}</strong></td></tr>
                    </table>
                </td>
                <td>
                    <div class="label" style="margin-bottom:6px;font-size:10px;font-weight:bold;">Tipos de tutela presentadas</div>
                    <table style="width:100%;border:none">
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-info">Para calificación</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $distribucionTutelas['calificacion']??0 }}</strong></td></tr>
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-purple">Por debido proceso</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $distribucionTutelas['debido_proceso']??0 }}</strong></td></tr>
                    </table>
                </td>
                <td>
                    <div class="label" style="margin-bottom:6px;font-size:10px;font-weight:bold;">Resultados de tutela</div>
                    @php
                        $concedidos = $casos->filter(fn($c) => $c->resultado_fallo_tutela === 'concedido')->count();
                        $negados    = $casos->filter(fn($c) => $c->resultado_fallo_tutela === 'negado')->count();
                        $parciales  = $casos->filter(fn($c) => $c->resultado_fallo_tutela === 'parcial')->count();
                    @endphp
                    <table style="width:100%;border:none">
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-ok">Concedido</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $concedidos }}</strong></td></tr>
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-danger">Negado</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $negados }}</strong></td></tr>
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-warn">Parcial</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $parciales }}</strong></td></tr>
                    </table>
                </td>
                <td>
                    <div class="label" style="margin-bottom:6px;font-size:10px;font-weight:bold;">Segunda instancia</div>
                    @php
                        $revoca   = $casos->filter(fn($c) => $c->resultado_fallo_segunda_instancia === 'revoca')->count();
                        $confirma = $casos->filter(fn($c) => $c->resultado_fallo_segunda_instancia === 'confirma')->count();
                    @endphp
                    <table style="width:100%;border:none">
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-ok">Revoca</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $revoca }}</strong></td></tr>
                        <tr><td style="border:none;padding:2px 0"><span class="pill pill-danger">Confirma</span></td><td style="border:none;padding:2px 0;text-align:right"><strong>{{ $confirma }}</strong></td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ANÁLISIS ESTRATÉGICO ASEGURADORAS                          --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Análisis estratégico de aseguradoras</div>
        <table class="cards mb-8">
            <tr>
                <td class="box-green"><div class="label">Mayor pago</div><div class="value green" style="font-size:13px">{{ $topAseguradoraPagos['aseguradora']??'N/A' }}</div><div class="sub">${{ number_format($topAseguradoraPagos['total_pagado']??0,0,',','.') }}</div></td>
                <td class="box-orange"><div class="label">Más tutelas</div><div class="value orange" style="font-size:13px">{{ $aseguradoraMayorTutela['aseguradora']??'N/A' }}</div><div class="sub">{{ number_format($aseguradoraMayorTutela['tasa_tutela']??0,1,',','.') }}% tasa tutela</div></td>
                <td class="box-red"><div class="label">Más lenta pagando</div><div class="value red" style="font-size:13px">{{ $aseguradoraMasLentaPago['aseguradora']??'N/A' }}</div><div class="sub">{{ number_format($aseguradoraMasLentaPago['tiempo_promedio_pago_dias']??0,1,',','.') }} días promedio</div></td>
                <td class="box-purple"><div class="label">Más negaciones</div><div class="value purple" style="font-size:13px">{{ $aseguradoraMasNegaciones['aseguradora']??'N/A' }}</div><div class="sub">{{ $aseguradoraMasNegaciones['casos_nego']??0 }} negaciones registradas</div></td>
            </tr>
        </table>

        @if(($aseguradorasEstrategicas??collect())->count())
            <table>
                <thead>
                    <tr>
                        <th>Aseguradora</th>
                        <th>Casos</th>
                        <th>Pagados</th>
                        <th>T.pago</th>
                        <th>T.tutela</th>
                        <th>T.apel.</th>
                        <th>Dictamen</th>
                        <th>Negó</th>
                        <th>No resp.</th>
                        <th>2ª rev.</th>
                        <th>2ª conf.</th>
                        <th>Resp.(días)</th>
                        <th>Pago(días)</th>
                        <th>Total pagado</th>
                        <th>Prom./caso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aseguradorasEstrategicas as $item)
                        <tr>
                            <td>{{ $item['aseguradora'] }}</td>
                            <td>{{ $item['total_casos'] }}</td>
                            <td>{{ $item['casos_pagados'] }}</td>
                            <td>{{ number_format($item['tasa_pago'],1,',','.') }}%</td>
                            <td>{{ number_format($item['tasa_tutela'],1,',','.') }}%</td>
                            <td>{{ number_format($item['tasa_apelacion'],1,',','.') }}%</td>
                            <td><span class="pill pill-ok">{{ $item['casos_emitio_dictamen']??0 }}</span></td>
                            <td><span class="pill pill-danger">{{ $item['casos_nego']??0 }}</span></td>
                            <td><span class="pill pill-warn">{{ $item['casos_no_respondio']??0 }}</span></td>
                            <td><span class="pill pill-ok">{{ $item['casos_segunda_revoca']??0 }}</span></td>
                            <td><span class="pill pill-danger">{{ $item['casos_segunda_confirma']??0 }}</span></td>
                            <td>{{ number_format($item['tiempo_promedio_respuesta_dias'],1,',','.') }}</td>
                            <td>{{ number_format($item['tiempo_promedio_pago_dias'],1,',','.') }}</td>
                            <td>${{ number_format($item['total_pagado'],0,',','.') }}</td>
                            <td>${{ number_format($item['promedio_pagado_por_caso'],0,',','.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- CASOS CONSOLIDADOS                                          --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Casos consolidados</div>
        <div class="section-sub">Incluye campos del flujo jurídico completo</div>
        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Víctima</th>
                    <th>Cédula</th>
                    <th>Aseguradora</th>
                    <th>Estado</th>
                    <th>Alerta</th>
                    <th>Av.</th>
                    <th>Resp. aseg.</th>
                    <th>Tipo tutela</th>
                    <th>Fallo tutela</th>
                    <th>Cumplimiento</th>
                    <th>2ª instancia</th>
                    <th>V. estimado</th>
                    <th>V. pagado</th>
                    <th>Honor.</th>
                    <th>Ganancia</th>
                    <th>Neto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($casos as $caso)
                    @php
                        $tipoRespTexto = match($caso->tipo_respuesta_aseguradora??'') {
                            'emitio_dictamen' => 'Dictamen',
                            'nego'            => 'Negó',
                            'no_respondio'    => 'No resp.',
                            default           => 'Pend.',
                        };
                        $tipoTutelaTexto = match($caso->tipo_tutela??'') {
                            'tutela_calificacion'   => 'Calific.',
                            'tutela_debido_proceso' => 'Deb.proc.',
                            default                 => 'N/A',
                        };
                        $resultadoSegundaTexto = match($caso->resultado_fallo_segunda_instancia??'') {
                            'revoca'   => 'Revoca',
                            'confirma' => 'Confirma',
                            default    => 'N/A',
                        };
                        $cumplimientoTexto = !empty($caso->fecha_cumplimiento_tutela)
                            ? optional($caso->fecha_cumplimiento_tutela)->format('d/m/Y')
                            : 'Pend.';
                    @endphp
                    <tr>
                        <td>{{ $caso->numero_caso }}</td>
                        <td>{{ $caso->nombre_completo }}</td>
                        <td>{{ $caso->cedula }}</td>
                        <td>{{ $caso->aseguradora?:'N/A' }}</td>
                        <td style="font-size:9px">{{ $caso->estado?:'N/A' }}</td>
                        <td style="font-size:9px">{{ $caso->texto_alerta }}</td>
                        <td>{{ $caso->porcentaje_avance??0 }}%</td>
                        <td>{{ $tipoRespTexto }}</td>
                        <td>{{ $tipoTutelaTexto }}</td>
                        <td>{{ $caso->resultado_fallo_tutela ? ucfirst($caso->resultado_fallo_tutela) : 'N/A' }}</td>
                        <td>{{ $cumplimientoTexto }}</td>
                        <td>{{ $resultadoSegundaTexto }}</td>
                        <td>${{ number_format($caso->valor_estimado??0,0,',','.') }}</td>
                        <td>${{ number_format($caso->valor_pagado??0,0,',','.') }}</td>
                        <td>{{ $caso->porcentaje_honorarios ? number_format($caso->porcentaje_honorarios,0,',','.').'%' : 'N/A' }}</td>
                        <td>${{ number_format($caso->ganancia_equipo??0,0,',','.') }}</td>
                        <td>${{ number_format($caso->valor_neto_cliente??0,0,',','.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="17">No hay casos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- RESUMEN FINANCIERO POR ASEGURADORA                         --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Resumen financiero por aseguradora</div>
        @if(($porAseguradora??collect())->count())
            <table>
                <thead>
                    <tr><th>Aseguradora</th><th>Total casos</th><th>Total pagado</th><th>Ganancia equipo</th></tr>
                </thead>
                <tbody>
                    @foreach($porAseguradora as $item)
                        <tr>
                            <td>{{ $item->aseguradora?:'N/A' }}</td>
                            <td>{{ $item->total_casos }}</td>
                            <td>${{ number_format($item->total_pagado,0,',','.') }}</td>
                            <td>${{ number_format($item->total_equipo,0,',','.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- BITÁCORA                                                    --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Últimos movimientos de bitácora</div>
        @if(($bitacoras??collect())->count())
            <table>
                <thead>
                    <tr><th>Fecha</th><th>Caso</th><th>Movimiento</th><th>Descripción</th></tr>
                </thead>
                <tbody>
                    @foreach($bitacoras as $movimiento)
                        <tr>
                            <td>{{ $movimiento->fecha_evento ?: optional($movimiento->created_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($movimiento->caso)->numero_caso?:'N/A' }}</td>
                            <td>{{ $movimiento->titulo }}</td>
                            <td>{{ $movimiento->descripcion }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table><tr><td>No hay movimientos registrados.</td></tr></table>
        @endif
    </div>

    <div class="footer">
        INDEMNI SOAT · Reporte generado automáticamente · {{ now()->format('Y-m-d H:i') }}
    </div>

</div>
</body>
</html>