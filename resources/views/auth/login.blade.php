{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — INDEMNISOAT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Login layout ── */
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            display: grid;
            grid-template-columns: 1fr 420px;
            max-width: 940px;
            width: 100%;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 40px 100px rgba(0,0,0,0.75), 0 0 0 1px rgba(255,255,255,0.03);
            animation: loginRise 0.55s cubic-bezier(.22,.68,0,1.1) both;
        }

        [data-theme="light"] .login-card {
            border-color: rgba(0,0,0,0.08);
            box-shadow: 0 20px 60px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);
        }

        @keyframes loginRise {
            from { opacity: 0; transform: translateY(24px) scale(0.985); }
            to   { opacity: 1; transform: translateY(0)    scale(1); }
        }

        /* ── Panel izquierdo ── */
        .login-left {
            position: relative;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            background: linear-gradient(150deg, #04091A 0%, #081630 45%, #060F24 100%);
        }

        [data-theme="light"] .login-left {
            background: linear-gradient(150deg, #0A28A0 0%, #1138CC 45%, #081B80 100%);
        }

        /* Orbes */
        .login-left::before {
            content: '';
            position: absolute;
            width: 460px; height: 460px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(27,79,255,0.22) 0%, transparent 68%);
            top: -120px; left: -100px;
            pointer-events: none;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(212,170,72,0.16) 0%, transparent 65%);
            bottom: -50px; right: -30px;
            pointer-events: none;
        }

        /* Grid lines decorativas */
        .login-grid-lines {
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: 0.03;
            background-image:
                linear-gradient(rgba(255,255,255,1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,1) 1px, transparent 1px);
            background-size: 44px 44px;
        }

        .login-left-content {
            position: relative;
            z-index: 2;
        }

        /* Logo panel izquierdo */
        .login-hero-logo {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 36px;
        }

        .login-hero-logo-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.13);
            backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        .login-hero-logo-name {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
            line-height: 1.1;
        }

        .login-hero-logo-name span { color: #5B85FF; }

        .login-hero-logo-sub {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            margin-top: 3px;
        }

        /* Badge */
        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 13px;
            border-radius: 20px;
            background: rgba(27,79,255,0.14);
            border: 1px solid rgba(27,79,255,0.28);
            font-size: 9.5px;
            font-weight: 700;
            color: rgba(255,255,255,0.65);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 22px;
        }

        .login-badge-dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            background: #4B78FF;
            box-shadow: 0 0 6px rgba(75,120,255,0.9);
            animation: pulseDot 2.2s ease infinite;
        }

        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.55; transform: scale(0.75); }
        }

        /* Heading */
        .login-heading {
            font-family: 'Playfair Display', serif;
            font-size: 30px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.6px;
            line-height: 1.22;
            margin-bottom: 14px;
        }

        .login-heading em {
            font-style: normal;
            background: linear-gradient(135deg, #D4AA48 20%, #F5D070 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            font-size: 12.5px;
            color: rgba(255,255,255,0.42);
            line-height: 1.72;
            max-width: 290px;
            margin-bottom: 32px;
        }

        /* Features */
        .login-features {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .login-feature {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(255,255,255,0.06);
            transition: background 0.2s;
        }

        .login-feature:hover {
            background: rgba(255,255,255,0.06);
        }

        .login-feature-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .login-feature-icon.blue   { background: rgba(27,79,255,0.18); }
        .login-feature-icon.gold   { background: rgba(212,170,72,0.18); }
        .login-feature-icon.teal   { background: rgba(8,145,178,0.16); }

        .login-feature-title {
            font-size: 12.5px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            margin-bottom: 2px;
        }

        .login-feature-desc {
            font-size: 10.5px;
            color: rgba(255,255,255,0.33);
            line-height: 1.45;
        }

        /* Stats */
        .login-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .login-stat-item { text-align: center; }

        .login-stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: #D4AA48;
            line-height: 1;
            margin-bottom: 4px;
        }

        .login-stat-label {
            font-size: 9.5px;
            color: rgba(255,255,255,0.28);
            letter-spacing: 0.4px;
        }

        .login-footer-text {
            font-size: 9.5px;
            color: rgba(255,255,255,0.18);
            margin-top: 20px;
            position: relative;
            z-index: 2;
        }

        /* ── Panel derecho ── */
        .login-right {
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--bg-card);
            border-left: 1px solid var(--border);
            gap: 0;
        }

        /* Logo pequeño */
        .login-right-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }

        .login-right-logo-name {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-1);
            letter-spacing: -0.3px;
            line-height: 1.1;
        }

        .login-right-logo-name span { color: #1B4FFF; }

        .login-right-logo-sub {
            font-size: 9.5px;
            color: var(--text-3);
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* Títulos */
        .login-welcome-title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-1);
            letter-spacing: -0.4px;
            margin-bottom: 5px;
        }

        .login-welcome-sub {
            font-size: 12.5px;
            color: var(--text-2);
            margin-bottom: 26px;
            line-height: 1.5;
        }

        /* Alertas */
        .login-alert {
            border-radius: 9px;
            padding: 11px 14px;
            font-size: 12.5px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-bottom: 16px;
        }

        .login-alert.success {
            background: rgba(5,150,105,0.09);
            border: 1px solid rgba(5,150,105,0.22);
            color: #1DBD7F;
        }

        .login-alert.error {
            background: rgba(229,57,53,0.07);
            border: 1px solid rgba(229,57,53,0.2);
            color: #F26F6F;
        }

        /* Formulario */
        .login-form { display: flex; flex-direction: column; gap: 14px; }

        .login-field { display: flex; flex-direction: column; gap: 5px; }

        .login-input-wrap { position: relative; }

        .login-input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-3);
            pointer-events: none;
            display: flex;
        }

        .login-input-wrap .is-input {
            padding-left: 36px;
        }

        /* Botón submit */
        .login-submit {
            width: 100%;
            padding: 13px;
            border-radius: 9px;
            background: linear-gradient(135deg, #1340D6 0%, #1B4FFF 100%);
            border: none;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.2px;
            transition: all 0.22s;
            box-shadow: 0 4px 18px rgba(27,79,255,0.38);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            margin-top: 6px;
        }

        .login-submit::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            transition: left 0.5s ease;
        }

        .login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(27,79,255,0.5);
        }

        .login-submit:hover::before {
            left: 160%;
        }

        .login-submit:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(27,79,255,0.35);
        }

        /* Recordarme */
        .login-remember {
            display: flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
        }

        .login-remember-text {
            font-size: 12.5px;
            color: var(--text-2);
        }

        /* SSL */
        .login-ssl {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 16px;
            padding: 9px 14px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 11px;
            color: var(--text-3);
        }

        .login-ssl-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #059669;
            box-shadow: 0 0 6px rgba(5,150,105,0.65);
            flex-shrink: 0;
        }

        /* Soporte */
        .login-support {
            text-align: center;
            font-size: 11.5px;
            color: var(--text-3);
            margin-top: 10px;
        }

        .login-support a {
            color: #1B4FFF;
            font-weight: 600;
            text-decoration: none;
        }

        .login-support a:hover { text-decoration: underline; }

        /* Animaciones panel derecho */
        .login-right > * {
            animation: slideInRight 0.5s cubic-bezier(.22,.68,0,1.1) both;
        }

        .login-right > *:nth-child(1) { animation-delay: 0.12s; }
        .login-right > *:nth-child(2) { animation-delay: 0.19s; }
        .login-right > *:nth-child(3) { animation-delay: 0.25s; }
        .login-right > *:nth-child(4) { animation-delay: 0.30s; }
        .login-right > *:nth-child(5) { animation-delay: 0.35s; }
        .login-right > *:nth-child(6) { animation-delay: 0.40s; }
        .login-right > *:nth-child(7) { animation-delay: 0.45s; }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(12px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Animaciones panel izquierdo */
        .login-left-content > * {
            animation: slideInLeft 0.6s cubic-bezier(.22,.68,0,1.1) both;
        }

        .login-left-content > *:nth-child(1) { animation-delay: 0.08s; }
        .login-left-content > *:nth-child(2) { animation-delay: 0.16s; }
        .login-left-content > *:nth-child(3) { animation-delay: 0.24s; }
        .login-left-content > *:nth-child(4) { animation-delay: 0.31s; }
        .login-left-content > *:nth-child(5) { animation-delay: 0.38s; }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-12px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Theme toggle */
        .theme-btn {
            position: fixed;
            top: 16px; right: 16px;
            z-index: 500;
            width: 38px; height: 38px;
            border-radius: 9px;
            border: 1px solid var(--border-2);
            background: var(--bg-card);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 15px;
            color: var(--text-2);
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }

        .theme-btn:hover {
            background: var(--bg-hover);
            border-color: var(--border-3);
            transform: scale(1.05);
        }

        /* Responsive */
        @media (max-width: 740px) {
            .login-card {
                grid-template-columns: 1fr;
                border-radius: 18px;
            }
            .login-left { display: none; }
            .login-right {
                padding: 40px 28px;
            }
        }
    </style>
</head>
<body>

<div class="app-mesh"></div>

{{-- Theme toggle --}}
<button id="themeToggle" type="button" class="theme-btn" title="Cambiar tema">
    <span id="themeIcon">☀️</span>
</button>

<div class="login-wrap">
    <div class="login-card">

        {{-- ═══════ PANEL IZQUIERDO ═══════ --}}
        <div class="login-left">
            <div class="login-grid-lines"></div>

            <div class="login-left-content">

                {{-- Logo --}}
                <div class="login-hero-logo">
                    <div class="login-hero-logo-icon">
                        <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
                            <defs>
                                <linearGradient id="lg_shield" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#4B78FF"/>
                                    <stop offset="100%" stop-color="#1B4FFF"/>
                                </linearGradient>
                                <linearGradient id="lg_gold" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#F5D070"/>
                                    <stop offset="100%" stop-color="#D4AA48"/>
                                </linearGradient>
                            </defs>
                            <path d="M24 3C13 3 6 9 6 18L6 30C6 38 14 44 24 46C34 44 42 38 42 30L42 18C42 9 35 3 24 3Z"
                                  fill="url(#lg_shield)" opacity="0.92"/>
                            <line x1="12" y1="22" x2="36" y2="22" stroke="url(#lg_gold)" stroke-width="2.2" stroke-linecap="round"/>
                            <line x1="24" y1="22" x2="24" y2="33" stroke="url(#lg_gold)" stroke-width="2.2" stroke-linecap="round"/>
                            <polygon points="24,15 20,22 28,22" fill="url(#lg_gold)"/>
                            <line x1="13" y1="22" x2="13" y2="28" stroke="url(#lg_gold)" stroke-width="1.5" stroke-linecap="round"/>
                            <ellipse cx="13" cy="29.5" rx="5.5" ry="2" fill="none" stroke="url(#lg_gold)" stroke-width="1.5"/>
                            <line x1="35" y1="22" x2="35" y2="28" stroke="url(#lg_gold)" stroke-width="1.5" stroke-linecap="round"/>
                            <ellipse cx="35" cy="29.5" rx="5.5" ry="2" fill="none" stroke="url(#lg_gold)" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <div>
                        <div class="login-hero-logo-name">INDEMNI<span>SOAT</span></div>
                        <div class="login-hero-logo-sub">Sistema Jurídico</div>
                    </div>
                </div>

                {{-- Badge --}}
                <div class="login-badge">
                    <span class="login-badge-dot"></span>
                    Software Jurídico Certificado
                </div>

                {{-- Heading --}}
                <h1 class="login-heading">
                    Gestión <em>inteligente</em><br>de reclamaciones<br>SOAT
                </h1>

                <p class="login-subtitle">
                    Centralizamos todo el proceso jurídico: desde la primera
                    consulta hasta el cobro exitoso de la indemnización.
                </p>

                {{-- Features --}}
                <div class="login-features">
                    <div class="login-feature">
                        <div class="login-feature-icon blue">⚖️</div>
                        <div>
                            <div class="login-feature-title">Seguimiento jurídico completo</div>
                            <div class="login-feature-desc">Control de poderes, juntas, calificaciones y estados procesales</div>
                        </div>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon gold">🛡️</div>
                        <div>
                            <div class="login-feature-title">Alertas de prescripción</div>
                            <div class="login-feature-desc">Notificaciones automáticas para casos en riesgo legal</div>
                        </div>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon teal">💰</div>
                        <div>
                            <div class="login-feature-title">Control de cobros y honorarios</div>
                            <div class="login-feature-desc">Trazabilidad total desde el estimado hasta el pago final</div>
                        </div>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="login-stats">
                    <div class="login-stat-item">
                        <div class="login-stat-value">100%</div>
                        <div class="login-stat-label">Digital</div>
                    </div>
                    <div class="login-stat-item">
                        <div class="login-stat-value">24/7</div>
                        <div class="login-stat-label">Disponible</div>
                    </div>
                    <div class="login-stat-item">
                        <div class="login-stat-value">SSL</div>
                        <div class="login-stat-label">Cifrado</div>
                    </div>
                </div>

            </div>{{-- fin left-content --}}

            <div class="login-footer-text">
                © {{ date('Y') }} INDEMNISOAT · Datos confidenciales y protegidos
            </div>
        </div>

        {{-- ═══════ PANEL DERECHO — FORMULARIO ═══════ --}}
        <div class="login-right">

            {{-- Logo --}}
            <div class="login-right-logo">
                <svg width="36" height="36" viewBox="0 0 48 48" fill="none">
                    <defs>
                        <linearGradient id="rg_s" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#4B78FF"/>
                            <stop offset="100%" stop-color="#1B4FFF"/>
                        </linearGradient>
                        <linearGradient id="rg_g" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#F5D070"/>
                            <stop offset="100%" stop-color="#D4AA48"/>
                        </linearGradient>
                    </defs>
                    <path d="M24 3C13 3 6 9 6 18L6 30C6 38 14 44 24 46C34 44 42 38 42 30L42 18C42 9 35 3 24 3Z" fill="url(#rg_s)"/>
                    <line x1="12" y1="22" x2="36" y2="22" stroke="url(#rg_g)" stroke-width="2.2" stroke-linecap="round"/>
                    <line x1="24" y1="22" x2="24" y2="33" stroke="url(#rg_g)" stroke-width="2.2" stroke-linecap="round"/>
                    <polygon points="24,15 20,22 28,22" fill="url(#rg_g)"/>
                    <line x1="13" y1="22" x2="13" y2="28" stroke="url(#rg_g)" stroke-width="1.5" stroke-linecap="round"/>
                    <ellipse cx="13" cy="29.5" rx="5.5" ry="2" fill="none" stroke="url(#rg_g)" stroke-width="1.5"/>
                    <line x1="35" y1="22" x2="35" y2="28" stroke="url(#rg_g)" stroke-width="1.5" stroke-linecap="round"/>
                    <ellipse cx="35" cy="29.5" rx="5.5" ry="2" fill="none" stroke="url(#rg_g)" stroke-width="1.5"/>
                </svg>
                <div>
                    <div class="login-right-logo-name">INDEMNI<span>SOAT</span></div>
                    <div class="login-right-logo-sub">Sistema Jurídico</div>
                </div>
            </div>

            {{-- Títulos --}}
            <div>
                <h2 class="login-welcome-title">Bienvenido de vuelta</h2>
                <p class="login-welcome-sub">Ingresa tus credenciales para acceder al sistema</p>
            </div>

            {{-- Alertas --}}
            @if(session('success'))
                <div class="login-alert success">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                        <circle cx="12" cy="12" r="10" fill="rgba(5,150,105,0.2)"/>
                        <path d="M8 12l2.5 2.5L16 9" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="login-alert error">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                        <circle cx="12" cy="12" r="10" fill="rgba(229,57,53,0.2)"/>
                        <path d="M12 8v4M12 16h.01" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login.post') }}" class="login-form">
                @csrf

                {{-- Email --}}
                <div class="login-field">
                    <label class="is-form-label" for="email">Correo electrónico</label>
                    <div class="login-input-wrap">
                        <span class="login-input-icon">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M3 8l9 6 9-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="usuario@indemnisoat.co"
                               autocomplete="email" autofocus
                               class="is-input"
                               style="{{ $errors->has('email') ? 'border-color:rgba(229,57,53,0.5);' : '' }}">
                    </div>
                </div>

                {{-- Contraseña --}}
                <div class="login-field">
                    <label class="is-form-label" for="password">Contraseña</label>
                    <div class="login-input-wrap" style="position:relative;">
                        <span class="login-input-icon">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                                <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <input type="password" id="pwdInput" name="password"
                               placeholder="••••••••••"
                               autocomplete="current-password"
                               class="is-input"
                               style="{{ $errors->has('password') ? 'border-color:rgba(229,57,53,0.5);' : '' }}">
                        <button type="button" id="pwdToggle"
                                style="position:absolute;right:11px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;cursor:pointer;
                                       color:var(--text-3);padding:4px;display:flex;
                                       transition:color 0.2s;">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" id="eyeIcon">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Recordarme --}}
                <label class="login-remember" id="rememberLabel">
                    <div class="is-checkbox-box {{ old('recordar') ? 'checked' : '' }}"
                         id="rememberBox"
                         style="{{ old('recordar') ? 'background:#059669;border-color:#059669;' : '' }}">
                        <svg width="9" height="7" viewBox="0 0 9 7" fill="none"
                             style="{{ old('recordar') ? '' : 'display:none' }}" id="rememberCheck">
                            <path d="M1 3.5l2.5 2.5L8 1" stroke="#fff" stroke-width="1.6"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input type="checkbox" name="recordar" value="1"
                           id="rememberInput" style="display:none;"
                           {{ old('recordar') ? 'checked' : '' }}>
                    <span class="login-remember-text">Mantener sesión activa en este dispositivo</span>
                </label>

                {{-- Submit --}}
                <button type="submit" class="login-submit">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Iniciar sesión
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>

            </form>

            {{-- SSL --}}
            <div class="login-ssl">
                <span class="login-ssl-dot"></span>
                Conexión cifrada SSL · Datos protegidos
            </div>

            <div class="login-support">
                ¿Problemas? <a href="#">Contactar soporte</a>
            </div>

        </div>{{-- fin panel derecho --}}
    </div>{{-- fin login-card --}}
</div>

<script>
(function () {
    /* Tema */
    const html = document.documentElement;
    const btn  = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const KEY  = 'is_theme';

    function applyTheme(t) {
        html.setAttribute('data-theme', t);
        icon.textContent = t === 'dark' ? '☀️' : '🌙';
        localStorage.setItem(KEY, t);
    }

    const saved = localStorage.getItem(KEY) ||
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(saved);
    btn.addEventListener('click', () =>
        applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));

    /* Checkbox recordarme */
    const box   = document.getElementById('rememberBox');
    const input = document.getElementById('rememberInput');
    const check = document.getElementById('rememberCheck');

    document.getElementById('rememberLabel').addEventListener('click', function(e) {
        e.preventDefault();
        const checked = !input.checked;
        input.checked = checked;
        box.style.background  = checked ? '#059669' : '';
        box.style.borderColor = checked ? '#059669' : '';
        check.style.display   = checked ? '' : 'none';
    });

    /* Toggle contraseña */
    const pwdInput  = document.getElementById('pwdInput');
    const pwdToggle = document.getElementById('pwdToggle');
    let   visible   = false;

    if (pwdToggle && pwdInput) {
        pwdToggle.addEventListener('click', function() {
            visible = !visible;
            pwdInput.type = visible ? 'text' : 'password';
            pwdToggle.style.color = visible ? '#1B4FFF' : '';
        });
    }
})();
</script>

</body>
</html>
