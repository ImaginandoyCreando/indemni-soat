<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Bitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteController extends Controller
{
    // =========================================================================
    // EXPORTAR EXCEL (CSV)
    // =========================================================================

    public function exportarExcel()
    {
        $casos     = Caso::orderByDesc('id')->get();
        $bitacoras = Bitacora::with('caso')
            ->orderByDesc('fecha_evento')
            ->orderByDesc('id')
            ->take(50)
            ->get();

        $nombreArchivo = 'reporte_indemni_soat_' . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($casos, $bitacoras) {
            $handle = fopen('php://output', 'w');

            // BOM para Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['REPORTE INDEMNI SOAT'], ';');
            fputcsv($handle, ['Fecha de generación', now()->format('Y-m-d H:i:s')], ';');
            fputcsv($handle, [], ';');

            // ── Resumen general ───────────────────────────────────────────────
            $totalCasos                  = $casos->count();
            $casosPagados                = $casos->filter(fn ($c) => $c->estaPagado())->count();
            $casosActivos                = $casos->filter(fn ($c) => !$c->estaPagado() && $c->estado !== 'Cerrado')->count();
            $casosTutela                 = $casos->filter(fn ($c) => !empty($c->fecha_tutela))->count();
            $casosListosReclamar         = $casos->filter(fn ($c) => $c->requiereCobroAseguradora())->count();
            $casosListosApelar           = $casos->filter(fn ($c) => $c->requiereApelacion())->count();
            $casosPendientesHonorarios   = $casos->filter(fn ($c) => $c->requierePagoHonorariosJunta())->count();
            $casosListosSolicitudJunta   = $casos->filter(fn ($c) => $c->requiereSolicitudJunta())->count();
            $casosQuejaNoPago            = $casos->filter(fn ($c) => $c->requiereQuejaNoPago())->count();
            // NUEVOS
            $casosCumplimientoTutela     = $casos->filter(fn ($c) => $c->requiereCumplimientoTutela())->count();
            $casosDesacatoPendiente      = $casos->filter(fn ($c) => $c->requiereIncidenteDesacato())->count();
            $casosSegundaInstancia       = $casos->filter(fn ($c) => $c->requiereSegundaInstancia())->count();
            $casosCerradosSegunda        = $casos->filter(fn ($c) => $c->casoCerradoSegundaInstancia())->count();
            $casosCumplimientoSegunda    = $casos->filter(fn ($c) => $c->requiereCumplimientoSegundaInstancia())->count();

            $totalEstimado       = (float) $casos->sum('valor_estimado');
            $totalReclamado      = (float) $casos->sum('valor_reclamado');
            $totalRecuperado     = (float) $casos->sum('valor_pagado');
            $totalGananciaEquipo = (float) $casos->sum('ganancia_equipo');
            $totalNetoClientes   = (float) $casos->sum('valor_neto_cliente');
            $saldoPendiente      = $totalEstimado - $totalRecuperado;
            $promedioGanancia    = $casosPagados > 0 ? ($totalGananciaEquipo / $casosPagados) : 0;

            fputcsv($handle, ['RESUMEN GENERAL'], ';');
            fputcsv($handle, ['Total casos',                       $totalCasos], ';');
            fputcsv($handle, ['Casos pagados',                     $casosPagados], ';');
            fputcsv($handle, ['Casos activos',                     $casosActivos], ';');
            fputcsv($handle, ['Casos con tutela',                  $casosTutela], ';');
            fputcsv($handle, ['Casos listos para cobrar',          $casosListosReclamar], ';');
            fputcsv($handle, ['Casos listos para apelar',          $casosListosApelar], ';');
            fputcsv($handle, ['Pendientes honorarios junta',       $casosPendientesHonorarios], ';');
            fputcsv($handle, ['Listos para solicitud a junta',     $casosListosSolicitudJunta], ';');
            fputcsv($handle, ['Casos para queja por no pago',      $casosQuejaNoPago], ';');
            // NUEVAS ALERTAS
            fputcsv($handle, ['Esperando cumplimiento tutela',     $casosCumplimientoTutela], ';');
            fputcsv($handle, ['Desacato pendiente',                $casosDesacatoPendiente], ';');
            fputcsv($handle, ['Pendiente segunda instancia',       $casosSegundaInstancia], ';');
            fputcsv($handle, ['Cerrados en segunda instancia',     $casosCerradosSegunda], ';');
            fputcsv($handle, ['2ª instancia revocó sin cumplir',   $casosCumplimientoSegunda], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['Valor estimado total',              $totalEstimado], ';');
            fputcsv($handle, ['Valor reclamado total',             $totalReclamado], ';');
            fputcsv($handle, ['Total recuperado',                  $totalRecuperado], ';');
            fputcsv($handle, ['Ganancia total equipo',             $totalGananciaEquipo], ';');
            fputcsv($handle, ['Neto total clientes',               $totalNetoClientes], ';');
            fputcsv($handle, ['Saldo pendiente estimado',          $saldoPendiente], ';');
            fputcsv($handle, ['Promedio ganancia por caso pagado', round($promedioGanancia, 2)], ';');
            fputcsv($handle, [], ';');

            // ── Distribución de respuestas (NUEVO) ───────────────────────────
            fputcsv($handle, ['DISTRIBUCIÓN RESPUESTAS ASEGURADORA'], ';');
            fputcsv($handle, ['Emitió dictamen', $casos->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'emitio_dictamen')->count()], ';');
            fputcsv($handle, ['Negó la solicitud', $casos->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'nego')->count()], ';');
            fputcsv($handle, ['No respondió', $casos->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'no_respondio')->count()], ';');
            fputcsv($handle, ['Sin respuesta aún', $casos->filter(fn ($c) => !$c->tipo_respuesta_aseguradora && $c->fecha_solicitud_aseguradora)->count()], ';');
            fputcsv($handle, [], ';');

            // ── Distribución de tutelas (NUEVO) ──────────────────────────────
            fputcsv($handle, ['DISTRIBUCIÓN TIPOS DE TUTELA'], ';');
            fputcsv($handle, ['Para calificación', $casos->filter(fn ($c) => $c->tipo_tutela === 'tutela_calificacion')->count()], ';');
            fputcsv($handle, ['Por debido proceso', $casos->filter(fn ($c) => $c->tipo_tutela === 'tutela_debido_proceso')->count()], ';');
            fputcsv($handle, [], ';');

            // ── Detalle de casos ──────────────────────────────────────────────
            fputcsv($handle, ['DETALLE DE CASOS'], ';');
            fputcsv($handle, [
                'Número de caso',
                'Víctima',
                'Cédula',
                'Teléfono',
                'Correo',
                'Departamento',
                'Ciudad',
                'Dirección',
                'Aseguradora',
                'Junta asignada',
                'Estado',
                'Alerta',
                'Color alerta',
                'Avance %',
                'Fecha accidente',
                'Fecha solicitud aseguradora',
                // NUEVOS
                'Tipo respuesta aseguradora',
                'Fecha respuesta / dictamen',
                'Fecha apelación',
                // NUEVOS
                'Tipo tutela',
                'Fecha tutela',
                'Fecha fallo tutela',
                'Resultado fallo tutela',
                // NUEVO
                'Fecha cumplimiento tutela',
                'Tipo cumplimiento tutela',
                'Fecha incidente desacato',
                'Fecha impugnación',
                // NUEVOS
                'Fecha fallo segunda instancia',
                'Resultado segunda instancia',
                'Fecha pago honorarios',
                'Alta ortopedia',
                'Fecha alta ortopedia',
                'FURPEN completo',
                'Fecha FURPEN recibido',
                'Fecha envío junta',
                'Fecha dictamen junta',
                'Fecha reclamación final',
                'Fecha pago final',
                'Fecha prescripción',
                'Días para prescripción',
                'PCL',
                'SMLDV aplicados',
                'SMLDV año accidente',
                'Valor estimado',
                'Valor reclamado',
                'Valor pagado',
                'Honorarios %',
                'Ganancia equipo',
                'Valor neto cliente',
                'Tiene poder',
                'Fecha entrega poder',
                'Fecha poder firmado',
                'Tiene contrato',
                'Fecha entrega contrato',
                'Fecha contrato firmado',
                'Observación alta ortopedia',
                'Observación FURPEN',
                'Observación reclamación',
                'Observación pago',
                'Observaciones',
            ], ';');

            foreach ($casos as $caso) {
                $textoTipoResp = match($caso->tipo_respuesta_aseguradora ?? '') {
                    'emitio_dictamen' => 'Emitió dictamen',
                    'nego'            => 'Negó la solicitud',
                    'no_respondio'    => 'No respondió',
                    default           => '',
                };
                $textoTipoTutela = match($caso->tipo_tutela ?? '') {
                    'tutela_calificacion'   => 'Para calificación',
                    'tutela_debido_proceso' => 'Por debido proceso',
                    default                 => '',
                };
                $textoTipoCumpl = match($caso->tipo_cumplimiento_tutela ?? '') {
                    'voluntario' => 'Voluntario',
                    'desacato'   => 'Tras desacato',
                    default      => '',
                };
                $textoResultadoSegunda = match($caso->resultado_fallo_segunda_instancia ?? '') {
                    'confirma' => 'Confirma — caso cerrado',
                    'revoca'   => 'Revoca — aseguradora debe cumplir',
                    default    => '',
                };

                fputcsv($handle, [
                    $caso->numero_caso,
                    $caso->nombre_completo,
                    $caso->cedula,
                    $caso->telefono,
                    $caso->correo,
                    $caso->departamento,
                    $caso->ciudad,
                    $caso->direccion,
                    $caso->aseguradora,
                    $caso->junta_asignada,
                    $caso->estado,
                    $caso->texto_alerta,
                    $caso->color_alerta,
                    $caso->porcentaje_avance,
                    optional($caso->fecha_accidente)->format('Y-m-d'),
                    optional($caso->fecha_solicitud_aseguradora)->format('Y-m-d'),
                    // NUEVOS
                    $textoTipoResp,
                    optional($caso->fecha_respuesta_aseguradora)->format('Y-m-d'),
                    optional($caso->fecha_apelacion)->format('Y-m-d'),
                    // NUEVOS
                    $textoTipoTutela,
                    optional($caso->fecha_tutela)->format('Y-m-d'),
                    optional($caso->fecha_fallo_tutela)->format('Y-m-d'),
                    $caso->resultado_fallo_tutela,
                    // NUEVOS
                    optional($caso->fecha_cumplimiento_tutela)->format('Y-m-d'),
                    $textoTipoCumpl,
                    optional($caso->fecha_incidente_desacato)->format('Y-m-d'),
                    optional($caso->fecha_impugnacion)->format('Y-m-d'),
                    // NUEVOS
                    optional($caso->fecha_fallo_segunda_instancia)->format('Y-m-d'),
                    $textoResultadoSegunda,
                    optional($caso->fecha_pago_honorarios)->format('Y-m-d'),
                    $caso->alta_ortopedia ? 'Sí' : 'No',
                    optional($caso->fecha_alta_ortopedia)->format('Y-m-d'),
                    $caso->furpen_completo ? 'Sí' : 'No',
                    optional($caso->fecha_furpen_recibido)->format('Y-m-d'),
                    optional($caso->fecha_envio_junta)->format('Y-m-d'),
                    optional($caso->fecha_dictamen_junta)->format('Y-m-d'),
                    optional($caso->fecha_reclamacion_final)->format('Y-m-d'),
                    optional($caso->fecha_pago_final)->format('Y-m-d'),
                    optional($caso->fecha_prescripcion)->format('Y-m-d'),
                    $caso->diasParaPrescripcion(),
                    $caso->porcentaje_pcl,
                    $caso->smldv_aplicados,
                    $caso->smldv_anio_accidente,
                    $caso->valor_estimado,
                    $caso->valor_reclamado,
                    $caso->valor_pagado,
                    $caso->porcentaje_honorarios,
                    $caso->ganancia_equipo,
                    $caso->valor_neto_cliente,
                    $caso->tiene_poder ? 'Sí' : 'No',
                    optional($caso->fecha_entrega_poder)->format('Y-m-d'),
                    optional($caso->fecha_poder_firmado)->format('Y-m-d'),
                    $caso->tiene_contrato ? 'Sí' : 'No',
                    optional($caso->fecha_entrega_contrato)->format('Y-m-d'),
                    optional($caso->fecha_contrato_firmado)->format('Y-m-d'),
                    $caso->observacion_alta_ortopedia,
                    $caso->observacion_furpen,
                    $caso->observacion_reclamacion,
                    $caso->observacion_pago,
                    $caso->observaciones,
                ], ';');
            }

            // ── Bitácora ──────────────────────────────────────────────────────
            fputcsv($handle, [], ';');
            fputcsv($handle, ['BITÁCORA RECIENTE'], ';');
            fputcsv($handle, ['Fecha', 'Caso', 'Movimiento', 'Descripción'], ';');

            foreach ($bitacoras as $bitacora) {
                fputcsv($handle, [
                    $bitacora->fecha_evento ?: optional($bitacora->created_at)->format('Y-m-d H:i:s'),
                    optional($bitacora->caso)->numero_caso,
                    $bitacora->titulo,
                    $bitacora->descripcion,
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');

        return $response;
    }

    // =========================================================================
    // EXPORTAR PDF
    // =========================================================================

    public function exportarPdf()
    {
        $casos     = Caso::orderByDesc('id')->get();
        $bitacoras = Bitacora::with('caso')
            ->orderByDesc('fecha_evento')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        // ── Contadores ────────────────────────────────────────────────────────
        $totalCasos                  = $casos->count();
        $casosPagados                = $casos->filter(fn ($c) => $c->estaPagado())->count();
        $casosActivos                = $casos->filter(fn ($c) => !$c->estaPagado() && $c->estado !== 'Cerrado')->count();
        $casosTutela                 = $casos->filter(fn ($c) => !empty($c->fecha_tutela))->count();
        $casosListosReclamar         = $casos->filter(fn ($c) => $c->requiereCobroAseguradora())->count();
        $casosListosApelar           = $casos->filter(fn ($c) => $c->requiereApelacion())->count();
        $casosPendientesHonorarios   = $casos->filter(fn ($c) => $c->requierePagoHonorariosJunta())->count();
        $casosListosSolicitudJunta   = $casos->filter(fn ($c) => $c->requiereSolicitudJunta())->count();
        $casosQuejaNoPago            = $casos->filter(fn ($c) => $c->requiereQuejaNoPago())->count();
        // NUEVOS
        $casosCumplimientoTutela     = $casos->filter(fn ($c) => $c->requiereCumplimientoTutela())->count();
        $casosDesacatoPendiente      = $casos->filter(fn ($c) => $c->requiereIncidenteDesacato())->count();
        $casosSegundaInstancia       = $casos->filter(fn ($c) => $c->requiereSegundaInstancia())->count();
        $casosCerradosSegunda        = $casos->filter(fn ($c) => $c->casoCerradoSegundaInstancia())->count();
        $casosCumplimientoSegunda    = $casos->filter(fn ($c) => $c->requiereCumplimientoSegundaInstancia())->count();

        // ── Financieros ───────────────────────────────────────────────────────
        $totalEstimado       = (float) $casos->sum('valor_estimado');
        $totalReclamado      = (float) $casos->sum('valor_reclamado');
        $totalRecuperado     = (float) $casos->sum('valor_pagado');
        $totalGananciaEquipo = (float) $casos->sum('ganancia_equipo');
        $totalNetoClientes   = (float) $casos->sum('valor_neto_cliente');
        $saldoPendiente      = $totalEstimado - $totalRecuperado;
        $promedioGananciaEquipo = $casosPagados > 0 ? ($totalGananciaEquipo / $casosPagados) : 0;

        // ── Colecciones de alerta ─────────────────────────────────────────────
        $alertasSinRespuesta         = $casos->filter(fn ($c) => $c->requiereRespuestaAseguradora());
        $alertasTutela               = $casos->filter(fn ($c) => $c->requiereTutela());
        $alertasApelarDictamen       = $casos->filter(fn ($c) => $c->requiereApelacion());
        $alertasHonorariosJunta      = $casos->filter(fn ($c) => $c->requierePagoHonorariosJunta());
        $alertasSolicitudJunta       = $casos->filter(fn ($c) => $c->requiereSolicitudJunta());
        $alertasReclamacion          = $casos->filter(fn ($c) => $c->requiereCobroAseguradora());
        $alertasPagoPendiente        = $casos->filter(fn ($c) => $c->requierePagoPendiente());
        $alertasSeguimientoTutela    = $casos->filter(fn ($c) => $c->requiereSeguimientoTutela());
        $alertasQuejaNoPago          = $casos->filter(fn ($c) => $c->requiereQuejaNoPago());
        $alertasDesacato             = $casos->filter(fn ($c) => $c->requiereIncidenteDesacato());
        $alertasImpugnacion          = $casos->filter(fn ($c) => $c->requiereImpugnacion());
        // NUEVAS
        $alertasCumplimientoTutela   = $casos->filter(fn ($c) => $c->requiereCumplimientoTutela());
        $alertasSegundaInstancia     = $casos->filter(fn ($c) => $c->requiereSegundaInstancia());
        $alertasCumplimientoSegunda  = $casos->filter(fn ($c) => $c->requiereCumplimientoSegundaInstancia());

        $alertasEsperandoFalloTutela = $casos->filter(function ($caso) {
            return !empty($caso->fecha_tutela)
                && !$caso->requiereSeguimientoTutela()
                && !$caso->estaPagado()
                && empty($caso->fecha_fallo_tutela);
        });

        // ── Distribuciones (NUEVAS) ───────────────────────────────────────────
        $distribucionRespuestas = [
            'emitio_dictamen' => $casos->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'emitio_dictamen')->count(),
            'nego'            => $casos->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'nego')->count(),
            'no_respondio'    => $casos->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'no_respondio')->count(),
            'sin_respuesta'   => $casos->filter(fn ($c) => !$c->tipo_respuesta_aseguradora && $c->fecha_solicitud_aseguradora)->count(),
        ];
        $distribucionTutelas = [
            'calificacion'   => $casos->filter(fn ($c) => $c->tipo_tutela === 'tutela_calificacion')->count(),
            'debido_proceso' => $casos->filter(fn ($c) => $c->tipo_tutela === 'tutela_debido_proceso')->count(),
        ];

        // ── Panel de vencimientos ─────────────────────────────────────────────
        $vencimientos = collect();

        foreach ($casos as $caso) {
            if ($caso->estaPagado() || $caso->casoCerradoSegundaInstancia()) continue;

            if (!empty($caso->fecha_solicitud_aseguradora) && empty($caso->tipo_respuesta_aseguradora)) {
                $dias = Carbon::parse($caso->fecha_solicitud_aseguradora)->diffInDays(Carbon::today());
                if ($dias >= 30) {
                    $vencimientos->push(['prioridad'=>'Crítico','color'=>'red','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>'Sin respuesta aseguradora','dias'=>$dias,'fecha_base'=>optional($caso->fecha_solicitud_aseguradora)->format('Y-m-d')]);
                }
            }

            if ($caso->tipo_respuesta_aseguradora === 'emitio_dictamen' && !empty($caso->fecha_respuesta_aseguradora) && empty($caso->fecha_apelacion)) {
                $dias = Carbon::parse($caso->fecha_respuesta_aseguradora)->diffInDays(Carbon::today());
                if ($dias >= 10) {
                    $vencimientos->push(['prioridad'=>'Urgente','color'=>'orange','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>'Apelación pendiente','dias'=>$dias,'fecha_base'=>optional($caso->fecha_respuesta_aseguradora)->format('Y-m-d')]);
                }
            }

            if (!empty($caso->fecha_apelacion) && empty($caso->fecha_pago_honorarios)) {
                $dias = Carbon::parse($caso->fecha_apelacion)->diffInDays(Carbon::today());
                if ($dias >= 10) {
                    $vencimientos->push(['prioridad'=>'Urgente','color'=>'orange','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>'Pago honorarios junta pendiente','dias'=>$dias,'fecha_base'=>optional($caso->fecha_apelacion)->format('Y-m-d')]);
                }
            }

            if (!empty($caso->fecha_fallo_tutela) && $caso->resultado_fallo_tutela === 'concedido' && empty($caso->fecha_cumplimiento_tutela) && empty($caso->fecha_incidente_desacato) && empty($caso->fecha_pago_honorarios)) {
                $dias = Carbon::parse($caso->fecha_fallo_tutela)->diffInDays(Carbon::today());
                if ($dias >= 5) {
                    $vencimientos->push(['prioridad'=>$dias >= 14 ? 'Crítico':'Urgente','color'=>$dias >= 14 ? 'red':'orange','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>$dias >= 14 ? 'Desacato — no cumplieron':'Esperando cumplimiento fallo tutela','dias'=>$dias,'fecha_base'=>optional($caso->fecha_fallo_tutela)->format('Y-m-d')]);
                }
            }

            if (!empty($caso->fecha_impugnacion) && empty($caso->fecha_fallo_segunda_instancia)) {
                $dias = Carbon::parse($caso->fecha_impugnacion)->diffInDays(Carbon::today());
                if ($dias >= 20) {
                    $vencimientos->push(['prioridad'=>'Urgente','color'=>'orange','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>'Segunda instancia pendiente','dias'=>$dias,'fecha_base'=>optional($caso->fecha_impugnacion)->format('Y-m-d')]);
                }
            }

            if (!empty($caso->fecha_fallo_segunda_instancia) && $caso->resultado_fallo_segunda_instancia === 'revoca' && empty($caso->fecha_cumplimiento_tutela) && empty($caso->fecha_pago_honorarios)) {
                $dias = Carbon::parse($caso->fecha_fallo_segunda_instancia)->diffInDays(Carbon::today());
                if ($dias >= 5) {
                    $vencimientos->push(['prioridad'=>'Crítico','color'=>'red','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>'2ª instancia revocó — aseguradora debe cumplir','dias'=>$dias,'fecha_base'=>optional($caso->fecha_fallo_segunda_instancia)->format('Y-m-d')]);
                }
            }

            if (!empty($caso->fecha_pago_honorarios) && empty($caso->fecha_envio_junta)) {
                $dias = Carbon::parse($caso->fecha_pago_honorarios)->diffInDays(Carbon::today());
                if ($dias >= 20) {
                    $vencimientos->push(['prioridad'=>'Seguimiento','color'=>'blue','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>'Solicitud a junta pendiente','dias'=>$dias,'fecha_base'=>optional($caso->fecha_pago_honorarios)->format('Y-m-d')]);
                }
            }

            if (!empty($caso->fecha_reclamacion_final) && empty($caso->fecha_pago_final)) {
                $dias = Carbon::parse($caso->fecha_reclamacion_final)->diffInDays(Carbon::today());
                if ($dias >= 15) {
                    $vencimientos->push(['prioridad'=>'Crítico','color'=>'red','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>'Pago final atrasado','dias'=>$dias,'fecha_base'=>optional($caso->fecha_reclamacion_final)->format('Y-m-d')]);
                }
            }

            if (!empty($caso->fecha_tutela) && empty($caso->fecha_fallo_tutela) && empty($caso->fecha_pago_final)) {
                $dias = Carbon::parse($caso->fecha_tutela)->diffInDays(Carbon::today());
                if ($dias >= 5) {
                    $vencimientos->push(['prioridad'=>'Urgente','color'=>'orange','caso_id'=>$caso->id,'numero_caso'=>$caso->numero_caso,'victima'=>$caso->nombre_completo,'aseguradora'=>$caso->aseguradora,'evento'=>'Tutela en seguimiento','dias'=>$dias,'fecha_base'=>optional($caso->fecha_tutela)->format('Y-m-d')]);
                }
            }
        }

        $vencimientos                 = $vencimientos->sortByDesc('dias')->values();
        $casosCriticos                = $vencimientos->where('color', 'red')->count();
        $casosUrgentes                = $vencimientos->where('color', 'orange')->count();
        $pagosAtrasados               = $vencimientos->where('evento', 'Pago final atrasado')->count();
        $tutelasPendientesSeguimiento = $vencimientos->where('evento', 'Tutela en seguimiento')->count();

        // ── Agrupaciones DB ───────────────────────────────────────────────────
        $casosPorAseguradora = Caso::select('aseguradora', DB::raw('count(*) as total'), DB::raw('coalesce(sum(valor_estimado),0) as valor_estimado_total'), DB::raw('coalesce(sum(valor_pagado),0) as valor_pagado_total'), DB::raw('coalesce(sum(ganancia_equipo),0) as ganancia_equipo_total'), DB::raw('coalesce(sum(valor_neto_cliente),0) as valor_neto_cliente_total'))->groupBy('aseguradora')->orderByDesc('valor_pagado_total')->get();

        $porAseguradora = Caso::selectRaw('aseguradora, COUNT(*) as total_casos, COALESCE(SUM(valor_pagado),0) as total_pagado, COALESCE(SUM(ganancia_equipo),0) as total_equipo')->groupBy('aseguradora')->orderByDesc('total_pagado')->get();

        $casosPorEstado = Caso::select('estado', DB::raw('count(*) as total'))->groupBy('estado')->orderByDesc('total')->get();

        // ── Análisis estratégico ──────────────────────────────────────────────
        $aseguradorasEstrategicas = $casos
            ->groupBy(fn ($c) => $c->aseguradora ?: 'Sin aseguradora')
            ->map(function ($items, $aseguradora) {
                $totalCasosA    = $items->count();
                $pagadosA       = $items->filter(fn ($c) => $c->estaPagado())->count();
                $tutelaA        = $items->filter(fn ($c) => !empty($c->fecha_tutela))->count();
                $apelA          = $items->filter(fn ($c) => !empty($c->fecha_apelacion))->count();
                $negoA          = $items->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'nego')->count();
                $nrA            = $items->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'no_respondio')->count();
                $dictamenA      = $items->filter(fn ($c) => $c->tipo_respuesta_aseguradora === 'emitio_dictamen')->count();
                $segundaRevA    = $items->filter(fn ($c) => $c->resultado_fallo_segunda_instancia === 'revoca')->count();
                $segundaConA    = $items->filter(fn ($c) => $c->resultado_fallo_segunda_instancia === 'confirma')->count();
                $totalPagado    = (float) $items->sum(fn ($c) => (float) ($c->valor_pagado ?? 0));
                $totalGanancia  = (float) $items->sum(fn ($c) => (float) ($c->ganancia_equipo ?? 0));
                $promedio       = $pagadosA > 0 ? $totalPagado / $pagadosA : 0;
                $tiemposPago    = $items->filter(fn ($c) => !empty($c->fecha_reclamacion_final) && !empty($c->fecha_pago_final))->map(fn ($c) => Carbon::parse($c->fecha_reclamacion_final)->diffInDays(Carbon::parse($c->fecha_pago_final)));
                $tiemposResp    = $items->filter(fn ($c) => !empty($c->fecha_solicitud_aseguradora) && !empty($c->fecha_respuesta_aseguradora))->map(fn ($c) => Carbon::parse($c->fecha_solicitud_aseguradora)->diffInDays(Carbon::parse($c->fecha_respuesta_aseguradora)));

                return [
                    'aseguradora'                    => $aseguradora,
                    'total_casos'                    => $totalCasosA,
                    'casos_pagados'                  => $pagadosA,
                    'casos_tutela'                   => $tutelaA,
                    'casos_apelacion'                => $apelA,
                    'casos_nego'                     => $negoA,
                    'casos_no_respondio'             => $nrA,
                    'casos_emitio_dictamen'          => $dictamenA,
                    'casos_segunda_revoca'           => $segundaRevA,
                    'casos_segunda_confirma'         => $segundaConA,
                    'total_pagado'                   => $totalPagado,
                    'total_ganancia'                 => $totalGanancia,
                    'promedio_pagado_por_caso'       => $promedio,
                    'tasa_pago'                      => $totalCasosA > 0 ? round(($pagadosA / $totalCasosA) * 100, 1) : 0,
                    'tasa_tutela'                    => $totalCasosA > 0 ? round(($tutelaA   / $totalCasosA) * 100, 1) : 0,
                    'tasa_apelacion'                 => $totalCasosA > 0 ? round(($apelA     / $totalCasosA) * 100, 1) : 0,
                    'tiempo_promedio_pago_dias'      => $tiemposPago->count() > 0 ? round($tiemposPago->avg(), 1) : 0,
                    'tiempo_promedio_respuesta_dias' => $tiemposResp->count() > 0 ? round($tiemposResp->avg(), 1) : 0,
                ];
            })
            ->sortByDesc('total_pagado')
            ->values();

        $topAseguradoraPagos      = $aseguradorasEstrategicas->sortByDesc('total_pagado')->first();
        $aseguradoraMayorTutela   = $aseguradorasEstrategicas->sortByDesc('tasa_tutela')->first();
        $aseguradoraMasLentaPago  = $aseguradorasEstrategicas->sortByDesc('tiempo_promedio_pago_dias')->first();
        $aseguradoraMasNegaciones = $aseguradorasEstrategicas->sortByDesc('casos_nego')->first();

        // ── Generar PDF ───────────────────────────────────────────────────────
        $pdf = Pdf::loadView('reportes.dashboard_pdf', compact(
            'casos', 'bitacoras',
            'totalCasos', 'casosPagados', 'casosActivos', 'casosTutela',
            'casosListosReclamar', 'casosListosApelar', 'casosPendientesHonorarios',
            'casosListosSolicitudJunta', 'casosQuejaNoPago',
            // NUEVOS contadores
            'casosCumplimientoTutela', 'casosDesacatoPendiente', 'casosSegundaInstancia',
            'casosCerradosSegunda', 'casosCumplimientoSegunda',
            // Financieros
            'totalEstimado', 'totalReclamado', 'totalRecuperado',
            'totalGananciaEquipo', 'totalNetoClientes', 'saldoPendiente', 'promedioGananciaEquipo',
            // Alertas
            'alertasSinRespuesta', 'alertasTutela', 'alertasApelarDictamen',
            'alertasHonorariosJunta', 'alertasSolicitudJunta', 'alertasReclamacion',
            'alertasPagoPendiente', 'alertasEsperandoFalloTutela', 'alertasSeguimientoTutela',
            'alertasQuejaNoPago', 'alertasDesacato', 'alertasImpugnacion',
            // NUEVAS alertas
            'alertasCumplimientoTutela', 'alertasSegundaInstancia', 'alertasCumplimientoSegunda',
            // Vencimientos
            'casosCriticos', 'casosUrgentes', 'pagosAtrasados', 'tutelasPendientesSeguimiento', 'vencimientos',
            // Tablas
            'casosPorAseguradora', 'porAseguradora', 'casosPorEstado',
            // Aseguradoras
            'aseguradorasEstrategicas', 'topAseguradoraPagos',
            'aseguradoraMayorTutela', 'aseguradoraMasLentaPago', 'aseguradoraMasNegaciones',
            // NUEVAS distribuciones
            'distribucionRespuestas', 'distribucionTutelas'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('dashboard_indemni_soat_' . now()->format('Ymd_His') . '.pdf');
    }
}