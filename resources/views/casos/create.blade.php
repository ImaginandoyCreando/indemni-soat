@extends('layouts.app')

@section('title', 'Nuevo Caso')

@section('content')

<style>
/* ── Animación de sección activa ── */
.is-form-section { transition: box-shadow .2s; }
.is-form-section:focus-within {
    box-shadow: 0 0 0 2px rgba(27,79,255,0.15);
    border-color: rgba(27,79,255,0.25) !important;
}
/* ── Checkbox campo ── */
.is-cb-field {
    display:flex; align-items:flex-start; gap:10px;
    padding:12px 14px; border-radius:8px;
    border:1px solid var(--border); background:var(--bg-input);
    cursor:pointer; transition:all .18s;
}
.is-cb-field:hover { border-color:var(--border-2); }
.is-cb-sq {
    width:17px; height:17px; border-radius:4px; flex-shrink:0;
    border:1.5px solid var(--border-2); background:var(--bg-input);
    display:flex; align-items:center; justify-content:center;
    margin-top:1px; transition:all .2s;
}
.is-cb-sq.on { background:#059669; border-color:#059669; }
/* ── Validación en tiempo real ── */
.is-input.valid { border-color: #059669 !important; }
.is-input.invalid { border-color: #F26F6F !important; background: rgba(229,57,53,0.05); }
.is-error-msg { color: #F26F6F; font-size: 11px; margin-top: 4px; display: none; }
.is-error-msg.show { display: block; }
</style>

{{-- ── Cabecera ── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:center;gap:14px;margin-bottom:28px;">
    <a href="{{ route('casos.index') }}"
       style="width:38px;height:38px;border-radius:8px;
              border:1px solid var(--border-2);background:var(--bg-input);
              display:flex;align-items:center;justify-content:center;
              color:var(--text-2);font-size:18px;text-decoration:none;
              transition:all .2s;flex-shrink:0;"
       onmouseover="this.style.background='var(--bg-hover)';this.style.color='var(--text-1)'"
       onmouseout="this.style.background='var(--bg-input)';this.style.color='var(--text-2)'">
        ←
    </a>
    <div>
        <div class="is-page-title">Registrar Nuevo Caso</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:3px;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                <div style="display:flex;gap:4px;">
                    <span style="display:inline-flex;width:20px;height:20px;border-radius:50%;background:#4B78FF;color:white;font-size:10px;font-weight:700;align-items:center;justify-content:center;">1</span>
                    <span style="display:inline-flex;width:20px;height:20px;border-radius:50%;background:var(--border);color:var(--text-3);font-size:10px;font-weight:700;align-items:center;justify-content:center;">2</span>
                    <span style="display:inline-flex;width:20px;height:20px;border-radius:50%;background:var(--border);color:var(--text-3);font-size:10px;font-weight:700;align-items:center;justify-content:center;">3</span>
                </div>
                <span style="color:#4B78FF;font-weight:600;">Paso 1 de 3</span>
            </div>
            Los campos con <span style="color:#F26F6F;">*</span> son obligatorios
        </div>
    </div>
</div>

{{-- ── Barra de pasos ── --}}
<div class="is-steps is-animate-rise is-stagger-1">
    <div class="is-step active">
        <div class="is-step-num">1</div>
        <div class="is-step-label">
            <strong>Víctima</strong>
            <span>Datos personales</span>
        </div>
    </div>
    <div class="is-step">
        <div class="is-step-num">2</div>
        <div class="is-step-label">
            <strong>Documentación</strong>
            <span>Poderes y contratos</span>
        </div>
    </div>
    <div class="is-step">
        <div class="is-step-num">3</div>
        <div class="is-step-label">
            <strong>Estado</strong>
            <span>Proceso inicial</span>
        </div>
    </div>
</div>

{{-- ── Errores globales ── --}}
@if($errors->any())
    <div class="is-animate-rise is-stagger-1"
         style="background:rgba(229,57,53,0.08);
                border:1px solid rgba(229,57,53,0.22);
                border-radius:10px;padding:14px 18px;
                margin-bottom:20px;">
        <div style="font-size:13px;font-weight:700;
                    color:#F26F6F;margin-bottom:8px;">
            Corrige los siguientes errores:
        </div>
        <ul style="margin:0;padding-left:18px;
                   font-size:13px;color:#F26F6F;line-height:1.7;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('casos.store') }}">
@csrf

{{-- ════════════════════════════════════════════
     SECCIÓN 1 — Información general de la víctima
════════════════════════════════════════════ --}}
<div class="is-form-section is-animate-rise is-stagger-2">
    <div class="is-form-section-header">
        <div class="is-section-num">1</div>
        <div class="is-section-title">Información general de la víctima</div>
    </div>
    <div class="is-form-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            <div>
                <label class="is-form-label" for="nombres">
                    Nombres <span style="color:#F26F6F;">*</span>
                </label>
                <input type="text" id="nombres" name="nombres"
                       class="is-input"
                       value="{{ old('nombres') }}"
                       placeholder="Ej. Carlos Andrés"
                       required>
            </div>

            <div>
                <label class="is-form-label" for="apellidos">
                    Apellidos <span style="color:#F26F6F;">*</span>
                </label>
                <input type="text" id="apellidos" name="apellidos"
                       class="is-input"
                       value="{{ old('apellidos') }}"
                       placeholder="Ej. Pérez García"
                       required>
            </div>

            <div>
                <label class="is-form-label" for="cedula">
                    Cédula de ciudadanía <span style="color:#F26F6F;">*</span>
                </label>
                <input type="text" id="cedula" name="cedula"
                       class="is-input"
                       value="{{ old('cedula') }}"
                       placeholder="Sin puntos ni comas"
                       required>
            </div>

            <div>
                <label class="is-form-label" for="telefono">Teléfono / Celular</label>
                <input type="text" id="telefono" name="telefono"
                       class="is-input"
                       value="{{ old('telefono') }}"
                       placeholder="300 000 0000">
            </div>

            <div>
                <label class="is-form-label" for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo"
                       class="is-input"
                       value="{{ old('correo') }}"
                       placeholder="victima@correo.com">
            </div>

            <div>
                <label class="is-form-label" for="departamento">Departamento</label>
                <input type="text" id="departamento" name="departamento"
                       class="is-input"
                       value="{{ old('departamento') }}"
                       placeholder="Ej. Cundinamarca">
            </div>

            <div>
                <label class="is-form-label" for="ciudad">Ciudad / Municipio</label>
                <input type="text" id="ciudad" name="ciudad"
                       class="is-input"
                       value="{{ old('ciudad') }}"
                       placeholder="Ej. Bogotá D.C.">
            </div>

            <div>
                <label class="is-form-label" for="direccion">Dirección de residencia</label>
                <input type="text" id="direccion" name="direccion"
                       class="is-input"
                       value="{{ old('direccion') }}"
                       placeholder="Calle / Carrera / Apto">
            </div>

            <div>
                <label class="is-form-label" for="fecha_accidente">
                    Fecha del accidente
                </label>
                <input type="date" id="fecha_accidente" name="fecha_accidente"
                       class="is-input"
                       value="{{ old('fecha_accidente') }}">
            </div>

            <div>
                <label class="is-form-label" for="aseguradora">
                    Aseguradora <span style="color:#F26F6F;">*</span>
                </label>
                <select id="aseguradora" name="aseguradora"
                        class="is-select" required>
                    <option value="">Seleccionar aseguradora</option>
                    @foreach($aseguradoras as $aseguradora)
                        <option value="{{ $aseguradora }}"
                            {{ old('aseguradora') == $aseguradora ? 'selected' : '' }}>
                            {{ $aseguradora }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Junta — full width --}}
            <div style="grid-column:1/-1;">
                <label class="is-form-label" for="junta_asignada">
                    Junta médica asignada
                </label>
                <select id="junta_asignada" name="junta_asignada"
                        class="is-select">
                    <option value="">Seleccionar (opcional al inicio)</option>
                    @foreach($juntas as $junta)
                        <option value="{{ $junta }}"
                            {{ old('junta_asignada') == $junta ? 'selected' : '' }}>
                            {{ $junta }}
                        </option>
                    @endforeach
                </select>
                <div class="is-field-hint">
                    Puedes asignarla más adelante cuando se programe la cita
                    médico-laboral.
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECCIÓN 2 — Documentación inicial
════════════════════════════════════════════ --}}
<div class="is-form-section is-animate-rise is-stagger-3">
    <div class="is-form-section-header">
        <div class="is-section-num">2</div>
        <div class="is-section-title">Documentación inicial</div>
    </div>
    <div class="is-form-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            {{-- Entrega del poder --}}
            <div>
                <label class="is-form-label">
                    Fecha en que se entregó el poder
                </label>
                <input type="date" name="fecha_entrega_poder"
                       class="is-input"
                       value="{{ old('fecha_entrega_poder') }}">
                <div class="is-field-hint">
                    Úsala cuando entregas el poder a la víctima para firma
                    o cuando inicia ese pendiente.
                </div>
            </div>

            {{-- Fecha poder firmado --}}
            <div>
                <label class="is-form-label">Fecha poder firmado (real)</label>
                <input type="date" name="fecha_poder_firmado"
                       class="is-input"
                       value="{{ old('fecha_poder_firmado') }}">
                <div class="is-field-hint">
                    Fecha real en que la víctima devolvió el poder firmado.
                </div>
            </div>

            {{-- Checkbox: víctima entregó poder --}}
            <div>
                <label class="is-form-label" style="margin-bottom:7px;">
                    ¿La víctima ya entregó el poder?
                </label>
                <div class="is-cb-field"
                     id="cbFieldPoder"
                     onclick="toggleCb('cbPoder','cbInputPoder')">
                    <div class="is-cb-sq {{ old('tiene_poder') ? 'on' : '' }}"
                         id="cbPoder">
                        <svg width="9" height="7" viewBox="0 0 9 7" fill="none"
                             style="{{ old('tiene_poder') ? '' : 'display:none' }}"
                             id="cbPoderCheck">
                            <path d="M1 3.5l2.5 2.5L8 1" stroke="#fff"
                                  stroke-width="1.6" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input type="checkbox" id="cbInputPoder" name="tiene_poder"
                           value="1" style="display:none;"
                           {{ old('tiene_poder') ? 'checked' : '' }}>
                    <div>
                        <strong style="display:block;font-size:13px;
                                       font-weight:600;color:var(--text-1);">
                            Sí, ya entregó el poder firmado
                        </strong>
                        <span style="font-size:11px;color:var(--text-3);line-height:1.4;">
                            Marca solo cuando el poder esté recibido por el equipo.
                        </span>
                    </div>
                </div>
            </div>

            {{-- Checkbox: equipo tiene el poder --}}
            <div>
                <label class="is-form-label" style="margin-bottom:7px;">
                    ¿El poder ya está en poder del equipo?
                </label>
                <div class="is-cb-field"
                     onclick="toggleCb('cbContrato','cbInputContrato')">
                    <div class="is-cb-sq {{ old('tiene_contrato') ? 'on' : '' }}"
                         id="cbContrato">
                        <svg width="9" height="7" viewBox="0 0 9 7" fill="none"
                             style="{{ old('tiene_contrato') ? '' : 'display:none' }}"
                             id="cbContratoCheck">
                            <path d="M1 3.5l2.5 2.5L8 1" stroke="#fff"
                                  stroke-width="1.6" stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input type="checkbox" id="cbInputContrato" name="tiene_contrato"
                           value="1" style="display:none;"
                           {{ old('tiene_contrato') ? 'checked' : '' }}>
                    <div>
                        <strong style="display:block;font-size:13px;
                                       font-weight:600;color:var(--text-1);">
                            Sí, el poder está en poder del equipo
                        </strong>
                        <span style="font-size:11px;color:var(--text-3);line-height:1.4;">
                            Confirma cuando el abogado tenga el documento físico.
                        </span>
                    </div>
                </div>
            </div>

            {{-- Fecha entrega contrato --}}
            <div>
                <label class="is-form-label">
                    Fecha en que se entregó el contrato
                </label>
                <input type="date" name="fecha_entrega_contrato"
                       class="is-input"
                       value="{{ old('fecha_entrega_contrato') }}">
                <div class="is-field-hint">
                    Úsala cuando se le entrega el contrato de honorarios a
                    la víctima o cuando queda pendiente de firma.
                </div>
            </div>

            {{-- Fecha contrato firmado --}}
            <div>
                <label class="is-form-label">Fecha contrato firmado (real)</label>
                <input type="date" name="fecha_contrato_firmado"
                       class="is-input"
                       value="{{ old('fecha_contrato_firmado') }}">
                <div class="is-field-hint">
                    Fecha real en que la víctima devolvió el contrato firmado.
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECCIÓN 3 — Estado inicial del proceso
════════════════════════════════════════════ --}}
<div class="is-form-section is-animate-rise is-stagger-4">
    <div class="is-form-section-header">
        <div class="is-section-num">3</div>
        <div class="is-section-title">Estado inicial del proceso</div>
    </div>
    <div class="is-form-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

            <div style="grid-column:1/-1;">
                <label class="is-form-label" for="estado">
                    Estado del caso <span style="color:#F26F6F;">*</span>
                </label>
                <select id="estado" name="estado"
                        class="is-select" required>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}"
                            {{ old('estado') == $estado ? 'selected' : '' }}>
                            {{ $estado }}
                        </option>
                    @endforeach
                </select>
                <div class="is-field-hint">
                    Lo normal al crear un caso nuevo suele ser
                    <strong style="color:var(--text-1);">Nuevo</strong> o
                    <strong style="color:var(--text-1);">
                        Solicitud de calificación enviada
                    </strong>.
                </div>
            </div>

            <div style="grid-column:1/-1;">
                <label class="is-form-label" for="observaciones">
                    Observaciones iniciales
                </label>
                <textarea id="observaciones" name="observaciones"
                          class="is-textarea"
                          style="min-height:110px;"
                          placeholder="Contexto del accidente, condición actual del paciente,
documentos recibidos, notas relevantes para el seguimiento del caso...">{{ old('observaciones') }}</textarea>
            </div>

        </div>

        {{-- Acciones --}}
        <div style="display:flex;justify-content:flex-end;
                    gap:10px;margin-top:24px;flex-wrap:wrap;">
            <a href="{{ route('casos.index') }}" class="is-btn-ghost">
                Cancelar
            </a>
            <button type="submit" class="is-btn-gold">
                ✓ Guardar Caso
            </button>
        </div>

    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
function toggleCb(boxId, inputId) {
    const box   = document.getElementById(boxId);
    const input = document.getElementById(inputId);
    const check = document.getElementById(boxId + 'Check');

    input.checked = !input.checked;

    if (input.checked) {
        box.classList.add('on');
        if (check) check.style.display = '';
    } else {
        box.classList.remove('on');
        if (check) check.style.display = 'none';
    }
}

// Validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const requiredFields = ['nombres', 'apellidos', 'cedula', 'aseguradora'];
    
    requiredFields.forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('blur', function() {
                validateField(field);
            });
            
            field.addEventListener('input', function() {
                if (field.classList.contains('invalid')) {
                    validateField(field);
                }
            });
        }
    });
    
    function validateField(field) {
        const errorMsg = field.parentNode.querySelector('.is-error-msg');
        
        if (field.value.trim() === '') {
            field.classList.remove('valid');
            field.classList.add('invalid');
            if (errorMsg) errorMsg.classList.add('show');
        } else {
            field.classList.remove('invalid');
            field.classList.add('valid');
            if (errorMsg) errorMsg.classList.remove('show');
        }
    }
});
</script>
@endpush
