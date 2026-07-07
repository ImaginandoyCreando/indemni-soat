@extends('layouts.app')

@section('title', 'WhatsApp')

@section('content')
<div style="max-width:900px; margin:0 auto; padding:28px 20px;">

    {{-- ── Encabezado ──────────────────────────────────────────────────────── --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:28px;">
        <div style="
            width:42px; height:42px; border-radius:12px;
            background:linear-gradient(135deg,#25D366,#128C7E);
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
            box-shadow:0 4px 14px rgba(37,211,102,0.35);
        ">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="white">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.555 4.122 1.526 5.855L.057 23.882l6.198-1.625A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.003-1.366l-.359-.213-3.718.976.993-3.624-.234-.372A9.818 9.818 0 1112 21.818z"/>
            </svg>
        </div>
        <div>
            <h1 style="font-size:20px; font-weight:700; color:var(--text-1); margin:0; line-height:1.2;">
                Notificaciones WhatsApp
            </h1>
            <p style="font-size:12px; color:var(--text-3); margin:3px 0 0;">
                Alertas automáticas de casos jurídicos · 8:00 AM Colombia
            </p>
        </div>
    </div>

    {{-- ── Alerta de credenciales no configuradas ───────────────────────────── --}}
    @if(empty(config('whatsapp.instance_id')) || empty(config('whatsapp.token')))
    <div style="
        padding:14px 18px; border-radius:10px; margin-bottom:20px;
        background:rgba(251,191,36,0.1); border:1px solid rgba(251,191,36,0.3);
        display:flex; align-items:flex-start; gap:12px;
    ">
        <span style="font-size:18px; flex-shrink:0;">⚠️</span>
        <div>
            <div style="font-size:13px; font-weight:600; color:#D97706;">Credenciales no configuradas</div>
            <div style="font-size:12px; color:var(--text-3); margin-top:3px;">
                Agrega <code style="background:var(--bg-input);padding:1px 6px;border-radius:4px;font-size:11px;">WHATSAPP_INSTANCE_ID</code>
                y <code style="background:var(--bg-input);padding:1px 6px;border-radius:4px;font-size:11px;">WHATSAPP_TOKEN</code>
                en Koyeb → Environment para activar los envíos.
            </div>
        </div>
    </div>
    @endif

    {{-- ── Mensajes de sesión ───────────────────────────────────────────────── --}}
    @if(session('success'))
    <div style="
        padding:12px 16px; border-radius:10px; margin-bottom:20px;
        background:rgba(37,211,102,0.1); border:1px solid rgba(37,211,102,0.25);
        display:flex; align-items:center; gap:10px; font-size:13px; color:#16A34A;
    ">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="
        padding:12px 16px; border-radius:10px; margin-bottom:20px;
        background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25);
        display:flex; align-items:center; gap:10px; font-size:13px; color:#DC2626;
    ">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Grid superior: Formulario + Cómo funciona ───────────────────────── --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">

        {{-- Formulario --}}
        <div style="
            background:var(--bg-card); border:1px solid var(--border);
            border-radius:14px; padding:22px;
        ">
            <h2 style="font-size:14px; font-weight:700; color:var(--text-1); margin:0 0 18px; display:flex; align-items:center; gap:8px;">
                <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="7" r="3"/><path d="M3 17s1.5-4 7-4 7 4 7 4"/><path d="M14 3l2 2-2 2M16 5h-3"/></svg>
                Agregar contacto
            </h2>

            <form method="POST" action="{{ route('whatsapp.store') }}">
                @csrf
                <div style="display:flex; flex-direction:column; gap:13px;">

                    <div>
                        <label style="display:block; font-size:11.5px; font-weight:600; color:var(--text-3); margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">
                            Nombre completo
                        </label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required
                               placeholder="Ej: Juan Rodríguez"
                               style="
                                   width:100%; box-sizing:border-box;
                                   background:var(--bg-input); border:1px solid var(--border);
                                   border-radius:8px; padding:9px 12px;
                                   font-size:13px; color:var(--text-1);
                                   outline:none; transition:border-color .15s;
                                   font-family:inherit;
                               "
                               onfocus="this.style.borderColor='#25D366'" onblur="this.style.borderColor='var(--border)'">
                        @error('nombre')
                            <p style="font-size:11px; color:#EF4444; margin:4px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11.5px; font-weight:600; color:var(--text-3); margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">
                            Número WhatsApp
                            <span style="font-weight:400; text-transform:none; letter-spacing:0;">(con o sin +57)</span>
                        </label>
                        <input type="tel" name="numero" value="{{ old('numero') }}" required
                               placeholder="3001234567 o 573001234567"
                               style="
                                   width:100%; box-sizing:border-box;
                                   background:var(--bg-input); border:1px solid var(--border);
                                   border-radius:8px; padding:9px 12px;
                                   font-size:13px; color:var(--text-1);
                                   outline:none; transition:border-color .15s;
                                   font-family:inherit;
                               "
                               onfocus="this.style.borderColor='#25D366'" onblur="this.style.borderColor='var(--border)'">
                        @error('numero')
                            <p style="font-size:11px; color:#EF4444; margin:4px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label style="display:block; font-size:11.5px; font-weight:600; color:var(--text-3); margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">
                            Rol
                        </label>
                        <select name="rol" style="
                            width:100%; box-sizing:border-box;
                            background:var(--bg-input); border:1px solid var(--border);
                            border-radius:8px; padding:9px 12px;
                            font-size:13px; color:var(--text-1);
                            outline:none; transition:border-color .15s;
                            font-family:inherit; cursor:pointer;
                        ">
                            <option value="general" {{ old('rol') === 'general' ? 'selected' : '' }}>General</option>
                            <option value="admin"   {{ old('rol') === 'admin'   ? 'selected' : '' }}>Admin</option>
                            <option value="abogado" {{ old('rol') === 'abogado' ? 'selected' : '' }}>Abogado</option>
                        </select>
                    </div>

                    <button type="submit" style="
                        width:100%; padding:10px;
                        background:linear-gradient(135deg,#25D366,#128C7E);
                        color:white; font-size:13px; font-weight:600;
                        border:none; border-radius:8px; cursor:pointer;
                        font-family:inherit; letter-spacing:.2px;
                        box-shadow:0 3px 10px rgba(37,211,102,0.3);
                        transition:opacity .15s;
                    " onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                        Agregar contacto
                    </button>

                </div>
            </form>
        </div>

        {{-- Cómo funciona --}}
        <div style="
            background:var(--bg-card); border:1px solid var(--border);
            border-radius:14px; padding:22px;
        ">
            <h2 style="font-size:14px; font-weight:700; color:var(--text-1); margin:0 0 18px; display:flex; align-items:center; gap:8px;">
                <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="10" r="8"/><path d="M10 6v4l2.5 2.5"/></svg>
                ¿Cómo funciona?
            </h2>

            <div style="display:flex; flex-direction:column; gap:10px;">

                <div style="
                    display:flex; align-items:flex-start; gap:12px;
                    padding:11px 13px; border-radius:9px;
                    background:var(--bg-input); border:1px solid var(--border);
                ">
                    <span style="font-size:17px; flex-shrink:0; margin-top:1px;">⏰</span>
                    <div>
                        <div style="font-size:12.5px; font-weight:600; color:var(--text-1);">Envío automático diario</div>
                        <div style="font-size:11.5px; color:var(--text-3); margin-top:2px; line-height:1.5;">
                            Todos los días a las <strong style="color:var(--text-2);">8:00 AM Colombia</strong> el sistema revisa casos con alertas activas y notifica a todos los contactos.
                        </div>
                    </div>
                </div>

                <div style="
                    display:flex; align-items:flex-start; gap:12px;
                    padding:11px 13px; border-radius:9px;
                    background:rgba(239,68,68,0.07); border:1px solid rgba(239,68,68,0.18);
                ">
                    <span style="width:10px;height:10px;border-radius:50%;background:#EF4444;flex-shrink:0;margin-top:5px;"></span>
                    <div>
                        <div style="font-size:12.5px; font-weight:600; color:var(--text-1);">Alertas críticas · cada 7 días</div>
                        <div style="font-size:11.5px; color:var(--text-3); margin-top:2px;">Prescripción próxima, Desacato</div>
                    </div>
                </div>

                <div style="
                    display:flex; align-items:flex-start; gap:12px;
                    padding:11px 13px; border-radius:9px;
                    background:rgba(249,115,22,0.07); border:1px solid rgba(249,115,22,0.18);
                ">
                    <span style="width:10px;height:10px;border-radius:50%;background:#F97316;flex-shrink:0;margin-top:5px;"></span>
                    <div>
                        <div style="font-size:12.5px; font-weight:600; color:var(--text-1);">Alertas urgentes · cada 3 días</div>
                        <div style="font-size:11.5px; color:var(--text-3); margin-top:2px;">Sin respuesta aseguradora, Queja, Seguimiento tutela</div>
                    </div>
                </div>

                <div style="
                    display:flex; align-items:flex-start; gap:12px;
                    padding:11px 13px; border-radius:9px;
                    background:rgba(59,130,246,0.07); border:1px solid rgba(59,130,246,0.18);
                ">
                    <span style="width:10px;height:10px;border-radius:50%;background:#3B82F6;flex-shrink:0;margin-top:5px;"></span>
                    <div>
                        <div style="font-size:12.5px; font-weight:600; color:var(--text-1);">Otras alertas · una sola vez</div>
                        <div style="font-size:11.5px; color:var(--text-3); margin-top:2px;">Si el caso avanza, la alerta anterior se limpia automáticamente.</div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- ── Tabla de contactos ───────────────────────────────────────────────── --}}
    <div style="
        background:var(--bg-card); border:1px solid var(--border);
        border-radius:14px; overflow:hidden;
    ">
        {{-- Cabecera de la tabla --}}
        <div style="
            padding:16px 22px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between;
        ">
            <div>
                <h2 style="font-size:14px; font-weight:700; color:var(--text-1); margin:0;">
                    Contactos registrados
                </h2>
                <p style="font-size:11.5px; color:var(--text-3); margin:3px 0 0;">
                    {{ $contactos->count() }} {{ $contactos->count() === 1 ? 'contacto' : 'contactos' }} en total ·
                    {{ $contactos->where('activo', true)->count() }} activos
                </p>
            </div>
        </div>

        @if($contactos->isEmpty())
            <div style="padding:48px; text-align:center;">
                <div style="
                    width:52px; height:52px; border-radius:14px; margin:0 auto 14px;
                    background:var(--bg-input); display:flex; align-items:center; justify-content:center;
                ">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.5">
                        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                </div>
                <div style="font-size:13.5px; font-weight:600; color:var(--text-2);">Sin contactos aún</div>
                <div style="font-size:12px; color:var(--text-3); margin-top:5px;">
                    Agrega el primero usando el formulario de arriba.
                </div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--bg-input);">
                            <th style="padding:10px 22px; text-align:left; font-size:10.5px; font-weight:700; color:var(--text-3); text-transform:uppercase; letter-spacing:.6px; white-space:nowrap;">Nombre</th>
                            <th style="padding:10px 16px; text-align:left; font-size:10.5px; font-weight:700; color:var(--text-3); text-transform:uppercase; letter-spacing:.6px; white-space:nowrap;">Número</th>
                            <th style="padding:10px 16px; text-align:left; font-size:10.5px; font-weight:700; color:var(--text-3); text-transform:uppercase; letter-spacing:.6px; white-space:nowrap;">Rol</th>
                            <th style="padding:10px 16px; text-align:center; font-size:10.5px; font-weight:700; color:var(--text-3); text-transform:uppercase; letter-spacing:.6px; white-space:nowrap;">Estado</th>
                            <th style="padding:10px 22px; text-align:center; font-size:10.5px; font-weight:700; color:var(--text-3); text-transform:uppercase; letter-spacing:.6px; white-space:nowrap;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contactos as $contacto)
                        <tr style="border-top:1px solid var(--border); transition:background .12s;"
                            onmouseover="this.style.background='var(--bg-hover)'"
                            onmouseout="this.style.background='transparent'">

                            {{-- Nombre --}}
                            <td style="padding:14px 22px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="
                                        width:32px; height:32px; border-radius:8px; flex-shrink:0;
                                        background:linear-gradient(135deg,#25D366,#128C7E);
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:12px; font-weight:700; color:white;
                                    ">
                                        {{ strtoupper(substr($contacto->nombre, 0, 1)) }}
                                    </div>
                                    <span style="font-size:13px; font-weight:600; color:var(--text-1);">
                                        {{ $contacto->nombre }}
                                    </span>
                                </div>
                            </td>

                            {{-- Número --}}
                            <td style="padding:14px 16px;">
                                <span style="
                                    font-size:12.5px; color:var(--text-2); font-family:monospace;
                                    background:var(--bg-input); padding:3px 8px; border-radius:5px;
                                    border:1px solid var(--border); white-space:nowrap;
                                ">+{{ $contacto->numero }}</span>
                            </td>

                            {{-- Rol --}}
                            <td style="padding:14px 16px;">
                                <span style="
                                    font-size:11.5px; font-weight:600; padding:3px 9px; border-radius:20px;
                                    {{ $contacto->rol === 'admin' ? 'background:rgba(139,92,246,0.12);color:#7C3AED;' : ($contacto->rol === 'abogado' ? 'background:rgba(59,130,246,0.12);color:#2563EB;' : 'background:var(--bg-input);color:var(--text-3);') }}
                                    text-transform:capitalize;
                                ">{{ $contacto->rol }}</span>
                            </td>

                            {{-- Estado --}}
                            <td style="padding:14px 16px; text-align:center;">
                                <span style="
                                    font-size:11.5px; font-weight:600; padding:3px 10px; border-radius:20px;
                                    {{ $contacto->activo ? 'background:rgba(37,211,102,0.12);color:#16A34A;' : 'background:var(--bg-input);color:var(--text-3);' }}
                                    white-space:nowrap;
                                ">
                                    {{ $contacto->activo ? '● Activo' : '○ Inactivo' }}
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td style="padding:14px 22px;">
                                <div style="display:flex; align-items:center; justify-content:center; gap:6px;">

                                    {{-- Probar --}}
                                    <form method="POST" action="{{ route('whatsapp.probar', $contacto) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit"
                                                title="Enviar mensaje de prueba"
                                                onclick="return confirm('¿Enviar mensaje de prueba a {{ $contacto->nombre }}?')"
                                                style="
                                                    width:30px; height:30px; border-radius:7px; border:1px solid var(--border);
                                                    background:var(--bg-input); cursor:pointer; display:flex;
                                                    align-items:center; justify-content:center; transition:all .14s;
                                                    color:#16A34A;
                                                "
                                                onmouseover="this.style.background='rgba(37,211,102,0.12)';this.style.borderColor='rgba(37,211,102,0.3)'"
                                                onmouseout="this.style.background='var(--bg-input)';this.style.borderColor='var(--border)'">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Toggle activo --}}
                                    <form method="POST" action="{{ route('whatsapp.toggle', $contacto) }}" style="margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                title="{{ $contacto->activo ? 'Desactivar' : 'Activar' }}"
                                                style="
                                                    width:30px; height:30px; border-radius:7px; border:1px solid var(--border);
                                                    background:var(--bg-input); cursor:pointer; display:flex;
                                                    align-items:center; justify-content:center; transition:all .14s;
                                                    color:var(--text-3);
                                                "
                                                onmouseover="this.style.background='rgba(59,130,246,0.1)';this.style.borderColor='rgba(59,130,246,0.3)';this.style.color='#2563EB'"
                                                onmouseout="this.style.background='var(--bg-input)';this.style.borderColor='var(--border)';this.style.color='var(--text-3)'">
                                            @if($contacto->activo)
                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6M9 3h6a2 2 0 012 2v14a2 2 0 01-2 2H9a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                                </svg>
                                            @else
                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Eliminar --}}
                                    <form method="POST" action="{{ route('whatsapp.destroy', $contacto) }}" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Eliminar contacto"
                                                onclick="return confirm('¿Eliminar a {{ $contacto->nombre }}? Esta acción no se puede deshacer.')"
                                                style="
                                                    width:30px; height:30px; border-radius:7px; border:1px solid var(--border);
                                                    background:var(--bg-input); cursor:pointer; display:flex;
                                                    align-items:center; justify-content:center; transition:all .14s;
                                                    color:var(--text-3);
                                                "
                                                onmouseover="this.style.background='rgba(239,68,68,0.1)';this.style.borderColor='rgba(239,68,68,0.3)';this.style.color='#DC2626'"
                                                onmouseout="this.style.background='var(--bg-input)';this.style.borderColor='var(--border)';this.style.color='var(--text-3)'">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
