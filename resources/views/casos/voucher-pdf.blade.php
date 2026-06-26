<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Voucher - {{ $caso->numero_caso }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ── ENCABEZADO ── */
        .header {
            background: #1a1a2e;
            padding: 20px 24px;
            width: 100%;
        }
        .header-left {
            float: left;
            width: 55%;
        }
        .header-right {
            float: right;
            width: 40%;
            text-align: right;
        }
        .clearfix { clear: both; }

        .empresa-nombre {
            font-size: 20px;
            font-weight: bold;
            color: #f0c040;
            letter-spacing: 1px;
        }
        .empresa-sub {
            font-size: 9px;
            color: #aab0c0;
            margin-top: 4px;
        }
        .doc-tipo {
            font-size: 9px;
            color: #aab0c0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-numero {
            font-size: 16px;
            font-weight: bold;
            color: #f0c040;
            margin-top: 4px;
        }

        /* ── BANDA ESTADO ── */
        .estado-band {
            background: #f0c040;
            color: #1a1a2e;
            text-align: center;
            padding: 9px 24px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* ── SECCIÓN ── */
        .seccion {
            margin: 0 24px 16px 24px;
            border: 1px solid #d1d5db;
        }
        .seccion-titulo {
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            padding: 7px 14px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6b7280;
        }
        .seccion-body {
            padding: 14px 14px 4px 14px;
        }

        /* ── CAMPO ── */
        .campo-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #9ca3af;
            margin-bottom: 3px;
        }
        .campo-valor {
            font-size: 12px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 12px;
        }
        .campo-valor.grande {
            font-size: 15px;
        }

        /* ── GRILLA 2 COLUMNAS ── */
        .col-left {
            float: left;
            width: 48%;
        }
        .col-right {
            float: right;
            width: 48%;
        }

        /* ── CAJAS DE FECHAS ── */
        .fecha-wrap {
            margin: 0 24px 16px 24px;
        }
        .fecha-box {
            float: left;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            text-align: center;
            padding: 14px 10px;
            margin-right: 10px;
        }
        .fecha-box.destacada {
            background: #1a1a2e;
            border-color: #1a1a2e;
        }
        .fecha-box.destacada .campo-label { color: #aab0c0; }
        .fecha-box.destacada .campo-valor { color: #f0c040; }
        .fecha-box .campo-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #9ca3af;
            margin-bottom: 6px;
        }
        .fecha-box .campo-valor {
            font-size: 13px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 0;
        }

        /* ── AVISO ── */
        .aviso {
            margin: 0 24px 16px 24px;
            background: #fffbeb;
            border-left: 3px solid #f0c040;
            padding: 10px 14px;
            font-size: 9px;
            color: #92400e;
            line-height: 1.6;
        }

        /* ── PIE ── */
        .footer {
            margin: 10px 24px 24px 24px;
            border-top: 1px solid #d1d5db;
            padding-top: 12px;
        }
        .footer-text {
            float: left;
            width: 70%;
            font-size: 8px;
            color: #9ca3af;
            line-height: 1.7;
        }
        .footer-sello {
            float: right;
            width: 26%;
            text-align: center;
        }
        .sello-circulo {
            border: 2px solid #1a1a2e;
            width: 64px;
            height: 64px;
            margin: 0 auto;
            padding-top: 12px;
        }
        .sello-texto {
            font-size: 7px;
            font-weight: bold;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

{{-- ── ENCABEZADO ── --}}
<div class="header">
    <div class="header-left">
        <div class="empresa-nombre">INDEMNI SOAT</div>
        <div class="empresa-sub">Gestión Jurídica de Casos SOAT</div>
    </div>
    <div class="header-right">
        <div class="doc-tipo">Comprobante de Radicación</div>
        <div class="doc-numero">{{ $caso->numero_caso }}</div>
    </div>
    <div class="clearfix"></div>
</div>

{{-- ── BANDA ESTADO ── --}}
<div class="estado-band">
    Estado actual: {{ $caso->estado ?? 'N/A' }}
</div>

{{-- ── DATOS DE LA VÍCTIMA ── --}}
<div class="seccion">
    <div class="seccion-titulo">Datos de la Víctima</div>
    <div class="seccion-body">

        <div class="campo-label">Nombre completo</div>
        <div class="campo-valor grande">
            {{ strtoupper(trim(($caso->nombres ?? '') . ' ' . ($caso->apellidos ?? ''))) }}
        </div>

        <div class="col-left">
            <div class="campo-label">Cédula de ciudadanía</div>
            <div class="campo-valor">{{ $caso->cedula ?? '—' }}</div>
        </div>
        <div class="col-right">
            <div class="campo-label">Teléfono / Celular</div>
            <div class="campo-valor">{{ $caso->telefono ?? '—' }}</div>
        </div>
        <div class="clearfix"></div>

        <div class="col-left">
            <div class="campo-label">Ciudad / Departamento</div>
            <div class="campo-valor">
                {{ $caso->ciudad ?? '—' }}{{ $caso->departamento ? ', ' . $caso->departamento : '' }}
            </div>
        </div>
        <div class="col-right">
            <div class="campo-label">Aseguradora</div>
            <div class="campo-valor">{{ strtoupper($caso->aseguradora ?? '—') }}</div>
        </div>
        <div class="clearfix"></div>

        @if($caso->fecha_accidente || $caso->junta_asignada)
        <div class="col-left">
            @if($caso->fecha_accidente)
            <div class="campo-label">Fecha del accidente</div>
            <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->fecha_accidente)->format('d/m/Y') }}</div>
            @endif
        </div>
        <div class="col-right">
            @if($caso->junta_asignada)
            <div class="campo-label">Junta médica asignada</div>
            <div class="campo-valor">{{ $caso->junta_asignada }}</div>
            @endif
        </div>
        <div class="clearfix"></div>
        @endif

    </div>
</div>

{{-- ── FECHAS DEL PROCESO ── --}}
@php
    $cajas = [];
    $cajas[] = ['label' => 'Fecha de Radicación', 'valor' => \Carbon\Carbon::parse($caso->created_at)->format('d/m/Y'), 'destacada' => true];
    $cajas[] = ['label' => 'Solicitud a Aseguradora', 'valor' => $caso->fecha_solicitud_aseguradora ? \Carbon\Carbon::parse($caso->fecha_solicitud_aseguradora)->format('d/m/Y') : 'Pendiente', 'destacada' => false];
    if ($caso->fecha_tutela) $cajas[] = ['label' => 'Fecha Tutela', 'valor' => \Carbon\Carbon::parse($caso->fecha_tutela)->format('d/m/Y'), 'destacada' => false];
    if ($caso->fecha_pago_final) $cajas[] = ['label' => 'Fecha Pago Final', 'valor' => \Carbon\Carbon::parse($caso->fecha_pago_final)->format('d/m/Y'), 'destacada' => false];

    $nCajas   = count($cajas);
    $margen   = 10 * ($nCajas - 1);
    $pct      = intval((100 - ($margen / 5)) / $nCajas);
@endphp

<div style="margin:0 24px 6px 24px;">
    <div class="seccion-titulo" style="border:1px solid #d1d5db;padding:7px 14px;">Fechas del Proceso</div>
</div>
<div class="fecha-wrap" style="padding-bottom:2px;">
    @foreach($cajas as $caja)
    <div class="fecha-box {{ $caja['destacada'] ? 'destacada' : '' }}" style="width:{{ $pct }}%;">
        <div class="campo-label">{{ $caja['label'] }}</div>
        <div class="campo-valor">{{ $caja['valor'] }}</div>
    </div>
    @endforeach
    <div class="clearfix"></div>
</div>

{{-- ── OBSERVACIONES ── --}}
@if($caso->observaciones)
<div class="seccion">
    <div class="seccion-titulo">Observaciones</div>
    <div class="seccion-body" style="padding-bottom:14px;">
        <div style="font-size:10px;color:#4b5563;line-height:1.6;font-style:italic;">
            {{ $caso->observaciones }}
        </div>
    </div>
</div>
@endif

{{-- ── AVISO LEGAL ── --}}
<div class="aviso">
    Este comprobante certifica la radicación del caso <strong>{{ $caso->numero_caso }}</strong>
    en el sistema de gestión jurídica INDEMNI SOAT.
    Generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}.
    Válido como constancia interna de seguimiento.
</div>

{{-- ── PIE DE PÁGINA ── --}}
<div class="footer">
    <div class="footer-text">
        <strong>INDEMNI SOAT</strong> — Sistema de Gestión Jurídica<br>
        Generado por: {{ auth()->check() ? auth()->user()->name : 'Sistema' }}<br>
        {{ now()->format('d/m/Y H:i') }}
    </div>
    <div class="footer-sello">
        <div class="sello-circulo">
            <div class="sello-texto">
                INDEMNI<br>SOAT<br>OFICIAL
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>

</body>
</html>
