<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Alerta — INDEMNI SOAT</title>
<style>
  body{margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#111827}
  .wrap{max-width:620px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
  .header{padding:24px 32px;color:#fff}
  .header-critico{background:linear-gradient(135deg,#dc3545,#b02a37)}
  .header-urgente{background:linear-gradient(135deg,#e07b22,#c26418)}
  .header-info{background:linear-gradient(135deg,#2563eb,#1d4ed8)}
  .header h1{margin:0;font-size:22px;font-weight:bold}
  .header p{margin:6px 0 0;font-size:13px;opacity:.85}
  .body{padding:28px 32px}
  .caso-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:18px 20px;margin-bottom:20px}
  .caso-num{font-size:20px;font-weight:bold;color:#1f2937;margin-bottom:4px}
  .caso-victima{font-size:15px;color:#374151;margin-bottom:12px}
  .campo{display:flex;gap:8px;margin-bottom:6px;font-size:13px}
  .campo-label{font-weight:bold;color:#6b7280;min-width:120px;flex-shrink:0}
  .campo-value{color:#111827}
  .evento-box{border-left:4px solid #2563eb;padding:12px 16px;background:#eff6ff;border-radius:0 8px 8px 0;margin-bottom:20px}
  .evento-box.critico{border-color:#dc3545;background:#fff5f5}
  .evento-box.urgente{border-color:#e07b22;background:#fff8f0}
  .evento-titulo{font-size:16px;font-weight:bold;margin-bottom:4px}
  .evento-critico .evento-titulo{color:#842029}
  .evento-urgente .evento-titulo{color:#7c3b00}
  .evento-info .evento-titulo{color:#1d4ed8}
  .evento-detalle{font-size:13px;color:#374151}
  .badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:bold}
  .badge-critico{background:#f8d7da;color:#842029}
  .badge-urgente{background:#fff3cd;color:#7c3b00}
  .badge-info{background:#cff4fc;color:#055160}
  .btn{display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:bold;margin-top:8px}
  .footer{background:#f8fafc;padding:16px 32px;font-size:12px;color:#9ca3af;text-align:center;border-top:1px solid #e5e7eb}
  .divider{height:1px;background:#e5e7eb;margin:20px 0}
  .alerta-actual{background:#fef9c3;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px}
</style>
</head>
<body>
<div class="wrap">

  {{-- Header según nivel --}}
  <div class="header header-{{ $nivel }}">
    <h1>
      @if($nivel === 'critico') 🔴 Alerta crítica
      @elseif($nivel === 'urgente') 🟠 Acción urgente
      @else 🔵 Actualización del flujo
      @endif
    </h1>
    <p>INDEMNI SOAT · {{ now()->format('d/m/Y H:i') }} · Hola, {{ $destinatario->name }}</p>
  </div>

  <div class="body">

    {{-- Evento --}}
    <div class="evento-box {{ $nivel }} evento-{{ $nivel }}">
      <div class="evento-titulo">{{ $evento }}</div>
      @if($detalle)
        <div class="evento-detalle">{{ $detalle }}</div>
      @endif
    </div>

    {{-- Datos del caso --}}
    <div class="caso-box">
      <div class="caso-num">{{ $caso->numero_caso }}
        <span class="badge badge-{{ $nivel }}" style="font-size:11px;margin-left:8px">
          {{ $caso->texto_alerta }}
        </span>
      </div>
      <div class="caso-victima">{{ $caso->nombres }} {{ $caso->apellidos }}</div>

      <div class="campo">
        <span class="campo-label">Cédula</span>
        <span class="campo-value">{{ $caso->cedula }}</span>
      </div>
      <div class="campo">
        <span class="campo-label">Aseguradora</span>
        <span class="campo-value">{{ $caso->aseguradora ?: 'No registrada' }}</span>
      </div>
      <div class="campo">
        <span class="campo-label">Estado actual</span>
        <span class="campo-value">{{ $caso->estado ?: 'N/A' }}</span>
      </div>
      <div class="campo">
        <span class="campo-label">Avance</span>
        <span class="campo-value">{{ $caso->porcentaje_avance ?? 0 }}%</span>
      </div>
      @if($caso->fecha_prescripcion)
        <div class="campo">
          <span class="campo-label">Prescripción</span>
          <span class="campo-value">
            {{ \Carbon\Carbon::parse($caso->fecha_prescripcion)->format('d/m/Y') }}
            ({{ $caso->diasParaPrescripcion() }} días)
          </span>
        </div>
      @endif
      @if($caso->junta_asignada)
        <div class="campo">
          <span class="campo-label">Junta</span>
          <span class="campo-value">{{ $caso->junta_asignada }}</span>
        </div>
      @endif
    </div>

    {{-- Alerta actual del sistema --}}
    @if($caso->texto_alerta && $caso->texto_alerta !== 'Normal')
      <div class="alerta-actual">
        ⚠️ <strong>Alerta del sistema:</strong> {{ $caso->texto_alerta }}
      </div>
    @endif

    <p style="font-size:13px;color:#6b7280;margin-bottom:16px">
      Accede al sistema para registrar la siguiente acción en este caso.
    </p>

    <a href="{{ config('app.url') }}/casos/{{ $caso->id }}" class="btn">
      Ver caso en el sistema →
    </a>
  </div>

  <div class="footer">
    INDEMNI SOAT · Sistema de gestión jurídica<br>
    Este es un correo automático. No responder a este mensaje.
  </div>
</div>
</body>
</html>