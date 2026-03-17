<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Bitacora;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ─── Carga todos los casos activos en memoria (para métodos del modelo) ──
        $casosAlertas = Caso::orderByDesc('id')->get();

        // ─── Contadores básicos ───────────────────────────────────────────────────
        $totalCasos    = Caso::count();

        $casosPagados  = Caso::where(function ($q) {
            $q->whereNotNull('fecha_pago_final')->orWhere('estado', 'Pagado');
        })->count();

        $casosActivos  = Caso::where(function ($q) {
            $q->whereNull('fecha_pago_final')
              ->where(fn ($s) => $s->whereNull('estado')->orWhere('estado', '!=', 'Pagado'))
              ->where(fn ($s) => $s->whereNull('estado')->orWhere('estado', '!=', 'Cerrado'));
        })->count();

        $casosTutela   = Caso::whereNotNull('fecha_tutela')->count();

        // ─── Contadores de alertas flujo jurídico (con modelo) ───────────────────
        $casosListosReclamar          = $casosAlertas->filter(fn ($c) => $c->requiereCobroAseguradora())->count();
        $casosListosApelar            = $casosAlertas->filter(fn ($c) => $c->requiereApelacion())->count();
        $casosPendientesHonorarios    = $casosAlertas->filter(fn ($c) => $c->requierePagoHonorariosJunta())->count();
        $casosListosSolicitudJunta    = $casosAlertas->filter(fn ($c) => $c->requiereSolicitudJunta())->count();
        $casosQuejaNoPago             = $casosAlertas->filter(fn ($c) => $c->requiereQuejaNoPago())->count();

        // NUEVOS
        $casosCumplimientoTutela      = $casosAlertas->filter(fn ($c) => $c->requiereCumplimientoTutela())->count();
        $casosDesacatoPendiente       = $casosAlertas->filter(fn ($c) => $c->requiereIncidenteDesacato())->count();
        $casosSegundaInstancia        = $casosAlertas->filter(fn ($c) => $c->requiereSegundaInstancia())->count();
        $casosCerradosSegundaInstancia= $casosAlertas->filter(fn ($c) => $c->casoCerradoSegundaInstancia())->count();
        $casosCumplimientoSegunda     = $casosAlertas->filter(fn ($c) => $c->requiereCumplimientoSegundaInstancia())->count();

        // ─── Totales financieros ──────────────────────────────────────────────────
        $totalEstimado       = (float) Caso::sum('valor_estimado');
        $totalReclamado      = (float) Caso::sum('valor_reclamado');
        $totalRecuperado     = (float) Caso::sum('valor_pagado');
        $totalGananciaEquipo = (float) Caso::sum('ganancia_equipo');
        $totalNetoClientes   = (float) Caso::sum('valor_neto_cliente');

        $valorEstimadoTotal  = $totalEstimado;
        $valorReclamadoTotal = $totalReclamado;
        $valorPagadoTotal    = $totalRecuperado;
        $saldoPendiente      = $valorEstimadoTotal - $valorPagadoTotal;

        $promedioGananciaEquipo = $casosPagados > 0
            ? $totalGananciaEquipo / $casosPagados
            : 0;

        // ─── Agrupaciones por aseguradora y estado ────────────────────────────────
        $casosPorAseguradora = Caso::select(
                'aseguradora',
                DB::raw('count(*) as total'),
                DB::raw('coalesce(sum(valor_estimado),0) as valor_estimado_total'),
                DB::raw('coalesce(sum(valor_pagado),0) as valor_pagado_total'),
                DB::raw('coalesce(sum(ganancia_equipo),0) as ganancia_equipo_total'),
                DB::raw('coalesce(sum(valor_neto_cliente),0) as valor_neto_cliente_total')
            )
            ->groupBy('aseguradora')
            ->orderByDesc('valor_pagado_total')
            ->get();

        $porAseguradora = Caso::selectRaw('
                aseguradora,
                COUNT(*) as total_casos,
                COALESCE(SUM(valor_pagado),0) as total_pagado,
                COALESCE(SUM(ganancia_equipo),0) as total_equipo
            ')
            ->groupBy('aseguradora')
            ->orderByDesc('total_pagado')
            ->get();

        $casosPorEstado = Caso::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        // ─── Colecciones de alerta ────────────────────────────────────────────────
        $alertasSinRespuesta         = $casosAlertas->filter(fn ($c) => $c->requiereRespuestaAseguradora());
        $alertasTutela               = $casosAlertas->filter(fn ($c) => $c->requiereTutela());
        $alertasApelarDictamen       = $casosAlertas->filter(fn ($c) => $c->requiereApelacion());
        $alertasHonorariosJunta      = $casosAlertas->filter(fn ($c) => $c->requierePagoHonorariosJunta());
        $alertasSolicitudJunta       = $casosAlertas->filter(fn ($c) => $c->requiereSolicitudJunta());
        $alertasReclamacion          = $casosAlertas->filter(fn ($c) => $c->requiereCobroAseguradora());
        $alertasPagoPendiente        = $casosAlertas->filter(fn ($c) => $c->requierePagoPendiente());
        $alertasSeguimientoTutela    = $casosAlertas->filter(fn ($c) => $c->requiereSeguimientoTutela());
        $alertasQuejaNoPago          = $casosAlertas->filter(fn ($c) => $c->requiereQuejaNoPago());
        $alertasDesacato             = $casosAlertas->filter(fn ($c) => $c->requiereIncidenteDesacato());
        $alertasImpugnacion          = $casosAlertas->filter(fn ($c) => $c->requiereImpugnacion());

        // NUEVAS
        $alertasCumplimientoTutela   = $casosAlertas->filter(fn ($c) => $c->requiereCumplimientoTutela());
        $alertasSegundaInstancia     = $casosAlertas->filter(fn ($c) => $c->requiereSegundaInstancia());
        $alertasCumplimientoSegunda  = $casosAlertas->filter(fn ($c) => $c->requiereCumplimientoSegundaInstancia());

        $alertasEsperandoFalloTutela = $casosAlertas->filter(function ($caso) {
            return !empty($caso->fecha_tutela)
                && !$caso->requiereSeguimientoTutela()
                && !$caso->estaPagado()
                && empty($caso->fecha_fallo_tutela);
        });

        // ─── Panel de vencimientos ────────────────────────────────────────────────
        $vencimientos = collect();

        foreach ($casosAlertas as $caso) {
            if ($caso->estaPagado() || $caso->casoCerradoSegundaInstancia()) {
                continue;
            }

            // Sin respuesta aseguradora (30+ días)
            if (!empty($caso->fecha_solicitud_aseguradora) && empty($caso->tipo_respuesta_aseguradora)) {
                $dias = Carbon::parse($caso->fecha_solicitud_aseguradora)->diffInDays(Carbon::today());
                if ($dias >= 30) {
                    $vencimientos->push([
                        'prioridad'   => 'Crítico',
                        'color'       => 'red',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => 'Sin respuesta aseguradora',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_solicitud_aseguradora)->format('Y-m-d'),
                    ]);
                }
            }

            // Apelación pendiente (10+ días desde dictamen) — solo si emitió dictamen
            if ($caso->tipo_respuesta_aseguradora === 'emitio_dictamen'
                && !empty($caso->fecha_respuesta_aseguradora)
                && empty($caso->fecha_apelacion)) {
                $dias = Carbon::parse($caso->fecha_respuesta_aseguradora)->diffInDays(Carbon::today());
                if ($dias >= 10) {
                    $vencimientos->push([
                        'prioridad'   => 'Urgente',
                        'color'       => 'orange',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => 'Apelación pendiente',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_respuesta_aseguradora)->format('Y-m-d'),
                    ]);
                }
            }

            // Pago honorarios pendiente (10+ días desde apelación)
            if (!empty($caso->fecha_apelacion) && empty($caso->fecha_pago_honorarios)) {
                $dias = Carbon::parse($caso->fecha_apelacion)->diffInDays(Carbon::today());
                if ($dias >= 10) {
                    $vencimientos->push([
                        'prioridad'   => 'Urgente',
                        'color'       => 'orange',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => 'Pago honorarios junta pendiente',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_apelacion)->format('Y-m-d'),
                    ]);
                }
            }

            // Cumplimiento tutela concedida (dentro de las 2 semanas — urgente)
            if (!empty($caso->fecha_fallo_tutela)
                && $caso->resultado_fallo_tutela === 'concedido'
                && empty($caso->fecha_cumplimiento_tutela)
                && empty($caso->fecha_incidente_desacato)
                && empty($caso->fecha_pago_honorarios)) {
                $dias = Carbon::parse($caso->fecha_fallo_tutela)->diffInDays(Carbon::today());
                if ($dias >= 5) {
                    $vencimientos->push([
                        'prioridad'   => $dias >= 14 ? 'Crítico' : 'Urgente',
                        'color'       => $dias >= 14 ? 'red' : 'orange',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => $dias >= 14
                            ? 'Desacato — no cumplieron el fallo'
                            : 'Esperando cumplimiento fallo tutela',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_fallo_tutela)->format('Y-m-d'),
                    ]);
                }
            }

            // Segunda instancia pendiente (impugnó pero no hay fallo)
            if (!empty($caso->fecha_impugnacion) && empty($caso->fecha_fallo_segunda_instancia)) {
                $dias = Carbon::parse($caso->fecha_impugnacion)->diffInDays(Carbon::today());
                if ($dias >= 20) {
                    $vencimientos->push([
                        'prioridad'   => 'Urgente',
                        'color'       => 'orange',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => 'Segunda instancia pendiente',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_impugnacion)->format('Y-m-d'),
                    ]);
                }
            }

            // Cumplimiento segunda instancia revocó — aseguradora no ha cumplido
            if (!empty($caso->fecha_fallo_segunda_instancia)
                && $caso->resultado_fallo_segunda_instancia === 'revoca'
                && empty($caso->fecha_cumplimiento_tutela)
                && empty($caso->fecha_pago_honorarios)) {
                $dias = Carbon::parse($caso->fecha_fallo_segunda_instancia)->diffInDays(Carbon::today());
                if ($dias >= 5) {
                    $vencimientos->push([
                        'prioridad'   => 'Crítico',
                        'color'       => 'red',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => 'Segunda instancia revocó — aseguradora debe cumplir',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_fallo_segunda_instancia)->format('Y-m-d'),
                    ]);
                }
            }

            // Solicitud a junta pendiente (20+ días desde honorarios)
            if (!empty($caso->fecha_pago_honorarios) && empty($caso->fecha_envio_junta)) {
                $dias = Carbon::parse($caso->fecha_pago_honorarios)->diffInDays(Carbon::today());
                if ($dias >= 20) {
                    $vencimientos->push([
                        'prioridad'   => 'Seguimiento',
                        'color'       => 'blue',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => 'Solicitud a junta pendiente',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_pago_honorarios)->format('Y-m-d'),
                    ]);
                }
            }

            // Pago final atrasado (15+ días desde reclamación)
            if (!empty($caso->fecha_reclamacion_final) && empty($caso->fecha_pago_final)) {
                $dias = Carbon::parse($caso->fecha_reclamacion_final)->diffInDays(Carbon::today());
                if ($dias >= 15) {
                    $vencimientos->push([
                        'prioridad'   => 'Crítico',
                        'color'       => 'red',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => 'Pago final atrasado',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_reclamacion_final)->format('Y-m-d'),
                    ]);
                }
            }

            // Tutela en seguimiento (5+ días desde presentación, sin fallo)
            if (!empty($caso->fecha_tutela) && empty($caso->fecha_fallo_tutela) && empty($caso->fecha_pago_final)) {
                $dias = Carbon::parse($caso->fecha_tutela)->diffInDays(Carbon::today());
                if ($dias >= 5) {
                    $vencimientos->push([
                        'prioridad'   => 'Urgente',
                        'color'       => 'orange',
                        'caso_id'     => $caso->id,
                        'numero_caso' => $caso->numero_caso,
                        'victima'     => $caso->nombre_completo,
                        'aseguradora' => $caso->aseguradora,
                        'evento'      => 'Tutela en seguimiento',
                        'dias'        => $dias,
                        'fecha_base'  => optional($caso->fecha_tutela)->format('Y-m-d'),
                    ]);
                }
            }
        }

        $vencimientos = $vencimientos->sortByDesc('dias')->values();

        // ─── Contadores del panel de vencimientos ────────────────────────────────
        $casosCriticos                = $vencimientos->where('color', 'red')->count();
        $casosUrgentes                = $vencimientos->where('color', 'orange')->count();
        $pagosAtrasados               = $vencimientos->where('evento', 'Pago final atrasado')->count();
        $tutelasPendientesSeguimiento = $vencimientos->where('evento', 'Tutela en seguimiento')->count();

        // ─── Análisis estratégico por aseguradora ────────────────────────────────
        $aseguradorasEstrategicas = $casosAlertas
            ->groupBy(fn ($c) => $c->aseguradora ?: 'Sin aseguradora')
            ->map(function ($items, $aseguradora) {

                $totalCasosAseg   = $items->count();
                $casosPagadosAseg = $items->filter(fn ($c) => $c->estaPagado())->count();
                $casosTutelaAseg  = $items->filter(fn ($c) => !empty($c->fecha_tutela))->count();
                $casosApelAseg    = $items->filter(fn ($c) => !empty($c->fecha_apelacion))->count();

                // NUEVAS métricas
                $casosNego        = $items->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'nego')->count();
                $casosNR          = $items->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'no_respondio')->count();
                $casosDictamen    = $items->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'emitio_dictamen')->count();
                $casosSegundaRev  = $items->filter(fn ($c) => $c->resultado_fallo_segunda_instancia === 'revoca')->count();
                $casosSegundaCon  = $items->filter(fn ($c) => $c->resultado_fallo_segunda_instancia === 'confirma')->count();

                $totalPagado    = (float) $items->sum(fn ($c) => (float) ($c->valor_pagado ?? 0));
                $totalGanancia  = (float) $items->sum(fn ($c) => (float) ($c->ganancia_equipo ?? 0));
                $promedioPorCaso = $casosPagadosAseg > 0 ? $totalPagado / $casosPagadosAseg : 0;

                $tiemposPago = $items
                    ->filter(fn ($c) => !empty($c->fecha_reclamacion_final) && !empty($c->fecha_pago_final))
                    ->map(fn ($c) => Carbon::parse($c->fecha_reclamacion_final)->diffInDays(Carbon::parse($c->fecha_pago_final)));

                $tiemposResp = $items
                    ->filter(fn ($c) => !empty($c->fecha_solicitud_aseguradora) && !empty($c->fecha_respuesta_aseguradora))
                    ->map(fn ($c) => Carbon::parse($c->fecha_solicitud_aseguradora)->diffInDays(Carbon::parse($c->fecha_respuesta_aseguradora)));

                return [
                    'aseguradora'                     => $aseguradora,
                    'total_casos'                     => $totalCasosAseg,
                    'casos_pagados'                   => $casosPagadosAseg,
                    'casos_tutela'                    => $casosTutelaAseg,
                    'casos_apelacion'                 => $casosApelAseg,
                    'casos_nego'                      => $casosNego,       // NUEVO
                    'casos_no_respondio'              => $casosNR,         // NUEVO
                    'casos_emitio_dictamen'           => $casosDictamen,   // NUEVO
                    'casos_segunda_revoca'            => $casosSegundaRev, // NUEVO
                    'casos_segunda_confirma'          => $casosSegundaCon, // NUEVO
                    'total_pagado'                    => $totalPagado,
                    'total_ganancia'                  => $totalGanancia,
                    'promedio_pagado_por_caso'        => $promedioPorCaso,
                    'tasa_pago'                       => $totalCasosAseg > 0 ? round(($casosPagadosAseg / $totalCasosAseg) * 100, 1) : 0,
                    'tasa_tutela'                     => $totalCasosAseg > 0 ? round(($casosTutelaAseg   / $totalCasosAseg) * 100, 1) : 0,
                    'tasa_apelacion'                  => $totalCasosAseg > 0 ? round(($casosApelAseg     / $totalCasosAseg) * 100, 1) : 0,
                    'tiempo_promedio_pago_dias'       => $tiemposPago->count() > 0 ? round($tiemposPago->avg(), 1) : 0,
                    'tiempo_promedio_respuesta_dias'  => $tiemposResp->count() > 0 ? round($tiemposResp->avg(), 1) : 0,
                ];
            })
            ->sortByDesc('total_pagado')
            ->values();

        $topAseguradoraPagos      = $aseguradorasEstrategicas->sortByDesc('total_pagado')->first();
        $aseguradoraMayorTutela   = $aseguradorasEstrategicas->sortByDesc('tasa_tutela')->first();
        $aseguradoraMasLentaPago  = $aseguradorasEstrategicas->sortByDesc('tiempo_promedio_pago_dias')->first();

        // NUEVO: aseguradora con más negativas
        $aseguradoraMasNegaciones = $aseguradorasEstrategicas->sortByDesc('casos_nego')->first();

        // ─── Últimos pagados y movimientos ───────────────────────────────────────
        $ultimosPagados = Caso::whereNotNull('fecha_pago_final')
            ->orderByDesc('fecha_pago_final')
            ->limit(8)
            ->get();

        $ultimosMovimientos = Bitacora::with('caso')
            ->orderByDesc('fecha_evento')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        // ─── Datos para gráficas ──────────────────────────────────────────────────
        $labelsAseguradoras       = $porAseguradora->pluck('aseguradora')->map(fn ($v) => $v ?: 'Sin aseguradora')->values()->all();
        $dataPagadoAseguradoras   = $porAseguradora->pluck('total_pagado')->map(fn ($v) => (float) $v)->values()->all();
        $dataEquipoAseguradoras   = $porAseguradora->pluck('total_equipo')->map(fn ($v) => (float) $v)->values()->all();
        $labelsEstados            = $casosPorEstado->pluck('estado')->map(fn ($v) => $v ?: 'Sin estado')->values()->all();
        $dataEstados              = $casosPorEstado->pluck('total')->map(fn ($v) => (int) $v)->values()->all();

        // Datos gráfica distribución tipos de respuesta aseguradora (NUEVO)
        $distribucionRespuestas = [
            'emitio_dictamen' => Caso::where('tipo_respuesta_aseguradora', 'emitio_dictamen')->count(),
            'nego'            => Caso::where('tipo_respuesta_aseguradora', 'nego')->count(),
            'no_respondio'    => Caso::where('tipo_respuesta_aseguradora', 'no_respondio')->count(),
            'sin_respuesta'   => Caso::whereNull('tipo_respuesta_aseguradora')->whereNotNull('fecha_solicitud_aseguradora')->count(),
        ];

        // Datos gráfica tipos de tutela (NUEVO)
        $distribucionTutelas = [
            'calificacion'   => Caso::where('tipo_tutela', 'tutela_calificacion')->count(),
            'debido_proceso' => Caso::where('tipo_tutela', 'tutela_debido_proceso')->count(),
        ];

        // ─── Pagos mensuales ──────────────────────────────────────────────────────
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $pagosMensuales = Caso::selectRaw("
                    strftime('%Y', fecha_pago_final) as anio,
                    strftime('%m', fecha_pago_final) as mes,
                    COALESCE(SUM(valor_pagado),0)   as total_pagado,
                    COALESCE(SUM(ganancia_equipo),0) as total_equipo
                ")
                ->whereNotNull('fecha_pago_final')
                ->groupByRaw("strftime('%Y', fecha_pago_final), strftime('%m', fecha_pago_final)")
                ->orderByRaw("strftime('%Y', fecha_pago_final), strftime('%m', fecha_pago_final)")
                ->get();
        } else {
            $pagosMensuales = Caso::selectRaw("
                    EXTRACT(YEAR FROM fecha_pago_final) as anio,
                    LPAD(EXTRACT(MONTH FROM fecha_pago_final)::text, 2, '0') as mes,
                    COALESCE(SUM(valor_pagado),0)   as total_pagado,
                    COALESCE(SUM(ganancia_equipo),0) as total_equipo
                ")
                ->whereNotNull('fecha_pago_final')
                ->groupByRaw("EXTRACT(YEAR FROM fecha_pago_final), EXTRACT(MONTH FROM fecha_pago_final)")
                ->orderByRaw("EXTRACT(YEAR FROM fecha_pago_final), EXTRACT(MONTH FROM fecha_pago_final)")
                ->get();
        }

        $labelsPagosMensuales = $pagosMensuales->map(fn ($i) => $i->mes . '/' . $i->anio)->values()->all();
        $dataPagosMensuales   = $pagosMensuales->pluck('total_pagado')->map(fn ($v) => (float) $v)->values()->all();
        $dataEquipoMensual    = $pagosMensuales->pluck('total_equipo')->map(fn ($v) => (float) $v)->values()->all();

        // ─── Retornar vista ───────────────────────────────────────────────────────
        return view('dashboard.index', compact(
            // Contadores básicos
            'totalCasos',
            'casosPagados',
            'casosActivos',
            'casosTutela',
            // Alertas flujo existentes
            'casosListosReclamar',
            'casosListosApelar',
            'casosPendientesHonorarios',
            'casosListosSolicitudJunta',
            'casosQuejaNoPago',
            // Alertas flujo NUEVAS
            'casosCumplimientoTutela',
            'casosDesacatoPendiente',
            'casosSegundaInstancia',
            'casosCerradosSegundaInstancia',
            'casosCumplimientoSegunda',
            // Financieros
            'totalEstimado',
            'totalReclamado',
            'totalRecuperado',
            'totalGananciaEquipo',
            'totalNetoClientes',
            'valorEstimadoTotal',
            'valorReclamadoTotal',
            'valorPagadoTotal',
            'saldoPendiente',
            'promedioGananciaEquipo',
            // Agrupaciones
            'casosPorAseguradora',
            'porAseguradora',
            'casosPorEstado',
            // Colecciones de alertas
            'alertasSinRespuesta',
            'alertasTutela',
            'alertasApelarDictamen',
            'alertasHonorariosJunta',
            'alertasSolicitudJunta',
            'alertasReclamacion',
            'alertasPagoPendiente',
            'alertasEsperandoFalloTutela',
            'alertasSeguimientoTutela',
            'alertasQuejaNoPago',
            'alertasDesacato',
            'alertasImpugnacion',
            'alertasCumplimientoTutela',       // NUEVA
            'alertasSegundaInstancia',          // NUEVA
            'alertasCumplimientoSegunda',       // NUEVA
            // Panel de vencimientos
            'casosCriticos',
            'casosUrgentes',
            'pagosAtrasados',
            'tutelasPendientesSeguimiento',
            'vencimientos',
            // Análisis aseguradoras
            'aseguradorasEstrategicas',
            'topAseguradoraPagos',
            'aseguradoraMayorTutela',
            'aseguradoraMasLentaPago',
            'aseguradoraMasNegaciones',         // NUEVA
            // Últimos registros
            'ultimosPagados',
            'ultimosMovimientos',
            // Datos gráficas
            'labelsAseguradoras',
            'dataPagadoAseguradoras',
            'dataEquipoAseguradoras',
            'labelsEstados',
            'dataEstados',
            'labelsPagosMensuales',
            'dataPagosMensuales',
            'dataEquipoMensual',
            'distribucionRespuestas',           // NUEVA
            'distribucionTutelas'               // NUEVA
        ));
    }
}