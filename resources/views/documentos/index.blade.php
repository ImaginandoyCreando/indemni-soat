@extends('layouts.app')

@section('title', 'Expediente del Caso')

@section('content')
<style>
/* ── Grid de documentos ── */
.is-docs-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));
    gap:16px;
    margin-bottom:24px;
}
.is-doc-card {
    background:var(--bg-card);
    border:1px solid var(--border);
    border-radius:8px;
    padding:18px;
    transition:all .2s;
    cursor:pointer;
    position:relative;
}
.is-doc-card:hover {
    border-color:var(--cobalt-glow,rgba(27,79,255,0.4));
    transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(0,0,0,0.08);
}
.is-doc-icon {
    width:48px;height:48px;
    background:var(--bg-input);
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin-bottom:12px;
    font-size:20px;
}
.is-doc-name {
    font-size:14px;
    font-weight:600;
    color:var(--text-1);
    margin-bottom:4px;
    line-height:1.3;
}
.is-doc-date {
    font-size:11px;
    color:var(--text-3);
    margin-bottom:8px;
}
.is-doc-actions {
    display:flex;
    gap:8px;
    margin-top:12px;
}
.is-doc-btn {
    padding:6px 10px;
    border-radius:6px;
    font-size:11px;
    font-weight:600;
    text-decoration:none;
    transition:all .2s;
    display:inline-flex;
    align-items:center;
    gap:4px;
}
.is-doc-btn-primary {
    background:var(--cobalt-glow,rgba(27,79,255,0.12));
    color:#4B78FF;
    border:1px solid rgba(27,79,255,0.25);
}
.is-doc-btn-primary:hover {
    background:rgba(27,79,255,0.22);
}
.is-doc-btn-danger {
    background:rgba(229,57,53,0.12);
    color:#F26F6F;
    border:1px solid rgba(229,57,53,0.25);
}
.is-doc-btn-danger:hover {
    background:rgba(229,57,53,0.22);
}
.is-doc-empty {
    grid-column:1/-1;
    text-align:center;
    padding:60px 20px;
    color:var(--text-3);
    font-style:italic;
    font-size:14px;
}
</style>

{{-- ── Cabecera ── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:center;gap:14px;margin-bottom:28px;">
    <a href="{{ route('casos.show', $caso) }}"
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
        <div class="is-page-title">Expediente Digital</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:3px;">
            {{ $caso->numero_caso }} — {{ $caso->nombres }} {{ $caso->apellidos }}
        </div>
    </div>
</div>

@session('success')
    <div class="is-animate-rise is-stagger-1"
         style="background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.22);
                border-radius:8px;padding:11px 16px;margin-bottom:16px;
                font-size:13px;color:#1DBD7F;display:flex;align-items:center;gap:8px;">
        <span>✓</span> {{ session('success') }}
    </div>
@endsession

@session('error')
    <div class="is-animate-rise is-stagger-1"
         style="background:rgba(229,57,53,0.08);border:1px solid rgba(229,57,53,0.22);
                border-radius:8px;padding:11px 16px;margin-bottom:16px;
                font-size:13px;color:#F26F6F;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 11v1M8 5v.01" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        {{ session('error') }}
    </div>
@endsession

{{-- ── Subir documento ── }}
@if(auth()->user()->puedeEditar())
    <div class="is-animate-rise is-stagger-1"
         style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:8px;padding:20px;margin-bottom:24px;">
        <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;">
            Subir documento al expediente
        </div>
        <form method="POST" action="{{ route('casos.documentos.store', $caso) }}" enctype="multipart/form-data">
            @csrf
            <div class="is-form-section">
                <div class="is-form-label">Tipo de documento</div>
                <select name="tipo_documento" class="is-select" required>
                    <option value="">Seleccionar...</option>
                    <option value="poder">Poder</option>
                    <option value="contrato">Contrato de honorarios</option>
                    <option value="copia_cedula">Copia de cédula</option>
                    <option value="historia_clinica">Historia clínica</option>
                    <option value="facturas">Facturas médicas</option>
                    <option value="desprendibles">Desprendibles</option>
                    <option value="otros">Otros</option>
                </select>
            </div>
            <div class="is-form-section">
                <div class="is-form-label">Archivo (PDF, imagen o documento)</div>
                <input type="file" name="archivo" class="is-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
            </div>
            <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;">
                <button type="submit" class="is-btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                        <path d="M8 2v6M4 6h8M2 10h12l-1 4H3l-1-4z" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    Subir documento
                </button>
            </div>
        </form>
    </div>
@endif

{{-- ── Lista de documentos ── --}}
<div class="is-animate-rise is-stagger-2"
     style="background:var(--bg-card);border:1px solid var(--border);
            border-radius:8px;padding:20px;">
    <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:20px;">
        Documentos del expediente
    </div>
    
    @if($documentos->count() > 0)
        <div class="is-docs-grid">
            @foreach($documentos as $documento)
                <div class="is-doc-card">
                    <div class="is-doc-icon">
                        📄
                    </div>
                    <div class="is-doc-name">
                        {{ $documento->nombre_original }}
                    </div>
                    <div class="is-doc-date">
                        Subido el {{ $documento->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="is-doc-actions">
                        <a href="{{ route('casos.documentos.show', [$caso, $documento]) }}" 
                           class="is-doc-btn is-doc-btn-primary" target="_blank">
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                <path d="M1 3h14v8H1V3zm2 2v4h10V5H3z" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                            Ver
                        </a>
                        @if(auth()->user()->puedeEditar())
                            <form method="POST" action="{{ route('casos.documentos.destroy', [$caso, $documento]) }}" 
                                  style="display:inline;"
                                  onsubmit="return confirm('¿Eliminar este documento del expediente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="is-doc-btn is-doc-btn-danger">
                                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                        <path d="M2 4h12M5 4V3a1 1 0 011-1h4a1 1 0 011 1v1M7 8v4M4 4v8a2 2 0 002 2h4a2 2 0 002-2V4" 
                                              stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="is-doc-empty">
            <svg width="48" height="48" viewBox="0 0 16 16" fill="none" style="opacity:0.2;margin-bottom:12px;">
                <path d="M3 8h10M8 3v10" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            No hay documentos en el expediente de este caso.
        </div>
    @endif
</div>
@endsection
