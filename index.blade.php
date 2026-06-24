@extends('layouts.app')

@section('title', 'Plantillas de Documentos')

@section('content')

<style>
.is-plantilla-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px 18px;
    transition: border-color .2s, box-shadow .2s;
}
.is-plantilla-card:hover {
    border-color: var(--border-2);
    box-shadow: var(--shadow-sm);
}
.is-var-chip {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    background: rgba(27,79,255,.10);
    color: #4B78FF;
    font-family: monospace;
    margin: 2px 2px 2px 0;
}
.is-ext-badge {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}
.ext-docx { background: rgba(27,79,255,.12); color: #4B78FF; }
.ext-pdf  { background: rgba(229,57,53,.12);  color: #F26F6F; }
.ext-xlsx { background: rgba(5,150,105,.12);  color: #1DBD7F; }
</style>

{{-- Cabecera --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;
            margin-bottom:24px;gap:14px;flex-wrap:wrap;" class="is-animate-rise">
    <div>
        <div class="is-page-title">Plantillas de Documentos</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">
            Sube las plantillas DOCX, PDF o XLSX con variables <code>{{nombre_variable}}</code>.
            El sistema las detecta automáticamente.
        </div>
    </div>
    <button onclick="document.getElementById('modal-subir').style.display='flex'"
            class="is-btn-primary">
        + Subir plantilla
    </button>
</div>

@if(session('success'))
    <div class="is-alert is-alert-success is-animate-rise" style="margin-bottom:16px;">
        ✓ {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="is-alert is-alert-danger is-animate-rise" style="margin-bottom:16px;">
        @foreach($errors->all() as $e)
            <div>{{ $e }}</div>
        @endforeach
    </div>
@endif

{{-- Agrupado por tipo --}}
@foreach($tipos as $tipoKey => $tipoLabel)
    @php $grupo = $plantillas->where('tipo', $tipoKey); @endphp
    <div class="is-animate-rise" style="margin-bottom:20px;">
        <div style="font-family:'Playfair Display',serif;font-size:15px;font-weight:700;
                    color:var(--text-1);padding-bottom:8px;margin-bottom:12px;
                    border-bottom:1px solid var(--border);">
            {{ $tipoLabel }}
        </div>

        @if($grupo->count())
            <div style="display:grid;gap:10px;">
                @foreach($grupo->sortByDesc('created_at') as $plantilla)
                    <div class="is-plantilla-card">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                                    <span class="is-ext-badge ext-{{ $plantilla->extension }}">
                                        {{ $plantilla->extension }}
                                    </span>
                                    <span style="font-size:14px;font-weight:600;color:var(--text-1);">
                                        {{ $plantilla->nombre }}
                                    </span>
                                    <span style="font-size:11px;color:var(--text-3);">
                                        Subida {{ $plantilla->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                @if(count($plantilla->variables_detectadas ?? []))
                                    <div style="margin-top:6px;">
                                        <span style="font-size:10px;color:var(--text-3);
                                                     font-weight:700;text-transform:uppercase;
                                                     letter-spacing:.5px;margin-right:6px;">
                                            Variables detectadas:
                                        </span>
                                        @foreach($plantilla->variables_detectadas as $v)
                                            <span class="is-var-chip">{{'{{'}}{{ $v }}{{'}}'}}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div style="font-size:11px;color:var(--text-3);margin-top:4px;">
                                        ⚠ No se detectaron variables. Asegúrate de usar el formato <code>{{'{{'}}variable{{'}}'}}</code>
                                    </div>
                                @endif
                            </div>

                            <form method="POST"
                                  action="{{ route('plantillas.destroy', $plantilla) }}"
                                  onsubmit="return confirm('¿Eliminar esta plantilla?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="is-btn-danger-sm">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="is-empty" style="padding:14px;font-size:12px;color:var(--text-3);
                                          background:var(--bg-input);border:1px dashed var(--border-2);
                                          border-radius:8px;text-align:center;">
                Sin plantilla para este tipo. Sube una para habilitar la generación.
            </div>
        @endif
    </div>
@endforeach


{{-- ═══ MODAL SUBIR PLANTILLA ═══ --}}
<div id="modal-subir"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);
            z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:14px;padding:28px 30px;width:100%;max-width:520px;
                max-height:90vh;overflow-y:auto;">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div style="font-family:'Playfair Display',serif;font-size:17px;
                        font-weight:700;color:var(--text-1);">
                Subir plantilla
            </div>
            <button onclick="document.getElementById('modal-subir').style.display='none'"
                    style="background:none;border:none;font-size:20px;color:var(--text-3);
                           cursor:pointer;line-height:1;">×</button>
        </div>

        <div style="background:rgba(27,79,255,.07);border:1px solid rgba(27,79,255,.18);
                    border-radius:8px;padding:12px 14px;margin-bottom:18px;font-size:12px;
                    color:var(--text-2);line-height:1.6;">
            <strong style="color:#4B78FF;">Formato de variables:</strong> En tu documento escribe
            <code style="background:rgba(27,79,255,.12);padding:1px 5px;border-radius:4px;">{{'{{'}}nombre_variable{{'}}'}}</code>
            donde quieras que aparezca un dato variable. Ejemplo:
            <code>{{'{{'}}nombre_victima{{'}}'}}</code>, <code>{{'{{'}}aseguradora{{'}}'}}</code>,
            <code>{{'{{'}}fecha_accidente{{'}}'}}</code>.
            <br><br>
            Variables que se pre-rellenan automáticamente desde el caso:
            <code>nombre_victima</code>, <code>cedula</code>, <code>aseguradora</code>,
            <code>ciudad</code>, <code>departamento</code>, <code>fecha_accidente</code>,
            <code>numero_caso</code>, <code>porcentaje_pcl</code>, <code>junta_asignada</code>.
        </div>

        <form method="POST" action="{{ route('plantillas.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="is-form-group">
                <label class="is-label">Tipo de documento *</label>
                <select name="tipo" class="is-select" required>
                    <option value="">— Selecciona —</option>
                    @foreach($tipos as $k => $v)
                        <option value="{{ $k }}" {{ old('tipo') == $k ? 'selected' : '' }}>
                            {{ $v }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="is-form-group" style="margin-top:14px;">
                <label class="is-label">Nombre descriptivo *</label>
                <input type="text" name="nombre" class="is-input"
                       value="{{ old('nombre') }}"
                       placeholder="Ej: Solicitud aseguradora v2"
                       required maxlength="200">
            </div>

            <div class="is-form-group" style="margin-top:14px;">
                <label class="is-label">Archivo (DOCX, PDF o XLSX — máx. 20 MB) *</label>
                <input type="file" name="archivo" class="is-input"
                       accept=".docx,.pdf,.xlsx" required>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px;">
                <button type="button"
                        onclick="document.getElementById('modal-subir').style.display='none'"
                        class="is-btn-ghost">
                    Cancelar
                </button>
                <button type="submit" class="is-btn-primary">
                    Subir y analizar
                </button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>
    document.getElementById('modal-subir').style.display = 'flex';
</script>
@endif

@endsection
