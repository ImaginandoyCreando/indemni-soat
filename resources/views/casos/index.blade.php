<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>INDEMNI SOAT - Casos</title>
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
        }
        .brand{
            font-size:28px;font-weight:bold;
            margin-bottom:30px;line-height:1.2;letter-spacing:.4px;
        }
        .menu a{
            display:block;padding:12px 14px;margin-bottom:10px;
            text-decoration:none;color:#fff;background:#374151;
            border-radius:10px;transition:.2s ease;
        }
        .menu a:hover{background:#2563eb}
        .menu a.active{background:#2563eb}
        .user-box{
            margin-top:auto;
            padding-top:16px;
            border-top:1px solid #374151;
        }
        .user-name{font-size:13px;font-weight:bold;color:#fff;margin-bottom:2px}
        .user-role{font-size:11px;color:#9ca3af;margin-bottom:10px}
        .logout-btn{
            width:100%;padding:8px;background:#374151;color:#fff;
            border:none;border-radius:6px;font-size:12px;cursor:pointer;
            font-family:inherit;transition:.2s;
        }
        .logout-btn:hover{background:#dc3545}
        .content{flex:1;padding:30px}
        .container{max-width:1900px;margin:0 auto}
        .topbar{
            display:flex;justify-content:space-between;align-items:center;
            margin-bottom:20px;gap:15px;flex-wrap:wrap;
        }
        .btn{
            display:inline-block;padding:8px 12px;background:#2563eb;
            color:#fff;text-decoration:none;border-radius:8px;border:none;
            cursor:pointer;margin:0;font-size:13px;line-height:1.2;transition:.2s ease;
        }
        .btn:hover{opacity:.92}
        .btn-danger{background:#dc3545}
        .btn-warning{background:#facc15;color:#111827}
        .btn-secondary{background:#6b7280}
        .btn-light{background:#e5e7eb;color:#111827}
        .btn-save{background:#2563eb}
        .btn-cancel{background:#e5e7eb;color:#111827}
        .btn-success{background:#198754}
        .btn-info{background:#0dcaf0;color:#111827}
        .btn-purple{background:#7c3aed;color:#fff}

        .table-wrap{
            overflow:auto;background:#fff;border-radius:12px;
            border:1px solid #d7dce3;box-shadow:0 8px 24px rgba(15,23,42,.04);
        }
        table{width:100%;border-collapse:collapse;background:#fff}
        th,td{
            padding:10px;border-bottom:1px solid #e5e7eb;
            text-align:left;vertical-align:top;white-space:normal;font-size:14px;
        }
        th{background:#eef2f7;white-space:nowrap;font-size:13px}
        th a{color:#111827;text-decoration:none;font-weight:bold}
        .alert{
            padding:12px 14px;background:#d1e7dd;color:#0f5132;
            border-radius:8px;margin-bottom:15px;border:1px solid #badbcc;
        }
        .alert-info{
            padding:12px 14px;background:#cff4fc;color:#055160;
            border-radius:8px;margin-bottom:15px;border:1px solid #b6effb;
        }
        form.inline{display:inline}
        .filters{
            background:#fff;border:1px solid #d7dce3;border-radius:12px;
            padding:18px;margin-bottom:20px;box-shadow:0 8px 24px rgba(15,23,42,.04);
        }
        .filter-grid{
            display:grid;
            grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;
            gap:12px;align-items:end;
        }
        .filters label{display:block;font-weight:bold;margin-bottom:6px;font-size:13px}
        .filters input,.filters select{
            width:100%;padding:10px;border:1px solid #cfd6df;
            border-radius:8px;background:#fff;
        }
        .badge-count{
            display:inline-block;margin-left:10px;background:#e9ecef;
            color:#374151;padding:6px 10px;border-radius:999px;font-size:14px;
        }
        .pagination{
            margin-top:20px;background:#fff;border:1px solid #d7dce3;
            border-radius:12px;padding:15px;
        }
        .money{font-weight:bold;color:#198754;white-space:nowrap}
        .money-blue{font-weight:bold;color:#1d4ed8;white-space:nowrap}
        .pill{
            display:inline-block;padding:6px 10px;border-radius:999px;
            font-size:13px;background:#e5e7eb;color:#374151;white-space:nowrap;
        }
        .priority{
            display:inline-block;padding:6px 10px;border-radius:999px;
            font-size:12px;font-weight:bold;white-space:nowrap;
        }
        .priority-red{background:#f8d7da;color:#842029}
        .priority-orange{background:#fff3cd;color:#997404}
        .priority-blue{background:#cff4fc;color:#055160}
        .priority-green{background:#d1e7dd;color:#0f5132}
        .priority-gray{background:#e2e3e5;color:#41464b}
        tr.row-red{background:#fff5f5}
        tr.row-orange{background:#fffaf0}
        tr.row-blue{background:#f3fbff}
        tr.row-green{background:#f3fff7}
        .legend{
            background:#fff;border:1px solid #d7dce3;border-radius:12px;
            padding:14px 18px;margin-bottom:20px;display:flex;
            gap:10px;flex-wrap:wrap;align-items:center;
            box-shadow:0 8px 24px rgba(15,23,42,.04);
        }
        .legend-title{font-weight:bold;margin-right:6px}
        .actions-cell{min-width:1600px}
        .actions-group{display:flex;flex-wrap:wrap;gap:6px}
        .junta-col{min-width:260px;max-width:320px;white-space:normal;line-height:1.35}
        .victima-col{min-width:220px}
        .numero-col{min-width:140px}
        .aseguradora-col{min-width:160px}
        .valor-col{min-width:130px}
        .fin-col{min-width:120px}
        .meta-cell{line-height:1.5}
        .mini{
            display:inline-block;padding:4px 8px;border-radius:999px;
            font-size:11px;font-weight:bold;margin:2px 4px 2px 0;
        }
        .mini-ok{background:#d1e7dd;color:#0f5132}
        .mini-warn{background:#fff3cd;color:#997404}
        .mini-info{background:#cff4fc;color:#055160}
        .mini-muted{background:#e5e7eb;color:#374151}
        .mini-danger{background:#f8d7da;color:#842029}
        .mini-purple{background:#ede9fe;color:#5b21b6}

        .modal-overlay{
            position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(15,23,42,.55);backdrop-filter:blur(4px);
            display:none;align-items:center;justify-content:center;
            z-index:10000;padding:20px;
        }
        .modal-overlay.active{display:flex}
        .modal{
            width:100%;max-width:560px;background:#ffffff;border-radius:16px;
            padding:28px;box-shadow:0 25px 80px rgba(0,0,0,.25);
            animation:modalFade .22s ease;border:1px solid #e5e7eb;
            max-height:90vh;overflow-y:auto;
        }
        @keyframes modalFade{
            from{opacity:0;transform:translateY(18px)}
            to{opacity:1;transform:translateY(0)}
        }
        .modal-title{font-size:24px;font-weight:700;margin-bottom:6px}
        .modal-subtitle{color:#64748b;margin-bottom:22px;line-height:1.45}
        .modal-grid{display:grid;gap:16px}
        .modal label{font-weight:600;font-size:14px;margin-bottom:6px;display:block}
        .modal input,.modal select,.modal textarea{
            width:100%;padding:11px 12px;border-radius:10px;
            border:1px solid #d1d5db;font-size:14px;font-family:inherit;
        }
        .modal input:focus,.modal select:focus,.modal textarea:focus,
        .filters input:focus,.filters select:focus{
            outline:none;border-color:#2563eb;
            box-shadow:0 0 0 3px rgba(37,99,235,.12);
        }
        .modal textarea{min-height:95px;resize:vertical}
        .modal-actions{
            margin-top:22px;display:flex;justify-content:flex-end;
            gap:10px;flex-wrap:wrap;
        }
        .modal-hint{font-size:12px;color:#64748b;margin-top:4px;line-height:1.4}

        @media (max-width:1600px){.filter-grid{grid-template-columns:1fr 1fr 1fr 1fr}}
        @media (max-width:1100px){.filter-grid{grid-template-columns:1fr 1fr}}
        @media (max-width:900px){
            .layout{flex-direction:column}
            .sidebar{width:100%}
            .filter-grid{grid-template-columns:1fr}
            .content{padding:18px}
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

    <main class="content">
        <div class="container">
            <div class="topbar">
                <div>
                    <h1 style="margin:0;">INDEMNI SOAT - Casos</h1>
                    <span class="badge-count">{{ $casos->total() }} resultado(s)</span>
                </div>
                @if(auth()->user()->puedeEditar())
                    <a href="{{ route('casos.create') }}" class="btn">Nuevo caso</a>
                @endif
            </div>

            @if(session('success'))
                <div class="alert">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="alert-info">{{ session('info') }}</div>
            @endif

            {{-- FILTROS --}}
            <div class="filters">
                <form method="GET" action="{{ route('casos.index') }}">
                    <div class="filter-grid">
                        <div>
                            <label for="buscar">Buscar</label>
                            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" placeholder="Nombre, apellido, cédula o número de caso">
                        </div>
                        <div>
                            <label for="aseguradora">Aseguradora</label>
                            <select name="aseguradora" id="aseguradora">
                                <option value="">Todas</option>
                                @foreach($aseguradoras as $aseg)
                                    <option value="{{ $aseg }}" {{ request('aseguradora') == $aseg ? 'selected' : '' }}>{{ $aseg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="estado">Estado</label>
                            <select name="estado" id="estado">
                                <option value="">Todos</option>
                                @foreach($estados as $est)
                                    <option value="{{ $est }}" {{ request('estado') == $est ? 'selected' : '' }}>{{ $est }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="alerta">Alerta</label>
                            <select name="alerta" id="alerta">
                                <option value="">Todas</option>
                                @foreach($alertasDisponibles as $al)
                                    <option value="{{ $al['valor'] }}" {{ request('alerta') == $al['valor'] ? 'selected' : '' }}>{{ $al['texto'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="fecha_desde">Fecha desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                        </div>
                        <div>
                            <label for="fecha_hasta">Fecha hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                        </div>
                        <div>
                            <label for="tiene_poder">Poder firmado</label>
                            <select name="tiene_poder" id="tiene_poder">
                                <option value="">Todos</option>
                                <option value="1" {{ request('tiene_poder') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('tiene_poder') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div>
                            <label for="tiene_contrato">Contrato firmado</label>
                            <select name="tiene_contrato" id="tiene_contrato">
                                <option value="">Todos</option>
                                <option value="1" {{ request('tiene_contrato') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('tiene_contrato') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div>
                            <label for="alta_ortopedia">Alta ortopedia</label>
                            <select name="alta_ortopedia" id="alta_ortopedia">
                                <option value="">Todos</option>
                                <option value="1" {{ request('alta_ortopedia') === '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ request('alta_ortopedia') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div>
                            <label for="furpen_completo">FURPEN</label>
                            <select name="furpen_completo" id="furpen_completo">
                                <option value="">Todos</option>
                                <option value="1" {{ request('furpen_completo') === '1' ? 'selected' : '' }}>Completo</option>
                                <option value="0" {{ request('furpen_completo') === '0' ? 'selected' : '' }}>Pendiente</option>
                            </select>
                        </div>
                        <div>
                            <input type="hidden" name="sort" value="{{ request('sort', 'id') }}">
                            <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">
                            <button type="submit" class="btn">Filtrar</button>
                        </div>
                        <div>
                            <a href="{{ route('casos.index') }}" class="btn btn-light">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="legend">
                <span class="legend-title">Prioridades 2.0:</span>
                <span class="priority priority-red">Crítica / tutela / desacato / prescripción</span>
                <span class="priority priority-orange">Pendientes documentales y jurídicos</span>
                <span class="priority priority-blue">Seguimiento / junta / cobro</span>
                <span class="priority priority-green">Pagado</span>
                <span class="priority priority-gray">Normal</span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Prioridad</th>
                            <th class="numero-col">
                                <a href="{{ route('casos.index', array_merge(request()->query(), ['sort'=>'numero_caso','direction'=>(($sort==='numero_caso'&&$direction==='asc')?'desc':'asc')])) }}">Número</a>
                            </th>
                            <th class="victima-col">
                                <a href="{{ route('casos.index', array_merge(request()->query(), ['sort'=>'nombres','direction'=>(($sort==='nombres'&&$direction==='asc')?'desc':'asc')])) }}">Víctima</a>
                            </th>
                            <th>
                                <a href="{{ route('casos.index', array_merge(request()->query(), ['sort'=>'cedula','direction'=>(($sort==='cedula'&&$direction==='asc')?'desc':'asc')])) }}">Cédula</a>
                            </th>
                            <th class="aseguradora-col">
                                <a href="{{ route('casos.index', array_merge(request()->query(), ['sort'=>'aseguradora','direction'=>(($sort==='aseguradora'&&$direction==='asc')?'desc':'asc')])) }}">Aseguradora</a>
                            </th>
                            <th>
                                <a href="{{ route('casos.index', array_merge(request()->query(), ['sort'=>'estado','direction'=>(($sort==='estado'&&$direction==='asc')?'desc':'asc')])) }}">Estado</a>
                            </th>
                            <th>PCL</th>
                            <th class="valor-col">Valor estimado</th>
                            <th class="valor-col">Valor pagado</th>
                            <th class="fin-col">% Honor.</th>
                            <th class="valor-col">Ganancia equipo</th>
                            <th class="valor-col">Neto cliente</th>
                            <th>Avance</th>
                            <th class="junta-col">
                                <a href="{{ route('casos.index', array_merge(request()->query(), ['sort'=>'junta_asignada','direction'=>(($sort==='junta_asignada'&&$direction==='asc')?'desc':'asc')])) }}">Junta</a>
                            </th>
                            <th class="actions-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($casos as $caso)
                            @php
                                $priorityClass = match($caso->color_alerta) {
                                    'red'           => 'priority-red',
                                    'orange'        => 'priority-orange',
                                    'cyan','blue'   => 'priority-blue',
                                    'green'         => 'priority-green',
                                    default         => 'priority-gray',
                                };
                                $rowClass = match($caso->color_alerta) {
                                    'red'           => 'row-red',
                                    'orange'        => 'row-orange',
                                    'cyan','blue'   => 'row-blue',
                                    'green'         => 'row-green',
                                    default         => '',
                                };
                                $diasPrescripcion = method_exists($caso,'diasParaPrescripcion') ? $caso->diasParaPrescripcion() : null;
                            @endphp

                            <tr class="{{ $rowClass }}">
                                <td>
                                    <span class="priority {{ $priorityClass }}">{{ $caso->texto_alerta }}</span>
                                </td>

                                <td class="numero-col">
                                    <strong>{{ $caso->numero_caso }}</strong>
                                    <div style="margin-top:6px">
                                        @if($caso->estaPrescrito())
                                            <span class="mini mini-danger">Prescrito</span>
                                        @elseif($diasPrescripcion !== null)
                                            <span class="mini {{ $diasPrescripcion <= 90 ? 'mini-danger' : 'mini-muted' }}">
                                                Prescripción: {{ $diasPrescripcion }} día(s)
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="victima-col meta-cell">
                                    <div><strong>{{ $caso->nombres }} {{ $caso->apellidos }}</strong></div>
                                    <div style="margin-top:6px">
                                        <span class="mini {{ $caso->tiene_poder ? 'mini-ok' : 'mini-warn' }}">Poder {{ $caso->tiene_poder ? '✓' : 'Pend.' }}</span>
                                        <span class="mini {{ $caso->tiene_contrato ? 'mini-ok' : 'mini-warn' }}">Contrato {{ $caso->tiene_contrato ? '✓' : 'Pend.' }}</span>
                                    </div>
                                    <div style="margin-top:4px">
                                        <span class="mini {{ $caso->alta_ortopedia ? 'mini-ok' : 'mini-info' }}">Alta ortopedia {{ $caso->alta_ortopedia ? '✓' : 'Pend.' }}</span>
                                        <span class="mini {{ $caso->furpen_completo ? 'mini-ok' : 'mini-info' }}">FURPEN {{ $caso->furpen_completo ? '✓' : 'Pend.' }}</span>
                                    </div>
                                    @if(!empty($caso->tipo_respuesta_aseguradora))
                                        <div style="margin-top:4px">
                                            @php
                                                $textoTipoResp = match($caso->tipo_respuesta_aseguradora) {
                                                    'emitio_dictamen' => 'Dictamen emitido',
                                                    'nego'            => 'Negó solicitud',
                                                    'no_respondio'    => 'No respondió',
                                                    default           => $caso->tipo_respuesta_aseguradora,
                                                };
                                            @endphp
                                            <span class="mini mini-muted">{{ $textoTipoResp }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($caso->tipo_tutela))
                                        <div style="margin-top:4px">
                                            <span class="mini mini-purple">
                                                {{ $caso->tipo_tutela === 'tutela_calificacion' ? 'Tutela calificación' : 'Tutela debido proceso' }}
                                            </span>
                                        </div>
                                    @endif
                                    @if(!empty($caso->resultado_fallo_tutela))
                                        <div style="margin-top:4px">
                                            <span class="mini mini-muted">Fallo tutela: {{ ucfirst($caso->resultado_fallo_tutela) }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($caso->resultado_fallo_segunda_instancia))
                                        <div style="margin-top:4px">
                                            <span class="mini {{ $caso->resultado_fallo_segunda_instancia === 'revoca' ? 'mini-ok' : 'mini-danger' }}">
                                                2ª instancia: {{ ucfirst($caso->resultado_fallo_segunda_instancia) }}
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <td>{{ $caso->cedula }}</td>
                                <td class="aseguradora-col">{{ $caso->aseguradora ?: 'N/A' }}</td>
                                <td><span class="pill">{{ $caso->estado ?: 'N/A' }}</span></td>
                                <td>{{ $caso->porcentaje_pcl ?: 'N/A' }}</td>
                                <td class="money valor-col">{{ $caso->valor_estimado ? '$'.number_format($caso->valor_estimado,0,',','.') : 'N/A' }}</td>
                                <td class="money valor-col">{{ $caso->valor_pagado ? '$'.number_format($caso->valor_pagado,0,',','.') : 'N/A' }}</td>
                                <td class="fin-col">{{ $caso->porcentaje_honorarios ? number_format($caso->porcentaje_honorarios,0,',','.').'%' : 'N/A' }}</td>
                                <td class="money-blue valor-col">{{ $caso->ganancia_equipo ? '$'.number_format($caso->ganancia_equipo,0,',','.') : 'N/A' }}</td>
                                <td class="money valor-col">{{ $caso->valor_neto_cliente ? '$'.number_format($caso->valor_neto_cliente,0,',','.') : 'N/A' }}</td>
                                <td>{{ $caso->porcentaje_avance ?? 0 }}%</td>
                                <td class="junta-col">{{ $caso->junta_asignada ?: 'N/A' }}</td>

                                <td class="actions-cell">
                                    <div class="actions-group">

                                        <a href="{{ route('casos.show', $caso) }}" class="btn btn-secondary">Ver</a>

                                        @if(auth()->user()->puedeEditar())
                                            <a href="{{ route('casos.edit', $caso) }}" class="btn btn-warning">Editar</a>
                                        @endif

                                        <a href="{{ route('casos.documentos.index', $caso) }}" class="btn">Expediente</a>
                                        <a href="{{ route('casos.bitacoras.index', $caso) }}" class="btn btn-secondary">Bitácora</a>

                                        @if(auth()->user()->puedeAccionarFlujo())

                                            @if(empty($caso->fecha_solicitud_aseguradora) && !$caso->requierePoderContrato() && !$caso->estaPrescrito())
                                                <button type="button" class="btn btn-abrir-modal-fecha"
                                                    data-action="{{ route('casos.marcarSolicitudAseguradora', $caso) }}"
                                                    data-campo="fecha_solicitud_aseguradora"
                                                    data-label="Fecha de solicitud"
                                                    data-titulo="Registrar solicitud a aseguradora"
                                                    data-boton="Guardar solicitud"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Solicitud aseguradora
                                                </button>
                                            @elseif(!empty($caso->fecha_solicitud_aseguradora))
                                                <button type="button" class="btn btn-light" disabled>Solicitud ✓</button>
                                            @else
                                                <button type="button" class="btn btn-light" disabled>Falta poder/contrato</button>
                                            @endif

                                            @if(!empty($caso->fecha_solicitud_aseguradora) && empty($caso->tipo_respuesta_aseguradora))
                                                <button type="button" class="btn btn-abrir-modal-respuesta"
                                                    data-action="{{ route('casos.marcarRespuestaAseguradora', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Respuesta
                                                </button>
                                            @elseif(!empty($caso->tipo_respuesta_aseguradora))
                                                <button type="button" class="btn btn-light" disabled>Respuesta ✓</button>
                                            @endif

                                            @if($caso->tipo_respuesta_aseguradora === 'emitio_dictamen' && empty($caso->fecha_apelacion))
                                                <button type="button" class="btn btn-warning btn-abrir-modal-fecha"
                                                    data-action="{{ route('casos.marcarApelacion', $caso) }}"
                                                    data-campo="fecha_apelacion"
                                                    data-label="Fecha de apelación"
                                                    data-titulo="Registrar apelación del dictamen"
                                                    data-boton="Guardar apelación"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Apelar
                                                </button>
                                            @elseif(!empty($caso->fecha_apelacion))
                                                <button type="button" class="btn btn-light" disabled>Apelación ✓</button>
                                            @endif

                                            @if($caso->requiereTutela())
                                                <button type="button" class="btn btn-warning btn-abrir-modal-tutela"
                                                    data-action="{{ route('casos.marcarTutela', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}"
                                                    data-tipo-respuesta="{{ $caso->tipo_respuesta_aseguradora }}">
                                                    Tutela
                                                </button>
                                            @elseif(!empty($caso->fecha_tutela))
                                                <button type="button" class="btn btn-light" disabled>Tutela ✓</button>
                                            @endif

                                            @if(!empty($caso->fecha_tutela) && empty($caso->fecha_fallo_tutela))
                                                <button type="button" class="btn btn-danger btn-abrir-modal-fallo"
                                                    data-action="{{ route('casos.marcarFalloTutela', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}"
                                                    data-resultado="{{ $caso->resultado_fallo_tutela }}">
                                                    Fallo tutela
                                                </button>
                                            @elseif(!empty($caso->fecha_fallo_tutela))
                                                <button type="button" class="btn btn-light" disabled>Fallo tutela ✓</button>
                                            @endif

                                            @if($caso->requiereCumplimientoTutela())
                                                <button type="button" class="btn btn-success btn-abrir-modal-cumplimiento"
                                                    data-action="{{ route('casos.marcarCumplimientoTutela', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}"
                                                    data-tipo-tutela="{{ $caso->tipo_tutela }}">
                                                    Cumplimiento
                                                </button>
                                            @elseif(!empty($caso->fecha_cumplimiento_tutela))
                                                <button type="button" class="btn btn-light" disabled>Cumplimiento ✓</button>
                                            @endif

                                            @if($caso->requiereIncidenteDesacato())
                                                <button type="button" class="btn btn-danger btn-abrir-modal-fecha"
                                                    data-action="{{ route('casos.marcarIncidenteDesacato', $caso) }}"
                                                    data-campo="fecha_incidente_desacato"
                                                    data-label="Fecha del incidente de desacato"
                                                    data-titulo="Registrar incidente de desacato"
                                                    data-boton="Guardar desacato"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Desacato
                                                </button>
                                            @elseif(!empty($caso->fecha_incidente_desacato))
                                                <button type="button" class="btn btn-light" disabled>Desacato ✓</button>
                                            @endif

                                            @if($caso->requiereImpugnacion())
                                                <button type="button" class="btn btn-warning btn-abrir-modal-fecha"
                                                    data-action="{{ route('casos.marcarImpugnacion', $caso) }}"
                                                    data-campo="fecha_impugnacion"
                                                    data-label="Fecha de impugnación"
                                                    data-titulo="Registrar impugnación"
                                                    data-boton="Guardar impugnación"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Impugnación
                                                </button>
                                            @elseif(!empty($caso->fecha_impugnacion))
                                                <button type="button" class="btn btn-light" disabled>Impugnación ✓</button>
                                            @endif

                                            @if($caso->requiereSegundaInstancia())
                                                <button type="button" class="btn btn-purple btn-abrir-modal-segunda"
                                                    data-action="{{ route('casos.marcarFalloSegundaInstancia', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Segunda instancia
                                                </button>
                                            @elseif(!empty($caso->fecha_fallo_segunda_instancia))
                                                <button type="button" class="btn btn-light" disabled>2ª instancia ✓</button>
                                            @endif

                                            @if(!empty($caso->fecha_apelacion) && empty($caso->fecha_pago_honorarios))
                                                <button type="button" class="btn btn-abrir-modal-fecha"
                                                    data-action="{{ route('casos.marcarPagoHonorarios', $caso) }}"
                                                    data-campo="fecha_pago_honorarios"
                                                    data-label="Fecha de pago honorarios"
                                                    data-titulo="Registrar pago de honorarios"
                                                    data-boton="Guardar honorarios"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Honorarios
                                                </button>
                                            @elseif(!empty($caso->fecha_pago_honorarios))
                                                <button type="button" class="btn btn-light" disabled>Honorarios ✓</button>
                                            @endif

                                            @if($caso->requiereAltaOrtopedia())
                                                <button type="button" class="btn btn-info btn-abrir-modal-alta"
                                                    data-action="{{ route('casos.marcarAltaOrtopedia', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Alta ortopedia
                                                </button>
                                            @elseif($caso->alta_ortopedia)
                                                <button type="button" class="btn btn-light" disabled>Alta ortopedia ✓</button>
                                            @endif

                                            @if($caso->requiereSolicitudJunta())
                                                <button type="button" class="btn btn-secondary btn-abrir-modal-fecha"
                                                    data-action="{{ route('casos.marcarSolicitudJunta', $caso) }}"
                                                    data-campo="fecha_envio_junta"
                                                    data-label="Fecha de envío a junta"
                                                    data-titulo="Registrar solicitud a junta"
                                                    data-boton="Guardar junta"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Junta
                                                </button>
                                            @elseif(!empty($caso->fecha_envio_junta))
                                                <button type="button" class="btn btn-light" disabled>Junta ✓</button>
                                            @endif

                                            @if(!empty($caso->fecha_envio_junta) && empty($caso->fecha_dictamen_junta))
                                                <button type="button" class="btn btn-abrir-modal-fecha"
                                                    data-action="{{ route('casos.marcarDictamenJunta', $caso) }}"
                                                    data-campo="fecha_dictamen_junta"
                                                    data-label="Fecha de dictamen"
                                                    data-titulo="Registrar dictamen de junta"
                                                    data-boton="Guardar dictamen"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Dictamen junta
                                                </button>
                                            @elseif(!empty($caso->fecha_dictamen_junta))
                                                <button type="button" class="btn btn-light" disabled>Dictamen ✓</button>
                                            @endif

                                            @if($caso->requiereFurpen())
                                                <button type="button" class="btn btn-info btn-abrir-modal-furpen"
                                                    data-action="{{ route('casos.marcarFurpen', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    FURPEN
                                                </button>
                                            @elseif($caso->furpen_completo)
                                                <button type="button" class="btn btn-light" disabled>FURPEN ✓</button>
                                            @endif

                                            @if($caso->requiereCobroAseguradora())
                                                <button type="button" class="btn btn-abrir-modal-reclamacion"
                                                    data-action="{{ route('casos.marcarReclamacion', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}">
                                                    Reclamar
                                                </button>
                                            @elseif(!empty($caso->fecha_reclamacion_final))
                                                <button type="button" class="btn btn-light" disabled>Reclamación ✓</button>
                                            @endif

                                            @if($caso->requierePagoPendiente())
                                                <button type="button" class="btn btn-secondary btn-abrir-modal-pago"
                                                    data-action="{{ route('casos.marcarPago', $caso) }}"
                                                    data-caso="{{ $caso->numero_caso }}"
                                                    data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                                    data-fecha="{{ now()->toDateString() }}"
                                                    data-honorarios="{{ $caso->porcentaje_honorarios }}">
                                                    Pago
                                                </button>
                                            @elseif(!empty($caso->fecha_pago_final))
                                                <button type="button" class="btn btn-light" disabled>Pago ✓</button>
                                            @endif

                                        @endif {{-- fin puedeAccionarFlujo --}}

                                        @if(auth()->user()->puedeEliminar())
                                            <form class="inline" action="{{ route('casos.destroy', $caso) }}" method="POST"
                                                onsubmit="return confirm('¿Eliminar este caso?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15">No hay casos registrados con esos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">{{ $casos->links() }}</div>
        </div>
    </main>
</div>

{{-- MODALES — sin cambios respecto al original --}}
<div class="modal-overlay" id="modalFechaOverlay">
    <div class="modal">
        <div class="modal-title" id="modalFechaTitulo">Registrar fecha</div>
        <div class="modal-subtitle"><strong id="modalFechaCaso"></strong><br><span id="modalFechaVictima"></span></div>
        <form id="modalFechaForm" method="POST">
            @csrf
            <div class="modal-grid"><div><label id="modalFechaLabel">Fecha</label><input type="date" id="modal_fecha_generica" required></div></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalFecha">Cancelar</button>
                <button type="submit" class="btn btn-save" id="modalFechaBoton">Guardar</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalRespuestaOverlay">
    <div class="modal">
        <div class="modal-title">Registrar respuesta de aseguradora</div>
        <div class="modal-subtitle"><strong id="modalRespuestaCaso"></strong><br><span id="modalRespuestaVictima"></span></div>
        <form id="modalRespuestaForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div>
                    <label>Tipo de respuesta <span style="color:#ef4444">*</span></label>
                    <select name="tipo_respuesta_aseguradora" id="modal_tipo_respuesta" required onchange="toggleFechaRespuesta()">
                        <option value="">— Seleccionar —</option>
                        <option value="emitio_dictamen">Emitió dictamen (calificó)</option>
                        <option value="nego">Negó la solicitud</option>
                        <option value="no_respondio">No respondió (pasó 1 mes)</option>
                    </select>
                    <p class="modal-hint">Emitió dictamen → flujo de apelación.<br>Negó / No respondió → tutela para calificación.</p>
                </div>
                <div id="bloque_modal_fecha_respuesta">
                    <label>Fecha de respuesta / dictamen</label>
                    <input type="date" name="fecha_respuesta_aseguradora" id="modal_fecha_respuesta">
                    <p class="modal-hint">Dejar vacío si la respuesta fue "no respondió".</p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalRespuesta">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar respuesta</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalTutelaOverlay">
    <div class="modal">
        <div class="modal-title">Registrar tutela</div>
        <div class="modal-subtitle"><strong id="modalTutelaCaso"></strong><br><span id="modalTutelaVictima"></span></div>
        <form id="modalTutelaForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div><label>Fecha de tutela <span style="color:#ef4444">*</span></label><input type="date" name="fecha_tutela" id="modal_fecha_tutela" required></div>
                <div>
                    <label>Tipo de tutela <span style="color:#ef4444">*</span></label>
                    <select name="tipo_tutela" id="modal_tipo_tutela" required>
                        <option value="">— Seleccionar —</option>
                        <option value="tutela_calificacion">Para calificación (aseguradora negó o no respondió)</option>
                        <option value="tutela_debido_proceso">Por debido proceso (no pagan honorarios)</option>
                    </select>
                    <p class="modal-hint"><strong>Calificación</strong>: la aseguradora negó o no respondió.<br><strong>Debido proceso</strong>: apelaron pero no pagan honorarios.</p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalTutela">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar tutela</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalFalloOverlay">
    <div class="modal">
        <div class="modal-title">Registrar fallo de tutela</div>
        <div class="modal-subtitle"><strong id="modalFalloCaso"></strong><br><span id="modalFalloVictima"></span></div>
        <form id="modalFalloForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div><label>Fecha fallo de tutela <span style="color:#ef4444">*</span></label><input type="date" name="fecha_fallo_tutela" id="modal_fecha_fallo_tutela" required></div>
                <div>
                    <label>Resultado del fallo <span style="color:#ef4444">*</span></label>
                    <select name="resultado_fallo_tutela" id="modal_resultado_fallo_tutela" required>
                        <option value="">— Seleccionar —</option>
                        <option value="concedido">Concedido → esperar cumplimiento</option>
                        <option value="negado">Negado → impugnar</option>
                        <option value="parcial">Parcial</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalFallo">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar fallo</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalCumplimientoOverlay">
    <div class="modal">
        <div class="modal-title">Registrar cumplimiento del fallo</div>
        <div class="modal-subtitle"><strong id="modalCumplimientoCaso"></strong><br><span id="modalCumplimientoVictima"></span></div>
        <form id="modalCumplimientoForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div><label>Fecha de cumplimiento <span style="color:#ef4444">*</span></label><input type="date" name="fecha_cumplimiento_tutela" id="modal_fecha_cumplimiento" required></div>
                <div>
                    <label>Tipo de cumplimiento <span style="color:#ef4444">*</span></label>
                    <select name="tipo_cumplimiento_tutela" id="modal_tipo_cumplimiento" required>
                        <option value="">— Seleccionar —</option>
                        <option value="voluntario">Voluntario (cumplió dentro de las 2 semanas)</option>
                        <option value="desacato">Tras incidente de desacato</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalCumplimiento">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar cumplimiento</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalSegundaOverlay">
    <div class="modal">
        <div class="modal-title">Registrar fallo de segunda instancia</div>
        <div class="modal-subtitle"><strong id="modalSegundaCaso"></strong><br><span id="modalSegundaVictima"></span></div>
        <form id="modalSegundaForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div><label>Fecha del fallo <span style="color:#ef4444">*</span></label><input type="date" name="fecha_fallo_segunda_instancia" id="modal_fecha_segunda" required></div>
                <div>
                    <label>Resultado <span style="color:#ef4444">*</span></label>
                    <select name="resultado_fallo_segunda_instancia" id="modal_resultado_segunda" required onchange="mostrarHintSegunda()">
                        <option value="">— Seleccionar —</option>
                        <option value="confirma">Confirma — el caso se pierde</option>
                        <option value="revoca">Revoca — la aseguradora debe cumplir</option>
                    </select>
                    <p class="modal-hint" id="hint_segunda">Confirma: caso cerrado. Revoca: la aseguradora debe cumplir.</p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalSegunda">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar fallo</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalPagoOverlay">
    <div class="modal">
        <div class="modal-title">Registrar pago final</div>
        <div class="modal-subtitle"><strong id="modalPagoCaso"></strong><br><span id="modalPagoVictima"></span></div>
        <form id="modalPagoForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div><label>Valor pagado</label><input type="number" step="0.01" min="0" name="valor_pagado" id="modal_valor_pagado" required></div>
                <div><label>Fecha de pago</label><input type="date" name="fecha_pago_final" id="modal_fecha_pago_final" required></div>
                <div><label>Honorarios equipo</label><select name="porcentaje_honorarios" id="modal_porcentaje_honorarios"><option value="">Seleccionar</option><option value="40">40%</option><option value="50">50%</option></select></div>
                <div><label>Observación</label><textarea name="observacion_pago" id="modal_observacion_pago" placeholder="Ej: pago por transferencia, comprobante adjunto..."></textarea></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalPago">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar pago</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalReclamacionOverlay">
    <div class="modal">
        <div class="modal-title">Registrar reclamación final</div>
        <div class="modal-subtitle"><strong id="modalReclamacionCaso"></strong><br><span id="modalReclamacionVictima"></span></div>
        <form id="modalReclamacionForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div><label>Valor reclamado</label><input type="number" step="0.01" min="0" name="valor_reclamado" id="modal_valor_reclamado" required></div>
                <div><label>Fecha de reclamación</label><input type="date" name="fecha_reclamacion_final" id="modal_fecha_reclamacion_final" required></div>
                <div><label>Observación</label><textarea name="observacion_reclamacion" id="modal_observacion_reclamacion" placeholder="Ej: radicado generado, anexos completos..."></textarea></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalReclamacion">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar reclamación</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalAltaOverlay">
    <div class="modal">
        <div class="modal-title">Registrar alta por ortopedia</div>
        <div class="modal-subtitle"><strong id="modalAltaCaso"></strong><br><span id="modalAltaVictima"></span></div>
        <form id="modalAltaForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div><label>Fecha alta ortopedia</label><input type="date" name="fecha_alta_ortopedia" id="modal_fecha_alta_ortopedia" required></div>
                <div><label>Observación</label><textarea name="observacion_alta_ortopedia" id="modal_observacion_alta_ortopedia" placeholder="Ej: alta emitida por especialista..."></textarea></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalAlta">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar alta</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalFurpenOverlay">
    <div class="modal">
        <div class="modal-title">Registrar FURPEN completo</div>
        <div class="modal-subtitle"><strong id="modalFurpenCaso"></strong><br><span id="modalFurpenVictima"></span></div>
        <form id="modalFurpenForm" method="POST">
            @csrf
            <div class="modal-grid">
                <div><label>Fecha FURPEN recibido</label><input type="date" name="fecha_furpen_recibido" id="modal_fecha_furpen_recibido" required></div>
                <div><label>Observación</label><textarea name="observacion_furpen" id="modal_observacion_furpen" placeholder="Ej: documentación completa..."></textarea></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-cancel" id="cerrarModalFurpen">Cancelar</button>
                <button type="submit" class="btn btn-save">Guardar FURPEN</button>
            </div>
        </form>
    </div>
</div>

<script>
function initModal(overlayId, cerrarId) {
    const overlay = document.getElementById(overlayId);
    const cerrar  = document.getElementById(cerrarId);
    if (!overlay || !cerrar) return;
    function cerrar_() { overlay.classList.remove('active'); document.body.style.overflow = ''; }
    cerrar.addEventListener('click', cerrar_);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) cerrar_(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && overlay.classList.contains('active')) cerrar_(); });
    return { cerrar: cerrar_ };
}

document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const overlay = document.getElementById('modalFechaOverlay');
        const form    = document.getElementById('modalFechaForm');
        initModal('modalFechaOverlay', 'cerrarModalFecha');
        document.querySelectorAll('.btn-abrir-modal-fecha').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalFechaTitulo').textContent  = btn.dataset.titulo  || 'Registrar fecha';
                document.getElementById('modalFechaCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalFechaVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modalFechaLabel').textContent   = btn.dataset.label   || 'Fecha';
                document.getElementById('modalFechaBoton').textContent   = btn.dataset.boton   || 'Guardar';
                const inputFecha = document.getElementById('modal_fecha_generica');
                inputFecha.value = btn.dataset.fecha || '';
                form.querySelectorAll('input.hidden-dynamic').forEach(el => el.remove());
                const campo  = btn.dataset.campo || 'fecha';
                const hidden = document.createElement('input');
                hidden.type  = 'hidden'; hidden.name = campo;
                hidden.classList.add('hidden-dynamic');
                hidden.value = inputFecha.value;
                form.appendChild(hidden);
                inputFecha.oninput = () => hidden.value = inputFecha.value;
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => inputFecha.focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalRespuestaOverlay');
        const form    = document.getElementById('modalRespuestaForm');
        initModal('modalRespuestaOverlay', 'cerrarModalRespuesta');
        document.querySelectorAll('.btn-abrir-modal-respuesta').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalRespuestaCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalRespuestaVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_tipo_respuesta').value = '';
                document.getElementById('modal_fecha_respuesta').value = btn.dataset.fecha || '';
                toggleFechaRespuesta();
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_tipo_respuesta').focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalTutelaOverlay');
        const form    = document.getElementById('modalTutelaForm');
        initModal('modalTutelaOverlay', 'cerrarModalTutela');
        document.querySelectorAll('.btn-abrir-modal-tutela').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalTutelaCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalTutelaVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_fecha_tutela').value = btn.dataset.fecha || '';
                const sel   = document.getElementById('modal_tipo_tutela');
                const tipoR = btn.dataset.tipoRespuesta || '';
                if (tipoR === 'nego' || tipoR === 'no_respondio') sel.value = 'tutela_calificacion';
                else if (tipoR === 'emitio_dictamen') sel.value = 'tutela_debido_proceso';
                else sel.value = '';
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_fecha_tutela').focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalFalloOverlay');
        const form    = document.getElementById('modalFalloForm');
        initModal('modalFalloOverlay', 'cerrarModalFallo');
        document.querySelectorAll('.btn-abrir-modal-fallo').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalFalloCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalFalloVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_fecha_fallo_tutela').value     = btn.dataset.fecha     || '';
                document.getElementById('modal_resultado_fallo_tutela').value = btn.dataset.resultado || '';
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_fecha_fallo_tutela').focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalCumplimientoOverlay');
        const form    = document.getElementById('modalCumplimientoForm');
        initModal('modalCumplimientoOverlay', 'cerrarModalCumplimiento');
        document.querySelectorAll('.btn-abrir-modal-cumplimiento').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalCumplimientoCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalCumplimientoVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_fecha_cumplimiento').value = btn.dataset.fecha || '';
                document.getElementById('modal_tipo_cumplimiento').value  = '';
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_fecha_cumplimiento').focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalSegundaOverlay');
        const form    = document.getElementById('modalSegundaForm');
        initModal('modalSegundaOverlay', 'cerrarModalSegunda');
        document.querySelectorAll('.btn-abrir-modal-segunda').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalSegundaCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalSegundaVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_fecha_segunda').value     = btn.dataset.fecha || '';
                document.getElementById('modal_resultado_segunda').value = '';
                mostrarHintSegunda();
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_fecha_segunda').focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalPagoOverlay');
        const form    = document.getElementById('modalPagoForm');
        initModal('modalPagoOverlay', 'cerrarModalPago');
        document.querySelectorAll('.btn-abrir-modal-pago').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalPagoCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalPagoVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_fecha_pago_final').value         = btn.dataset.fecha      || '';
                document.getElementById('modal_valor_pagado').value             = '';
                document.getElementById('modal_porcentaje_honorarios').value    = btn.dataset.honorarios || '';
                document.getElementById('modal_observacion_pago').value         = '';
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_valor_pagado').focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalReclamacionOverlay');
        const form    = document.getElementById('modalReclamacionForm');
        initModal('modalReclamacionOverlay', 'cerrarModalReclamacion');
        document.querySelectorAll('.btn-abrir-modal-reclamacion').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalReclamacionCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalReclamacionVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_fecha_reclamacion_final').value = btn.dataset.fecha || '';
                document.getElementById('modal_valor_reclamado').value         = '';
                document.getElementById('modal_observacion_reclamacion').value = '';
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_valor_reclamado').focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalAltaOverlay');
        const form    = document.getElementById('modalAltaForm');
        initModal('modalAltaOverlay', 'cerrarModalAlta');
        document.querySelectorAll('.btn-abrir-modal-alta').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalAltaCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalAltaVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_fecha_alta_ortopedia').value       = btn.dataset.fecha || '';
                document.getElementById('modal_observacion_alta_ortopedia').value = '';
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_fecha_alta_ortopedia').focus(), 50);
            });
        });
    })();
    (function () {
        const overlay = document.getElementById('modalFurpenOverlay');
        const form    = document.getElementById('modalFurpenForm');
        initModal('modalFurpenOverlay', 'cerrarModalFurpen');
        document.querySelectorAll('.btn-abrir-modal-furpen').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = btn.dataset.action;
                document.getElementById('modalFurpenCaso').textContent    = btn.dataset.caso    || '';
                document.getElementById('modalFurpenVictima').textContent = btn.dataset.victima || '';
                document.getElementById('modal_fecha_furpen_recibido').value  = btn.dataset.fecha || '';
                document.getElementById('modal_observacion_furpen').value     = '';
                overlay.classList.add('active'); document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('modal_fecha_furpen_recibido').focus(), 50);
            });
        });
    })();
});

function toggleFechaRespuesta() {
    const tipo   = document.getElementById('modal_tipo_respuesta').value;
    const bloque = document.getElementById('bloque_modal_fecha_respuesta');
    bloque.style.display = (tipo === 'no_respondio') ? 'none' : '';
}
function mostrarHintSegunda() {
    const val   = document.getElementById('modal_resultado_segunda').value;
    const hint  = document.getElementById('hint_segunda');
    if (val === 'confirma') hint.innerHTML = '<strong style="color:#dc3545">Caso cerrado desfavorablemente — no hay más acciones jurídicas.</strong>';
    else if (val === 'revoca') hint.innerHTML = '<strong style="color:#198754">La aseguradora debe cumplir lo ordenado — registrar cumplimiento y continuar flujo.</strong>';
    else hint.innerHTML = 'Confirma: el caso queda cerrado. Revoca: la aseguradora debe calificar o pagar honorarios.';
}
</script>
</body>
</html>