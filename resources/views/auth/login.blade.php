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
        /* ── Login-specific styles ── */
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            display: grid;
            grid-template-columns: 1fr 440px;
            max-width: 960px;
            width: 100%;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid var(--border-2);
            box-shadow: 0 32px 80px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.04);
            animation: loginRise 0.6s cubic-bezier(.22,.68,0,1.1) both;
        }

        @keyframes loginRise {
            from { opacity: 0; transform: translateY(28px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Panel izquierdo ── */
        .login-left {
            position: relative;
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            background: linear-gradient(155deg, #060F22 0%, #0C1E3D 40%, #071530 100%);
        }

        [data-theme="light"] .login-left {
            background: linear-gradient(155deg, #0C2FA3 0%, #1340D6 45%, #091F7A 100%);
        }

        /* Orbes de fondo */
        .login-left::before {
            content: '';
            position: absolute;
            width: 420px; height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(27,79,255,0.28) 0%, transparent 70%);
            top: -100px; left: -80px;
            pointer-events: none;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(184,146,42,0.18) 0%, transparent 70%);
            bottom: -60px; right: -40px;
            pointer-events: none;
        }

        /* Líneas decorativas */
        .login-grid-lines {
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: 0.04;
            background-image:
                linear-gradient(rgba(255,255,255,1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,1) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .login-left-content { position: relative; z-index: 2; }

        /* Logo grande panel izquierdo */
        .login-hero-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 44px;
        }

        .login-hero-logo-icon {
            width: 56px; height: 56px;
            border-radius: 16px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);
        }

        .login-hero-logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .login-hero-logo-text span { color: #4B78FF; }

        .login-hero-logo-sub {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
            margin-top: 2px;
        }

        /* Badge certificado */
        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            border-radius: 20px;
            background: rgba(27,79,255,0.15);
            border: 1px solid rgba(27,79,255,0.3);
            font-size: 10px;
            font-weight: 700;
            color: rgba(255,255,255,0.7);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .login-badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #4B78FF;
            box-shadow: 0 0 6px rgba(75,120,255,0.8);
            animation: pulseDot 2s ease infinite;
        }

        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.6; transform: scale(0.8); }
        }

        .login-heading {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.8px;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .login-heading em {
            font-style: normal;
            background: linear-gradient(135deg, #D4AA48, #F5D070);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            font-size: 13px;
            color: rgba(255,255,255,0.48);
            line-height: 1.7;
            max-width: 300px;
            margin-bottom: 40px;
        }

        /* Features */
        .login-features { display: flex; flex-direction: column; gap: 16px; }

        .login-feature {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 12px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            transition: background 0.2s;
        }

        .login-feature:hover {
            background: rgba(255,255,255,0.07);
        }

        .login-feature-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .login-feature-icon.blue   { background: rgba(27,79,255,0.2); }
        .login-feature-icon.gold   { background: rgba(184,146,42,0.2); }
        .login-feature-icon.teal   { background: rgba(8,145,178,0.18); }

        .login-feature-title {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.88);
            margin-bottom: 3px;
        }

        .login-feature-desc {
            font-size: 11px;
            color: rgba(255,255,255,0.38);
            line-height: 1.4;
        }

        /* Stats row */
        .login-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 32px;
            padding-top: 28px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .login-stat-item { text-align: center; }

        .login-stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #D4AA48;
            line-height: 1;
            margin-bottom: 4px;
        }

        .login-stat-label {
            font-size: 10px;
            color: rgba(255,255,255,0.3);
            letter-spacing: 0.5px;
        }

        .login-footer {
            font-size: 10px;
            color: rgba(255,255,255,0.22);
            margin-top: 24px;
        }

        /* ── Panel derecho ── */
        .login-right {
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--bg-card);
            border-left: 1px solid var(--border);
        }

        /* Logo pequeño panel derecho */
        .login-right-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }

        .login-right-logo-name {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-1);
            letter-spacing: -0.3px;
        }

        .login-right-logo-name span { color: #1B4FFF; }

        .login-welcome-title {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--text-1);
            margin-bottom: 6px;
            letter-spacing: -0.4px;
        }

        .login-welcome-sub {
            font-size: 13px;
            color: var(--text-2);
            margin-bottom: 32px;
            line-height: 1.5;
        }

        /* Input con ícono */
        .login-input-wrap {
            position: relative;
            margin-bottom: 16px;
        }

        .login-input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-3);
            pointer-events: none;
            display: flex;
        }

        .login-input-wrap .is-input {
            padding-left: 38px;
        }

        /* Submit button mejorado */
        .login-submit {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1340D6, #1B4FFF);
            border: none;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.3px;
            transition: all 0.2s;
            box-shadow: 0 4px 20px rgba(27,79,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .login-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12), transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(27,79,255,0.5);
        }

        .login-submit:hover::before { opacity: 1; }
        .login-submit:active { transform: translateY(0); }

        /* SSL Badge */
        .login-ssl {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 18px;
            padding: 9px 14px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 11px;
            color: var(--text-3);
        }

        .login-ssl-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #059669;
            box-shadow: 0 0 6px rgba(5,150,105,0.6);
            flex-shrink: 0;
        }

        /* Animaciones escalonadas panel derecho */
        .login-right > * {
            animation: slideInRight 0.5s cubic-bezier(.22,.68,0,1.1) both;
        }

        .login-right > *:nth-child(1) { animation-delay: 0.15s; }
        .login-right > *:nth-child(2) { animation-delay: 0.22s; }
        .login-right > *:nth-child(3) { animation-delay: 0.28s; }
        .login-right > *:nth-child(4) { animation-delay: 0.33s; }
        .login-right > *:nth-child(5) { animation-delay: 0.38s; }
        .login-right > *:nth-child(6) { animation-delay: 0.43s; }
        .login-right > *:nth-child(7) { animation-delay: 0.48s; }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(14px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Animaciones panel izquierdo */
        .login-left-content > * {
            animation: slideInLeft 0.6s cubic-bezier(.22,.68,0,1.1) both;
        }

        .login-left-content > *:nth-child(1) { animation-delay: 0.1s; }
        .login-left-content > *:nth-child(2) { animation-delay: 0.18s; }
        .login-left-content > *:nth-child(3) { animation-delay: 0.26s; }
        .login-left-content > *:nth-child(4) { animation-delay: 0.33s; }
        .login-left-content > *:nth-child(5) { animation-delay: 0.40s; }
        .login-left-content > *:nth-child(6) { animation-delay: 0.47s; }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-14px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* Theme toggle */
        .theme-btn {
            position: fixed;
            top: 18px; right: 18px;
            z-index: 500;
            width: 40px; height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border-2);
            background: var(--bg-card);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-2);
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }

        .theme-btn:hover {
            background: var(--bg-hover);
            border-color: var(--border-3);
        }

        @media (max-width: 720px) {
            .login-card { grid-template-columns: 1fr; }
            .login-left { display: none; }
            .login-right { padding: 40px 28px; }
        }
    </style>
</head>
<body>

<div class="app-mesh"></div>

{{-- Theme toggle --}}
<button id="themeToggle" type="button" class="theme-btn">
    <span id="themeIcon">☀️</span>
</button>

<div class="login-wrap">
    <div class="login-card">

        {{-- ═══════════════════════════════
             PANEL IZQUIERDO
        ═══════════════════════════════ --}}
        <div class="login-left">
            <div class="login-grid-lines"></div>

            <div class="login-left-content">

                {{-- Logo grande --}}
                <div class="login-hero-logo">
                    <div class="login-hero-logo-icon">
                        {{-- SVG Logo --}}
                        <svg width="34" height="34" viewBox="0 0 48 48" fill="none">
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
                                  fill="url(#lg_shield)" opacity="0.9"/>
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
                        <div class="login-hero-logo-text">INDEMNI<span>SOAT</span></div>
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

            <div class="login-footer">
                © {{ date('Y') }} INDEMNISOAT · Datos confidenciales y protegidos
            </div>
        </div>

        {{-- ═══════════════════════════════
             PANEL DERECHO — FORMULARIO
        ═══════════════════════════════ --}}
        <div class="login-right">

            {{-- Logo --}}
            <div class="login-right-logo">
                <svg width="38" height="38" viewBox="0 0 48 48" fill="none">
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
                    <path d="M24 3C13 3 6 9 6 18L6 30C6 38 14 44 24 46C34 44 42 38 42 30L42 18C42 9 35 3 24 3Z"
                          fill="url(#rg_s)"/>
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
                    <div style="font-size:10px;color:var(--text-3);letter-spacing:0.6px;margin-top:1px;">
                        Sistema Jurídico
                    </div>
                </div>
            </div>

            {{-- Títulos --}}
            <div>
                <h2 class="login-welcome-title">Bienvenido de vuelta</h2>
                <p class="login-welcome-sub">Ingresa tus credenciales para acceder al sistema</p>
            </div>

            {{-- Alertas --}}
            @if(session('success'))
                <div style="background:rgba(5,150,105,0.1);border:1px solid rgba(5,150,105,0.25);
                            border-radius:10px;padding:12px 16px;
                            font-size:13px;color:#1DBD7F;
                            display:flex;align-items:center;gap:9px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" fill="rgba(5,150,105,0.2)"/>
                        <path d="M8 12l2.5 2.5L16 9" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:rgba(229,57,53,0.08);border:1px solid rgba(229,57,53,0.22);
                            border-radius:10px;padding:12px 16px;
                            font-size:13px;color:#F26F6F;
                            display:flex;align-items:flex-start;gap:9px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                        <circle cx="12" cy="12" r="10" fill="rgba(229,57,53,0.2)"/>
                        <path d="M12 8v4M12 16h.01" stroke="#E53935" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login.post') }}" style="display:flex;flex-direction:column;gap:0;">
                @csrf

                {{-- Email --}}
                <div style="margin-bottom:14px;">
                    <label class="is-form-label" for="email">Correo electrónico</label>
                    <div class="login-input-wrap">
                        <span class="login-input-icon">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M3 8l9 6 9-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="usuario@indemnisoat.co"
                               autocomplete="email" autofocus
                               class="is-input"
                               style="{{ $errors->has('email') ? 'border-color:rgba(229,57,53,0.55);' : '' }}">
                    </div>
                </div>

                {{-- Password --}}
                <div style="margin-bottom:18px;">
                    <label class="is-form-label" for="password">Contraseña</label>
                    <div class="login-input-wrap" style="position:relative;">
                        <span class="login-input-icon">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                                <rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <input type="password" id="password" name="password"
                               placeholder="••••••••••"
                               autocomplete="current-password"
                               class="is-input"
                               id="pwdInput"
                               style="{{ $errors->has('password') ? 'border-color:rgba(229,57,53,0.55);' : '' }}">
                        {{-- Toggle ver contraseña --}}
                        <button type="button" id="pwdToggle"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                       background:none;border:none;cursor:pointer;
                                       color:var(--text-3);padding:4px;display:flex;">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" id="eyeIcon">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Recordarme --}}
                <label style="display:flex;align-items:center;gap:9px;
                              margin-bottom:22px;cursor:pointer;" id="rememberLabel">
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
                    <span style="font-size:13px;color:var(--text-2);">
                        Mantener sesión activa en este dispositivo
                    </span>
                </label>

                {{-- Botón submit --}}
                <button type="submit" class="login-submit">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Iniciar sesión
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>

            </form>

            {{-- SSL --}}
            <div class="login-ssl">
                <span class="login-ssl-dot"></span>
                Conexión cifrada SSL · Datos protegidos
            </div>

            <div style="text-align:center;font-size:12px;color:var(--text-3);margin-top:12px;">
                ¿Problemas?
                <a href="#" style="color:#1B4FFF;font-weight:600;text-decoration:none;">
                    Contactar soporte
                </a>
            </div>

        </div>{{-- fin panel derecho --}}
    </div>{{-- fin login-card --}}
</div>

<script>
(function () {
    /* ── Tema ── */
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

    /* ── Checkbox recordarme ── */
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

    /* ── Toggle contraseña ── */
    const pwdInput  = document.getElementById('pwdInput') || document.getElementById('password');
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
