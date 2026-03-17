<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Resumen diario — INDEMNI SOAT</title>
<style>
  body{margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#111827}
  .wrap{max-width:680px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
  .header{padding:24px 32px;background:linear-gradient(135deg,#1f2937,#172033);color:#fff}
  .header h1{margin:0;font-size:22px;font-weight:bold}
  .header p{margin:6px 0 0;font-size:13px;opacity:.8}
  .body{padding:28px 32px}
  .resumen-cards{display:table;width:100%;border-collapse:separate;border-spacing:12px;margin-bottom:24px}
  .resumen-card{display:table-cell;text-align:center;padding:16px;border-radius:10px;width:33%}
  .card-red{background:#fff5f5;border:1px solid #f5c2c7}
  .card-orange{background:#fffaf0;border:1px solid #ffe69c}
  .card-blue{background:#f3fbff;border:1px solid #b6effb}
  .card-num{font-size:32px;font-weight:bold;margin-bottom:4px}
  .card-num-red{color:#842029}
  .card-num-orange{color:#7c3b00}
  .card-num-blue{color:#055160}
  .card-label{font-size:12px;color:#6b7280}
  .section-title{font-size:15px;font-weight:bold;margin:20px 0 10px;padding-left:10px;border-left:4px solid #2563eb;color:#111827}
  .section-title.red{border-color:#dc3545}
  .section-title.orange{border-color:#e07b22}
  .section-title.blue{border-color:#0ea5e9}
  .caso-row{border:1px solid #e5e7eb;border-radius:8px;padding:14px 16px;margin-bottom:8px}
  .caso-row-red{border-color:#f5c2c7;background:#fff9f9}
  .caso-row-orange{border-color:#ffe69c;background:#fffdf0}
  .caso-row-blue{border-color:#b6effb;background:#f9fdff}
  .caso-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px}
  .caso-num{font-weight:bold;font-size:15px;color:#1f2937}
  .caso-victima{font-size:13px;color:#374151;margin-bottom:4px}
  .caso-meta{font-size:12px;color:#6b7280}
  .badge{display:inline-block;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:bold}
  .badge-red{background:#f8d7da;color:#842029}
  .badge-orange{background:#fff3cd;color:#7c3b00}
  .badge-blue{background:#cff4fc;color:#055160}
  .alerta-text{font-size:12px;font-weight:bold;color:#374151;margin-top:4px}
  .btn-ver{display:inline-block;padding:6px 12px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;font-size:12px}
  .footer{background:#f8fafc;padding:16px 32px;font-size:12px;color:#9ca3af;text-align:center;border-top:1px solid #e5e7eb}
  .empty{text-align:center;padding:20px;color:#9ca3af;font-style:italic;font-size:13px}
  .divider{height:1px;background:#e5e7eb;margin:20px 0}
</style>
</head>
<body>
<div class="wrap">

  <div class="header">
    <h1>📋 Resumen diario — INDEMNI SOAT</h1>
    <p>{{ now()->format('d/m/Y') }} · Hola, {{ $destinatario->name }} · {{ $totalAlertas }} alerta(s) activa(s)</p>
  </div>

  <div class="body">

    {{-- Tarjetas resumen --}}
    <table class="resumen-cards">
      <tr>
        <td class="resumen-card card-red">
          <div class="card-num card-num-red">{{ $casosCriticos->count() }}</div>
          <div class="card-label">🔴 Críticos</div>
        </td>
        <td class="resumen-card card-orange">
          <div class="card-num card-num-orange">{{ $casosUrgentes->count() }}</div>
          <div class="card-label">🟠 Urgentes</div>
        </td>
        <td class="resumen-card card-blue">
          <div class="card-num card-num-blue">{{ $casosInfo->count() }}</div>
          <div class="card-label">🔵 Seguimiento</div>
        </td>
      </tr>
    </table>

    {{-- CRÍTICOS --}}
    @if($casosCriticos->count())
      <div class="section-title red">🔴 Casos críticos ({{ $casosCriticos->count() }})</div>
      @foreach($casosCriticos as $caso)
        <div class="caso-row caso-row-red">
          <div class="caso-header">
            <div>
              <div class="caso-num">{{ $caso->numero_caso }}</div>
              <div class="caso-victima">{{ $caso->nombres }} {{ $caso->apellidos }}</div>
            </div>
            <a href="{{ config('app.url') }}/casos/{{ $caso->id }}" class="btn-ver">Ver →</a>
          </div>
          <div class="caso-meta">
            {{ $caso->aseguradora ?: 'Sin aseguradora' }}
            @if($caso->fecha_prescripcion)
              · Prescripción: {{ \Carbon\Carbon::parse($caso->fecha_prescripcion)->format('d/m/Y') }}
              ({{ $caso->diasParaPrescripcion() }} días)
            @endif
          </div>
          <div class="alerta-text">⚠️ {{ $caso->texto_alerta }}</div>
        </div>
      @endforeach
    @endif

    {{-- URGENTES --}}
    @if($casosUrgentes->count())
      <div class="section-title orange">🟠 Casos urgentes ({{ $casosUrgentes->count() }})</div>
      @foreach($casosUrgentes as $caso)
        <div class="caso-row caso-row-orange">
          <div class="caso-header">
            <div>
              <div class="caso-num">{{ $caso->numero_caso }}</div>
              <div class="caso-victima">{{ $caso->nombres }} {{ $caso->apellidos }}</div>
            </div>
            <a href="{{ config('app.url') }}/casos/{{ $caso->id }}" class="btn-ver">Ver →</a>
          </div>
          <div class="caso-meta">{{ $caso->aseguradora ?: 'Sin aseguradora' }} · Estado: {{ $caso->estado ?: 'N/A' }}</div>
          <div class="alerta-text">{{ $caso->texto_alerta }}</div>
        </div>
      @endforeach
    @endif

    {{-- SEGUIMIENTO --}}
    @if($casosInfo->count())
      <div class="section-title blue">🔵 Casos en seguimiento ({{ $casosInfo->count() }})</div>
      @foreach($casosInfo as $caso)
        <div class="caso-row caso-row-blue">
          <div class="caso-header">
            <div>
              <div class="caso-num">{{ $caso->numero_caso }}</div>
              <div class="caso-victima">{{ $caso->nombres }} {{ $caso->apellidos }}</div>
            </div>
            <a href="{{ config('app.url') }}/casos/{{ $caso->id }}" class="btn-ver">Ver →</a>
          </div>
          <div class="caso-meta">{{ $caso->aseguradora ?: 'Sin aseguradora' }} · {{ $caso->estado ?: 'N/A' }}</div>
          <div class="alerta-text">{{ $caso->texto_alerta }}</div>
        </div>
      @endforeach
    @endif

    @if($totalAlertas === 0)
      <div class="empty">✅ No hay alertas activas hoy. ¡Todo bajo control!</div>
    @endif

  </div>

  <div class="footer">
    INDEMNI SOAT · Sistema de gestión jurídica · Resumen automático diario<br>
    Este correo se genera automáticamente cada mañana. No responder a este mensaje.
  </div>
</div>
</body>
</html>