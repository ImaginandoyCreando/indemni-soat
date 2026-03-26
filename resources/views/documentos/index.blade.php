@extends('layouts.app')

@section('title', 'Expediente del Caso')

@section('content')
<style>
/* ── Tabla de documentos ── */
.is-docs-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--bg-card);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.is-docs-table th {
    background: var(--bg-input);
    padding: 12px 16px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: var(--text-3);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border-bottom: 1px solid var(--border);
}
.is-docs-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
    color: var(--text-1);
}
.is-docs-table tr:last-child td {
    border-bottom: none;
}
.is-docs-table tr:hover {
    background: var(--bg-hover);
}
.is-doc-link {
    color: #4B78FF;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color .2s;
}
.is-doc-link:hover {
    color: #1B4FFF;
    text-decoration: underline;
}
.is-doc-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-3);
    font-style: italic;
    font-size: 14px;
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

@if($errors->any())
    <div class="is-animate-rise is-stagger-1"
         style="background:rgba(229,57,53,0.08);border:1px solid rgba(229,57,53,0.22);
                border-radius:8px;padding:11px 16px;margin-bottom:16px;
                font-size:13px;color:#F26F6F;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 11v1M8 5v.01" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <div>
            <strong>Corrige los siguientes errores:</strong>
            <ul style="margin:6px 0 0 0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ── Subir documento ── --}}
@if(auth()->user()->puedeEditar())
    <div class="is-animate-rise is-stagger-1"
         style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:8px;padding:20px;margin-bottom:24px;">
        <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;">
            Subir documento al expediente
        </div>
        <div style="font-size:12px;color:var(--text-3);margin-bottom:20px;line-height:1.4;">
            Puedes cargar soportes como cédula, historia clínica, dictámenes, tutelas, reclamaciones, comprobantes y demás anexos del caso.
        </div>
        <form method="POST" action="{{ route('casos.documentos.store', $caso) }}" enctype="multipart/form-data">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div class="is-form-section">
                    <div class="is-form-label">Tipo de documento</div>
                    <select name="tipo_documento" class="is-select" required>
                        <option value="">Seleccione...</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo }}" {{ old('tipo_documento') == $tipo ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="is-form-section">
                    <div class="is-form-label">Archivo</div>
                    <input type="file" name="archivo" class="is-input" required>
                    <div style="font-size:11px;color:var(--text-3);margin-top:4px;line-height:1.3;">
                        Formatos permitidos: PDF, JPG, JPEG, PNG, DOC, DOCX. Máximo 10 MB.
                    </div>
                </div>
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
        Documentos cargados
    </div>
    
    @if($documentos->count() > 0)
        <div style="overflow:auto;border-radius:8px;border:1px solid var(--border);">
            <table class="is-docs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Archivo</th>
                        <th>Fecha de carga</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documentos as $documento)
                        <tr>
                            <td>{{ $documento->id }}</td>
                            <td>{{ $documento->tipo_documento }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $documento->archivo) }}" 
                                   target="_blank" 
                                   class="is-doc-link">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                        <path d="M1 3h14v8H1V3zm2 2v4h10V5H3z" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    Ver archivo
                                </a>
                            </td>
                            <td>{{ optional($documento->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                @if(auth()->user()->puedeEditar())
                                    <form method="POST" action="{{ route('casos.documentos.destroy', [$caso, $documento]) }}" 
                                          style="display:inline;"
                                          onsubmit="return confirm('¿Eliminar este documento?')">
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
                                @else
                                    <span style="color:var(--text-3);font-size:11px;">Sin permisos</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="is-doc-empty">
            <svg width="48" height="48" viewBox="0 0 16 16" fill="none" style="opacity:0.2;margin-bottom:12px;">
                <path d="M3 8h10M8 3v10" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            No hay documentos cargados en este expediente.
        </div>
    @endif
</div>
@endsection
