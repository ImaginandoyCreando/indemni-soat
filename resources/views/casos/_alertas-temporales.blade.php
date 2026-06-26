{{--
    ALERTAS TEMPORALES DEL CASO
    ────────────────────────────────────────────────
    CÓMO INCLUIR en casos/show.blade.php:
      @include('casos._alertas-temporales', ['caso' => $caso])

    CÓMO INCLUIR en casos/index.blade.php (dentro del loop de casos):
      @include('casos._alertas-temporales', ['caso' => $caso, 'compacto' => true])

    REQUISITO: Importar el servicio en el controlador si quieres pasarlo:
      use App\Services\AlertaTemporalService;
      ...
      $alertas = AlertaTemporalService::calcular($caso);
    O simplemente incluye este partial y él lo calcula solo.
--}}

@php
    $alertasTemporales = \App\Services\AlertaTemporalService::calcular($caso);
    $compacto = $compacto ?? false;
@endphp

@if(count($alertasTemporales) > 0)
<div class="{{ $compacto ? '' : 'mb-6' }}" style="{{ $compacto ? '' : 'margin-bottom:20px;' }}">

    @if(!$compacto)
    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.8px;
                color:var(--text-3,#9ca3af); margin-bottom:10px;">
        Alertas del proceso
    </div>
    @endif

    <div style="display:flex; flex-direction:column; gap:8px;">
    @foreach($alertasTemporales as $alerta)

        @php
            $estilos = match($alerta['nivel']) {
                'critico' => [
                    'bg'     => '#fef2f2',
                    'border' => '#ef4444',
                    'color'  => '#991b1b',
                    'badge'  => '#ef4444',
                    'label'  => 'CRÍTICO',
                ],
                'urgente' => [
                    'bg'     => '#fff7ed',
                    'border' => '#f97316',
                    'color'  => '#9a3412',
                    'badge'  => '#f97316',
                    'label'  => 'URGENTE',
                ],
                default => [
                    'bg'     => '#eff6ff',
                    'border' => '#3b82f6',
                    'color'  => '#1e40af',
                    'badge'  => '#3b82f6',
                    'label'  => 'AVISO',
                ],
            };
        @endphp

        <div style="
            background: {{ $estilos['bg'] }};
            border-left: 4px solid {{ $estilos['border'] }};
            border-radius: 0 8px 8px 0;
            padding: {{ $compacto ? '8px 12px' : '12px 16px' }};
            display: flex;
            align-items: flex-start;
            gap: 10px;
        ">
            <div style="font-size:{{ $compacto ? '16px' : '20px' }}; line-height:1; flex-shrink:0; margin-top:1px;">
                {{ $alerta['icono'] }}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px; flex-wrap:wrap;">
                    <span style="
                        background: {{ $estilos['badge'] }};
                        color: #fff;
                        font-size: 9px;
                        font-weight: 700;
                        letter-spacing: .8px;
                        padding: 2px 7px;
                        border-radius: 20px;
                        text-transform: uppercase;
                        flex-shrink: 0;
                    ">{{ $estilos['label'] }}</span>
                    <span style="
                        font-size: {{ $compacto ? '12px' : '13px' }};
                        font-weight: 700;
                        color: {{ $estilos['color'] }};
                        line-height: 1.3;
                    ">{{ $alerta['titulo'] }}</span>
                </div>
                @if(!$compacto)
                <div style="font-size:12px; color:{{ $estilos['color'] }}; opacity:.85; line-height:1.5;">
                    {{ $alerta['mensaje'] }}
                </div>
                @endif
            </div>
            <div style="
                flex-shrink: 0;
                text-align: center;
                background: {{ $estilos['border'] }};
                color: #fff;
                border-radius: 8px;
                padding: 4px 10px;
                min-width: 44px;
            ">
                <div style="font-size:16px; font-weight:800; line-height:1.1;">{{ $alerta['dias'] }}</div>
                <div style="font-size:8px; opacity:.85; text-transform:uppercase; letter-spacing:.4px;">días</div>
            </div>
        </div>

    @endforeach
    </div>

</div>
@endif
