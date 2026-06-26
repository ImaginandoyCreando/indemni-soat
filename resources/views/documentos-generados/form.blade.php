@extends('layouts.app')

@section('title', 'Generar ' . $nombreTipo)

@section('content')

<style>
.is-gen-field {
    margin-bottom: 14px;
}
.is-gen-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-3);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.is-gen-label .auto-tag {
    font-size: 9px;
    padding: 1px 6px;
    border-radius: 20px;
    background: rgba(5,150,105,.12);
    color: #1DBD7F;
    font-weight: 700;
    letter-spacing: .3px;
    text-transform: uppercase;
}
.is-gen-label .manual-tag {
    font-size: 9px;
    padding: 1px 6px;
    border-radius: 20px;
    background: rgba(245,158,11,.12);
    color: #F5B942;
    font-weight: 700;
    letter-spacing: .3px;
    text-transform: uppercase;
}
.is-gen-select {
    width: 100%;
    background: var(--bg-input);
    border: 1px solid var(--border-2);
    border-radius: 7px;
    padding: 8px 12px;
    font-size: 13px;
    color: var(--text-1);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 30px;
    cursor: pointer;
    transition: border-color .15s;
}
.is-gen-select:focus {
    border-color: #4B78FF;
    box-shadow: 0 0 0 3px rgba(75,120,255,.12);
}
.is-gen-select.is-auto {
    background-color: rgba(5,150,105,.05);
    border-color: rgba(5,150,105,.25);
}
.is-prev-doc {
    background: var(--bg-input);
    border: 1px solid var(--border-2);
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    font-size: 12px;
    color: var(--text-2);
    margin-bottom: 6px;
}
</style>

{{-- Cabecera --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;
            margin-bottom:24px;gap:14px;flex-wrap:wrap;" class="is-animate-rise">
    <div>
        <div class="is-page-title">Generar documento</div>
        <div style="display:flex;align-items:center;gap:10px;margin-top:6px;flex-wrap:wrap;">
            <span style="font-family:'Playfair Display',serif;font-size:15px;
                         font-weight:700;color:#4B78FF;">
                {{ $caso->numero_caso }} — {{ $caso->nombre_completo }}
            </span>
        </div>
        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">
            {{ $nombreTipo }}
        </div>
    </div>
    <a href="{{ route('casos.show', $caso) }}" class="is-btn-ghost">← Volver al caso</a>
</div>

@if(!$plantilla)
    {{-- Sin plantilla --}}
    <div style="background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.25);
                border-radius:10px;padding:24px;text-align:center;" class="is-animate-rise">
        <div style="font-size:28px;margin-bottom:10px;">📄</div>
        <div style="font-size:15px;font-weight:700;color:var(--text-1);margin-bottom:8px;">
            No hay plantilla cargada para este tipo de documento
        </div>
        <div style="font-size:13px;color:var(--text-2);margin-bottom:18px;">
            Un administrador debe subir la plantilla DOCX antes de poder generar este documento.
        </div>
        @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('plantillas.index') }}" class="is-btn-primary">
                Ir a Plantillas → subir ahora
            </a>
        @endif
    </div>
@else
    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;"
         class="is-animate-rise">

        {{-- Formulario principal --}}
        <div>
            <div style="background:var(--bg-card);border:1px solid var(--border);
                        border-radius:10px;padding:22px 24px;">

                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                    <span style="font-size:13px;font-weight:700;color:var(--text-1);">
                        Plantilla: {{ $plantilla->nombre }}
                    </span>
                    <span class="is-ext-badge ext-{{ $plantilla->extension }}"
                          style="display:inline-block;padding:2px 8px;border-radius:20px;
                                 font-size:10px;font-weight:700;text-transform:uppercase;
                                 background:rgba(27,79,255,.10);color:#4B78FF;">
                        {{ strtoupper($plantilla->extension) }}
                    </span>
                </div>
                <div style="font-size:11px;color:var(--text-3);margin-bottom:20px;">
                    Revisa y completa los campos. Los marcados con
                    <span style="font-size:9px;padding:1px 5px;border-radius:20px;
                                 background:rgba(5,150,105,.12);color:#1DBD7F;font-weight:700;">
                        AUTO
                    </span>
                    se tomaron del caso automáticamente.
                </div>

                @if(session('success'))
                    <div class="is-alert is-alert-success" style="margin-bottom:16px;">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="is-alert is-alert-danger" style="margin-bottom:16px;">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif

                @if(count($variables) === 0)
                    <div style="background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.22);
                                border-radius:8px;padding:14px;font-size:13px;color:var(--text-2);
                                margin-bottom:16px;">
                        ⚠ Esta plantilla no tiene variables detectadas. Se descargará tal cual.
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('casos.generar.generar', [$caso, $tipo]) }}">
                    @csrf

                    @if(count($variables))
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                            @foreach($variables as $var)
                                @php
                                    $esAuto     = ($preRellenos[$var] ?? '') !== '';
                                    $valorViejo = old($var, $preRellenos[$var] ?? '');
                                    $esSelect   = isset($opcionesSelect[$var]);
                                    $etiqueta   = str_replace('_', ' ', $var);
                                @endphp
                                <div class="is-gen-field">
                                    <label class="is-gen-label">
                                        {{ $etiqueta }}
                                        @if($esAuto)
                                            <span class="auto-tag">AUTO</span>
                                        @else
                                            <span class="manual-tag">MANUAL</span>
                                        @endif
                                    </label>

                                    @if($esSelect)
                                        <select name="{{ $var }}"
                                                class="is-gen-select {{ $esAuto ? 'is-auto' : '' }}">
                                            <option value="">— Seleccionar —</option>
                                            @foreach($opcionesSelect[$var] as $opcion)
                                                <option value="{{ $opcion }}"
                                                        {{ $valorViejo === $opcion ? 'selected' : '' }}>
                                                    {{ $opcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text"
                                               name="{{ $var }}"
                                               class="is-input{{ $esAuto ? ' is-input-auto' : '' }}"
                                               value="{{ $valorViejo }}"
                                               placeholder="{{ ucfirst($etiqueta) }}"
                                               style="{{ $esAuto ? 'background:rgba(5,150,105,.05);border-color:rgba(5,150,105,.25);' : '' }}">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
                        <a href="{{ route('casos.show', $caso) }}" class="is-btn-ghost">
                            Cancelar
                        </a>
                        <button type="submit" class="is-btn-primary">
                            ⬇ Generar y descargar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Panel lateral: generados previos --}}
        <div>
            <div style="background:var(--bg-card);border:1px solid var(--border);
                        border-radius:10px;padding:18px 20px;">
                <div style="font-size:13px;font-weight:700;color:var(--text-1);margin-bottom:14px;">
                    Documentos generados anteriormente
                </div>

                @forelse($generadosPrevios as $doc)
                    <div class="is-prev-doc">
                        <div style="min-width:0;flex:1;">
                            <div style="font-weight:600;color:var(--text-1);
                                        white-space:nowrap;overflow:hidden;
                                        text-overflow:ellipsis;font-size:12px;">
                                {{ $doc->nombre_archivo }}
                            </div>
                            <div style="font-size:11px;color:var(--text-3);margin-top:2px;">
                                {{ $doc->created_at->format('d/m/Y H:i') }}
                                · {{ $doc->user?->name ?? 'Sistema' }}
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;flex-shrink:0;">
                            <a href="{{ route('casos.generar.descargar', [$caso, $doc]) }}"
                               class="is-btn-ghost"
                               style="font-size:11px;padding:4px 10px;">
                                ⬇
                            </a>
                            @if(auth()->user()->puedeEditar())
                                <form method="POST"
                                      action="{{ route('casos.generar.destroy', [$caso, $doc]) }}"
                                      onsubmit="return confirm('¿Eliminar?');">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="is-btn-ghost"
                                            style="font-size:11px;padding:4px 8px;color:#F26F6F;">
                                        ✕
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="font-size:12px;color:var(--text-3);text-align:center;
                                padding:14px;border:1px dashed var(--border-2);
                                border-radius:8px;">
                        Sin documentos generados aún para este tipo
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endif

@endsection
