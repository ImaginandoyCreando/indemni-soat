<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Voucher - {{ $caso->numero_caso }}</title>
    <style>
        * { margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #ffffff;
        }

        .page {
            width: 100%;
            padding: 30px 35px;
        }

        /* ── ENCABEZADO ── */
        .header {
            background: #1a1a2e;
            color: #ffffff;
            padding: 22px 28px;
            width: 100%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .empresa-nombre {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #f0c040;
        }

        .empresa-subtitulo {
            font-size: 10px;
            color: #aab0c0;
            margin-top: 3px;
        }

        .doc-tipo {
            font-size: 11px;
            color: #aab0c0;
            text-transform: uppercase;
        }

        .doc-numero {
            font-size: 18px;
            font-weight: bold;
            color: #f0c040;
            margin-top: 4px;
        }

        /* ── BANDA ESTADO ── */
        .estado-band {
            background: #f0c040;
            color: #1a1a2e;
            text-align: center;
            padding: 10px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 22px;
        }

        /* ── SECCIONES ── */
        .seccion {
            border: 1.5px solid #e0e4ef;
            margin-bottom: 16px;
            width: 100%;
        }

        .seccion-titulo {
            background: #f4f6fb;
            border-bottom: 1.5px solid #e0e4ef;
            padding: 8px 16px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6b7280;
        }

        .seccion-body {
            padding: 14px 16px;
        }

        /* ── GRID ── */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
        }

        .campo-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #9ca3af;
            margin-bottom: 3px;
        }

        .campo-valor {
            font-size: 13px;
            font-weight: bold;
            color: #1a1a2e;
            padding-bottom: 10px;
        }

        .campo-valor.grande {
            font-size: 16px;
        }

        /* ── FECHAS ── */
        .fecha-box {
            border: 1.5px solid #e0e4ef;
            background: #f4f6fb;
            padding: 12px 14px;
            text-align: center;
            vertical-align: middle;
        }

        .fecha-box.destacada {
            background: #1a1a2e;
            border-color: #1a1a2e;
        }

        .fecha-box.destacada .campo-label {
            color: #aab0c0;
        }

        .fecha-box.destacada .campo-valor {
            color: #f0c040;
        }

        .fecha-box .campo-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #9ca3af;
            margin-bottom: 5px;
        }

        .fecha-box .campo-valor {
            font-size: 15px;
            font-weight: bold;
            color: #1a1a2e;
        }

        /* ── OBSERVACIONES ── */
        .obs-texto {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
            font-style: italic;
        }

        /* ── PIE ── */
        .footer {
            margin-top: 20px;
            border-top: 1.5px solid #e0e4ef;
            padding-top: 14px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-left {
            font-size: 9px;
            color: #9ca3af;
            vertical-align: middle;
        }

        .footer-right {
            text-align: right;
            vertical-align: middle;
        }

        .sello {
            border: 2px solid #1a1a2e;
            width: 70px;
            height: 70px;
            text-align: center;
            padding-top: 14px;
            font-size: 8px;
            font-weight: bold;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .aviso {
            background: #fff8e1;
            border-left: 4px solid #f0c040;
            padding: 10px 14px;
            font-size: 10px;
            color: #78600a;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── ENCABEZADO ── --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="vertical-align:middle;">
                    <div class="empresa-nombre">INDEMNI SOAT</div>
                    <div class="empresa-subtitulo">Gestión Jurídica de Casos SOAT</div>
                </td>
                <td style="vertical-align:middle;text-align:right;">
                    <div class="doc-tipo">Comprobante de Radicación</div>
                    <div class="doc-numero">{{ $caso->numero_caso }}</div>
                </td>
            </tr>
        </table>
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
            <div class="campo-valor grande" style="margin-bottom:12px;">
                {{ strtoupper(trim(($caso->nombres ?? '') . ' ' . ($caso->apellidos ?? ''))) }}
            </div>
            <table class="grid-table">
                <tr>
                    <td style="width:50%;vertical-align:top;">
                        <div class="campo-label">Cédula de ciudadanía</div>
                        <div class="campo-valor">{{ $caso->cedula ?? '—' }}</div>
                    </td>
                    <td style="width:50%;vertical-align:top;">
                        <div class="campo-label">Teléfono / Celular</div>
                        <div class="campo-valor">{{ $caso->telefono ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">
                        <div class="campo-label">Ciudad / Departamento</div>
                        <div class="campo-valor">
                            {{ $caso->ciudad ?? '—' }}{{ $caso->departamento ? ', ' . $caso->departamento : '' }}
                        </div>
                    </td>
                    <td style="vertical-align:top;">
                        <div class="campo-label">Aseguradora</div>
                        <div class="campo-valor">{{ strtoupper($caso->aseguradora ?? '—') }}</div>
                    </td>
                </tr>
                @if($caso->fecha_accidente || $caso->junta_asignada)
                <tr>
                    @if($caso->fecha_accidente)
                    <td style="vertical-align:top;">
                        <div class="campo-label">Fecha del accidente</div>
                        <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->fecha_accidente)->format('d/m/Y') }}</div>
                    </td>
                    @else
                    <td></td>
                    @endif
                    @if($caso->junta_asignada)
                    <td style="vertical-align:top;">
                        <div class="campo-label">Junta médica asignada</div>
                        <div class="campo-valor">{{ $caso->junta_asignada }}</div>
                    </td>
                    @else
                    <td></td>
                    @endif
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- ── FECHAS DEL PROCESO ── --}}
    <div class="seccion">
        <div class="seccion-titulo">Fechas del Proceso</div>
        <div class="seccion-body">
            @php
                $columnasFechas = 2;
                if ($caso->fecha_tutela)    $columnasFechas++;
                if ($caso->fecha_pago_final) $columnasFechas++;
                $anchoCel = round(100 / $columnasFechas) . '%';
            @endphp
            <table class="grid-table">
                <tr>
                    <td style="width:{{ $anchoCel }};padding:4px;">
                        <div class="fecha-box destacada">
                            <div class="campo-label">Fecha de Radicación</div>
                            <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->created_at)->format('d/m/Y') }}</div>
                        </div>
                    </td>
                    <td style="width:{{ $anchoCel }};padding:4px;">
                        <div class="fecha-box">
                            <div class="campo-label">Solicitud a Aseguradora</div>
                            <div class="campo-valor">
                                {{ $caso->fecha_solicitud_aseguradora
                                    ? \Carbon\Carbon::parse($caso->fecha_solicitud_aseguradora)->format('d/m/Y')
                                    : 'Pendiente' }}
                            </div>
                        </div>
                    </td>
                    @if($caso->fecha_tutela)
                    <td style="width:{{ $anchoCel }};padding:4px;">
                        <div class="fecha-box">
                            <div class="campo-label">Fecha Tutela</div>
                            <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->fecha_tutela)->format('d/m/Y') }}</div>
                        </div>
                    </td>
                    @endif
                    @if($caso->fecha_pago_final)
                    <td style="width:{{ $anchoCel }};padding:4px;">
                        <div class="fecha-box">
                            <div class="campo-label">Fecha Pago Final</div>
                            <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->fecha_pago_final)->format('d/m/Y') }}</div>
                        </div>
                    </td>
                    @endif
                </tr>
            </table>
        </div>
    </div>

    {{-- ── OBSERVACIONES (solo si existen) ── --}}
    @if($caso->observaciones)
    <div class="seccion">
        <div class="seccion-titulo">Observaciones</div>
        <div class="seccion-body">
            <div class="obs-texto">{{ $caso->observaciones }}</div>
        </div>
    </div>
    @endif

    {{-- ── AVISO LEGAL ── --}}
    <div class="aviso">
        Este comprobante certifica la radicación del caso <strong>{{ $caso->numero_caso }}</strong>
        en el sistema de gestión jurídica INDEMNI SOAT.
        Documento generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}.
        Válido como constancia interna de seguimiento.
    </div>

    {{-- ── PIE DE PÁGINA ── --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <strong>INDEMNI SOAT</strong> — Sistema de Gestión Jurídica<br>
                    Generado por: {{ auth()->check() ? auth()->user()->name : 'Sistema' }}<br>
                    {{ now()->format('d/m/Y H:i') }}
                </td>
                <td class="footer-right">
                    <div class="sello">
                        INDEMNI<br>SOAT<br>OFICIAL
                    </div>
                </td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>
