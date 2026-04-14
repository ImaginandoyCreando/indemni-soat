@extends('layouts.app')

@section('title', 'Integración de Correos')

@section('content')

{{-- ── Mensajes flash ─────────────────────────────────────────────────────── --}}
@if(session('success'))
    <div style="background:rgba(29,189,127,0.12);border:1px solid #1DBD7F;border-radius:8px;
                padding:14px 18px;margin-bottom:20px;color:#1DBD7F;font-size:13px;
                display:flex;align-items:flex-start;gap:10px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:1px;">
            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M5.5 8l2 2 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <span style="white-space:pre-line;">{{ session('success') }}</span>
    </div>
@endif

@if(session('info'))
    <div style="background:rgba(75,120,255,0.10);border:1px solid #4B78FF;border-radius:8px;
                padding:14px 18px;margin-bottom:20px;color:#4B78FF;font-size:13px;
                display:flex;align-items:center;gap:10px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 7v4M8 5.5v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        {{ session('info') }}
    </div>
@endif

@if(session('error'))
    <div style="background:rgba(242,111,111,0.12);border:1px solid #F26F6F;border-radius:8px;
                padding:14px 18px;margin-bottom:20px;color:#F26F6F;font-size:13px;
                display:flex;align-items:center;gap:10px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M6 6l4 4M10 6l-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        {{ session('error') }}
    </div>
@endif

{{-- ── Cabecera ─────────────────────────────────────────────────────────────── --}}
<div class="is-animate-rise"
     style="display:flex;align-items:center;gap:14px;margin-bottom:28px;">
    <a href="{{ route('dashboard') }}"
       style="width:38px;height:38px;border-radius:6px;
              border:1px solid var(--border-2);background:var(--bg-input);
              display:flex;align-items:center;justify-content:center;
              color:var(--text-2);font-size:18px;text-decoration:none;
              transition:all .2s;flex-shrink:0;"
       onmouseover="this.style.background='var(--bg-hover)';this.style.color='var(--text-1)'"
       onmouseout="this.style.background='var(--bg-input)';this.style.color='var(--text-2)'">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>
    <div>
        <div class="is-page-title">Integración de Correos</div>
        <div style="font-size:12px;color:var(--text-2);margin-top:3px;">
            Automatización del seguimiento de casos
        </div>
    </div>
</div>

<div class="is-grid" style="grid-template-columns:1fr 1fr;gap:20px;">

    {{-- ── Cuentas configuradas ──────────────────────────────────────────────── --}}
    <div class="is-animate-rise is-stagger-1"
         style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:8px;padding:20px;">
        <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;
                    display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M2 4h12l-1 8H3L2 4z" stroke="currentColor" stroke-width="1.5"/>
                <path d="M2 4l6 5 6-5" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Cuentas de Correo Configuradas
        </div>

        @foreach($emailIntegrations ?? [] as $integration)
            <div style="padding:12px;border:1px solid var(--border);border-radius:6px;margin-bottom:8px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-weight:600;color:var(--text-1);font-size:13px;">
                            {{ $integration->email_address }}
                        </div>
                        <div style="font-size:11px;color:var(--text-3);margin-top:2px;">
                            {{ ucfirst($integration->email_provider) }}
                        </div>
                    </div>
                    <span style="padding:4px 10px;border-radius:12px;font-size:10px;font-weight:600;
                               {{ $integration->is_active
                                    ? 'background:rgba(29,189,127,0.12);color:#1DBD7F;'
                                    : 'background:rgba(242,111,111,0.12);color:#F26F6F;' }}">
                        {{ $integration->is_active ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
            </div>
        @endforeach

        @if(empty($emailIntegrations) || $emailIntegrations->count() == 0)
            <div style="text-align:center;padding:40px;color:var(--text-3);font-style:italic;">
                No hay cuentas configuradas
            </div>
        @endif

        {{-- Abre el modal de agregar cuenta --}}
        <button class="is-btn-primary" style="margin-top:16px;width:100%;"
                onclick="document.getElementById('modal-agregar-cuenta').style.display='flex'">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            Agregar Cuenta
        </button>
    </div>

    {{-- ── Estadísticas ──────────────────────────────────────────────────────── --}}
    <div class="is-animate-rise is-stagger-2"
         style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:8px;padding:20px;">
        <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;
                    display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M2 12V8h3v4H2zM6 12V5h3v7H6zM10 12V2h3v10h-3z"
                      stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            </svg>
            Estadísticas de Procesamiento
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div style="background:var(--bg-input);border-radius:6px;padding:16px;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#4B78FF;">
                    {{ $stats['emails_today'] ?? 0 }}
                </div>
                <div style="font-size:11px;color:var(--text-3);margin-top:4px;">Correos hoy</div>
            </div>
            <div style="background:var(--bg-input);border-radius:6px;padding:16px;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#1DBD7F;">
                    {{ $stats['cases_updated'] ?? 0 }}
                </div>
                <div style="font-size:11px;color:var(--text-3);margin-top:4px;">Casos actualizados</div>
            </div>
            <div style="background:var(--bg-input);border-radius:6px;padding:16px;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#F26F6F;">
                    {{ $stats['overdue_cases'] ?? 0 }}
                </div>
                <div style="font-size:11px;color:var(--text-3);margin-top:4px;">Casos vencidos</div>
            </div>
            <div style="background:var(--bg-input);border-radius:6px;padding:16px;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#FFB800;">
                    {{ $stats['pending_alerts'] ?? 0 }}
                </div>
                <div style="font-size:11px;color:var(--text-3);margin-top:4px;">Alertas pendientes</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Correos recientes ─────────────────────────────────────────────────────── --}}
<div class="is-animate-rise is-stagger-3"
     style="background:var(--bg-card);border:1px solid var(--border);
            border-radius:8px;padding:20px;margin-top:20px;">
    <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;
                display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M2 4h12l-1 8H3L2 4z" stroke="currentColor" stroke-width="1.5"/>
            <path d="M2 4l6 5 6-5" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        Correos Procesados Recientemente
    </div>

    <div style="overflow:auto;border-radius:8px;border:1px solid var(--border);">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:var(--bg-input);">
                    <th style="padding:12px;text-align:left;font-size:11px;font-weight:700;color:var(--text-3);">Fecha</th>
                    <th style="padding:12px;text-align:left;font-size:11px;font-weight:700;color:var(--text-3);">De</th>
                    <th style="padding:12px;text-align:left;font-size:11px;font-weight:700;color:var(--text-3);">Asunto</th>
                    <th style="padding:12px;text-align:left;font-size:11px;font-weight:700;color:var(--text-3);">Caso</th>
                    <th style="padding:12px;text-align:left;font-size:11px;font-weight:700;color:var(--text-3);">Tipo</th>
                    <th style="padding:12px;text-align:left;font-size:11px;font-weight:700;color:var(--text-3);">Aseguradora</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentEmails ?? [] as $email)
                    <tr style="border-bottom:1px solid var(--border);transition:background .15s;"
                        onmouseover="this.style.background='var(--bg-hover)'"
                        onmouseout="this.style.background=''">
                        <td style="padding:12px;font-size:13px;white-space:nowrap;">
                            {{ $email->email_date ? $email->email_date->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td style="padding:12px;font-size:13px;">{{ $email->from_email ?? '—' }}</td>
                        <td style="padding:12px;font-size:13px;">
                            {{ \Illuminate\Support\Str::limit($email->subject ?? '', 50) }}
                        </td>
                        <td style="padding:12px;font-size:13px;">
                            @if($email->caso)
                                <a href="{{ route('casos.show', $email->caso) }}"
                                   style="color:#4B78FF;text-decoration:none;font-weight:600;">
                                    {{ $email->caso->numero_caso }}
                                </a>
                            @else
                                <span style="color:var(--text-3);">—</span>
                            @endif
                        </td>
                        <td style="padding:12px;">
                            @php
                                $typeColors = [
                                    'solicitud_enviada'   => '#4B78FF',
                                    'respuesta_positiva'  => '#1DBD7F',
                                    'respuesta_negativa'  => '#F26F6F',
                                    'en_proceso'          => '#FFB800',
                                    'requiere_documentos' => '#8B5CF6',
                                    'citacion'            => '#EC4899',
                                    'otro'                => '#64748B',
                                ];
                                $typeLabels = [
                                    'solicitud_enviada'   => 'Solicitud',
                                    'respuesta_positiva'  => 'Positiva',
                                    'respuesta_negativa'  => 'Negativa',
                                    'en_proceso'          => 'En Proceso',
                                    'requiere_documentos' => 'Documentos',
                                    'citacion'            => 'Citación',
                                    'otro'                => 'Otro',
                                ];
                                $color = $typeColors[$email->email_type] ?? '#64748B';
                                $label = $typeLabels[$email->email_type] ?? 'Otro';
                            @endphp
                            <span style="padding:4px 8px;border-radius:12px;font-size:10px;font-weight:600;
                                         background:{{ $color }}22;color:{{ $color }};">
                                {{ $label }}
                            </span>
                        </td>
                        <td style="padding:12px;font-size:13px;">{{ $email->detected_insurance ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:48px;text-align:center;color:var(--text-3);font-style:italic;">
                            No hay correos procesados recientemente
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Configuración de alertas ─────────────────────────────────────────────── --}}
<div class="is-animate-rise is-stagger-4"
     style="background:var(--bg-card);border:1px solid var(--border);
            border-radius:8px;padding:20px;margin-top:20px;">
    <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;
                display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 5v3.5l2 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        Configuración de Alertas
    </div>

    {{-- Formulario de configuración --}}
    <form method="POST" action="{{ route('emails.saveConfig') }}" id="form-config">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="is-form-section">
                <div class="is-form-label">Alerta de casos sin respuesta (días)</div>
                <input type="number" name="dias_sin_respuesta" class="is-input"
                       value="{{ $config['dias_sin_respuesta'] ?? 30 }}" min="15" max="90">
            </div>
            <div class="is-form-section">
                <div class="is-form-label">Frecuencia de revisión</div>
                <select name="frecuencia_revision" class="is-select">
                    <option value="1h"  {{ ($config['frecuencia'] ?? '') === '1h'  ? 'selected' : '' }}>Cada hora</option>
                    <option value="6h"  {{ ($config['frecuencia'] ?? '6h') === '6h' ? 'selected' : '' }}>Cada 6 horas</option>
                    <option value="24h" {{ ($config['frecuencia'] ?? '') === '24h' ? 'selected' : '' }}>Cada día</option>
                </select>
            </div>
        </div>

        <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;margin-top:16px;">
            {{-- Guardar configuración --}}
            <button type="submit" class="is-btn-primary" id="btn-guardar">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                    <path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Guardar Configuración
            </button>

            {{-- Sincronizar — POST a emails.sync --}}
            <form method="POST" action="{{ route('emails.sync') }}" style="display:inline;" id="form-sync">
                @csrf
                <button type="submit" class="is-btn-ghost" id="btn-sync"
                        onclick="this.disabled=true;this.innerHTML='<span style=\'opacity:.6\'>Sincronizando…</span>';this.closest(\'form\').submit();">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                        <path d="M13 3v4h-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3 13v-4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.5 9A5.5 5.5 0 0112.5 7M12.5 7A5.5 5.5 0 013.5 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Sincronizar Ahora
                </button>
            </form>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Modal: Agregar Cuenta                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-agregar-cuenta"
     style="display:none;position:fixed;inset:0;z-index:1000;
            background:rgba(0,0,0,.45);backdrop-filter:blur(2px);
            align-items:center;justify-content:center;"
     onclick="if(event.target===this)cerrarModal()">

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;
                padding:28px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.3);">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <div style="font-size:17px;font-weight:700;color:var(--text-1);">Agregar Cuenta de Correo</div>
            <button onclick="cerrarModal()"
                    style="background:none;border:none;cursor:pointer;color:var(--text-3);
                           font-size:20px;line-height:1;padding:4px;">✕</button>
        </div>

        <form method="POST" action="{{ route('emails.addAccount') }}">
            @csrf
            <div class="is-form-section" style="margin-bottom:14px;">
                <div class="is-form-label">Dirección de correo</div>
                <input type="email" name="email_address" class="is-input"
                       placeholder="ejemplo@outlook.com" required>
            </div>

            <div class="is-form-section" style="margin-bottom:14px;">
                <div class="is-form-label">Proveedor</div>
                <select name="email_provider" class="is-select" id="select-provider"
                        onchange="togglePasswordField(this.value)">
                    <option value="outlook">Outlook / Hotmail</option>
                    <option value="gmail">Gmail</option>
                    <option value="imap">IMAP personalizado</option>
                </select>
            </div>

            <div class="is-form-section" style="margin-bottom:14px;" id="field-password">
                <div class="is-form-label">Contraseña de aplicación</div>
                <input type="password" name="password" class="is-input"
                       placeholder="Contraseña o token de aplicación">
                <div style="font-size:11px;color:var(--text-3);margin-top:4px;">
                    Para Outlook/Hotmail usa tu contraseña normal o token de app.
                </div>
            </div>

            <div class="is-form-section" style="margin-bottom:20px;" id="field-imap" style="display:none;">
                <div class="is-form-label">Servidor IMAP</div>
                <input type="text" name="imap_host" class="is-input"
                       placeholder="imap.tudominio.com">
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="is-btn-ghost" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="is-btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                        <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Agregar Cuenta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function cerrarModal() {
    document.getElementById('modal-agregar-cuenta').style.display = 'none';
}

function togglePasswordField(provider) {
    const imapField = document.getElementById('field-imap');
    if (imapField) {
        imapField.style.display = provider === 'imap' ? 'block' : 'none';
    }
}

// Mostrar modal si hay error de validación al agregar cuenta
@if($errors->any())
    document.getElementById('modal-agregar-cuenta').style.display = 'flex';
@endif
</script>

@endsection
