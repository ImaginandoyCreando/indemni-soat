<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Voucher - {{ $caso->numero_caso }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
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
            border-radius: 8px 8px 0 0;
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
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
            letter-spacing: 0.5px;
        }

        .doc-tipo {
            font-size: 11px;
            color: #aab0c0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .doc-numero {
            font-size: 18px;
            font-weight: bold;
            color: #f0c040;
            margin-top: 4px;
        }

        /* ── BANDA DE ESTADO ── */
        .estado-band {
            background: #f0c040;
            color: #1a1a2e;
            text-align: center;
            padding: 10px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            border-radius: 0 0 8px 8px;
            margin-bottom: 22px;
        }

        /* ── SECCIÓN PRINCIPAL ── */
        .seccion {
            border: 1.5px solid #e0e4ef;
            border-radius: 8px;
            margin-bottom: 16px;
            overflow: hidden;
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

        /* ── GRILLA DE CAMPOS ── */
        .grid-2 {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .col {
            display: table-cell;
            width: 50%;
            padding: 0 8px 12px 0;
            vertical-align: top;
        }

        .col:last-child {
            padding-right: 0;
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
        }

        .campo-valor.grande {
            font-size: 16px;
        }

        /* ── FECHAS DESTACADAS ── */
        .fechas-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .fecha-box {
            display: table-cell;
            background: #f4f6fb;
            border: 1.5px solid #e0e4ef;
            border-radius: 8px;
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
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
            font-size: 9px;
            color: #9ca3af;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .sello {
            display: inline-block;
            border: 2px solid #1a1a2e;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            line-height: 1.2;
            text-align: center;
            padding-top: 14px;
            font-size: 8px;
            font-weight: bold;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .aviso {
            background: #fff8e1;
            border-left: 4px solid #f0c040;
            padding: 10px 14px;
            border-radius: 4px;
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
        <div class="header-left">
            <div class="empresa-nombre">INDEMNI SOAT</div>
            <div class="empresa-subtitulo">Gestión Jurídica de Casos SOAT</div>
        </div>
        <div class="header-right">
            <div class="doc-tipo">Comprobante de Radicación</div>
            <div class="doc-numero">{{ $caso->numero_caso }}</div>
        </div>
    </div>

    {{-- ── BANDA ESTADO ── --}}
    <div class="estado-band">
        Estado actual: {{ $caso->estado }}
    </div>

    {{-- ── DATOS DE LA VÍCTIMA ── --}}
    <div class="seccion">
        <div class="seccion-titulo">Datos de la Víctima</div>
        <div class="seccion-body">
            <div class="grid-2">
                <div class="col" style="width:100%">
                    <div class="campo-label">Nombre completo</div>
                    <div class="campo-valor grande">{{ strtoupper($caso->nombre_completo) }}</div>
                </div>
            </div>
            <div class="grid-2" style="margin-top:10px">
                <div class="col">
                    <div class="campo-label">Cédula de ciudadanía</div>
                    <div class="campo-valor">{{ $caso->cedula }}</div>
                </div>
                <div class="col">
                    <div class="campo-label">Teléfono / Celular</div>
                    <div class="campo-valor">{{ $caso->telefono ?: '—' }}</div>
                </div>
            </div>
            <div class="grid-2">
                <div class="col">
                    <div class="campo-label">Ciudad / Municipio</div>
                    <div class="campo-valor">{{ $caso->ciudad ?: '—' }}{{ $caso->departamento ? ', ' . $caso->departamento : '' }}</div>
                </div>
                <div class="col">
                    <div class="campo-label">Aseguradora</div>
                    <div class="campo-valor">{{ strtoupper($caso->aseguradora) }}</div>
                </div>
            </div>
            @if($caso->fecha_accidente)
            <div class="grid-2">
                <div class="col">
                    <div class="campo-label">Fecha del accidente</div>
                    <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->fecha_accidente)->format('d/m/Y') }}</div>
                </div>
                @if($caso->junta_asignada)
                <div class="col">
                    <div class="campo-label">Junta médica asignada</div>
                    <div class="campo-valor">{{ $caso->junta_asignada }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ── FECHAS DE RADICACIÓN ── --}}
    <div class="seccion">
        <div class="seccion-titulo">Fechas del Proceso</div>
        <div class="seccion-body">
            <div class="fechas-grid">
                <div class="fecha-box destacada">
                    <div class="campo-label">Fecha de Radicación del Caso</div>
                    <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->created_at)->format('d/m/Y') }}</div>
                </div>
                <div class="fecha-box">
                    <div class="campo-label">Solicitud a Aseguradora</div>
                    <div class="campo-valor">
                        {{ $caso->fecha_solicitud_aseguradora
                            ? \Carbon\Carbon::parse($caso->fecha_solicitud_aseguradora)->format('d/m/Y')
                            : 'Pendiente' }}
                    </div>
                </div>
                @if($caso->fecha_tutela)
                <div class="fecha-box">
                    <div class="campo-label">Fecha Tutela</div>
                    <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->fecha_tutela)->format('d/m/Y') }}</div>
                </div>
                @endif
                @if($caso->fecha_pago_final)
                <div class="fecha-box">
                    <div class="campo-label">Fecha Pago Final</div>
                    <div class="campo-valor">{{ \Carbon\Carbon::parse($caso->fecha_pago_final)->format('d/m/Y') }}</div>
                </div>
                @endif
            </div>
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
        Este comprobante certifica la radicación del caso <strong>{{ $caso->numero_caso }}</strong> en el sistema de gestión jurídica INDEMNI SOAT.
        Documento generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}.
        Válido como constancia interna de seguimiento.
    </div>

    {{-- ── PIE DE PÁGINA ── --}}
    <div class="footer">
        <div class="footer-left">
            <strong>INDEMNI SOAT</strong> — Sistema de Gestión Jurídica<br>
            Generado por: {{ auth()->user()->name ?? 'Sistema' }}<br>
            {{ now()->format('d/m/Y H:i') }}
        </div>
        <div class="footer-right">
            <div class="sello">
                INDEMNI<br>SOAT<br>OFICIAL
            </div>
        </div>
    </div>

</div>
</body>
</html>
