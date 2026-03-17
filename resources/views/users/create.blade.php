{{-- resources/views/users/create.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ isset($user) ? 'Editar usuario' : 'Nuevo usuario' }} — INDEMNI SOAT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;margin:0;color:#111827}
.layout{display:flex;min-height:100vh}
.sidebar{width:260px;background:linear-gradient(180deg,#1f2937 0%,#172033 100%);color:#fff;padding:25px 18px;flex-shrink:0;display:flex;flex-direction:column}
.brand{font-size:28px;font-weight:bold;margin-bottom:30px;line-height:1.2}
.menu a{display:block;padding:12px 14px;margin-bottom:8px;text-decoration:none;color:#fff;background:#374151;border-radius:8px;transition:.2s}
.menu a:hover,.menu a.active{background:#2563eb}
.user-box{margin-top:auto;padding-top:20px;border-top:1px solid #374151;font-size:13px}
.user-name{font-weight:bold;margin-bottom:4px}
.user-role{font-size:11px;color:#9ca3af}
.logout-btn{display:block;margin-top:10px;padding:8px 12px;background:#374151;color:#fff;text-decoration:none;border-radius:6px;font-size:12px;text-align:center;border:none;cursor:pointer;width:100%}
.logout-btn:hover{background:#dc3545}
.content{flex:1;padding:30px}
.card{background:#fff;border-radius:12px;padding:28px;max-width:560px;box-shadow:0 8px 24px rgba(15,23,42,.06)}
h2{margin:0 0 24px 0}
label{display:block;font-size:13px;font-weight:bold;color:#374151;margin-bottom:6px}
input,select{width:100%;padding:11px 13px;border:1.5px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;margin-bottom:16px}
input:focus,select:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.helper{font-size:11px;color:#6b7280;margin-top:-12px;margin-bottom:14px;display:block}
.btn{display:inline-block;padding:11px 20px;background:#2563eb;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px;text-decoration:none}
.btn:hover{opacity:.9}
.btn-secondary{background:#6b7280}
.actions{display:flex;gap:10px;margin-top:8px;flex-wrap:wrap}
.alert-error{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;margin-bottom:18px;font-size:13px;color:#dc2626}
.alert-error ul{margin:0;padding-left:18px}
.field-error{font-size:11px;color:#dc2626;margin-top:-12px;margin-bottom:12px;display:block}
.divider{border:none;border-top:1px solid #e5e7eb;margin:20px 0}
@media(max-width:900px){.layout{flex-direction:column}.sidebar{width:100%}}
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">INDEMNI<br>SOAT</div>
        <nav class="menu">
            <a href="{{ route('casos.index') }}">Casos</a>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('users.index') }}" class="active">Usuarios</a>
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
        <div class="card">
            <h2>{{ isset($user) ? 'Editar usuario' : 'Crear nuevo usuario' }}</h2>

            @if($errors->any())
                <div class="alert-error">
                    <strong>Corrige los siguientes errores:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if(isset($user)) @method('PUT') @endif

                <label>Nombre completo</label>
                <input type="text" name="name"
                    value="{{ old('name', $user->name ?? '') }}"
                    placeholder="Ej: Juan Pérez" required>
                @error('name') <span class="field-error">{{ $message }}</span> @enderror

                <label>Correo electrónico</label>
                <input type="email" name="email"
                    value="{{ old('email', $user->email ?? '') }}"
                    placeholder="juan@firma.com" required>
                @error('email') <span class="field-error">{{ $message }}</span> @enderror

                <label>Rol</label>
                <select name="role" required>
                    <option value="">— Seleccionar —</option>
                    <option value="admin"
                        {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>
                        Administrador — acceso total + gestión de usuarios
                    </option>
                    <option value="abogado"
                        {{ old('role', $user->role ?? '') === 'abogado' ? 'selected' : '' }}>
                        Abogado — crear/editar casos y acciones del flujo
                    </option>
                    <option value="readonly"
                        {{ old('role', $user->role ?? '') === 'readonly' ? 'selected' : '' }}>
                        Solo lectura — ver casos y dashboard sin modificar
                    </option>
                </select>
                @error('role') <span class="field-error">{{ $message }}</span> @enderror

                <hr class="divider">

                <label>
                    Contraseña
                    @if(isset($user))
                        <span style="font-weight:normal;color:#6b7280"> — dejar vacío para no cambiarla</span>
                    @endif
                </label>
                <input type="password" name="password"
                    placeholder="{{ isset($user) ? 'Nueva contraseña (opcional)' : 'Mínimo 8 caracteres' }}"
                    {{ isset($user) ? '' : 'required' }}>
                <span class="helper">Mínimo 8 caracteres.</span>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror

                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirmation"
                    placeholder="{{ isset($user) ? 'Repetir nueva contraseña' : 'Repetir contraseña' }}"
                    {{ isset($user) ? '' : 'required' }}>
                @error('password_confirmation') <span class="field-error">{{ $message }}</span> @enderror

                <div class="actions">
                    <button type="submit" class="btn">
                        {{ isset($user) ? 'Guardar cambios' : 'Crear usuario' }}
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>