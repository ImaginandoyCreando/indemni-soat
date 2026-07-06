@extends('layouts.app')

@section('title', 'Notificaciones WhatsApp')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-7 h-7 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.555 4.122 1.526 5.855L.057 23.882l6.198-1.625A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 01-5.003-1.366l-.359-.213-3.718.976.993-3.624-.234-.372A9.818 9.818 0 1112 21.818z"/>
            </svg>
            Notificaciones WhatsApp
        </h1>
    </div>

    {{-- Alertas de sesion --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Info configuracion --}}
    @if(empty(config('whatsapp.instance_id')) || empty(config('whatsapp.token')))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg text-sm">
            ⚠️ <strong>Credenciales no configuradas.</strong>
            Agrega <code class="bg-yellow-100 px-1 rounded">WHATSAPP_INSTANCE_ID</code> y
            <code class="bg-yellow-100 px-1 rounded">WHATSAPP_TOKEN</code> en
            Koyeb → Secrets para activar los envios.
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- ── Formulario nuevo contacto ───────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Agregar contacto</h2>

            <form method="POST" action="{{ route('whatsapp.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400 focus:outline-none"
                           placeholder="Ej: Juan Abogado">
                    @error('nombre')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Número WhatsApp
                        <span class="text-gray-400 font-normal">(con o sin +57)</span>
                    </label>
                    <input type="tel" name="numero" value="{{ old('numero') }}" required
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400 focus:outline-none"
                           placeholder="Ej: 3001234567 o 573001234567">
                    @error('numero')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Rol</label>
                    <select name="rol"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400 focus:outline-none">
                        <option value="general" {{ old('rol') === 'general' ? 'selected' : '' }}>General</option>
                        <option value="admin"   {{ old('rol') === 'admin'   ? 'selected' : '' }}>Admin</option>
                        <option value="abogado" {{ old('rol') === 'abogado' ? 'selected' : '' }}>Abogado</option>
                    </select>
                </div>

                <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg transition text-sm">
                    Agregar contacto
                </button>
            </form>
        </div>

        {{-- ── Info de horario ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl shadow p-6 flex flex-col gap-4">
            <h2 class="text-lg font-semibold text-gray-700">¿Cómo funciona?</h2>
            <ul class="text-sm text-gray-600 space-y-2">
                <li class="flex gap-2">
                    <span class="text-green-500">⏰</span>
                    Las notificaciones se envían automáticamente todos los días a las <strong>8:00 AM (Colombia)</strong>.
                </li>
                <li class="flex gap-2">
                    <span class="text-green-500">🔴</span>
                    <span>Alertas <strong>críticas</strong> (prescripción, desacato): se reenvían cada <strong>7 días</strong>.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-orange-400">🟠</span>
                    <span>Alertas <strong>urgentes</strong> (sin respuesta, seguimiento tutela, queja): cada <strong>3 días</strong>.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-blue-400">🔵</span>
                    <span>Otras alertas: se notifican <strong>una sola vez</strong>.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-gray-400">✅</span>
                    <span>Si el caso cambia de estado, la alerta anterior se limpia automáticamente.</span>
                </li>
            </ul>
        </div>
    </div>

    {{-- ── Tabla de contactos ───────────────────────────────────────────── --}}
    <div class="mt-6 bg-white rounded-xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-700">
                Contactos registrados
                <span class="text-sm font-normal text-gray-400">({{ $contactos->count() }} en total)</span>
            </h2>
        </div>

        @if($contactos->isEmpty())
            <div class="p-8 text-center text-gray-400 text-sm">
                No hay contactos registrados aún. Agrega el primero arriba.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nombre</th>
                            <th class="px-4 py-3 text-left">Número</th>
                            <th class="px-4 py-3 text-left">Rol</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($contactos as $contacto)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $contacto->nombre }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 font-mono">
                                    +{{ $contacto->numero }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 capitalize">
                                        {{ $contacto->rol }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $contacto->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $contacto->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">

                                        {{-- Probar envio --}}
                                        <form method="POST" action="{{ route('whatsapp.probar', $contacto) }}">
                                            @csrf
                                            <button type="submit"
                                                    title="Enviar mensaje de prueba"
                                                    onclick="return confirm('¿Enviar mensaje de prueba a {{ $contacto->nombre }}?')"
                                                    class="text-green-600 hover:text-green-800 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Activar / Desactivar --}}
                                        <form method="POST" action="{{ route('whatsapp.toggle', $contacto) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    title="{{ $contacto->activo ? 'Desactivar' : 'Activar' }}"
                                                    class="text-blue-500 hover:text-blue-700 transition">
                                                @if($contacto->activo)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M10 9v6m4-6v6M9 3h6a2 2 0 012 2v14a2 2 0 01-2 2H9a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Eliminar --}}
                                        <form method="POST" action="{{ route('whatsapp.destroy', $contacto) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Eliminar"
                                                    onclick="return confirm('¿Eliminar a {{ $contacto->nombre }}?')"
                                                    class="text-red-500 hover:text-red-700 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
