@extends('layouts.app')

@section('title', isset($user) ? 'Editar Usuario' : 'Nuevo Usuario')

@section('content')
<style>
/* ── Formulario de usuario ── */
.is-user-form {
    max-width:560px;margin:auto;
    background:var(--bg-card);border:1px solid var(--border);
    border-radius:12px;padding:28px;box-shadow:0 8px 24px rgba(0,0,0,0.04);
}
.is-form-section { margin-bottom:24px; }
.is-form-section:last-child { margin-bottom:0; }
.is-divider {
    border:none;border-top:1px solid var(--border);
    margin:24px 0;
}
/* ── Validación visual ── */
.is-input.valid { border-color: #059669 !important; }
.is-input.invalid { border-color: #F26F6F !important; background: rgba(229,57,53,0.05); }
.is-error-msg { color: #F26F6F; font-size: 11px; margin-top: 4px; display: none; }
.is-error-msg.show { display: block; }
</style>

{{-- ── Cabecera ── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:center;gap:14px;margin-bottom:28px;">
    <a href="{{ route('users.index') }}"
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
        <div class="is-page-title">{{ isset($user) ? 'Editar Usuario' : 'Nuevo Usuario' }}</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:3px;">
            @if(isset($user))
                Modificando datos de <strong>{{ $user->name }}</strong>
            @else
                Creando nuevo usuario en el sistema
            @endif
        </div>
    </div>
</div>

@if($errors->any())
    <div class="is-animate-rise is-stagger-1"
         style="background:rgba(229,57,53,0.08);border:1px solid rgba(229,57,53,0.22);
                border-radius:10px;padding:11px 16px;margin-bottom:16px;
                font-size:13px;color:#F26F6F;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 11v1M8 5v.01" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <div>
            <strong>Corrige los siguientes errores:</strong>
            <ul style="margin:6px 0 0 16px;padding:0;">
                @foreach($errors->all() as $error)
                    <li style="margin-bottom:2px;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- ── Formulario ── --}}
<div class="is-user-form is-animate-rise is-stagger-1">
    <form method="POST"
          action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        {{-- Información Básica --}}
        <div class="is-form-section">
            <div class="is-form-label">Nombre completo</div>
            <input type="text" name="name" class="is-input"
                   value="{{ old('name', $user->name ?? '') }}"
                   placeholder="Ej: Juan Pérez" required>
            @error('name') 
                <div class="is-error-msg show">{{ $message }}</div> 
            @enderror
        </div>

        <div class="is-form-section">
            <div class="is-form-label">Correo electrónico</div>
            <input type="email" name="email" class="is-input"
                   value="{{ old('email', $user->email ?? '') }}"
                   placeholder="usuario@correo.com" required>
            @error('email') 
                <div class="is-error-msg show">{{ $message }}</div> 
            @enderror
        </div>

        <div class="is-form-section">
            <div class="is-form-label">Rol</div>
            <select name="role" class="is-select" required>
                <option value="">Seleccionar rol...</option>
                <option value="admin"
                    {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>
                    Administrador — Acceso total + gestión de usuarios
                </option>
                <option value="abogado"
                    {{ old('role', $user->role ?? '') === 'abogado' ? 'selected' : '' }}>
                    Abogado — Crear/editar casos y acciones del flujo
                </option>
                <option value="readonly"
                    {{ old('role', $user->role ?? '') === 'readonly' ? 'selected' : '' }}>
                    Solo lectura — Ver casos y dashboard sin modificar
                </option>
            </select>
            @error('role') 
                <div class="is-error-msg show">{{ $message }}</div> 
            @enderror
        </div>

        <div class="is-divider"></div>

        {{-- Contraseña --}}
        <div class="is-form-section">
            <div class="is-form-label">
                Contraseña
                @if(isset($user))
                    <span style="font-weight:normal;color:var(--text-3);font-size:11px;">
                        — Dejar vacío para no cambiarla
                    </span>
                @endif
            </div>
            <input type="password" name="password" class="is-input"
                   placeholder="{{ isset($user) ? 'Nueva contraseña (opcional)' : 'Mínimo 8 caracteres' }}"
                   {{ isset($user) ? '' : 'required' }}>
            <div style="font-size:11px;color:var(--text-3);margin-top:4px;">
                Mínimo 8 caracteres
            </div>
            @error('password') 
                <div class="is-error-msg show">{{ $message }}</div> 
            @enderror
        </div>

        <div class="is-form-section">
            <div class="is-form-label">Confirmar contraseña</div>
            <input type="password" name="password_confirmation" class="is-input"
                   placeholder="{{ isset($user) ? 'Repetir nueva contraseña' : 'Repetir contraseña' }}"
                   {{ isset($user) ? '' : 'required' }}>
            @error('password_confirmation') 
                <div class="is-error-msg show">{{ $message }}</div> 
            @enderror
        </div>

        {{-- Acciones --}}
        <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;margin-top:28px;">
            <a href="{{ route('users.index') }}" class="is-btn-ghost">
                Cancelar
            </a>
            <button type="submit" class="is-btn-primary">
                @if(isset($user))
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                        <path d="M4 12v1a1 1 0 001 1h6a1 1 0 001-1v-1M9 7V3a1 1 0 00-1-1H4a1 1 0 00-1 1v4" 
                              stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    Guardar cambios
                @else
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                        <path d="M8 2v6M4 6h8M2 10h12l-1 4H3l-1-4z" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    Crear usuario
                @endif
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const requiredFields = ['name', 'email', 'role'];
    
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
@endsection