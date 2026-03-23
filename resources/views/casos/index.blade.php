@extends('layouts.app')

@section('title', 'Casos')

@section('content')

{{-- ── Estilos específicos de esta vista ── --}}
<style>
/* ── Colores de fila por prioridad ── */
[data-theme="dark"]  tr.row-red    { background: rgba(229,57,53,0.06); }
[data-theme="dark"]  tr.row-orange { background: rgba(245,158,11,0.05); }
[data-theme="dark"]  tr.row-blue   { background: rgba(8,145,178,0.05); }
[data-theme="dark"]  tr.row-green  { background: rgba(5,150,105,0.05); }
[data-theme="light"] tr.row-red    { background: #fff5f5; }
[data-theme="light"] tr.row-orange { background: #fffaf0; }
[data-theme="light"] tr.row-blue   { background: #f3fbff; }
[data-theme="light"] tr.row-green  { background: #f3fff7; }

/* ── Mini badges ── */
.mini {
    display:inline-block; padding:3px 8px; border-radius:999px;
    font-size:11px; font-weight:700; margin:2px 3px 2px 0;
    white-space:nowrap;
}
.mini-ok     { background:rgba(5,150,105,0.12);  color:#1DBD7F; }
.mini-warn   { background:rgba(245,158,11,0.12); color:#F5B942; }
.mini-info   { background:rgba(8,145,178,0.12);  color:#22B8D4; }
.mini-muted  { background:var(--bg-input); color:var(--text-3);
               border:1px solid var(--border-2); }
.mini-danger { background:rgba(229,57,53,0.12);  color:#F26F6F; }
.mini-purple { background:rgba(124,58,237,0.12); color:#A78BFA; }

/* ── Botones de acción en tabla ── */
.is-act-btn {
    display:inline-flex; align-items:center;
    padding:5px 10px; border-radius:6px;
    font-size:11px; font-weight:700;
    cursor:pointer; border:1px solid transparent;
    transition:all .18s; white-space:nowrap;
    font-family:'DM Sans',sans-serif; text-decoration:none;
    line-height:1.3;
}
.is-act-btn:disabled,
.is-act-btn[disabled] {
    opacity:0.45; cursor:not-allowed; pointer-events:none;
}
/* Variantes */
.act-default  { background:rgba(27,79,255,0.12);  color:#4B78FF;
                border-color:rgba(27,79,255,0.2); }
.act-default:hover  { background:rgba(27,79,255,0.22); color:#4B78FF; }

.act-gray    { background:var(--bg-input); color:var(--text-2);
               border-color:var(--border-2); }
.act-gray:hover { background:var(--bg-hover); color:var(--text-1); }

.act-warn    { background:rgba(245,158,11,0.12); color:#F5B942;
               border-color:rgba(245,158,11,0.25); }
.act-warn:hover { background:rgba(245,158,11,0.22); }

.act-danger  { background:rgba(229,57,53,0.1);  color:#F26F6F;
               border-color:rgba(229,57,53,0.22); }
.act-danger:hover { background:rgba(229,57,53,0.2); }

.act-success { background:rgba(5,150,105,0.1);  color:#1DBD7F;
               border-color:rgba(5,150,105,0.22); }
.act-success:hover { background:rgba(5,150,105,0.2); }

.act-teal    { background:rgba(8,145,178,0.1);  color:#22B8D4;
               border-color:rgba(8,145,178,0.22); }
.act-teal:hover { background:rgba(8,145,178,0.2); }

.act-purple  { background:rgba(124,58,237,0.1); color:#A78BFA;
               border-color:rgba(124,58,237,0.22); }
.act-purple:hover { background:rgba(124,58,237,0.2); }

.act-done    { background:var(--bg-input); color:var(--text-3);
               border-color:var(--border); cursor:default; }

/* ── Modales ── */
.is-modal-overlay {
    position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(6,13,24,0.75);
    backdrop-filter:blur(6px);
    display:none; align-items:center; justify-content:center;
    z-index:10000; padding:20px;
}
.is-modal-overlay.active { display:flex; }

.is-modal {
    width:100%; max-width:540px;
    background:var(--bg-card);
    border:1px solid var(--border-2);
    border-radius:20px;
    padding:28px 30px;
    box-shadow:var(--shadow-md);
    animation:modalRise .22s cubic-bezier(.22,.68,0,1.2);
    max-height:90vh; overflow-y:auto;
    transition:background .3s;
}
@keyframes modalRise {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.is-modal-title {
    font-family:'Playfair Display',serif;
    font-size:20px; font-weight:700;
    color:var(--text-1); margin-bottom:4px;
    transition:color .3s;
}
.is-modal-sub {
    font-size:12px; color:var(--text-2);
    margin-bottom:20px; line-height:1.5;
}
.is-modal-grid { display:grid; gap:14px; }
.is-modal label {
    display:block; font-size:10px; font-weight:700;
    color:var(--text-3); letter-spacing:.7px;
    text-transform:uppercase; margin-bottom:6px;
}
.is-modal-hint {
    font-size:11px; color:var(--text-3);
    margin-top:5px; line-height:1.4;
}
.is-modal-actions {
    margin-top:22px; display:flex;
    justify-content:flex-end; gap:10px; flex-wrap:wrap;
}

/* ── Paginación override ── */
.is-pagination nav { display:flex; align-items:center; gap:4px; flex-wrap:wrap; }
.is-pagination nav a,
.is-pagination nav span {
    padding:6px 11px; border-radius:7px; font-size:12px; font-weight:600;
    color:var(--text-2); transition:all .18s; text-decoration:none;
    border:1px solid var(--border);
    background:var(--bg-input);
}
.is-pagination nav a:hover { color:var(--text-1); background:var(--bg-hover); }
.is-pagination nav [aria-current="page"] span,
.is-pagination nav span.font-bold {
    background:var(--cobalt-glow,rgba(27,79,255,.15));
    color:#4B78FF; border-color:rgba(27,79,255,.25);
}

/* ── Tabla col widths ── */
.col-prio   { width:100px; }
.col-num    { min-width:130px; }
.col-vic    { min-width:200px; }
.col-doc    { min-width:80px; }
.col-aseg   { min-width:120px; }
.col-est    { min-width:110px; }
.col-pcl    { min-width:60px; }
.col-val    { min-width:110px; white-space:nowrap; }
.col-acc    { min-width:480px; }

form.inline { display:inline; }

/* ── SCROLLBAR HORIZONTAL TABLA ── */
.tabla-scroll {
    overflow-x: auto;
    overflow-y: visible;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(27,79,255,0.5) var(--border);
}
.tabla-scroll::-webkit-scrollbar {
    height: 6px;
}
.tabla-scroll::-webkit-scrollbar-track {
    background: var(--border);
    border-radius: 0 0 8px 8px;
}
.tabla-scroll::-webkit-scrollbar-thumb {
    background: rgba(27,79,255,0.5);
    border-radius: 3px;
}
.tabla-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(27,79,255,0.8);
}
    
/* ── Fix: permitir scroll horizontal dentro del wrap ── */
.is-table-wrap { overflow-x: auto !important; overflow-y: visible !important; }
.tabla-scroll  { overflow-x: unset !important; }
</style>

{{-- ────────────────────────────────────────────────────
     CABECERA DE PÁGINA
──────────────────────────────────────────────────── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:flex-start;
            justify-content:space-between;margin-bottom:22px;gap:14px;flex-wrap:wrap;">
    <div>
        <div class="is-page-title">Casos SOAT</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">
            <span style="background:var(--cobalt-glow,rgba(27,79,255,.12));
                         color:#4B78FF;padding:3px 10px;border-radius:20px;
                         font-weight:700;font-size:11px;">
                {{ $casos->total() }} resultado(s)
            </span>
        </div>
    </div>
    @if(auth()->user()->puedeEditar())
        <a href="{{ route('casos.create') }}" class="is-btn-primary">
            <span style="width:16px;height:16px;border-radius:50%;
                         background:rgba(255,255,255,.2);display:inline-flex;
                         align-items:center;justify-content:center;font-size:13px;">+</span>
            Nuevo caso
        </a>
    @endif
</div>

{{-- Alertas de sesión --}}
@if(session('success'))
    <div class="is-animate-rise is-stagger-1"
         style="background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.22);
                border-radius:10px;padding:11px 16px;margin-bottom:16px;
                font-size:13px;color:#1DBD7F;display:flex;align-items:center;gap:8px;">
        <span>✓</span> {{ session('success') }}
    </div>
@endif
@if(session('info'))
    <div class="is-animate-rise is-stagger-1"
         style="background:rgba(8,145,178,0.08);border:1px solid rgba(8,145,178,0.22);
                border-radius:10px;padding:11px 16px;margin-bottom:16px;
                font-size:13px;color:#22B8D4;display:flex;align-items:center;gap:8px;">
        <span>ℹ</span> {{ session('info') }}
    </div>
@endif

{{-- ────────────────────────────────────────────────────
     FILTROS
──────────────────────────────────────────────────── --}}
<div class="is-filter-panel is-animate-rise is-stagger-1" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('casos.index') }}">

        {{-- Fila 1 --}}
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;
                    gap:12px;align-items:end;margin-bottom:12px;">
            <div>
                <div class="is-form-label">Buscar</div>
                <input type="text" name="buscar" class="is-input"
                       value="{{ request('buscar') }}"
                       placeholder="Nombre, apellido, cédula o número de caso">
            </div>
            <div>
                <div class="is-form-label">Aseguradora</div>
                <select name="aseguradora" class="is-select">
                    <option value="">Todas</option>
                    @foreach($aseguradoras as $aseg)
                        <option value="{{ $aseg }}"
                            {{ request('aseguradora') == $aseg ? 'selected' : '' }}>
                            {{ $aseg }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="is-form-label">Estado</div>
                <select name="estado" class="is-select">
                    <option value="">Todos</option>
                    @foreach($estados as $est)
                        <option value="{{ $est }}"
                            {{ request('estado') == $est ? 'selected' : '' }}>
                            {{ $est }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="is-form-label">Alerta</div>
                <select name="alerta" class="is-select">
                    <option value="">Todas</option>
                    @foreach($alertasDisponibles as $al)
                        <option value="{{ $al['valor'] }}"
                            {{ request('alerta') == $al['valor'] ? 'selected' : '' }}>
                            {{ $al['texto'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="is-btn-primary"
                        style="padding:10px 18px;font-size:12px;">
                    Filtrar
                </button>
                <a href="{{ route('casos.index') }}" class="is-btn-ghost"
                   style="padding:10px 12px;font-size:12px;">✕</a>
            </div>
        </div>

        {{-- Fila 2 --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr;
                    gap:12px;align-items:end;">
            <div>
                <div class="is-form-label">Fecha desde</div>
                <input type="date" name="fecha_desde" class="is-input"
                       value="{{ request('fecha_desde') }}">
            </div>
            <div>
                <div class="is-form-label">Fecha hasta</div>
                <input type="date" name="fecha_hasta" class="is-input"
                       value="{{ request('fecha_hasta') }}">
            </div>
            <div>
                <div class="is-form-label">Poder firmado</div>
                <select name="tiene_poder" class="is-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('tiene_poder')==='1' ? 'selected':'' }}>Sí</option>
                    <option value="0" {{ request('tiene_poder')==='0' ? 'selected':'' }}>No</option>
                </select>
            </div>
            <div>
                <div class="is-form-label">Contrato firmado</div>
                <select name="tiene_contrato" class="is-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('tiene_contrato')==='1' ? 'selected':'' }}>Sí</option>
                    <option value="0" {{ request('tiene_contrato')==='0' ? 'selected':'' }}>No</option>
                </select>
            </div>
            <div>
                <div class="is-form-label">Alta ortopedia</div>
                <select name="alta_ortopedia" class="is-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('alta_ortopedia')==='1' ? 'selected':'' }}>Sí</option>
                    <option value="0" {{ request('alta_ortopedia')==='0' ? 'selected':'' }}>No</option>
                </select>
            </div>
        </div>

        {{-- Fila 3 --}}
        <div style="display:grid;grid-template-columns:1fr 4fr;gap:12px;margin-top:12px;">
            <div>
                <div class="is-form-label">FURPEN</div>
                <select name="furpen_completo" class="is-select">
                    <option value="">Todos</option>
                    <option value="1" {{ request('furpen_completo')==='1' ? 'selected':'' }}>Completo</option>
                    <option value="0" {{ request('furpen_completo')==='0' ? 'selected':'' }}>Pendiente</option>
                </select>
            </div>
            <div></div>
        </div>

        <input type="hidden" name="sort"      value="{{ request('sort','id') }}">
        <input type="hidden" name="direction" value="{{ request('direction','desc') }}">
    </form>
</div>

{{-- ── Leyenda de prioridades ── --}}
<div class="is-priority-row is-animate-rise is-stagger-2"
     style="margin-bottom:16px;padding:12px 16px;
            background:var(--bg-card);border:1px solid var(--border);
            border-radius:10px;">
    <span style="font-size:10px;font-weight:700;color:var(--text-3);
                 letter-spacing:.8px;text-transform:uppercase;margin-right:4px;">
        Prioridades:
    </span>
    <span class="is-pill is-pill-red">
        <span style="width:6px;height:6px;border-radius:50%;
                     background:#E53935;flex-shrink:0;display:inline-block;
                     box-shadow:0 0 5px #E53935;"></span>
        Crítica / Tutela / Prescripción
    </span>
    <span class="is-pill is-pill-amber">
        <span style="width:6px;height:6px;border-radius:50%;
                     background:#F59E0B;flex-shrink:0;display:inline-block;"></span>
        Documentos pendientes
    </span>
    <span class="is-pill is-pill-teal">
        <span style="width:6px;height:6px;border-radius:50%;
                     background:#0891B2;flex-shrink:0;display:inline-block;"></span>
        Seguimiento / Junta / Cobro
    </span>
    <span class="is-pill is-pill-green">
        <span style="width:6px;height:6px;border-radius:50%;
                     background:#059669;flex-shrink:0;display:inline-block;"></span>
        Pagado
    </span>
    <span class="is-pill"
          style="background:var(--bg-input);color:var(--text-2);
                 border:1.5px solid var(--border-2);">
        <span style="width:6px;height:6px;border-radius:50%;
                     background:var(--text-3);flex-shrink:0;display:inline-block;"></span>
        Normal
    </span>
</div>

{{-- ────────────────────────────────────────────────────
     TABLA
──────────────────────────────────────────────────── --}}
<div class="is-table-wrap is-table is-animate-rise is-stagger-3">

    {{-- Toolbar --}}
    <div class="is-table-header">
        <div style="font-size:13px;color:var(--text-2);">
            Mostrando
            <strong style="color:var(--text-1);font-weight:600;">
                {{ $casos->total() }}
            </strong>
            casos
        </div>
        <div style="display:flex;gap:6px;">
            <div style="width:30px;height:30px;border-radius:7px;
                        border:1px solid var(--border-2);background:var(--bg-input);
                        display:flex;align-items:center;justify-content:center;
                        cursor:pointer;color:var(--text-2);font-size:12px;"
                 title="Exportar">
                ↓
            </div>
        </div>
    </div>

    <div class="tabla-scroll">
        <table>
            <thead>
                <tr>
                    <th class="col-prio">Prioridad</th>
                    <th class="col-num">
                        <a href="{{ route('casos.index', array_merge(request()->query(),
                            ['sort'=>'numero_caso',
                             'direction'=>(($sort==='numero_caso'&&$direction==='asc')?'desc':'asc')])) }}"
                           style="color:var(--text-3);text-decoration:none;">
                            Número
                            @if($sort==='numero_caso')
                                {{ $direction==='asc' ? '↑' : '↓' }}
                            @endif
                        </a>
                    </th>
                    <th class="col-vic">
                        <a href="{{ route('casos.index', array_merge(request()->query(),
                            ['sort'=>'nombres',
                             'direction'=>(($sort==='nombres'&&$direction==='asc')?'desc':'asc')])) }}"
                           style="color:var(--text-3);text-decoration:none;">
                            Víctima
                            @if($sort==='nombres'){{ $direction==='asc' ? '↑' : '↓' }}@endif
                        </a>
                    </th>
                    <th class="col-doc">
                        <a href="{{ route('casos.index', array_merge(request()->query(),
                            ['sort'=>'cedula',
                             'direction'=>(($sort==='cedula'&&$direction==='asc')?'desc':'asc')])) }}"
                           style="color:var(--text-3);text-decoration:none;">
                            Cédula
                            @if($sort==='cedula'){{ $direction==='asc' ? '↑' : '↓' }}@endif
                        </a>
                    </th>
                    <th class="col-aseg">
                        <a href="{{ route('casos.index', array_merge(request()->query(),
                            ['sort'=>'aseguradora',
                             'direction'=>(($sort==='aseguradora'&&$direction==='asc')?'desc':'asc')])) }}"
                           style="color:var(--text-3);text-decoration:none;">
                            Aseguradora
                            @if($sort==='aseguradora'){{ $direction==='asc' ? '↑' : '↓' }}@endif
                        </a>
                    </th>
                    <th class="col-est">
                        <a href="{{ route('casos.index', array_merge(request()->query(),
                            ['sort'=>'estado',
                             'direction'=>(($sort==='estado'&&$direction==='asc')?'desc':'asc')])) }}"
                           style="color:var(--text-3);text-decoration:none;">
                            Estado
                            @if($sort==='estado'){{ $direction==='asc' ? '↑' : '↓' }}@endif
                        </a>
                    </th>
                    <th class="col-pcl">PCL</th>
                    <th class="col-val">V. Estimado</th>
                    <th class="col-val">V. Pagado</th>
                    <th>% Honor.</th>
                    <th class="col-val">Gan. Equipo</th>
                    <th class="col-val">Neto cliente</th>
                    <th>Avance</th>
                    <th>
                        <a href="{{ route('casos.index', array_merge(request()->query(),
                            ['sort'=>'junta_asignada',
                             'direction'=>(($sort==='junta_asignada'&&$direction==='asc')?'desc':'asc')])) }}"
                           style="color:var(--text-3);text-decoration:none;">
                            Junta
                            @if($sort==='junta_asignada'){{ $direction==='asc' ? '↑' : '↓' }}@endif
                        </a>
                    </th>
                    <th class="col-acc">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($casos as $caso)
                @php
                    $rowClass = match($caso->color_alerta) {
                        'red'         => 'row-red',
                        'orange'      => 'row-orange',
                        'cyan','blue' => 'row-blue',
                        'green'       => 'row-green',
                        default       => '',
                    };
                    $badgeClass = match($caso->color_alerta) {
                        'red'         => 'is-badge is-badge-red',
                        'orange'      => 'is-badge is-badge-amber',
                        'cyan','blue' => 'is-badge is-badge-teal',
                        'green'       => 'is-badge is-badge-green',
                        default       => 'is-badge is-badge-gray',
                    };
                    $diasPrescripcion = method_exists($caso,'diasParaPrescripcion')
                        ? $caso->diasParaPrescripcion() : null;
                @endphp

                <tr class="{{ $rowClass }}">

                    {{-- Prioridad --}}
                    <td>
                        <span class="{{ $badgeClass }}" style="font-size:10px;">
                            {{ $caso->texto_alerta }}
                        </span>
                    </td>

                    {{-- Número --}}
                    <td>
                        <strong style="font-family:'Playfair Display',serif;
                                       color:var(--text-1);font-size:13px;">
                            {{ $caso->numero_caso }}
                        </strong>
                        <div style="margin-top:4px;">
                            @if($caso->estaPrescrito())
                                <span class="mini mini-danger">Prescrito</span>
                            @elseif($diasPrescripcion !== null)
                                <span class="mini {{ $diasPrescripcion <= 90 ? 'mini-danger' : 'mini-muted' }}">
                                    Prescripción: {{ $diasPrescripcion }}d
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Víctima --}}
                    <td style="line-height:1.5;">
                        <strong style="display:block;color:var(--text-1);font-size:13px;">
                            {{ $caso->nombres }} {{ $caso->apellidos }}
                        </strong>
                        <div style="margin-top:5px;">
                            <span class="mini {{ $caso->tiene_poder ? 'mini-ok' : 'mini-warn' }}">
                                Poder {{ $caso->tiene_poder ? '✓' : 'Pend.' }}
                            </span>
                            <span class="mini {{ $caso->tiene_contrato ? 'mini-ok' : 'mini-warn' }}">
                                Contrato {{ $caso->tiene_contrato ? '✓' : 'Pend.' }}
                            </span>
                        </div>
                        <div style="margin-top:2px;">
                            <span class="mini {{ $caso->alta_ortopedia ? 'mini-ok' : 'mini-info' }}">
                                Alta ortop. {{ $caso->alta_ortopedia ? '✓' : 'Pend.' }}
                            </span>
                            <span class="mini {{ $caso->furpen_completo ? 'mini-ok' : 'mini-info' }}">
                                FURPEN {{ $caso->furpen_completo ? '✓' : 'Pend.' }}
                            </span>
                        </div>
                        @if(!empty($caso->tipo_respuesta_aseguradora))
                            @php
                                $textoTipoResp = match($caso->tipo_respuesta_aseguradora) {
                                    'emitio_dictamen' => 'Dictamen emitido',
                                    'nego'            => 'Negó solicitud',
                                    'no_respondio'    => 'No respondió',
                                    default           => $caso->tipo_respuesta_aseguradora,
                                };
                            @endphp
                            <div style="margin-top:2px;">
                                <span class="mini mini-muted">{{ $textoTipoResp }}</span>
                            </div>
                        @endif
                        @if(!empty($caso->tipo_tutela))
                            <div style="margin-top:2px;">
                                <span class="mini mini-purple">
                                    {{ $caso->tipo_tutela === 'tutela_calificacion'
                                        ? 'Tutela calificación'
                                        : 'Tutela debido proceso' }}
                                </span>
                            </div>
                        @endif
                        @if(!empty($caso->resultado_fallo_tutela))
                            <div style="margin-top:2px;">
                                <span class="mini mini-muted">
                                    Fallo: {{ ucfirst($caso->resultado_fallo_tutela) }}
                                </span>
                            </div>
                        @endif
                        @if(!empty($caso->resultado_fallo_segunda_instancia))
                            <div style="margin-top:2px;">
                                <span class="mini {{ $caso->resultado_fallo_segunda_instancia === 'revoca' ? 'mini-ok' : 'mini-danger' }}">
                                    2ª inst.: {{ ucfirst($caso->resultado_fallo_segunda_instancia) }}
                                </span>
                            </div>
                        @endif
                    </td>

                    {{-- Cédula --}}
                    <td style="font-size:12px;color:var(--text-3);">
                        {{ $caso->cedula }}
                    </td>

                    {{-- Aseguradora --}}
                    <td style="font-size:13px;font-weight:500;color:var(--text-1);">
                        {{ $caso->aseguradora ?: '—' }}
                    </td>

                    {{-- Estado --}}
                    <td>
                        <span class="is-badge is-badge-cobalt"
                              style="font-size:10px;white-space:nowrap;">
                            {{ $caso->estado ?: 'N/A' }}
                        </span>
                    </td>

                    {{-- PCL --}}
                    <td>
                        @if($caso->porcentaje_pcl)
                            <div class="is-pcl-wrap">
                                {{ $caso->porcentaje_pcl }}%
                                <div class="is-pcl-track">
                                    <div class="is-pcl-fill"
                                         style="width:{{ min($caso->porcentaje_pcl,100) }}%;
                                                background:#1B4FFF;">
                                    </div>
                                </div>
                            </div>
                        @else
                            <span style="color:var(--text-3);font-size:12px;">—</span>
                        @endif
                    </td>

                    {{-- Valor estimado --}}
                    <td style="font-family:'Playfair Display',serif;font-weight:700;
                               color:#D4AA48;font-size:13px;white-space:nowrap;">
                        {{ $caso->valor_estimado
                            ? '$'.number_format($caso->valor_estimado,0,',','.')
                            : '—' }}
                    </td>

                    {{-- Valor pagado --}}
                    <td style="font-family:'Playfair Display',serif;font-weight:700;
                               color:#1DBD7F;font-size:13px;white-space:nowrap;">
                        {{ $caso->valor_pagado
                            ? '$'.number_format($caso->valor_pagado,0,',','.')
                            : '—' }}
                    </td>

                    {{-- % Honorarios --}}
                    <td style="font-weight:600;color:var(--text-1);font-size:13px;">
                        {{ $caso->porcentaje_honorarios
                            ? number_format($caso->porcentaje_honorarios,0,',','.').'%'
                            : '—' }}
                    </td>

                    {{-- Ganancia equipo --}}
                    <td style="font-family:'Playfair Display',serif;font-weight:700;
                               color:#4B78FF;font-size:13px;white-space:nowrap;">
                        {{ $caso->ganancia_equipo
                            ? '$'.number_format($caso->ganancia_equipo,0,',','.')
                            : '—' }}
                    </td>

                    {{-- Neto cliente --}}
                    <td style="font-family:'Playfair Display',serif;font-weight:700;
                               color:#D4AA48;font-size:13px;white-space:nowrap;">
                        {{ $caso->valor_neto_cliente
                            ? '$'.number_format($caso->valor_neto_cliente,0,',','.')
                            : '—' }}
                    </td>

                    {{-- Avance --}}
                    <td>
                        <div class="is-pcl-wrap">
                            {{ $caso->porcentaje_avance ?? 0 }}%
                            <div class="is-pcl-track">
                                <div class="is-pcl-fill"
                                     style="width:{{ $caso->porcentaje_avance ?? 0 }}%;
                                            background:#059669;">
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Junta --}}
                    <td style="font-size:12px;color:var(--text-2);
                               max-width:220px;line-height:1.35;">
                        {{ $caso->junta_asignada ?: '—' }}
                    </td>

                    {{-- ── ACCIONES ── --}}
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:5px;">

                            <a href="{{ route('casos.show', $caso) }}"
                               class="is-act-btn act-gray">Ver</a>

                            @if(auth()->user()->puedeEditar())
                                <a href="{{ route('casos.edit', $caso) }}"
                                   class="is-act-btn act-warn">Editar</a>
                            @endif

                            <a href="{{ route('casos.documentos.index', $caso) }}"
                               class="is-act-btn act-default">Expediente</a>

                            <a href="{{ route('casos.bitacoras.index', $caso) }}"
                               class="is-act-btn act-gray">Bitácora</a>

                            @if(auth()->user()->puedeAccionarFlujo())

                                {{-- Solicitud aseguradora --}}
                                @if(empty($caso->fecha_solicitud_aseguradora) && !$caso->requierePoderContrato() && !$caso->estaPrescrito())
                                    <button type="button"
                                            class="is-act-btn act-default btn-abrir-modal-fecha"
                                            data-action="{{ route('casos.marcarSolicitudAseguradora', $caso) }}"
                                            data-campo="fecha_solicitud_aseguradora"
                                            data-label="Fecha de solicitud"
                                            data-titulo="Registrar solicitud a aseguradora"
                                            data-boton="Guardar solicitud"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}">
                                        Solicitud
                                    </button>
                                @elseif(!empty($caso->fecha_solicitud_aseguradora))
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Solicitud ✓
                                    </button>
                                @else
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Falta poder/contrato
                                    </button>
                                @endif

                                {{-- Respuesta aseguradora --}}
                                @if(!empty($caso->fecha_solicitud_aseguradora) && empty($caso->tipo_respuesta_aseguradora))
                                    <button type="button"
                                            class="is-act-btn act-default btn-abrir-modal-respuesta"
                                            data-action="{{ route('casos.marcarRespuestaAseguradora', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}">
                                        Respuesta
                                    </button>
                                @elseif(!empty($caso->tipo_respuesta_aseguradora))
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Respuesta ✓
                                    </button>
                                @endif

                                {{-- Apelación --}}
                                @if($caso->tipo_respuesta_aseguradora === 'emitio_dictamen' && empty($caso->fecha_apelacion))
                                    <button type="button"
                                            class="is-act-btn act-warn btn-abrir-modal-fecha"
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
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Apelación ✓
                                    </button>
                                @endif

                                {{-- Tutela --}}
                                @if($caso->requiereTutela())
                                    <button type="button"
                                            class="is-act-btn act-warn btn-abrir-modal-tutela"
                                            data-action="{{ route('casos.marcarTutela', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}"
                                            data-tipo-respuesta="{{ $caso->tipo_respuesta_aseguradora }}">
                                        Tutela
                                    </button>
                                @elseif(!empty($caso->fecha_tutela))
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Tutela ✓
                                    </button>
                                @endif

                                {{-- Fallo tutela --}}
                                @if(!empty($caso->fecha_tutela) && empty($caso->fecha_fallo_tutela))
                                    <button type="button"
                                            class="is-act-btn act-danger btn-abrir-modal-fallo"
                                            data-action="{{ route('casos.marcarFalloTutela', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}"
                                            data-resultado="{{ $caso->resultado_fallo_tutela }}">
                                        Fallo tutela
                                    </button>
                                @elseif(!empty($caso->fecha_fallo_tutela))
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Fallo tutela ✓
                                    </button>
                                @endif

                                {{-- Cumplimiento tutela --}}
                                @if($caso->requiereCumplimientoTutela())
                                    <button type="button"
                                            class="is-act-btn act-success btn-abrir-modal-cumplimiento"
                                            data-action="{{ route('casos.marcarCumplimientoTutela', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}"
                                            data-tipo-tutela="{{ $caso->tipo_tutela }}">
                                        Cumplimiento
                                    </button>
                                @elseif(!empty($caso->fecha_cumplimiento_tutela))
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Cumplimiento ✓
                                    </button>
                                @endif

                                {{-- Desacato --}}
                                @if($caso->requiereIncidenteDesacato())
                                    <button type="button"
                                            class="is-act-btn act-danger btn-abrir-modal-fecha"
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
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Desacato ✓
                                    </button>
                                @endif

                                {{-- Impugnación --}}
                                @if($caso->requiereImpugnacion())
                                    <button type="button"
                                            class="is-act-btn act-warn btn-abrir-modal-fecha"
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
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Impugnación ✓
                                    </button>
                                @endif

                                {{-- Segunda instancia --}}
                                @if($caso->requiereSegundaInstancia())
                                    <button type="button"
                                            class="is-act-btn act-purple btn-abrir-modal-segunda"
                                            data-action="{{ route('casos.marcarFalloSegundaInstancia', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}">
                                        2ª instancia
                                    </button>
                                @elseif(!empty($caso->fecha_fallo_segunda_instancia))
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        2ª instancia ✓
                                    </button>
                                @endif

                                {{-- Honorarios --}}
                                @if(!empty($caso->fecha_apelacion) && empty($caso->fecha_pago_honorarios))
                                    <button type="button"
                                            class="is-act-btn act-default btn-abrir-modal-fecha"
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
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Honorarios ✓
                                    </button>
                                @endif

                                {{-- Alta ortopedia --}}
                                @if($caso->requiereAltaOrtopedia())
                                    <button type="button"
                                            class="is-act-btn act-teal btn-abrir-modal-alta"
                                            data-action="{{ route('casos.marcarAltaOrtopedia', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}">
                                        Alta ortopedia
                                    </button>
                                @elseif($caso->alta_ortopedia)
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Alta ortopedia ✓
                                    </button>
                                @endif

                                {{-- Junta --}}
                                @if($caso->requiereSolicitudJunta())
                                    <button type="button"
                                            class="is-act-btn act-gray btn-abrir-modal-fecha"
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
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Junta ✓
                                    </button>
                                @endif

                                {{-- Dictamen junta --}}
                                @if(!empty($caso->fecha_envio_junta) && empty($caso->fecha_dictamen_junta))
                                    <button type="button"
                                            class="is-act-btn act-default btn-abrir-modal-fecha"
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
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Dictamen ✓
                                    </button>
                                @endif

                                {{-- FURPEN --}}
                                @if($caso->requiereFurpen())
                                    <button type="button"
                                            class="is-act-btn act-teal btn-abrir-modal-furpen"
                                            data-action="{{ route('casos.marcarFurpen', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}">
                                        FURPEN
                                    </button>
                                @elseif($caso->furpen_completo)
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        FURPEN ✓
                                    </button>
                                @endif

                                {{-- Reclamación --}}
                                @if($caso->requiereCobroAseguradora())
                                    <button type="button"
                                            class="is-act-btn act-default btn-abrir-modal-reclamacion"
                                            data-action="{{ route('casos.marcarReclamacion', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}">
                                        Reclamar
                                    </button>
                                @elseif(!empty($caso->fecha_reclamacion_final))
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Reclamación ✓
                                    </button>
                                @endif

                                {{-- Pago --}}
                                @if($caso->requierePagoPendiente())
                                    <button type="button"
                                            class="is-act-btn act-success btn-abrir-modal-pago"
                                            data-action="{{ route('casos.marcarPago', $caso) }}"
                                            data-caso="{{ $caso->numero_caso }}"
                                            data-victima="{{ $caso->nombres }} {{ $caso->apellidos }}"
                                            data-fecha="{{ now()->toDateString() }}"
                                            data-honorarios="{{ $caso->porcentaje_honorarios }}">
                                        Pago
                                    </button>
                                @elseif(!empty($caso->fecha_pago_final))
                                    <button type="button" class="is-act-btn act-done" disabled>
                                        Pago ✓
                                    </button>
                                @endif

                            @endif {{-- fin puedeAccionarFlujo --}}

                            @if(auth()->user()->puedeEliminar())
                                <form class="inline"
                                      action="{{ route('casos.destroy', $caso) }}"
                                      method="POST"
                                      onsubmit="return confirm('¿Eliminar este caso permanentemente?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="is-act-btn act-danger">
                                        Eliminar
                                    </button>
                                </form>
                            @endif

                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="15"
                        style="text-align:center;padding:40px 20px;
                               color:var(--text-3);font-size:14px;">
                        No hay casos registrados con esos filtros.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Paginación --}}
<div class="is-pagination"
     style="margin-top:16px;padding:14px 18px;
            background:var(--bg-card);border:1px solid var(--border);
            border-radius:10px;">
    {{ $casos->links() }}
</div>

@endsection

{{-- ════════════════════════════════════════════════════════
     MODALES — lógica 100% intacta, solo estilos nuevos
════════════════════════════════════════════════════════ --}}
@push('scripts')

{{-- ── Modal: Fecha genérica ── --}}
<div class="is-modal-overlay" id="modalFechaOverlay">
    <div class="is-modal">
        <div class="is-modal-title" id="modalFechaTitulo">Registrar fecha</div>
        <div class="is-modal-sub">
            <strong id="modalFechaCaso"></strong><br>
            <span id="modalFechaVictima"></span>
        </div>
        <form id="modalFechaForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label id="modalFechaLabel">Fecha</label>
                    <input type="date" id="modal_fecha_generica"
                           class="is-input" required>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalFecha">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary" id="modalFechaBoton">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Respuesta aseguradora ── --}}
<div class="is-modal-overlay" id="modalRespuestaOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar respuesta de aseguradora</div>
        <div class="is-modal-sub">
            <strong id="modalRespuestaCaso"></strong><br>
            <span id="modalRespuestaVictima"></span>
        </div>
        <form id="modalRespuestaForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Tipo de respuesta <span style="color:#F26F6F">*</span></label>
                    <select name="tipo_respuesta_aseguradora" id="modal_tipo_respuesta"
                            class="is-select" required onchange="toggleFechaRespuesta()">
                        <option value="">— Seleccionar —</option>
                        <option value="emitio_dictamen">Emitió dictamen (calificó)</option>
                        <option value="nego">Negó la solicitud</option>
                        <option value="no_respondio">No respondió (pasó 1 mes)</option>
                    </select>
                    <p class="is-modal-hint">
                        Emitió dictamen → flujo de apelación.<br>
                        Negó / No respondió → tutela para calificación.
                    </p>
                </div>
                <div id="bloque_modal_fecha_respuesta">
                    <label>Fecha de respuesta / dictamen</label>
                    <input type="date" name="fecha_respuesta_aseguradora"
                           id="modal_fecha_respuesta" class="is-input">
                    <p class="is-modal-hint">
                        Dejar vacío si la respuesta fue "no respondió".
                    </p>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalRespuesta">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">
                    Guardar respuesta
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Tutela ── --}}
<div class="is-modal-overlay" id="modalTutelaOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar tutela</div>
        <div class="is-modal-sub">
            <strong id="modalTutelaCaso"></strong><br>
            <span id="modalTutelaVictima"></span>
        </div>
        <form id="modalTutelaForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Fecha de tutela <span style="color:#F26F6F">*</span></label>
                    <input type="date" name="fecha_tutela" id="modal_fecha_tutela"
                           class="is-input" required>
                </div>
                <div>
                    <label>Tipo de tutela <span style="color:#F26F6F">*</span></label>
                    <select name="tipo_tutela" id="modal_tipo_tutela"
                            class="is-select" required>
                        <option value="">— Seleccionar —</option>
                        <option value="tutela_calificacion">
                            Para calificación (aseguradora negó o no respondió)
                        </option>
                        <option value="tutela_debido_proceso">
                            Por debido proceso (no pagan honorarios)
                        </option>
                    </select>
                    <p class="is-modal-hint">
                        <strong style="color:var(--text-1)">Calificación</strong>:
                        aseguradora negó o no respondió.<br>
                        <strong style="color:var(--text-1)">Debido proceso</strong>:
                        apelaron pero no pagan honorarios.
                    </p>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalTutela">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">Guardar tutela</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Fallo tutela ── --}}
<div class="is-modal-overlay" id="modalFalloOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar fallo de tutela</div>
        <div class="is-modal-sub">
            <strong id="modalFalloCaso"></strong><br>
            <span id="modalFalloVictima"></span>
        </div>
        <form id="modalFalloForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Fecha fallo de tutela <span style="color:#F26F6F">*</span></label>
                    <input type="date" name="fecha_fallo_tutela"
                           id="modal_fecha_fallo_tutela" class="is-input" required>
                </div>
                <div>
                    <label>Resultado del fallo <span style="color:#F26F6F">*</span></label>
                    <select name="resultado_fallo_tutela" id="modal_resultado_fallo_tutela"
                            class="is-select" required>
                        <option value="">— Seleccionar —</option>
                        <option value="concedido">Concedido → esperar cumplimiento</option>
                        <option value="negado">Negado → impugnar</option>
                        <option value="parcial">Parcial</option>
                    </select>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalFallo">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">Guardar fallo</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Cumplimiento tutela ── --}}
<div class="is-modal-overlay" id="modalCumplimientoOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar cumplimiento del fallo</div>
        <div class="is-modal-sub">
            <strong id="modalCumplimientoCaso"></strong><br>
            <span id="modalCumplimientoVictima"></span>
        </div>
        <form id="modalCumplimientoForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Fecha de cumplimiento <span style="color:#F26F6F">*</span></label>
                    <input type="date" name="fecha_cumplimiento_tutela"
                           id="modal_fecha_cumplimiento" class="is-input" required>
                </div>
                <div>
                    <label>Tipo de cumplimiento <span style="color:#F26F6F">*</span></label>
                    <select name="tipo_cumplimiento_tutela" id="modal_tipo_cumplimiento"
                            class="is-select" required>
                        <option value="">— Seleccionar —</option>
                        <option value="voluntario">
                            Voluntario (cumplió dentro de las 2 semanas)
                        </option>
                        <option value="desacato">Tras incidente de desacato</option>
                    </select>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalCumplimiento">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">Guardar cumplimiento</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Segunda instancia ── --}}
<div class="is-modal-overlay" id="modalSegundaOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar fallo de segunda instancia</div>
        <div class="is-modal-sub">
            <strong id="modalSegundaCaso"></strong><br>
            <span id="modalSegundaVictima"></span>
        </div>
        <form id="modalSegundaForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Fecha del fallo <span style="color:#F26F6F">*</span></label>
                    <input type="date" name="fecha_fallo_segunda_instancia"
                           id="modal_fecha_segunda" class="is-input" required>
                </div>
                <div>
                    <label>Resultado <span style="color:#F26F6F">*</span></label>
                    <select name="resultado_fallo_segunda_instancia"
                            id="modal_resultado_segunda" class="is-select" required
                            onchange="mostrarHintSegunda()">
                        <option value="">— Seleccionar —</option>
                        <option value="confirma">Confirma — el caso se pierde</option>
                        <option value="revoca">Revoca — la aseguradora debe cumplir</option>
                    </select>
                    <p class="is-modal-hint" id="hint_segunda">
                        Confirma: caso cerrado. Revoca: aseguradora debe cumplir.
                    </p>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalSegunda">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">Guardar fallo</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Pago final ── --}}
<div class="is-modal-overlay" id="modalPagoOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar pago final</div>
        <div class="is-modal-sub">
            <strong id="modalPagoCaso"></strong><br>
            <span id="modalPagoVictima"></span>
        </div>
        <form id="modalPagoForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Valor pagado</label>
                    <input type="number" step="0.01" min="0" name="valor_pagado"
                           id="modal_valor_pagado" class="is-input" required>
                </div>
                <div>
                    <label>Fecha de pago</label>
                    <input type="date" name="fecha_pago_final"
                           id="modal_fecha_pago_final" class="is-input" required>
                </div>
                <div>
                    <label>Honorarios equipo</label>
                    <select name="porcentaje_honorarios"
                            id="modal_porcentaje_honorarios" class="is-select">
                        <option value="">Seleccionar</option>
                        <option value="40">40%</option>
                        <option value="50">50%</option>
                    </select>
                </div>
                <div>
                    <label>Observación</label>
                    <textarea name="observacion_pago" id="modal_observacion_pago"
                              class="is-textarea"
                              placeholder="Ej: pago por transferencia..."></textarea>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalPago">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-gold">Guardar pago</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Reclamación ── --}}
<div class="is-modal-overlay" id="modalReclamacionOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar reclamación final</div>
        <div class="is-modal-sub">
            <strong id="modalReclamacionCaso"></strong><br>
            <span id="modalReclamacionVictima"></span>
        </div>
        <form id="modalReclamacionForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Valor reclamado</label>
                    <input type="number" step="0.01" min="0" name="valor_reclamado"
                           id="modal_valor_reclamado" class="is-input" required>
                </div>
                <div>
                    <label>Fecha de reclamación</label>
                    <input type="date" name="fecha_reclamacion_final"
                           id="modal_fecha_reclamacion_final" class="is-input" required>
                </div>
                <div>
                    <label>Observación</label>
                    <textarea name="observacion_reclamacion"
                              id="modal_observacion_reclamacion" class="is-textarea"
                              placeholder="Ej: radicado generado..."></textarea>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalReclamacion">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">Guardar reclamación</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Alta ortopedia ── --}}
<div class="is-modal-overlay" id="modalAltaOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar alta por ortopedia</div>
        <div class="is-modal-sub">
            <strong id="modalAltaCaso"></strong><br>
            <span id="modalAltaVictima"></span>
        </div>
        <form id="modalAltaForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Fecha alta ortopedia</label>
                    <input type="date" name="fecha_alta_ortopedia"
                           id="modal_fecha_alta_ortopedia" class="is-input" required>
                </div>
                <div>
                    <label>Observación</label>
                    <textarea name="observacion_alta_ortopedia"
                              id="modal_observacion_alta_ortopedia" class="is-textarea"
                              placeholder="Ej: alta emitida por especialista..."></textarea>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalAlta">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">Guardar alta</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: FURPEN ── --}}
<div class="is-modal-overlay" id="modalFurpenOverlay">
    <div class="is-modal">
        <div class="is-modal-title">Registrar FURPEN completo</div>
        <div class="is-modal-sub">
            <strong id="modalFurpenCaso"></strong><br>
            <span id="modalFurpenVictima"></span>
        </div>
        <form id="modalFurpenForm" method="POST">
            @csrf
            <div class="is-modal-grid">
                <div>
                    <label>Fecha FURPEN recibido</label>
                    <input type="date" name="fecha_furpen_recibido"
                           id="modal_fecha_furpen_recibido" class="is-input" required>
                </div>
                <div>
                    <label>Observación</label>
                    <textarea name="observacion_furpen" id="modal_observacion_furpen"
                              class="is-textarea"
                              placeholder="Ej: documentación completa..."></textarea>
                </div>
            </div>
            <div class="is-modal-actions">
                <button type="button" class="is-btn-ghost" id="cerrarModalFurpen">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">Guardar FURPEN</button>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     JAVASCRIPT — 100% intacto del original
════════════════════════════════════════════════════════ --}}
<script>
function initModal(overlayId, cerrarId) {
    const overlay = document.getElementById(overlayId);
    const cerrar  = document.getElementById(cerrarId);
    if (!overlay || !cerrar) return;
    function cerrar_() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    cerrar.addEventListener('click', cerrar_);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) cerrar_();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) cerrar_();
    });
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
                hidden.type  = 'hidden';
                hidden.name  = campo;
                hidden.classList.add('hidden-dynamic');
                hidden.value = inputFecha.value;
                form.appendChild(hidden);
                inputFecha.oninput = () => hidden.value = inputFecha.value;
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modal_tipo_respuesta').value        = '';
                document.getElementById('modal_fecha_respuesta').value       = btn.dataset.fecha || '';
                toggleFechaRespuesta();
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modal_fecha_tutela').value       = btn.dataset.fecha   || '';
                const sel   = document.getElementById('modal_tipo_tutela');
                const tipoR = btn.dataset.tipoRespuesta || '';
                if (tipoR === 'nego' || tipoR === 'no_respondio') sel.value = 'tutela_calificacion';
                else if (tipoR === 'emitio_dictamen')             sel.value = 'tutela_debido_proceso';
                else                                              sel.value = '';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modalFalloCaso').textContent            = btn.dataset.caso      || '';
                document.getElementById('modalFalloVictima').textContent          = btn.dataset.victima   || '';
                document.getElementById('modal_fecha_fallo_tutela').value         = btn.dataset.fecha     || '';
                document.getElementById('modal_resultado_fallo_tutela').value     = btn.dataset.resultado || '';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modal_fecha_cumplimiento').value       = btn.dataset.fecha   || '';
                document.getElementById('modal_tipo_cumplimiento').value        = '';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modal_fecha_segunda').value       = btn.dataset.fecha   || '';
                document.getElementById('modal_resultado_segunda').value   = '';
                mostrarHintSegunda();
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modalPagoCaso').textContent             = btn.dataset.caso       || '';
                document.getElementById('modalPagoVictima').textContent          = btn.dataset.victima    || '';
                document.getElementById('modal_fecha_pago_final').value          = btn.dataset.fecha      || '';
                document.getElementById('modal_valor_pagado').value              = '';
                document.getElementById('modal_porcentaje_honorarios').value     = btn.dataset.honorarios || '';
                document.getElementById('modal_observacion_pago').value          = '';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modal_fecha_reclamacion_final').value = btn.dataset.fecha   || '';
                document.getElementById('modal_valor_reclamado').value         = '';
                document.getElementById('modal_observacion_reclamacion').value = '';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modalAltaCaso').textContent                 = btn.dataset.caso    || '';
                document.getElementById('modalAltaVictima').textContent              = btn.dataset.victima || '';
                document.getElementById('modal_fecha_alta_ortopedia').value          = btn.dataset.fecha   || '';
                document.getElementById('modal_observacion_alta_ortopedia').value    = '';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
                document.getElementById('modalFurpenCaso').textContent           = btn.dataset.caso    || '';
                document.getElementById('modalFurpenVictima').textContent        = btn.dataset.victima || '';
                document.getElementById('modal_fecha_furpen_recibido').value     = btn.dataset.fecha   || '';
                document.getElementById('modal_observacion_furpen').value        = '';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
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
    const val  = document.getElementById('modal_resultado_segunda').value;
    const hint = document.getElementById('hint_segunda');
    if (val === 'confirma') {
        hint.innerHTML = '<strong style="color:#F26F6F">Caso cerrado desfavorablemente — no hay más acciones jurídicas.</strong>';
    } else if (val === 'revoca') {
        hint.innerHTML = '<strong style="color:#1DBD7F">La aseguradora debe cumplir lo ordenado — registrar cumplimiento y continuar flujo.</strong>';
    } else {
        hint.innerHTML = 'Confirma: el caso queda cerrado. Revoca: la aseguradora debe calificar o pagar honorarios.';
    }
}
</script>
@endpush
