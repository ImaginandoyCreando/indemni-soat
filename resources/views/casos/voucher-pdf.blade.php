<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Voucher</title>
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1a1a2e; margin: 0; padding: 0; }
table { border-collapse: collapse; }
.w100 { width: 100%; }
.lbl { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #9ca3af; padding-bottom: 3px; }
.val { font-size: 12px; font-weight: bold; color: #1a1a2e; padding-bottom: 10px; }
.val-lg { font-size: 15px; font-weight: bold; color: #1a1a2e; padding-bottom: 12px; }
.sec-head { background: #f3f4f6; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #6b7280; padding: 7px 14px; border-bottom: 1px solid #d1d5db; }
.sec-body { padding: 14px 14px 4px 14px; }
.fecha-dest { background: #1a1a2e; text-align: center; padding: 14px 10px; }
.fecha-norm { background: #f3f4f6; text-align: center; padding: 14px 10px; border: 1px solid #d1d5db; }
.lbl-dest { font-size: 8px; text-transform: uppercase; letter-spacing: 0.6px; color: #aab0c0; padding-bottom: 4px; }
.val-dest { font-size: 14px; font-weight: bold; color: #f0c040; }
.val-norm { font-size: 14px; font-weight: bold; color: #1a1a2e; }
</style>
</head>
<body>

{{-- ENCABEZADO --}}
<table class="w100" style="background:#1a1a2e; padding:20px 24px;" cellpadding="0" cellspacing="0">
<tr>
  <td style="vertical-align:middle; width:55%;">
    <div style="font-size:20px; font-weight:bold; color:#f0c040; letter-spacing:1px;">INDEMNI SOAT</div>
    <div style="font-size:9px; color:#aab0c0; margin-top:4px;">Gestión Jurídica de Casos SOAT</div>
  </td>
  <td style="vertical-align:middle; width:45%; text-align:right;">
    <div style="font-size:9px; color:#aab0c0; text-transform:uppercase; letter-spacing:1px;">Comprobante de Radicación</div>
    <div style="font-size:16px; font-weight:bold; color:#f0c040; margin-top:4px;">{{ $caso->numero_caso }}</div>
  </td>
</tr>
</table>

{{-- BANDA ESTADO --}}
<table class="w100" cellpadding="0" cellspacing="0">
<tr>
  <td style="background:#f0c040; color:#1a1a2e; text-align:center; padding:9px 24px; font-size:12px; font-weight:bold;">
    Estado: {{ $caso->estado ?? 'N/A' }}
  </td>
</tr>
</table>

<div style="height:18px;"></div>

{{-- DATOS DE LA VÍCTIMA --}}
<table class="w100" cellpadding="0" cellspacing="0" style="border:1px solid #d1d5db; margin-bottom:16px;">
<tr><td class="sec-head" colspan="2">Datos de la Víctima</td></tr>
<tr>
  <td class="sec-body" colspan="2">
    <div class="lbl">Nombre completo</div>
    <div class="val-lg">{{ strtoupper(trim(($caso->nombres ?? '') . ' ' . ($caso->apellidos ?? ''))) }}</div>
  </td>
</tr>
<tr>
  <td class="sec-body" style="width:50%;">
    <div class="lbl">Cédula de ciudadanía</div>
    <div class="val">{{ $caso->cedula ?? '—' }}</div>
  </td>
  <td class="sec-body" style="width:50%;">
    <div class="lbl">Teléfono / Celular</div>
    <div class="val">{{ $caso->telefono ?? '—' }}</div>
  </td>
</tr>
<tr>
  <td class="sec-body" style="width:50%;">
    <div class="lbl">Ciudad / Departamento</div>
    <div class="val">{{ ($caso->ciudad ?? '—') }}{{ $caso->departamento ? ', ' . $caso->departamento : '' }}</div>
  </td>
  <td class="sec-body" style="width:50%;">
    <div class="lbl">Aseguradora</div>
    <div class="val">{{ strtoupper($caso->aseguradora ?? '—') }}</div>
  </td>
</tr>
@if($caso->fecha_accidente || $caso->junta_asignada)
<tr>
  <td class="sec-body" style="width:50%;">
    @if($caso->fecha_accidente)
    <div class="lbl">Fecha del accidente</div>
    <div class="val">{{ \Carbon\Carbon::parse($caso->fecha_accidente)->format('d/m/Y') }}</div>
    @endif
  </td>
  <td class="sec-body" style="width:50%;">
    @if($caso->junta_asignada)
    <div class="lbl">Junta médica asignada</div>
    <div class="val">{{ $caso->junta_asignada }}</div>
    @endif
  </td>
</tr>
@endif
</table>

{{-- FECHAS DEL PROCESO --}}
<table class="w100" cellpadding="0" cellspacing="0" style="border:1px solid #d1d5db; margin-bottom:16px;">
<tr><td class="sec-head" colspan="4">Fechas del Proceso</td></tr>
<tr>
  {{-- Fecha radicación siempre --}}
  <td style="width:25%; padding:4px;">
    <div class="fecha-dest">
      <div class="lbl-dest">Fecha de Radicación</div>
      <div class="val-dest">{{ \Carbon\Carbon::parse($caso->created_at)->format('d/m/Y') }}</div>
    </div>
  </td>
  {{-- Solicitud aseguradora siempre --}}
  <td style="width:25%; padding:4px;">
    <div class="fecha-norm">
      <div class="lbl">Solicitud a Aseguradora</div>
      <div class="val-norm">
        @if($caso->fecha_solicitud_aseguradora)
          {{ \Carbon\Carbon::parse($caso->fecha_solicitud_aseguradora)->format('d/m/Y') }}
        @else
          Pendiente
        @endif
      </div>
    </div>
  </td>
  {{-- Tutela si existe --}}
  <td style="width:25%; padding:4px;">
    @if($caso->fecha_tutela)
    <div class="fecha-norm">
      <div class="lbl">Fecha Tutela</div>
      <div class="val-norm">{{ \Carbon\Carbon::parse($caso->fecha_tutela)->format('d/m/Y') }}</div>
    </div>
    @endif
  </td>
  {{-- Pago final si existe --}}
  <td style="width:25%; padding:4px;">
    @if($caso->fecha_pago_final)
    <div class="fecha-norm">
      <div class="lbl">Fecha Pago Final</div>
      <div class="val-norm">{{ \Carbon\Carbon::parse($caso->fecha_pago_final)->format('d/m/Y') }}</div>
    </div>
    @endif
  </td>
</tr>
</table>

{{-- OBSERVACIONES --}}
@if($caso->observaciones)
<table class="w100" cellpadding="0" cellspacing="0" style="border:1px solid #d1d5db; margin-bottom:16px;">
<tr><td class="sec-head">Observaciones</td></tr>
<tr>
  <td class="sec-body" style="padding-bottom:14px;">
    <div style="font-size:10px; color:#4b5563; line-height:1.6; font-style:italic;">{{ $caso->observaciones }}</div>
  </td>
</tr>
</table>
@endif

{{-- AVISO LEGAL --}}
<table class="w100" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
<tr>
  <td style="background:#fffbeb; border-left:3px solid #f0c040; padding:10px 14px; font-size:9px; color:#92400e; line-height:1.6;">
    Este comprobante certifica la radicación del caso <b>{{ $caso->numero_caso }}</b>
    en el sistema de gestión jurídica INDEMNI SOAT.
    Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}.
    Válido como constancia interna de seguimiento.
  </td>
</tr>
</table>

{{-- PIE --}}
<table class="w100" cellpadding="0" cellspacing="0" style="border-top:1px solid #d1d5db; padding-top:12px; margin-top:4px;">
<tr>
  <td style="vertical-align:middle; width:70%;">
    <div style="font-size:8px; color:#9ca3af; line-height:1.7;">
      <b>INDEMNI SOAT</b> — Sistema de Gestión Jurídica<br>
      Generado por: {{ auth()->check() ? auth()->user()->name : 'Sistema' }}<br>
      {{ now()->format('d/m/Y H:i') }}
    </div>
  </td>
  <td style="vertical-align:middle; width:30%; text-align:center;">
    <table style="margin:0 auto;" cellpadding="8" cellspacing="0">
    <tr>
      <td style="border:2px solid #1a1a2e; text-align:center; width:64px; height:64px; font-size:7px; font-weight:bold; color:#1a1a2e; text-transform:uppercase; letter-spacing:0.5px; line-height:1.6;">
        INDEMNI<br>SOAT<br>OFICIAL
      </td>
    </tr>
    </table>
  </td>
</tr>
</table>

</body>
</html>
