<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios — INDEMNI SOAT</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;margin:0;color:#111827}
.layout{display:flex;min-height:100vh}
.sidebar{width:260px;background:linear-gradient(180deg,#1f2937 0%,#172033 100%);color:#fff;padding:25px 18px;flex-shrink:0}
.brand{font-size:28px;font-weight:bold;margin-bottom:30px;line-height:1.2}
.menu a{display:block;padding:12px 14px;margin-bottom:8px;text-decoration:none;color:#fff;background:#374151;border-radius:8px;transition:.2s}
.menu a:hover{background:#2563eb}
.menu a.active{background:#2563eb}
.user-box{margin-top:auto;padding-top:20px;border-top:1px solid #374151;font-size:13px}
.user-name{font-weight:bold;margin-bottom:4px}
.user-role{font-size:11px;color:#9ca3af}
.logout-btn{display:block;margin-top:10px;padding:8px 12px;background:#374151;color:#fff;text-decoration:none;border-radius:6px;font-size:12px;text-align:center;border:none;cursor:pointer;width:100%}
.logout-btn:hover{background:#dc3545}
.content{flex:1;padding:30px}
.container{max-width:900px;margin:auto}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.btn{display:inline-block;padding:10px 16px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;border:none;cursor:pointer;font-size:13px}
.btn:hover{opacity:.9}
.btn-danger{background:#dc3545}
.btn-warning{background:#ffc107;color:#111}
.btn-secondary{background:#6b7280}
.alert{padding:12px 14px;border-radius:8px;margin-bottom:18px;font-size:13px}
.alert-success{background:#d1e7dd;color:#0f5132;border:1px solid #badbcc}
.alert-error{background:#f8d7da;color:#842029;border:1px solid #f5c2c7}
.table-wrap{background:#fff;border-radius:12px;border:1px solid #d7dce3;box-shadow:0 8px 24px rgba(15,23,42,.04);overflow:hidden}
table{width:100%;border-collapse:collapse}
th,td{padding:12px 14px;border-bottom:1px solid #e5e7eb;text-align:left;font-size:14px}
th{background:#eef2f7;font-size:13px;font-weight:bold}
.badge-rol{display:inline-block;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:bold;color:#fff}
.badge-admin{background:#2563eb}
.badge-abogado{background:#198754}
.badge-readonly{background:#6b7280}
.you-chip{display:inline-block;padding:2px 6px;border-radius:4px;font-size:10px;background:#fef3c7;color:#92400e;margin-left:6px;font-weight:bold}
form.inline{display:inline}
@media(max-width:900px){.layout{flex-direction:column}.sidebar{width:100%}}
</style>
</head>
<body>
<div class="layout">
    <aside class="sidebar" style="display:flex;flex-direction:column">
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
        <div class="container">
            <div class="topbar">
                <h1 style="margin:0">Gestión de usuarios</h1>
                <a href="{{ route('users.create') }}" class="btn">+ Nuevo usuario</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>
                                    <strong>{{ $usuario->name }}</strong>
                                    @if($usuario->id === auth()->id())
                                        <span class="you-chip">TÚ</span>
                                    @endif
                                </td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    <span class="badge-rol badge-{{ $usuario->role }}">
                                        {{ $usuario->textoRol() }}
                                    </span>
                                </td>
                                <td>{{ $usuario->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('users.edit', $usuario) }}" class="btn btn-warning" style="padding:6px 12px;font-size:12px">Editar</a>

                                    @if($usuario->id !== auth()->id())
                                        <form class="inline" action="{{ route('users.destroy', $usuario) }}" method="POST"
                                            onsubmit="return confirm('¿Eliminar al usuario {{ $usuario->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding:6px 12px;font-size:12px">Eliminar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:24px;color:#6b7280">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>