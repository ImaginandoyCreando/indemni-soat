{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'INDEMNISOAT') — Sistema Jurídico</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
    /* ── Scrollbar en tablas ── */
    .is-table-wrap {
        scrollbar-width: thin;
        scrollbar-color: rgba(27,79,255,0.4) transparent;
    }
    .is-table-wrap::-webkit-scrollbar        { height: 4px; width: 4px; }
    .is-table-wrap::-webkit-scrollbar-track  { background: transparent; }
    .is-table-wrap::-webkit-scrollbar-thumb  { background: rgba(27,79,255,0.4); border-radius: 4px; }
    .is-table-wrap::-webkit-scrollbar-thumb:hover { background: rgba(27,79,255,0.65); }

    /* ── Avatar dropdown ── */
    .is-avatar-wrap { position: relative; }

    .is-avatar { cursor: pointer; user-select: none; }

    .is-avatar-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 224px;
        background: var(--bg-card);
        border: 1px solid var(--border-2);
        border-radius: 14px;
        box-shadow: 0 16px 48px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.03);
        z-index: 9999;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-6px) scale(0.975);
        pointer-events: none;
        transition: opacity 0.16s ease, transform 0.16s ease;
    }

    [data-theme="light"] .is-avatar-menu {
        box-shadow: 0 8px 32px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.04);
    }

    .is-avatar-menu.open {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: all;
    }

    .is-avatar-menu-header {
        padding: 13px 15px 11px;
        border-bottom: 1px solid var(--border);
        background: var(--bg-input);
    }

    .is-avatar-menu-header-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-1);
        line-height: 1.2;
    }

    .is-avatar-menu-header-role {
        font-size: 11px;
        color: var(--text-3);
        margin-top: 2px;
    }

    .is-avatar-menu-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 10px 15px;
        font-size: 12.5px;
        font-weight: 500;
        color: var(--text-2);
        cursor: pointer;
        transition: background 0.14s, color 0.14s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        font-family: 'DM Sans', sans-serif;
        text-decoration: none;
        line-height: 1;
    }

    .is-avatar-menu-item:hover {
        background: var(--bg-hover);
        color: var(--text-1);
    }

    .is-avatar-menu-item.danger { color: #E53935; }
    .is-avatar-menu-item.danger:hover {
        background: rgba(229,57,53,0.07);
        color: #F26F6F;
    }

    .is-avatar-menu-divider {
        height: 1px;
        background: var(--border);
        margin: 2px 0;
    }

    /* ── Navbar user info ── */
    .is-nav-user-info {
        text-align: right;
        line-height: 1.2;
    }

    .is-nav-user-name {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-1);
    }

    .is-nav-user-role {
        font-size: 10.5px;
        color: var(--text-3);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
    }

    /* ── Nav right group ── */
    .is-nav-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ── Nav center ── */
    .is-nav-center {
        display: flex;
        gap: 2px;
    }

    /* ── Sidebar footer ── */
    .is-sidebar-user {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 10px 11px;
        border-radius: 6px;
        background: var(--bg-input);
        border: 1px solid var(--border);
        margin-bottom: 3px;
    }

    .is-sidebar-user-info {
        flex: 1;
        min-width: 0;
    }

    .is-sidebar-user-name {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-1);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .is-sidebar-user-role {
        font-size: 10px;
        color: var(--text-3);
        margin-top: 1px;
    }

    .is-sidebar-logout {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 8px 11px;
        border-radius: 6px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        color: #E53935;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.15s, color 0.15s;
        text-align: left;
    }

    .is-sidebar-logout:hover {
        background: rgba(229,57,53,0.07);
        color: #F26F6F;
    }
    </style>
</head>
<body>

<div class="app-mesh"></div>

{{-- ═══════════════════ NAVBAR ═══════════════════ --}}
<nav class="is-navbar">

    {{-- Marca --}}
    <a href="{{ route('casos.index') }}" class="is-nav-brand">
        <svg width="30" height="30" viewBox="0 0 48 48" fill="none">
            <defs>
                <linearGradient id="ng-shield" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#4B78FF"/>
                    <stop offset="100%" stop-color="#1B4FFF"/>
                </linearGradient>
                <linearGradient id="ng-gold" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#F0C84A"/>
                    <stop offset="100%" stop-color="#B8922A"/>
                </linearGradient>
            </defs>
            <path d="M24 3C13 3 6 9 6 18L6 30C6 38 14 44 24 46C34 44 42 38 42 30L42 18C42 9 35 3 24 3Z"
                  fill="url(#ng-shield)"/>
            <path d="M24 7C15 7 10 12 10 19L10 29C10 36 16 41 24 43C32 41 38 36 38 29L38 19C38 12 33 7 24 7Z"
                  fill="rgba(255,255,255,0.06)"/>
            <line x1="12" y1="22" x2="36" y2="22" stroke="url(#ng-gold)" stroke-width="2" stroke-linecap="round"/>
            <line x1="24" y1="22" x2="24" y2="32" stroke="url(#ng-gold)" stroke-width="2" stroke-linecap="round"/>
            <polygon points="24,17 21,22 27,22" fill="url(#ng-gold)"/>
            <line x1="14" y1="22" x2="14" y2="27" stroke="url(#ng-gold)" stroke-width="1.4" stroke-linecap="round" opacity="0.9"/>
            <ellipse cx="14" cy="28.5" rx="5" ry="2" fill="none" stroke="url(#ng-gold)" stroke-width="1.4"/>
            <line x1="34" y1="22" x2="34" y2="27" stroke="url(#ng-gold)" stroke-width="1.4" stroke-linecap="round" opacity="0.9"/>
            <ellipse cx="34" cy="28.5" rx="5" ry="2" fill="none" stroke="url(#ng-gold)" stroke-width="1.4"/>
        </svg>
        <span class="is-nav-brand-text">INDEMNI<span>SOAT</span></span>
    </a>

    {{-- Navegación central --}}
    <div class="is-nav-center">
        <a href="{{ route('casos.index') }}"
           class="is-nav-item {{ request()->routeIs('casos.*') ? 'active' : '' }}">
            Casos
        </a>
        <a href="{{ route('dashboard') }}"
           class="is-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('emails.index') }}"
           class="is-nav-item {{ request()->routeIs('emails.*') ? 'active' : '' }}">
            Correos
        </a>
        @if(auth()->check() && auth()->user()->puedeGestionarUsuarios())
            <a href="{{ route('users.index') }}"
               class="is-nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                Usuarios
            </a>
        @endif
    </div>

    {{-- Derecha --}}
    <div class="is-nav-right">
        @auth
            {{-- Info usuario --}}
            <div class="is-nav-user-info">
                <div class="is-nav-user-name">{{ auth()->user()->name }}</div>
                <div class="is-nav-user-role">
                    {{ auth()->user()->textoRol() }}
                    <span class="is-notif-dot"></span>
                </div>
            </div>

            {{-- Avatar + dropdown --}}
            <div class="is-avatar-wrap">
                <div class="is-avatar" id="avatarBtn" title="Opciones de cuenta">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>

                <div class="is-avatar-menu" id="avatarMenu">

                    <div class="is-avatar-menu-header">
                        <div class="is-avatar-menu-header-name">{{ auth()->user()->name }}</div>
                        <div class="is-avatar-menu-header-role">{{ auth()->user()->textoRol() }}</div>
                    </div>

                    <button class="is-avatar-menu-item" id="menuThemeBtn" type="button">
                        <span id="menuThemeIcon" style="font-size:13px;">🌙</span>
                        <span id="menuThemeLabel">Cambiar tema</span>
                    </button>

                    <div class="is-avatar-menu-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="is-avatar-menu-item danger">
                            <svg width="13" height="13" viewBox="0 0 16 16" fill="none"
                                 stroke="currentColor" stroke-width="1.5">
                                <path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3"/>
                                <path d="M11 11l3-3-3-3M14 8H6"/>
                            </svg>
                            Cerrar sesión
                        </button>
                    </form>

                </div>
            </div>
        @endauth
    </div>

</nav>

{{-- ═══════════════════ LAYOUT PRINCIPAL ═══════════════════ --}}
<div class="is-app-layout" style="padding-top:62px;">

    {{-- ═══ SIDEBAR ═══ --}}
    <aside class="is-sidebar">

        {{-- Principal --}}
        <div class="is-sidebar-section">Principal</div>

        <a href="{{ route('casos.index') }}"
           class="is-sidebar-item {{ request()->routeIs('casos.*') ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="1" y="3" width="14" height="11" rx="2"/>
                <path d="M5 1v4M11 1v4M1 7h14"/>
            </svg>
            Casos
            @if(isset($totalCasos))
                <span class="is-sidebar-badge blue">{{ $totalCasos }}</span>
            @endif
        </a>

        <a href="{{ route('dashboard') }}"
           class="is-sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="1" y="1" width="6" height="7" rx="1"/>
                <rect x="9" y="1" width="6" height="4" rx="1"/>
                <rect x="1" y="10" width="6" height="5" rx="1"/>
                <rect x="9" y="7" width="6" height="8" rx="1"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('emails.index') }}"
           class="is-sidebar-item {{ request()->routeIs('emails.*') ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M1 4v8a1 1 0 001 1h12a1 1 0 001-1V4M1 4l6 4 6-4M1 4h14"/>
            </svg>
            Correos
        </a>

        @if(auth()->check() && auth()->user()->puedeGestionarUsuarios())
        <a href="{{ route('users.index') }}"
           class="is-sidebar-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="6" cy="5" r="3"/>
                <path d="M1 13s1-3 5-3 5 3 5 3"/>
                <path d="M12 2l2 2-2 2M14 4h-3"/>
            </svg>
            Usuarios
        </a>
        @endif

        {{-- Alertas --}}
        <div class="is-sidebar-section">Alertas</div>

        <a href="{{ route('casos.index', ['alerta' => 'prescripcion']) }}"
           class="is-sidebar-item {{ request('alerta') === 'prescripcion' ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M8 2a5 5 0 015 5v2l1 2H2l1-2V7a5 5 0 015-5z"/>
                <path d="M6 13a2 2 0 004 0"/>
            </svg>
            Prescripción urgente
            @if(isset($casosCriticos) && $casosCriticos > 0)
                <span class="is-sidebar-badge red">{{ $casosCriticos }}</span>
            @endif
        </a>

        <a href="{{ route('casos.index', ['alerta' => 'documentos']) }}"
           class="is-sidebar-item {{ request('alerta') === 'documentos' ? 'active' : '' }}">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 2h6l4 4v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z"/>
                <path d="M9 2v4h4M6 9h4M6 12h4"/>
            </svg>
            Docs. pendientes
            @if(isset($casosDocsPendientes) && $casosDocsPendientes > 0)
                <span class="is-sidebar-badge blue">{{ $casosDocsPendientes }}</span>
            @endif
        </a>

        {{-- Módulos --}}
        <div class="is-sidebar-section">Módulos</div>

        <a href="{{ route('casos.index') }}#bitacoras" class="is-sidebar-item">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M3 8h10M3 4h10M3 12h7"/>
            </svg>
            Bitácoras
        </a>

        <a href="{{ route('dashboard') }}#reportes" class="is-sidebar-item">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M2 12V4a2 2 0 012-2h8a2 2 0 012 2v8"/>
                <path d="M6 6h4M6 9h4M6 12h2"/>
            </svg>
            Reportes
        </a>

        {{-- Footer usuario --}}
        <div class="is-sidebar-footer">
            @auth
            <div class="is-sidebar-user">
                <div class="is-avatar" style="width:28px;height:28px;font-size:10.5px;flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="is-sidebar-user-info">
                    <div class="is-sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="is-sidebar-user-role">{{ auth()->user()->textoRol() }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="is-sidebar-logout">
                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none"
                         stroke="currentColor" stroke-width="1.5">
                        <path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3"/>
                        <path d="M11 11l3-3-3-3M14 8H6"/>
                    </svg>
                    Cerrar sesión
                </button>
            </form>
            @endauth
        </div>

    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="is-main">
        @yield('content')
    </main>

</div>{{-- fin app-layout --}}

{{-- ═══════════════════ SCRIPTS ═══════════════════ --}}
<script>
(function () {
    const html    = document.documentElement;
    const STORAGE = 'is_theme';

    window.applyTheme = function(t) {
        html.setAttribute('data-theme', t);
        localStorage.setItem(STORAGE, t);
        updateMenuThemeLabel();
    };

    const saved = localStorage.getItem(STORAGE) ||
                  (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    window.applyTheme(saved);

    /* Fade-in stagger */
    document.querySelectorAll('.is-animate-rise').forEach(function (el, i) {
        el.style.animationDelay = (i * 0.055) + 's';
    });

    /* Avatar dropdown */
    const avatarBtn    = document.getElementById('avatarBtn');
    const avatarMenu   = document.getElementById('avatarMenu');
    const menuThemeBtn = document.getElementById('menuThemeBtn');

    function updateMenuThemeLabel() {
        const t     = html.getAttribute('data-theme');
        const icon  = document.getElementById('menuThemeIcon');
        const label = document.getElementById('menuThemeLabel');
        if (!icon || !label) return;
        icon.textContent  = t === 'dark' ? '☀️' : '🌙';
        label.textContent = t === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
    }

    updateMenuThemeLabel();

    if (avatarBtn && avatarMenu) {
        avatarBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            avatarMenu.classList.toggle('open');
            updateMenuThemeLabel();
        });

        document.addEventListener('click', function () {
            avatarMenu.classList.remove('open');
        });

        avatarMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    if (menuThemeBtn) {
        menuThemeBtn.addEventListener('click', function () {
            const current = html.getAttribute('data-theme');
            window.applyTheme(current === 'dark' ? 'light' : 'dark');
            if (avatarMenu) avatarMenu.classList.remove('open');
        });
    }

})();
</script>

@stack('scripts')

</body>
</html>
