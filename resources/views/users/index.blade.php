@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<style>
/* ── Badges de rol ── */
.is-role-badge {
    display:inline-flex;align-items:center;gap:4px;
    padding:4px 10px;border-radius:999px;
    font-size:11px;font-weight:700;
}
.is-role-admin { background:rgba(37,99,235,0.12);color:#2563eb; }
.is-role-abogado { background:rgba(25,135,84,0.12);color:#198754; }
.is-role-readonly { background:rgba(107,114,128,0.12);color:#6b7280; }

/* ── Botones de acción ── */
.is-action-btn {
    display:inline-flex;align-items:center;gap:6px;
    padding:6px 12px;border-radius:6px;
    font-size:11px;font-weight:600;
    text-decoration:none;border:1px solid transparent;
    transition:all .18s;cursor:pointer;
    font-family:'DM Sans',sans-serif;line-height:1.3;
}
.is-action-btn:hover { transform:translateY(-1px); }
.is-edit-btn { background:rgba(245,158,11,0.12);color:#F5B942;border-color:rgba(245,158,11,0.25); }
.is-edit-btn:hover { background:rgba(245,158,11,0.22); }
.is-delete-btn { background:rgba(229,57,53,0.12);color:#F26F6F;border-color:rgba(229,57,53,0.25); }
.is-delete-btn:hover { background:rgba(229,57,53,0.22); }

/* ── Chip "TÚ" ── */
.is-you-chip {
    display:inline-flex;align-items:center;
    padding:2px 8px;border-radius:4px;
    font-size:10px;font-weight:700;
    background:var(--cobalt-glow,rgba(27,79,255,.12));
    color:#4B78FF;margin-left:8px;
}
</style>

{{-- ── Cabecera ── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:flex-start;justify-content:space-between;
            margin-bottom:22px;gap:14px;flex-wrap:wrap;">
    <div>
        <div class="is-page-title">Gestión de Usuarios</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">
            <span style="background:var(--cobalt-glow,rgba(27,79,255,.12));
                         color:#4B78FF;padding:3px 10px;border-radius:20px;
                         font-weight:700;font-size:11px;">
                {{ $usuarios->count() }} usuario(s)
            </span>
        </div>
    </div>
    @if(auth()->user()->puedeGestionarUsuarios())
        <a href="{{ route('users.create') }}" class="is-btn-primary">
            <span style="width:16px;height:16px;border-radius:50%;
                         background:rgba(255,255,255,.2);display:inline-flex;
                         align-items:center;justify-content:center;font-size:13px;">+</span>
            Nuevo usuario
        </a>
    @endif
</div>

@session('success')
    <div class="is-animate-rise is-stagger-1"
         style="background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.22);
                border-radius:10px;padding:11px 16px;margin-bottom:16px;
                font-size:13px;color:#1DBD7F;display:flex;align-items:center;gap:8px;">
        <span>✓</span> {{ session('success') }}
    </div>
@endsession

{{-- ── Tabla ── --}}
<div class="is-table-wrap is-animate-rise is-stagger-1">
    <table>
        <thead>
            <tr>
                <th style="width:200px;">Nombre</th>
                <th style="width:250px;">Correo</th>
                <th style="width:120px;">Rol</th>
                <th style="width:100px;">Creado</th>
                <th style="width:140px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr class="is-animate-rise">
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:32px;height:32px;border-radius:8px;
                                        background:var(--bg-input);display:flex;
                                        align-items:center;justify-content:center;
                                        font-size:12px;font-weight:700;color:var(--text-2);">
                                {{ strtoupper(substr($usuario->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;color:var(--text-1);">
                                    {{ $usuario->name }}
                                </div>
                                @if($usuario->id === auth()->id())
                                    <span class="is-you-chip">TÚ</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--text-2);">{{ $usuario->email }}</td>
                    <td>
                        <span class="is-role-badge is-role-{{ $usuario->role }}">
                            {{ $usuario->textoRol() }}
                        </span>
                    </td>
                    <td style="color:var(--text-2);font-size:12px;">
                        {{ $usuario->created_at?->format('d/m/Y') ?? 'N/A' }}
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <a href="{{ route('users.edit', $usuario) }}" 
                               class="is-action-btn is-edit-btn">
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                    <path d="M11 2l3 3-6 6-3-3 6-6z" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                Editar
                            </a>
                            
                            @if($usuario->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $usuario) }}"
                                      style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar al usuario {{ $usuario->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="is-action-btn is-delete-btn">
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                                            <path d="M2 4h12M5 4V3a1 1 0 011-1h4a1 1 0 011 1v1M7 8v4M4 4v8a2 2 0 002 2h4a2 2 0 002-2V4" 
                                                  stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:32px;color:var(--text-3);">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <svg width="48" height="48" viewBox="0 0 16 16" fill="none" 
                                 style="opacity:0.3;">
                                <circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M2 13s1-3 5-3 5 3 5" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                            <div style="font-size:14px;">No hay usuarios registrados</div>
                            <div style="font-size:12px;color:var(--text-3);">
                                Crea el primer usuario para comenzar
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection