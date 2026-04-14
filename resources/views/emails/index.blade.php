@extends('layouts.app')

@section('title', 'Integración de Correos')

@section('content')
{{-- Cabecera --}}
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
            <path d="M3 8h10M8 3v10" stroke="currentColor" stroke-width="1.5"/>
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
    <!-- Configuración de Cuentas -->
    <div class="is-animate-rise is-stagger-1"
         style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:8px;padding:20px;">
        <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="margin-right:8px;">
                <path d="M3 2h10v12H3V2zm2 2v8h6V4H5z" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Cuentas de Correo Configuradas
        </div>
        
        <div style="space-y:12px;">
            @foreach($emailIntegrations ?? [] as $integration)
                <div style="padding:12px;border:1px solid var(--border);border-radius:6px;margin-bottom:8px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <div>
                            <div style="font-weight:600;color:var(--text-1);">{{ $integration->email_address }}</div>
                            <div style="font-size:11px;color:var(--text-3);">{{ $integration->email_provider }}</div>
                        </div>
                        <span style="padding:4px 8px;border-radius:12px;font-size:10px;font-weight:600;
                                   {{ $integration->is_active ? 'background:rgba(5,150,105,0.12);color:#1DBD7F;' : 'background:rgba(229,57,53,0.12);color:#F26F6F;' }}">
                            {{ $integration->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                </div>
            @endforeach
            
            @if(!isset($emailIntegrations) || $emailIntegrations->count() == 0)
                <div style="text-align:center;padding:40px;color:var(--text-3);font-style:italic;">
                    No hay cuentas configuradas
                </div>
            @endif
        </div>
        
        <button class="is-btn-primary" style="margin-top:16px;width:100%;">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                <path d="M8 2v6M4 6h8" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Agregar Cuenta
        </button>
    </div>

    <!-- Estadísticas -->
    <div class="is-animate-rise is-stagger-2"
         style="background:var(--bg-card);border:1px solid var(--border);
                border-radius:8px;padding:20px;">
        <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="margin-right:8px;">
                <path d="M2 8h12M8 2v6" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Estadísticas de Procesamiento
        </div>
        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div style="background:var(--bg-input);border-radius:6px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:700;color:#4B78FF;">{{ $stats['emails_today'] ?? 0 }}</div>
                <div style="font-size:11px;color:var(--text-3);margin-top:2px;">Correos hoy</div>
            </div>
            <div style="background:var(--bg-input);border-radius:6px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:700;color:#1DBD7F;">{{ $stats['cases_updated'] ?? 0 }}</div>
                <div style="font-size:11px;color:var(--text-3);margin-top:2px;">Casos actualizados</div>
            </div>
            <div style="background:var(--bg-input);border-radius:6px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:700;color:#F26F6F;">{{ $stats['overdue_cases'] ?? 0 }}</div>
                <div style="font-size:11px;color:var(--text-3);margin-top:2px;">Casos vencidos</div>
            </div>
            <div style="background:var(--bg-input);border-radius:6px;padding:12px;text-align:center;">
                <div style="font-size:20px;font-weight:700;color:#FFB800;">{{ $stats['pending_alerts'] ?? 0 }}</div>
                <div style="font-size:11px;color:var(--text-3);margin-top:2px;">Alertas pendientes</div>
            </div>
        </div>
    </div>
</div>

<!-- Correos Recientes -->
<div class="is-animate-rise is-stagger-3"
     style="background:var(--bg-card);border:1px solid var(--border);
            border-radius:8px;padding:20px;margin-top:20px;">
    <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="margin-right:8px;">
            <path d="M2 4h12l-1 8H3l-1-8z" stroke="currentColor" stroke-width="1.5"/>
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
                @foreach($recentEmails ?? [] as $email)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:12px;font-size:13px;">{{ $email->email_date->format('d/m/Y H:i') }}</td>
                        <td style="padding:12px;font-size:13px;">{{ $email->from_email }}</td>
                        <td style="padding:12px;font-size:13px;">{{ \Illuminate\Support\Str::limit($email->subject, 50) }}</td>
                        <td style="padding:12px;font-size:13px;">
                            <a href="{{ route('casos.show', $email->caso) }}" style="color:#4B78FF;text-decoration:none;font-weight:600;">
                                {{ $email->caso->numero_caso }}
                            </a>
                        </td>
                        <td style="padding:12px;">
                            <span style="padding:4px 8px;border-radius:12px;font-size:10px;font-weight:600;
                                       background:{{ $this->getTypeColor($email->email_type) }};color:white;">
                                {{ $this->getTypeLabel($email->email_type) }}
                            </span>
                        </td>
                        <td style="padding:12px;font-size:13px;">{{ $email->detected_insurance ?? 'N/A' }}</td>
                    </tr>
                @endforeach
                
                @if(!isset($recentEmails) || $recentEmails->count() == 0)
                    <tr>
                        <td colspan="6" style="padding:40px;text-align:center;color:var(--text-3);font-style:italic;">
                            No hay correos procesados recientemente
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Configuración de Alertas -->
<div class="is-animate-rise is-stagger-4"
     style="background:var(--bg-card);border:1px solid var(--border);
            border-radius:8px;padding:20px;margin-top:20px;">
    <div style="font-size:16px;font-weight:600;color:var(--text-1);margin-bottom:16px;">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="margin-right:8px;">
            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/>
            <path d="M8 11v1M8 5v.01" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        Configuración de Alertas
    </div>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="is-form-section">
            <div class="is-form-label">Alerta de casos sin respuesta (días)</div>
            <input type="number" class="is-input" value="30" min="15" max="90">
        </div>
        <div class="is-form-section">
            <div class="is-form-label">Frecuencia de revisión</div>
            <select class="is-select">
                <option>Cada hora</option>
                <option selected>Cada 6 horas</option>
                <option>Cada día</option>
            </select>
        </div>
    </div>
    
    <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;margin-top:16px;">
        <button class="is-btn-primary">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                <path d="M8 2v6M4 6h8" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Guardar Configuración
        </button>
        <button class="is-btn-ghost">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style="margin-right:6px;">
                <path d="M1 3h14v8H1V3zm2 2v4h10V5H3z" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            Sincronizar Ahora
        </button>
    </div>
</div>

<?php
function getTypeColor($type) {
    $colors = [
        'solicitud_enviada' => '#4B78FF',
        'respuesta_positiva' => '#1DBD7F',
        'respuesta_negativa' => '#F26F6F',
        'en_proceso' => '#FFB800',
        'requiere_documentos' => '#8B5CF6',
        'citacion' => '#EC4899',
        'otro' => '#64748B',
    ];
    return $colors[$type] ?? '#64748B';
}

function getTypeLabel($type) {
    $labels = [
        'solicitud_enviada' => 'Solicitud',
        'respuesta_positiva' => 'Positiva',
        'respuesta_negativa' => 'Negativa',
        'en_proceso' => 'En Proceso',
        'requiere_documentos' => 'Documentos',
        'citacion' => 'Citación',
        'otro' => 'Otro',
    ];
    return $labels[$type] ?? 'Otro';
}
?>
@endsection
