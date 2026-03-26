@extends('layouts.app')

@section('title', 'Bitácora del Caso')

@section('content')
<style>
/* ── Timeline de bitácora ── */
.is-bitacora-timeline {
    position:relative;
    padding-left:32px;
}
.is-bitacora-timeline::before {
    content:'';
    position:absolute;
    left:10px;
    top:0;
    bottom:0;
    width:2px;
    background:var(--border);
}
.is-bitacora-item {
    position:relative;
    margin-bottom:24px;
}
.is-bitacora-item::before {
    content:'';
    position:absolute;
    left:-22px;
    top:8px;
    width:12px;
    height:12px;
    border-radius:50%;
    background:var(--bg-card);
    border:2px solid var(--cobalt-glow,rgba(27,79,255,0.4));
}
.is-bitacora-date {
    font-size:11px;
    color:var(--text-3);
    margin-bottom:6px;
    font-weight:600;
    letter-spacing:0.5px;
}
.is-bitacora-content {
    background:var(--bg-input);
    border:1px solid var(--border);
    border-radius:8px;
    padding:14px 16px;
    font-size:13px;
    line-height:1.5;
    color:var(--text-1);
}
.is-bitacora-empty {
    text-align:center;
    padding:40px 20px;
    color:var(--text-3);
    font-style:italic;
    font-size:14px;
}
</style>

{{-- ── Cabecera ── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:center;gap:14px;margin-bottom:28px;">
    <a href="{{ route('casos.show', $caso->id) }}"
       style="width:38px;height:38px;border-radius:6px;
              border:1px solid var(--border-2);background:var(--bg-input);
              display:flex;align-items:center;justify-content:center;
              color:var(--text-2);font-size:18px;text-decoration:none;
              transition:all .2s;flex-shrink:0;"
       onmouseover="this.style.background='var(--bg-hover)';this.style.color='var(--text-1)'"
       onmouseout="this.style.background='var(--bg-input)';this.style.color='var(--text-2)'">
        ←
    </a>
    <div>
        <div class="is-page-title">Bitácora del Caso</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:3px;">
            Caso #{{ $caso->id }} — {{ $caso->nombres }} {{ $caso->apellidos }}
        </div>
    </div>
</div>

{{-- ── Info del caso ── --}}
<div class="is-animate-rise is-stagger-1"
     style="background:var(--bg-card);border:1px solid var(--border);
            border-radius:8px;padding:16px 18px;margin-bottom:20px;
            display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <div>
            <div style="font-size:11px;color:var(--text-3);margin-bottom:2px;">
                Estado actual
            </div>
            <div style="font-size:14px;font-weight:600;color:var(--text-1);">
                {{ $caso->estado }}
            </div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <span style="display:inline-flex;align-items:center;
                     padding:4px 10px;border-radius:999px;font-size:11px;
                     font-weight:600;background:var(--cobalt-glow,rgba(27,79,255,0.12));
                     color:#4B78FF;">
            #{{ $caso->id }}
        </span>
    </div>
</div>

{{-- ── Formulario agregar entrada ── --}}
@if(auth()->user()->puedeAccionarFlujo())
    <div class="is-animate-rise is-stagger-1"
         style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:8px;padding:20px;margin-bottom:24px;">
        <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;">
            Agregar entrada a bitácora
        </div>
        <form method="POST" action="{{ route('casos.bitacoras.store', $caso->id) }}">
            @csrf
            <div class="is-form-section">
                <div class="is-form-label">Título del movimiento</div>
                <input type="text" name="titulo" class="is-input"
                       placeholder="Ej: Documento recibido, Llamada realizada, etc."
                       required>
            </div>
            <div class="is-form-section">
                <div class="is-form-label">Descripción del evento</div>
                <textarea name="descripcion" class="is-textarea" rows="4"
                          placeholder="Describe la acción realizada, observación o evento importante..."></textarea>
            </div>
            <div class="is-form-section">
                <div class="is-form-label">Fecha del evento</div>
                <input type="date" name="fecha_evento" class="is-input">
            </div>
            <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;">
                <button type="submit" class="is-btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                        <path d="M8 2v6M4 6h8M2 10h12l-1 4H3l-1-4z" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    Agregar a bitácora
                </button>
            </div>
        </form>
    </div>
@endif

{{-- ── Timeline de eventos ── --}}
<div class="is-animate-rise is-stagger-2"
     style="background:var(--bg-card);border:1px solid var(--border);
            border-radius:8px;padding:20px;">
    <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:20px;">
        Historial de eventos
    </div>
    
    @if($bitacoras->count() > 0)
        <div class="is-bitacora-timeline">
            @foreach($bitacoras as $bitacora)
                <div class="is-bitacora-item">
                    <div class="is-bitacora-date">
                        {{ $bitacora->created_at->format('d/m/Y H:i') }} — {{ $bitacora->user->name }}
                    </div>
                    @if($bitacora->titulo)
                        <div style="font-weight:600;color:var(--text-1);margin-bottom:6px;">
                            {{ $bitacora->titulo }}
                        </div>
                    @endif
                    <div class="is-bitacora-content">
                        {{ $bitacora->descripcion }}
                    </div>
                    @if(auth()->user()->puedeAccionarFlujo())
                        <form method="POST" action="{{ route('casos.bitacoras.destroy', [$caso->id, $bitacora->id]) }}" 
                              style="margin-top:10px;display:inline-block;"
                              onsubmit="return confirm('¿Eliminar esta entrada de la bitácora?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="is-btn-danger" style="font-size:11px;padding:6px 10px;">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" style="margin-right:4px;">
                                    <path d="M2 4h12M5 4V3a1 1 0 011-1h4a1 1 0 011 1v1M7 8v4M4 4v8a2 2 0 002 2h4a2 2 0 002-2V4" 
                                          stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                Eliminar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
        <div class="is-bitacora-empty">
            <svg width="48" height="48" viewBox="0 0 16 16" fill="none" style="opacity:0.2;margin-bottom:12px;">
                <path d="M3 8h10M8 3v10" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            No hay eventos registrados en la bitácora de este caso.
        </div>
    @endif
</div>
@endsection