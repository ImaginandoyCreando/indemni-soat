<?php

namespace App\Http\Controllers;

use App\Models\Caso;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use App\Services\SoatIndemnizacionService;
use App\Services\NotificacionService;

class CasoController extends Controller
{
    // =========================================================================
    // INDEX / CRUD BÁSICO
    // =========================================================================

    public function index(Request $request)
    {
        $sort      = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');

        $columnasPermitidas = [
            'id', 'numero_caso', 'nombres', 'apellidos',
            'cedula', 'aseguradora', 'estado', 'junta_asignada',
        ];

        if (!in_array($sort, $columnasPermitidas))  $sort      = 'id';
        if (!in_array($direction, ['asc', 'desc'])) $direction = 'desc';

        $query = Caso::query();

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres',     'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('cedula',    'like', "%{$buscar}%")
                  ->orWhere('numero_caso', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('aseguradora'))    $query->where('aseguradora', $request->aseguradora);
        if ($request->filled('estado'))         $query->where('estado', $request->estado);
        if ($request->filled('alerta'))         $query->filtrarAlerta($request->alerta);
        if ($request->filled('fecha_desde'))    $query->whereDate('created_at', '>=', $request->fecha_desde);
        if ($request->filled('fecha_hasta'))    $query->whereDate('created_at', '<=', $request->fecha_hasta);
        if ($request->filled('tiene_poder'))    $query->where('tiene_poder',    $request->tiene_poder    === '1');
        if ($request->filled('tiene_contrato')) $query->where('tiene_contrato', $request->tiene_contrato === '1');
        if ($request->filled('alta_ortopedia')) $query->where('alta_ortopedia', $request->alta_ortopedia === '1');
        if ($request->filled('furpen_completo')) $query->where('furpen_completo', $request->furpen_completo === '1');

        $casos = $query->orderBy($sort, $direction)->paginate(10)->withQueryString();

        $aseguradoras = Caso::query()
            ->whereNotNull('aseguradora')->where('aseguradora', '!=', '')
            ->select('aseguradora')->distinct()->orderBy('aseguradora')->pluck('aseguradora');

        $estados = Caso::query()
            ->whereNotNull('estado')->where('estado', '!=', '')
            ->select('estado')->distinct()->orderBy('estado')->pluck('estado');

        $alertasDisponibles = collect([
            ['valor' => 'documentacion_inicial',         'texto' => 'Falta poder / contrato'],
            ['valor' => 'poder_pendiente',               'texto' => 'Poder pendiente'],
            ['valor' => 'contrato_pendiente',            'texto' => 'Contrato pendiente'],
            ['valor' => 'sin_respuesta',                 'texto' => 'Sin respuesta de aseguradora'],
            ['valor' => 'apelar_dictamen',               'texto' => 'Apelar dictamen'],
            ['valor' => 'tutela',                        'texto' => 'Presentar tutela'],
            ['valor' => 'seguimiento_tutela',            'texto' => 'Seguimiento tutela'],
            ['valor' => 'cumplimiento_tutela',           'texto' => 'Esperando cumplimiento tutela'],
            ['valor' => 'desacato',                      'texto' => 'Incidente de desacato'],
            ['valor' => 'impugnacion',                   'texto' => 'Impugnación pendiente'],
            ['valor' => 'segunda_instancia',             'texto' => 'Pendiente segunda instancia'],
            ['valor' => 'caso_cerrado',                  'texto' => 'Caso cerrado segunda instancia'],
            ['valor' => 'cumplimiento_segunda_instancia','texto' => 'Cumplimiento segunda instancia'],
            ['valor' => 'honorarios_junta',              'texto' => 'Pagar honorarios junta'],
            ['valor' => 'alta_ortopedia_pendiente',      'texto' => 'Alta ortopedia pendiente'],
            ['valor' => 'solicitud_junta',               'texto' => 'Solicitar a junta'],
            ['valor' => 'furpen_pendiente',              'texto' => 'FURPEN pendiente'],
            ['valor' => 'reclamacion',                   'texto' => 'Cobrar a aseguradora'],
            ['valor' => 'pago_pendiente',                'texto' => 'Pago pendiente'],
            ['valor' => 'queja',                         'texto' => 'Queja por no pago'],
            ['valor' => 'prescripcion_critica',          'texto' => 'Prescripción próxima'],
            ['valor' => 'prescrito',                     'texto' => 'Caso prescrito'],
            ['valor' => 'pagado',                        'texto' => 'Pagado'],
            ['valor' => 'normal',                        'texto' => 'Normal'],
        ]);

        return view('casos.index', compact(
            'casos', 'aseguradoras', 'estados',
            'sort', 'direction', 'alertasDisponibles'
        ));
    }

    public function create()
    {
        return view('casos.create', [
            'aseguradoras' => $this->getAseguradoras(),
            'juntas'       => $this->getJuntas(),
            'estados'      => $this->getEstados(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombres'               => 'required|string|max:255',
            'apellidos'             => 'required|string|max:255',
            'cedula'                => 'required|string|max:30',
            'telefono'              => 'nullable|string|max:30',
            'correo'                => 'nullable|email|max:255',
            'departamento'          => 'nullable|string|max:100',
            'ciudad'                => 'nullable|string|max:100',
            'direccion'             => 'nullable|string|max:255',
            'aseguradora'           => 'required|string|max:150',
            'junta_asignada'        => 'nullable|string|max:150',
            'estado'                => 'required|string|max:100',
            'fecha_accidente'       => 'nullable|date',
            'observaciones'         => 'nullable|string',
            'tiene_poder'           => 'nullable|boolean',
            'fecha_entrega_poder'   => 'nullable|date',
            'fecha_poder_firmado'   => 'nullable|date',
            'tiene_contrato'        => 'nullable|boolean',
            'fecha_entrega_contrato'=> 'nullable|date',
            'fecha_contrato_firmado'=> 'nullable|date',
        ]);

        $ultimoId   = Caso::max('id') ?? 0;
        $numeroCaso = 'ISOAT-' . date('Y') . '-' . str_pad($ultimoId + 1, 5, '0', STR_PAD_LEFT);

        $caso = Caso::create([
            'numero_caso'            => $numeroCaso,
            'nombres'                => $request->nombres,
            'apellidos'              => $request->apellidos,
            'cedula'                 => $request->cedula,
            'telefono'               => $request->telefono,
            'correo'                 => $request->correo,
            'departamento'           => $request->departamento,
            'ciudad'                 => $request->ciudad,
            'direccion'              => $request->direccion,
            'fecha_accidente'        => $request->fecha_accidente,
            'aseguradora'            => $request->aseguradora,
            'junta_asignada'         => $request->junta_asignada,
            'estado'                 => $request->estado,
            'observaciones'          => $request->observaciones,
            'tiene_poder'            => $request->boolean('tiene_poder'),
            'fecha_entrega_poder'    => $request->fecha_entrega_poder,
            'fecha_poder_firmado'    => $request->fecha_poder_firmado,
            'tiene_contrato'         => $request->boolean('tiene_contrato'),
            'fecha_entrega_contrato' => $request->fecha_entrega_contrato,
            'fecha_contrato_firmado' => $request->fecha_contrato_firmado,
        ]);

        $this->registrarBitacora($caso->id, 'Caso creado',
            'Se creó el caso ' . $caso->numero_caso . ' para ' . $caso->nombre_completo . '.');

        if ($caso->tiene_poder) {
            $this->registrarBitacora($caso->id, 'Poder registrado',
                'Se registró que la víctima entregó poder firmado.',
                $caso->fecha_poder_firmado ?: now()->toDateString());
        }

        if ($caso->tiene_contrato) {
            $this->registrarBitacora($caso->id, 'Contrato registrado',
                'Se registró que la víctima entregó contrato firmado.',
                $caso->fecha_contrato_firmado ?: now()->toDateString());
        }

        return redirect()->route('casos.index')->with('success', 'Caso creado correctamente.');
    }

    public function show(Caso $caso)
    {
        return view('casos.show', compact('caso'));
    }

    public function edit(Caso $caso)
    {
        return view('casos.edit', [
            'caso'         => $caso,
            'aseguradoras' => $this->getAseguradoras(),
            'juntas'       => $this->getJuntas(),
            'estados'      => $this->getEstados(),
        ]);
    }

    public function update(Request $request, Caso $caso)
    {
        $request->validate([
            'nombres'                           => 'required|string|max:255',
            'apellidos'                         => 'required|string|max:255',
            'cedula'                            => 'required|string|max:30',
            'telefono'                          => 'nullable|string|max:30',
            'correo'                            => 'nullable|email|max:255',
            'departamento'                      => 'nullable|string|max:100',
            'ciudad'                            => 'nullable|string|max:100',
            'direccion'                         => 'nullable|string|max:255',
            'aseguradora'                       => 'required|string|max:150',
            'junta_asignada'                    => 'nullable|string|max:150',
            'estado'                            => 'required|string|max:100',
            'fecha_accidente'                   => 'nullable|date',
            'fecha_solicitud_aseguradora'       => 'nullable|date',
            'fecha_respuesta_aseguradora'       => 'nullable|date',
            'tipo_respuesta_aseguradora'        => 'nullable|string|in:emitio_dictamen,nego,no_respondio',
            'fecha_apelacion'                   => 'nullable|date',
            'fecha_tutela'                      => 'nullable|date',
            'tipo_tutela'                       => 'nullable|string|in:tutela_calificacion,tutela_debido_proceso',
            'fecha_pago_honorarios'             => 'nullable|date',
            'fecha_envio_junta'                 => 'nullable|date',
            'fecha_dictamen_junta'              => 'nullable|date',
            'fecha_reclamacion_final'           => 'nullable|date',
            'fecha_pago_final'                  => 'nullable|date',
            'porcentaje_pcl'                    => 'nullable|numeric|min:0',
            'valor_reclamado'                   => 'nullable|numeric|min:0',
            'valor_pagado'                      => 'nullable|numeric|min:0',
            'porcentaje_honorarios'             => 'nullable|numeric|in:40,50',
            'observaciones'                     => 'nullable|string',
            'tiene_poder'                       => 'nullable|boolean',
            'fecha_entrega_poder'               => 'nullable|date',
            'fecha_poder_firmado'               => 'nullable|date',
            'tiene_contrato'                    => 'nullable|boolean',
            'fecha_entrega_contrato'            => 'nullable|date',
            'fecha_contrato_firmado'            => 'nullable|date',
            'alta_ortopedia'                    => 'nullable|boolean',
            'fecha_alta_ortopedia'              => 'nullable|date',
            'observacion_alta_ortopedia'        => 'nullable|string',
            'furpen_completo'                   => 'nullable|boolean',
            'fecha_furpen_recibido'             => 'nullable|date',
            'observacion_furpen'                => 'nullable|string',
            'fecha_fallo_tutela'                => 'nullable|date',
            'resultado_fallo_tutela'            => 'nullable|string|in:concedido,negado,parcial',
            'fecha_incidente_desacato'          => 'nullable|date',
            'fecha_cumplimiento_tutela'         => 'nullable|date',
            'tipo_cumplimiento_tutela'          => 'nullable|string|in:voluntario,desacato',
            'fecha_impugnacion'                 => 'nullable|date',
            'fecha_fallo_segunda_instancia'     => 'nullable|date',
            'resultado_fallo_segunda_instancia' => 'nullable|string|in:confirma,revoca',
        ]);

        $anterior = $caso->replicate();

        $servicio = new SoatIndemnizacionService();
        $calculo  = $servicio->calcular($request->fecha_accidente, $request->porcentaje_pcl);
        $finanzas = $this->calcularFinanzas($request->valor_pagado, $request->porcentaje_honorarios);

        $estadoFinal = $this->resolverEstadoAutomatico(
            $request->estado,
            $request->fecha_solicitud_aseguradora,
            $request->fecha_respuesta_aseguradora,
            $request->fecha_apelacion,
            $request->fecha_tutela,
            $request->fecha_pago_honorarios,
            $request->fecha_envio_junta,
            $request->fecha_dictamen_junta,
            $request->fecha_reclamacion_final,
            $request->fecha_pago_final,
            $request->fecha_fallo_tutela,
            $request->resultado_fallo_tutela,
            $request->fecha_incidente_desacato,
            $request->fecha_impugnacion,
            $request->boolean('alta_ortopedia'),
            $request->boolean('furpen_completo'),
            $request->tipo_respuesta_aseguradora,
            $request->tipo_tutela,
            $request->fecha_cumplimiento_tutela,
            $request->fecha_fallo_segunda_instancia,
            $request->resultado_fallo_segunda_instancia
        );

        $caso->update([
            'nombres'                           => $request->nombres,
            'apellidos'                         => $request->apellidos,
            'cedula'                            => $request->cedula,
            'telefono'                          => $request->telefono,
            'correo'                            => $request->correo,
            'departamento'                      => $request->departamento,
            'ciudad'                            => $request->ciudad,
            'direccion'                         => $request->direccion,
            'fecha_accidente'                   => $request->fecha_accidente,
            'aseguradora'                       => $request->aseguradora,
            'junta_asignada'                    => $request->junta_asignada,
            'estado'                            => $estadoFinal,
            'fecha_solicitud_aseguradora'       => $request->fecha_solicitud_aseguradora,
            'fecha_respuesta_aseguradora'       => $request->fecha_respuesta_aseguradora,
            'tipo_respuesta_aseguradora'        => $request->tipo_respuesta_aseguradora,
            'fecha_apelacion'                   => $request->fecha_apelacion,
            'fecha_tutela'                      => $request->fecha_tutela,
            'tipo_tutela'                       => $request->tipo_tutela,
            'fecha_pago_honorarios'             => $request->fecha_pago_honorarios,
            'fecha_envio_junta'                 => $request->fecha_envio_junta,
            'fecha_dictamen_junta'              => $request->fecha_dictamen_junta,
            'fecha_reclamacion_final'           => $request->fecha_reclamacion_final,
            'fecha_pago_final'                  => $request->fecha_pago_final,
            'porcentaje_pcl'                    => $request->porcentaje_pcl,
            'smldv_aplicados'                   => $calculo['smldv_aplicados'] ?? null,
            'smldv_anio_accidente'              => $calculo['smldv_anio_accidente'] ?? null,
            'valor_reclamado'                   => $request->valor_reclamado,
            'valor_pagado'                      => $request->valor_pagado,
            'porcentaje_honorarios'             => $request->porcentaje_honorarios,
            'ganancia_equipo'                   => $finanzas['ganancia_equipo'],
            'valor_neto_cliente'                => $finanzas['valor_neto_cliente'],
            'valor_estimado'                    => $calculo['valor_estimado'] ?? null,
            'observaciones'                     => $request->observaciones,
            'tiene_poder'                       => $request->boolean('tiene_poder'),
            'fecha_entrega_poder'               => $request->fecha_entrega_poder,
            'fecha_poder_firmado'               => $request->fecha_poder_firmado,
            'tiene_contrato'                    => $request->boolean('tiene_contrato'),
            'fecha_entrega_contrato'            => $request->fecha_entrega_contrato,
            'fecha_contrato_firmado'            => $request->fecha_contrato_firmado,
            'alta_ortopedia'                    => $request->boolean('alta_ortopedia'),
            'fecha_alta_ortopedia'              => $request->fecha_alta_ortopedia,
            'observacion_alta_ortopedia'        => $request->observacion_alta_ortopedia,
            'furpen_completo'                   => $request->boolean('furpen_completo'),
            'fecha_furpen_recibido'             => $request->fecha_furpen_recibido,
            'observacion_furpen'                => $request->observacion_furpen,
            'fecha_fallo_tutela'                => $request->fecha_fallo_tutela,
            'resultado_fallo_tutela'            => $request->resultado_fallo_tutela,
            'fecha_incidente_desacato'          => $request->fecha_incidente_desacato,
            'fecha_cumplimiento_tutela'         => $request->fecha_cumplimiento_tutela,
            'tipo_cumplimiento_tutela'          => $request->tipo_cumplimiento_tutela,
            'fecha_impugnacion'                 => $request->fecha_impugnacion,
            'fecha_fallo_segunda_instancia'     => $request->fecha_fallo_segunda_instancia,
            'resultado_fallo_segunda_instancia' => $request->resultado_fallo_segunda_instancia,
        ]);

        $eventos = [
            'fecha_solicitud_aseguradora'   => 'Se registró solicitud de calificación a aseguradora',
            'fecha_respuesta_aseguradora'   => 'Se registró respuesta o dictamen de aseguradora',
            'fecha_apelacion'               => 'Se registró apelación del dictamen de aseguradora',
            'fecha_tutela'                  => 'Se registró tutela',
            'fecha_pago_honorarios'         => 'Se registró pago de honorarios a junta',
            'fecha_envio_junta'             => 'Se registró solicitud o envío a junta',
            'fecha_dictamen_junta'          => 'Se registró dictamen de junta',
            'fecha_reclamacion_final'       => 'Se registró cobro a aseguradora',
            'fecha_pago_final'              => 'Se registró pago final',
            'fecha_fallo_tutela'            => 'Se registró fallo de tutela',
            'fecha_incidente_desacato'      => 'Se registró incidente de desacato',
            'fecha_cumplimiento_tutela'     => 'Se registró cumplimiento del fallo de tutela',
            'fecha_impugnacion'             => 'Se registró impugnación',
            'fecha_fallo_segunda_instancia' => 'Se registró fallo de segunda instancia',
            'fecha_alta_ortopedia'          => 'Se registró alta por ortopedia',
            'fecha_furpen_recibido'         => 'Se registró recepción de FURPEN',
            'fecha_poder_firmado'           => 'Se registró poder firmado',
            'fecha_contrato_firmado'        => 'Se registró contrato firmado',
        ];

        foreach ($eventos as $campo => $titulo) {
            if (empty($anterior->$campo) && !empty($caso->$campo)) {
                $this->registrarBitacora($caso->id, $titulo,
                    $titulo . ' con fecha: ' . $caso->$campo, $caso->$campo);
            }
        }

        foreach ([
            'tiene_poder'    => ['Cambio en poder',           'víctima ya tiene poder firmado.',      'víctima aún no tiene poder firmado.'],
            'tiene_contrato' => ['Cambio en contrato',        'víctima ya tiene contrato firmado.',   'víctima aún no tiene contrato firmado.'],
            'alta_ortopedia' => ['Cambio en alta ortopedia',  'víctima ya tiene alta por ortopedia.', 'víctima aún no tiene alta.'],
            'furpen_completo'=> ['Cambio en FURPEN',          'FURPEN marcado como completo.',        'FURPEN marcado como pendiente.'],
        ] as $campo => [$titulo, $textoTrue, $textoFalse]) {
            if ((bool) $anterior->$campo !== (bool) $caso->$campo) {
                $this->registrarBitacora($caso->id, $titulo,
                    'Se marcó que la ' . ($caso->$campo ? $textoTrue : $textoFalse));
            }
        }

        if ($anterior->resultado_fallo_tutela !== $caso->resultado_fallo_tutela && !empty($caso->resultado_fallo_tutela)) {
            $this->registrarBitacora($caso->id, 'Resultado fallo tutela',
                'Resultado del fallo de tutela actualizado a: ' . $caso->resultado_fallo_tutela . '.');
        }

        if ($anterior->resultado_fallo_segunda_instancia !== $caso->resultado_fallo_segunda_instancia
            && !empty($caso->resultado_fallo_segunda_instancia)) {
            $this->registrarBitacora($caso->id, 'Resultado segunda instancia',
                'Resultado de segunda instancia actualizado a: ' . $caso->resultado_fallo_segunda_instancia . '.');
        }

        if ($anterior->tipo_respuesta_aseguradora !== $caso->tipo_respuesta_aseguradora
            && !empty($caso->tipo_respuesta_aseguradora)) {
            $textos = [
                'emitio_dictamen' => 'emitió dictamen',
                'nego'            => 'negó la solicitud',
                'no_respondio'    => 'no respondió (confirmado)',
            ];
            $this->registrarBitacora($caso->id, 'Tipo de respuesta de aseguradora',
                'Se registró que la aseguradora ' . ($textos[$caso->tipo_respuesta_aseguradora] ?? $caso->tipo_respuesta_aseguradora) . '.');
        }

        if ($anterior->tipo_tutela !== $caso->tipo_tutela && !empty($caso->tipo_tutela)) {
            $textos = [
                'tutela_calificacion'   => 'tutela para calificación',
                'tutela_debido_proceso' => 'tutela por debido proceso',
            ];
            $this->registrarBitacora($caso->id, 'Tipo de tutela registrado',
                'Se clasificó la tutela como: ' . ($textos[$caso->tipo_tutela] ?? $caso->tipo_tutela) . '.');
        }

        if ($anterior->estado !== $caso->estado) {
            $this->registrarBitacora($caso->id, 'Cambio de estado',
                'El estado cambió de "' . ($anterior->estado ?? 'N/A') . '" a "' . ($caso->estado ?? 'N/A') . '".');
        }

        if ($anterior->porcentaje_pcl != $caso->porcentaje_pcl || $anterior->valor_estimado != $caso->valor_estimado) {
            $this->registrarBitacora($caso->id, 'Actualización de cálculo legal',
                'PCL: ' . ($caso->porcentaje_pcl ?? 'N/A') . ', valor estimado: ' . ($caso->valor_estimado ?? 'N/A') . '.');
        }

        if ($anterior->valor_pagado != $caso->valor_pagado ||
            $anterior->porcentaje_honorarios != $caso->porcentaje_honorarios ||
            $anterior->ganancia_equipo != $caso->ganancia_equipo) {
            $this->registrarBitacora($caso->id, 'Actualización financiera',
                'Honorarios: ' . ($caso->porcentaje_honorarios ?? 'N/A') . '%, ganancia equipo: $' .
                number_format($caso->ganancia_equipo ?? 0, 0, ',', '.') . ', neto cliente: $' .
                number_format($caso->valor_neto_cliente ?? 0, 0, ',', '.') . '.');
        }

        $mensaje = 'Caso actualizado correctamente.';
        if (!empty($calculo['mensaje'])) $mensaje .= ' ' . $calculo['mensaje'];

        return redirect()->route('casos.show', $caso)->with('success', $mensaje);
    }

    public function destroy(Caso $caso)
    {
        $caso->delete();
        return redirect()->route('casos.index')->with('success', 'Caso eliminado correctamente.');
    }

    // =========================================================================
    // ACCIONES RÁPIDAS — FLUJO JURÍDICO
    // =========================================================================

    public function marcarSolicitudAseguradora(Request $request, Caso $caso)
    {
        $request->validate(['fecha_solicitud_aseguradora' => 'required|date']);

        if (empty($caso->fecha_solicitud_aseguradora)) {
            $fecha = $request->fecha_solicitud_aseguradora;
            $caso->update([
                'fecha_solicitud_aseguradora' => $fecha,
                'estado' => $this->resolverEstadoDesde($caso, ['fecha_solicitud_aseguradora' => $fecha]),
            ]);
            $this->registrarBitacora($caso->id,
                'Se registró solicitud de calificación a aseguradora',
                'Se registró solicitud desde acciones rápidas.', $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Solicitud enviada a aseguradora',
                "Fecha de solicitud: {$fecha}. Pendiente respuesta en máximo 30 días.",
                'info'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Solicitud a aseguradora registrada.');
    }

    public function marcarRespuestaAseguradora(Request $request, Caso $caso)
    {
        $request->validate([
            'tipo_respuesta_aseguradora'  => 'required|string|in:emitio_dictamen,nego,no_respondio',
            'fecha_respuesta_aseguradora' => 'required_unless:tipo_respuesta_aseguradora,no_respondio|nullable|date',
        ]);

        if (!empty($caso->tipo_respuesta_aseguradora)) {
            return redirect()->route('casos.index')->with('info', 'La respuesta de la aseguradora ya fue registrada.');
        }

        $tipo  = $request->tipo_respuesta_aseguradora;
        $fecha = ($tipo !== 'no_respondio') ? $request->fecha_respuesta_aseguradora : null;

        $datos = [
            'tipo_respuesta_aseguradora'  => $tipo,
            'fecha_respuesta_aseguradora' => $fecha,
        ];
        $datos['estado'] = $this->resolverEstadoDesde($caso, $datos);
        $caso->update($datos);

        $textos = [
            'emitio_dictamen' => 'emitió dictamen — flujo de apelación iniciado.',
            'nego'            => 'negó la solicitud — proceder con tutela para calificación.',
            'no_respondio'    => 'no respondió en el plazo — proceder con tutela para calificación.',
        ];

        $this->registrarBitacora($caso->id, 'Respuesta de aseguradora registrada',
            'Se registró que la aseguradora ' . ($textos[$tipo] ?? $tipo),
            $fecha ?? now()->toDateString());

        $nivelResp  = ($tipo === 'nego' || $tipo === 'no_respondio') ? 'urgente' : 'info';
        $textoNotif = match($tipo) {
            'emitio_dictamen' => 'Aseguradora emitió dictamen — procede apelación',
            'nego'            => 'Aseguradora NEGÓ la solicitud — presentar tutela para calificación',
            'no_respondio'    => 'Aseguradora NO respondió (1 mes) — presentar tutela para calificación',
            default           => 'Respuesta de aseguradora registrada',
        };
        NotificacionService::enviarAlertaFlujo($caso, $textoNotif, '', $nivelResp);

        return redirect()->route('casos.index')->with('success', 'Respuesta de aseguradora registrada.');
    }

    public function marcarApelacion(Request $request, Caso $caso)
    {
        $request->validate(['fecha_apelacion' => 'required|date']);

        if (empty($caso->fecha_apelacion)) {
            $fecha = $request->fecha_apelacion;
            $caso->update([
                'fecha_apelacion' => $fecha,
                'estado'          => $this->resolverEstadoDesde($caso, ['fecha_apelacion' => $fecha]),
            ]);
            $this->registrarBitacora($caso->id, 'Se registró apelación del dictamen',
                'Se marcó apelación desde acciones rápidas.', $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Apelación del dictamen registrada',
                "Fecha apelación: {$fecha}. Siguiente paso: pagar honorarios a la junta.",
                'info'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Apelación registrada.');
    }

    public function marcarTutela(Request $request, Caso $caso)
    {
        $request->validate([
            'fecha_tutela' => 'required|date',
            'tipo_tutela'  => 'required|string|in:tutela_calificacion,tutela_debido_proceso',
        ]);

        if (empty($caso->fecha_tutela)) {
            $fecha = $request->fecha_tutela;
            $tipo  = $request->tipo_tutela;

            $caso->update([
                'fecha_tutela' => $fecha,
                'tipo_tutela'  => $tipo,
                'estado'       => $this->resolverEstadoDesde($caso, [
                    'fecha_tutela' => $fecha,
                    'tipo_tutela'  => $tipo,
                ]),
            ]);

            $textos = [
                'tutela_calificacion'   => 'tutela para calificación',
                'tutela_debido_proceso' => 'tutela por debido proceso',
            ];

            $this->registrarBitacora($caso->id, 'Se registró tutela',
                'Se presentó ' . ($textos[$tipo] ?? $tipo) . ' desde acciones rápidas.', $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Tutela presentada — ' . ($textos[$tipo] ?? $tipo),
                "Fecha tutela: {$fecha}. Pendiente fallo del juez.",
                'urgente'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Tutela registrada.');
    }

    public function marcarFalloTutela(Request $request, Caso $caso)
    {
        $request->validate([
            'fecha_fallo_tutela'     => 'required|date',
            'resultado_fallo_tutela' => 'required|string|in:concedido,negado,parcial',
        ]);

        if (empty($caso->fecha_fallo_tutela)) {
            $fecha     = $request->fecha_fallo_tutela;
            $resultado = $request->resultado_fallo_tutela;

            $caso->update([
                'fecha_fallo_tutela'     => $fecha,
                'resultado_fallo_tutela' => $resultado,
                'estado'                 => $this->resolverEstadoDesde($caso, [
                    'fecha_fallo_tutela'     => $fecha,
                    'resultado_fallo_tutela' => $resultado,
                ]),
            ]);

            $this->registrarBitacora($caso->id, 'Se registró fallo de tutela',
                'Fallo de tutela registrado con resultado: ' . $resultado . '.', $fecha);

            $nivelFallo = $resultado === 'negado' ? 'critico' : 'urgente';
            $textoFallo = match($resultado) {
                'concedido' => 'Fallo de tutela CONCEDIDO — aseguradora tiene 14 días para cumplir',
                'negado'    => 'Fallo de tutela NEGADO — debe impugnarse',
                'parcial'   => 'Fallo de tutela PARCIAL — revisar siguiente acción',
                default     => 'Fallo de tutela registrado',
            };
            NotificacionService::enviarAlertaFlujo($caso, $textoFallo, '', $nivelFallo);
        }

        return redirect()->route('casos.index')->with('success', 'Fallo de tutela registrado.');
    }

    public function marcarCumplimientoTutela(Request $request, Caso $caso)
    {
        $request->validate([
            'fecha_cumplimiento_tutela' => 'required|date',
            'tipo_cumplimiento_tutela'  => 'required|string|in:voluntario,desacato',
        ]);

        if (!empty($caso->fecha_cumplimiento_tutela)) {
            return redirect()->route('casos.index')->with('info', 'El cumplimiento ya fue registrado.');
        }

        $fecha = $request->fecha_cumplimiento_tutela;
        $tipo  = $request->tipo_cumplimiento_tutela;

        $caso->update([
            'fecha_cumplimiento_tutela' => $fecha,
            'tipo_cumplimiento_tutela'  => $tipo,
            'estado'                    => $this->resolverEstadoDesde($caso, [
                'fecha_cumplimiento_tutela' => $fecha,
                'tipo_cumplimiento_tutela'  => $tipo,
            ]),
        ]);

        $textos = [
            'voluntario' => 'voluntariamente',
            'desacato'   => 'tras incidente de desacato',
        ];

        $this->registrarBitacora($caso->id, 'Se registró cumplimiento del fallo de tutela',
            'La aseguradora cumplió el fallo de tutela ' . ($textos[$tipo] ?? $tipo) .
            '. Proceder con el flujo correspondiente al tipo de tutela.', $fecha);

        NotificacionService::enviarAlertaFlujo(
            $caso,
            'Cumplimiento del fallo de tutela registrado',
            "Tipo: {$tipo}. Fecha: {$fecha}. " .
            ($caso->tipo_tutela === 'tutela_debido_proceso'
                ? 'Siguiente paso: registrar pago de honorarios.'
                : 'Siguiente paso: registrar dictamen de la aseguradora.'),
            'info'
        );

        $mensaje = 'Cumplimiento de tutela registrado. ';
        $mensaje .= ($caso->tipo_tutela === 'tutela_debido_proceso')
            ? 'Registre ahora el pago de honorarios.'
            : 'Registre ahora el dictamen de la aseguradora.';

        return redirect()->route('casos.index')->with('success', $mensaje);
    }

    public function marcarIncidenteDesacato(Request $request, Caso $caso)
    {
        $request->validate(['fecha_incidente_desacato' => 'required|date']);

        if (empty($caso->fecha_incidente_desacato)) {
            $fecha = $request->fecha_incidente_desacato;
            $caso->update([
                'fecha_incidente_desacato' => $fecha,
                'estado'                   => $this->resolverEstadoDesde($caso, ['fecha_incidente_desacato' => $fecha]),
            ]);
            $this->registrarBitacora($caso->id, 'Se registró incidente de desacato',
                'Se registró incidente de desacato desde acciones rápidas.', $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                '🚨 Incidente de desacato registrado',
                "La aseguradora no cumplió el fallo en el plazo legal. Fecha desacato: {$fecha}.",
                'critico'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Incidente de desacato registrado.');
    }

    public function marcarImpugnacion(Request $request, Caso $caso)
    {
        $request->validate(['fecha_impugnacion' => 'required|date']);

        if (empty($caso->fecha_impugnacion)) {
            $fecha = $request->fecha_impugnacion;
            $caso->update([
                'fecha_impugnacion' => $fecha,
                'estado'            => $this->resolverEstadoDesde($caso, ['fecha_impugnacion' => $fecha]),
            ]);
            $this->registrarBitacora($caso->id, 'Se registró impugnación',
                'Se registró impugnación desde acciones rápidas.', $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Impugnación registrada — pendiente segunda instancia',
                "Fecha impugnación: {$fecha}. Pendiente fallo de segunda instancia.",
                'urgente'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Impugnación registrada.');
    }

    public function marcarFalloSegundaInstancia(Request $request, Caso $caso)
    {
        $request->validate([
            'fecha_fallo_segunda_instancia'     => 'required|date',
            'resultado_fallo_segunda_instancia' => 'required|string|in:confirma,revoca',
        ]);

        if (!empty($caso->fecha_fallo_segunda_instancia)) {
            return redirect()->route('casos.index')->with('info', 'El fallo de segunda instancia ya fue registrado.');
        }

        $fecha     = $request->fecha_fallo_segunda_instancia;
        $resultado = $request->resultado_fallo_segunda_instancia;

        $caso->update([
            'fecha_fallo_segunda_instancia'     => $fecha,
            'resultado_fallo_segunda_instancia' => $resultado,
            'estado'                            => $this->resolverEstadoDesde($caso, [
                'fecha_fallo_segunda_instancia'     => $fecha,
                'resultado_fallo_segunda_instancia' => $resultado,
            ]),
        ]);

        $textoResultado = $resultado === 'confirma'
            ? 'CONFIRMA el fallo negado — el caso queda cerrado desfavorablemente.'
            : 'REVOCA el fallo negado — la aseguradora debe cumplir lo ordenado.';

        $this->registrarBitacora($caso->id, 'Se registró fallo de segunda instancia',
            'La segunda instancia ' . $textoResultado, $fecha);

        $nivelSegunda = $resultado === 'confirma' ? 'critico' : 'urgente';
        $textoSegunda = match($resultado) {
            'confirma' => 'Segunda instancia CONFIRMA — caso cerrado desfavorablemente',
            'revoca'   => 'Segunda instancia REVOCA — aseguradora debe cumplir lo ordenado',
            default    => 'Fallo de segunda instancia registrado',
        };
        NotificacionService::enviarAlertaFlujo($caso, $textoSegunda, '', $nivelSegunda);

        $mensaje = $resultado === 'confirma'
            ? 'Segunda instancia confirma — caso cerrado.'
            : 'Segunda instancia revoca — registre el cumplimiento de la aseguradora.';

        return redirect()->route('casos.index')->with('success', $mensaje);
    }

    public function marcarPagoHonorarios(Request $request, Caso $caso)
    {
        $request->validate(['fecha_pago_honorarios' => 'required|date']);

        if (empty($caso->fecha_pago_honorarios)) {
            $fecha = $request->fecha_pago_honorarios;
            $caso->update([
                'fecha_pago_honorarios' => $fecha,
                'estado'                => $this->resolverEstadoDesde($caso, ['fecha_pago_honorarios' => $fecha]),
            ]);
            $this->registrarBitacora($caso->id, 'Se registró pago de honorarios a junta',
                'Se marcó el pago de honorarios desde acciones rápidas.', $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Pago de honorarios a junta registrado',
                "Fecha pago honorarios: {$fecha}. Siguiente paso: solicitud a junta.",
                'info'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Pago de honorarios registrado.');
    }

    public function marcarAltaOrtopedia(Request $request, Caso $caso)
    {
        $request->validate([
            'fecha_alta_ortopedia'       => 'required|date',
            'observacion_alta_ortopedia' => 'nullable|string|max:1000',
        ]);

        if (!$caso->alta_ortopedia) {
            $fecha       = $request->fecha_alta_ortopedia;
            $observacion = $request->observacion_alta_ortopedia;

            $caso->update([
                'alta_ortopedia'             => true,
                'fecha_alta_ortopedia'       => $fecha,
                'observacion_alta_ortopedia' => $observacion,
                'estado'                     => $this->resolverEstadoDesde($caso, ['alta_ortopedia' => true]),
            ]);

            $desc = 'Se registró alta por ortopedia desde acciones rápidas.';
            if (!empty($observacion)) $desc .= ' Obs: ' . $observacion;
            $this->registrarBitacora($caso->id, 'Se registró alta por ortopedia', $desc, $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Alta por ortopedia registrada',
                "Fecha alta: {$fecha}." . ($observacion ? " Obs: {$observacion}" : ''),
                'info'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Alta por ortopedia registrada.');
    }

    public function marcarSolicitudJunta(Request $request, Caso $caso)
    {
        $request->validate(['fecha_envio_junta' => 'required|date']);

        if (empty($caso->fecha_envio_junta)) {
            $fecha = $request->fecha_envio_junta;
            $caso->update([
                'fecha_envio_junta' => $fecha,
                'estado'            => $this->resolverEstadoDesde($caso, ['fecha_envio_junta' => $fecha]),
            ]);
            $this->registrarBitacora($caso->id, 'Se registró solicitud a junta',
                'Se marcó solicitud a junta desde acciones rápidas.', $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Solicitud enviada a la junta',
                "Fecha envío: {$fecha}. Pendiente dictamen.",
                'info'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Solicitud a junta registrada.');
    }

    public function marcarDictamenJunta(Request $request, Caso $caso)
    {
        $request->validate(['fecha_dictamen_junta' => 'required|date']);

        if (empty($caso->fecha_dictamen_junta)) {
            $fecha = $request->fecha_dictamen_junta;
            $caso->update([
                'fecha_dictamen_junta' => $fecha,
                'estado'               => $this->resolverEstadoDesde($caso, ['fecha_dictamen_junta' => $fecha]),
            ]);
            $this->registrarBitacora($caso->id, 'Se registró dictamen de junta',
                'Se registró dictamen de junta desde acciones rápidas.', $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Dictamen de junta recibido',
                "Fecha dictamen: {$fecha}. Siguiente paso: completar FURPEN y reclamar.",
                'info'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Dictamen de junta registrado.');
    }

    public function marcarFurpen(Request $request, Caso $caso)
    {
        $request->validate([
            'fecha_furpen_recibido' => 'required|date',
            'observacion_furpen'    => 'nullable|string|max:1000',
        ]);

        if (!$caso->furpen_completo) {
            $fecha       = $request->fecha_furpen_recibido;
            $observacion = $request->observacion_furpen;

            $caso->update([
                'furpen_completo'       => true,
                'fecha_furpen_recibido' => $fecha,
                'observacion_furpen'    => $observacion,
                'estado'                => $this->resolverEstadoDesde($caso, ['furpen_completo' => true]),
            ]);

            $desc = 'Se registró FURPEN completo desde acciones rápidas.';
            if (!empty($observacion)) $desc .= ' Obs: ' . $observacion;
            $this->registrarBitacora($caso->id, 'Se registró recepción de FURPEN', $desc, $fecha);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'FURPEN completo registrado',
                "Fecha FURPEN: {$fecha}." . ($observacion ? " Obs: {$observacion}" : ''),
                'info'
            );
        }

        return redirect()->route('casos.index')->with('success', 'FURPEN registrado.');
    }

    public function marcarReclamacion(Request $request, Caso $caso)
    {
        $request->validate([
            'valor_reclamado'         => 'required|numeric|min:0',
            'fecha_reclamacion_final' => 'required|date',
            'observacion_reclamacion' => 'nullable|string|max:1000',
        ]);

        if (empty($caso->fecha_reclamacion_final)) {
            $caso->update([
                'fecha_reclamacion_final' => $request->fecha_reclamacion_final,
                'valor_reclamado'         => $request->valor_reclamado,
                'observacion_reclamacion' => $request->observacion_reclamacion,
                'estado'                  => $this->resolverEstadoDesde($caso, [
                    'fecha_reclamacion_final' => $request->fecha_reclamacion_final,
                ]),
            ]);

            $desc = 'Cobro registrado. Valor: $' . number_format($request->valor_reclamado, 0, ',', '.');
            if (!empty($request->observacion_reclamacion)) $desc .= '. Obs: ' . $request->observacion_reclamacion;
            $this->registrarBitacora($caso->id, 'Se registró cobro a aseguradora',
                $desc, $request->fecha_reclamacion_final);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                'Reclamación final enviada a aseguradora',
                'Valor reclamado: $' . number_format($request->valor_reclamado, 0, ',', '.') .
                '. Fecha: ' . $request->fecha_reclamacion_final . '. Pendiente pago.',
                'urgente'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Cobro a aseguradora registrado.');
    }

    public function marcarPago(Request $request, Caso $caso)
    {
        $request->validate([
            'valor_pagado'          => 'required|numeric|min:0',
            'fecha_pago_final'      => 'required|date',
            'porcentaje_honorarios' => 'nullable|numeric|in:40,50',
            'observacion_pago'      => 'nullable|string|max:1000',
        ]);

        $porcentajeHonorarios = $request->filled('porcentaje_honorarios')
            ? $request->porcentaje_honorarios
            : $caso->porcentaje_honorarios;

        $finanzas = $this->calcularFinanzas($request->valor_pagado, $porcentajeHonorarios);

        if (empty($caso->fecha_pago_final)) {
            $caso->update([
                'fecha_pago_final'      => $request->fecha_pago_final,
                'estado'                => 'Pagado',
                'valor_pagado'          => $request->valor_pagado,
                'porcentaje_honorarios' => $porcentajeHonorarios,
                'ganancia_equipo'       => $finanzas['ganancia_equipo'],
                'valor_neto_cliente'    => $finanzas['valor_neto_cliente'],
                'observacion_pago'      => $request->observacion_pago,
            ]);

            $desc = 'Pago final registrado. Valor: $' . number_format($request->valor_pagado, 0, ',', '.');
            if ($porcentajeHonorarios) $desc .= ' Honorarios: ' . $porcentajeHonorarios . '%.';
            if ($finanzas['ganancia_equipo']) $desc .= ' Ganancia equipo: $' . number_format($finanzas['ganancia_equipo'], 0, ',', '.') . '.';
            if (!empty($request->observacion_pago)) $desc .= ' Obs: ' . $request->observacion_pago;
            $this->registrarBitacora($caso->id, 'Se registró pago final', $desc, $request->fecha_pago_final);

            NotificacionService::enviarAlertaFlujo(
                $caso,
                '✅ CASO PAGADO — Gestión completada',
                'Valor pagado: $' . number_format($request->valor_pagado, 0, ',', '.') .
                '. Honorarios: ' . $porcentajeHonorarios . '%.' .
                ' Ganancia equipo: $' . number_format($finanzas['ganancia_equipo'] ?? 0, 0, ',', '.') . '.',
                'info'
            );
        }

        return redirect()->route('casos.index')->with('success', 'Pago final registrado.');
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function resolverEstadoDesde(Caso $caso, array $overrides = []): string
    {
        return $this->resolverEstadoAutomatico(
            $overrides['estado']                            ?? $caso->estado,
            $overrides['fecha_solicitud_aseguradora']       ?? $caso->fecha_solicitud_aseguradora,
            $overrides['fecha_respuesta_aseguradora']       ?? $caso->fecha_respuesta_aseguradora,
            $overrides['fecha_apelacion']                   ?? $caso->fecha_apelacion,
            $overrides['fecha_tutela']                      ?? $caso->fecha_tutela,
            $overrides['fecha_pago_honorarios']             ?? $caso->fecha_pago_honorarios,
            $overrides['fecha_envio_junta']                 ?? $caso->fecha_envio_junta,
            $overrides['fecha_dictamen_junta']              ?? $caso->fecha_dictamen_junta,
            $overrides['fecha_reclamacion_final']           ?? $caso->fecha_reclamacion_final,
            $overrides['fecha_pago_final']                  ?? $caso->fecha_pago_final,
            $overrides['fecha_fallo_tutela']                ?? $caso->fecha_fallo_tutela,
            $overrides['resultado_fallo_tutela']            ?? $caso->resultado_fallo_tutela,
            $overrides['fecha_incidente_desacato']          ?? $caso->fecha_incidente_desacato,
            $overrides['fecha_impugnacion']                 ?? $caso->fecha_impugnacion,
            isset($overrides['alta_ortopedia'])             ? (bool) $overrides['alta_ortopedia'] : (bool) $caso->alta_ortopedia,
            isset($overrides['furpen_completo'])            ? (bool) $overrides['furpen_completo'] : (bool) $caso->furpen_completo,
            $overrides['tipo_respuesta_aseguradora']        ?? $caso->tipo_respuesta_aseguradora,
            $overrides['tipo_tutela']                       ?? $caso->tipo_tutela,
            $overrides['fecha_cumplimiento_tutela']         ?? $caso->fecha_cumplimiento_tutela,
            $overrides['fecha_fallo_segunda_instancia']     ?? $caso->fecha_fallo_segunda_instancia,
            $overrides['resultado_fallo_segunda_instancia'] ?? $caso->resultado_fallo_segunda_instancia
        );
    }

    private function resolverEstadoAutomatico(
        $estadoManual,
        $fechaSolicitudAseguradora,
        $fechaRespuestaAseguradora,
        $fechaApelacion,
        $fechaTutela,
        $fechaPagoHonorarios,
        $fechaEnvioJunta,
        $fechaDictamenJunta,
        $fechaReclamacionFinal,
        $fechaPagoFinal,
        $fechaFalloTutela               = null,
        $resultadoFalloTutela           = null,
        $fechaIncidenteDesacato         = null,
        $fechaImpugnacion               = null,
        $altaOrtopedia                  = false,
        $furpenCompleto                 = false,
        $tipoRespuestaAseguradora       = null,
        $tipoTutela                     = null,
        $fechaCumplimientoTutela        = null,
        $fechaFalloSegundaInstancia     = null,
        $resultadoFalloSegundaInstancia = null
    ): string {

        if (!empty($fechaPagoFinal)) return 'Pagado';

        if (!empty($fechaReclamacionFinal)) return 'Cobro a aseguradora enviado';
        if (!empty($fechaDictamenJunta) && $furpenCompleto) return 'Listo para cobro a aseguradora';
        if (!empty($fechaDictamenJunta)) return 'Dictamen de junta recibido';
        if (!empty($fechaEnvioJunta)) return 'Solicitud enviada a junta';
        if (!empty($fechaPagoHonorarios) && $altaOrtopedia) return 'Listo para solicitud a junta';
        if (!empty($fechaPagoHonorarios)) return 'Pendiente alta por ortopedia';

        if (!empty($fechaFalloSegundaInstancia)) {
            if ($resultadoFalloSegundaInstancia === 'confirma') return 'Caso cerrado en segunda instancia';
            if ($resultadoFalloSegundaInstancia === 'revoca') {
                return $tipoTutela === 'tutela_calificacion'
                    ? 'Segunda instancia revoca - aseguradora debe calificar'
                    : 'Segunda instancia revoca - aseguradora debe pagar honorarios';
            }
            return 'Fallo de segunda instancia registrado';
        }

        if (!empty($fechaImpugnacion)) return 'Impugnación presentada';

        if (!empty($fechaFalloTutela)) {
            if ($resultadoFalloTutela === 'concedido') {
                if (!empty($fechaCumplimientoTutela)) {
                    return $tipoTutela === 'tutela_calificacion'
                        ? 'Tutela cumplida - pendiente dictamen aseguradora'
                        : 'Tutela cumplida - pendiente pago honorarios';
                }
                if (!empty($fechaIncidenteDesacato)) return 'Incidente de desacato presentado';
                return 'Fallo tutela concedido - esperando cumplimiento';
            }
            if ($resultadoFalloTutela === 'negado') return 'Fallo tutela negado - pendiente impugnación';
            return 'Fallo de tutela registrado';
        }

        if (!empty($fechaTutela)) {
            return $tipoTutela === 'tutela_calificacion'
                ? 'Tutela para calificación presentada'
                : 'Tutela por debido proceso presentada';
        }

        if (!empty($fechaApelacion)) return 'Apelación de dictamen presentada';

        if (!empty($tipoRespuestaAseguradora)) {
            return match ($tipoRespuestaAseguradora) {
                'emitio_dictamen' => 'Dictamen de aseguradora recibido',
                'nego'            => 'Aseguradora negó - presentar tutela para calificación',
                'no_respondio'    => 'Aseguradora no respondió - presentar tutela para calificación',
                default           => 'Respuesta de aseguradora registrada',
            };
        }

        if (!empty($fechaRespuestaAseguradora)) return 'Dictamen de aseguradora recibido';
        if (!empty($fechaSolicitudAseguradora)) return 'Solicitud de calificación enviada';

        return $estadoManual;
    }

    private function calcularFinanzas($valorPagado, $porcentajeHonorarios): array
    {
        if ($valorPagado === null || $valorPagado === '' ||
            $porcentajeHonorarios === null || $porcentajeHonorarios === '') {
            return ['ganancia_equipo' => null, 'valor_neto_cliente' => null];
        }
        $gananciaEquipo   = round(((float) $valorPagado * (float) $porcentajeHonorarios) / 100, 2);
        $valorNetoCliente = round((float) $valorPagado - $gananciaEquipo, 2);
        return ['ganancia_equipo' => $gananciaEquipo, 'valor_neto_cliente' => $valorNetoCliente];
    }

    private function registrarBitacora($casoId, $titulo, $descripcion, $fechaEvento = null): void
    {
        Bitacora::create([
            'caso_id'      => $casoId,
            'titulo'       => $titulo,
            'descripcion'  => $descripcion,
            'fecha_evento' => $fechaEvento ?: now()->toDateString(),
        ]);
    }

    private function getAseguradoras(): array
    {
        return [
            'Seguros del Estado',
            'Seguros Mundial',
            'Seguros Bolívar',
            'Previsora Compañía de Seguros',
            'AXA Colpatria',
            'Suramericana',
            'MAPFRE',
            'HDI Seguros',
        ];
    }

    private function getJuntas(): array
    {
        return [
            'Junta Regional de Calificación de Invalidez del Cesar',
            'Junta Regional de Calificación de Invalidez del Magdalena',
        ];
    }

    private function getEstados(): array
    {
        return [
            'Nuevo',
            'Solicitud de calificación enviada',
            'Dictamen de aseguradora recibido',
            'Aseguradora negó - presentar tutela para calificación',
            'Aseguradora no respondió - presentar tutela para calificación',
            'Apelación de dictamen presentada',
            'Tutela para calificación presentada',
            'Tutela por debido proceso presentada',
            'Fallo tutela concedido - esperando cumplimiento',
            'Fallo tutela negado - pendiente impugnación',
            'Fallo de tutela registrado',
            'Tutela cumplida - pendiente dictamen aseguradora',
            'Tutela cumplida - pendiente pago honorarios',
            'Incidente de desacato presentado',
            'Impugnación presentada',
            'Fallo de segunda instancia registrado',
            'Segunda instancia revoca - aseguradora debe calificar',
            'Segunda instancia revoca - aseguradora debe pagar honorarios',
            'Caso cerrado en segunda instancia',
            'Pendiente alta por ortopedia',
            'Listo para solicitud a junta',
            'Solicitud enviada a junta',
            'Dictamen de junta recibido',
            'Listo para cobro a aseguradora',
            'Cobro a aseguradora enviado',
            'Pagado',
            'Cerrado',
        ];
    }
}