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
</head>
<body>

<div class="app-mesh"></div>

{{-- ═══════════════════ NAVBAR ═══════════════════ --}}
<nav class="is-navbar">

    {{-- Marca --}}
    <a href="{{ route('casos.index') }}" class="is-nav-brand">
        <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="ng-shield" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#4B78FF"/>
                    <stop offset="100%" stop-color="#1B4FFF"/>
                </linearGradient>
                <linearGradient id="ng-gold" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#D4AA48"/>
                    <stop offset="100%" stop-color="#B8922A"/>
                </linearGradient>
            </defs>
            <path d="M24 3C13 3 6 9 6 18L6 30C6 38 14 44 24 46C34 44 42 38 42 30L42 18C42 9 35 3 24 3Z"
                  fill="url(#ng-shield)"/>
            <path d="M24 7C15 7 10 12 10 19L10 29C10 36 16 41 24 43C32 41 38 36 38 29L38 19C38 12 33 7 24 7Z"
                  fill="rgba(255,255,255,0.07)"/>
            <line x1="12" y1="22" x2="36" y2="22" stroke="url(#ng-gold)" stroke-width="2" stroke-linecap="round"/>
            <line x1="24" y1="22" x2="24" y2="32" stroke="url(#ng-gold)" stroke-width="2" stroke-linecap="round"/>
            <polygon points="24,17 21,22 27,22" fill="url(#ng-gold)"/>
            <line x1="14" y1="22" x2="14" y2="27" stroke="url(#ng-gold)" stroke-width="1.4" stroke-linecap="round" opacity="0.85"/>
            <ellipse cx="14" cy="28.5" rx="5" ry="2" fill="none" stroke="url(#ng-gold)" stroke-width="1.4"/>
            <line x1="34" y1="22" x2="34" y2="27" stroke="url(#ng-gold)" stroke-width="1.4" stroke-linecap="round" opacity="0.85"/>
            <ellipse cx="34" cy="28.5" rx="5" ry="2" fill="none" stroke="url(#ng-gold)" stroke-width="1.4"/>
        </svg>
        <span class="is-nav-brand-text">INDEMNI<span>SOAT</span></span>
    </a>

    {{-- Navegación central --}}
    <div style="display:flex; gap:2px;">
        <a href="{{ route('casos.index') }}"
           class="is-nav-item {{ request()->routeIs('casos.*') ? 'active' : '' }}">
            Casos
        </a>
        <a href="{{ route('dashboard') }}"
           class="is-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            Dashboard
        </a>
        @if(auth()->check() && auth()->user()->puedeGestionarUsuarios())
            <a href="{{ route('users.index') }}"
               class="is-nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                Usuarios
            </a>
        @endif
    </div>

    {{-- Derecha: tema + usuario --}}
    <div style="display:flex; align-items:center; gap:10px;">

        {{-- Toggle tema --}}
        <button class="is-theme-btn" id="themeToggle" title="Cambiar tema" type="button">
            <span id="themeIcon">☀️</span>
        </button>

        @auth
        <div style="text-align:right;">
            <div style="font-size:13px; font-weight:600; color:var(--text-1);">
                {{ auth()->user()->name }}
            </div>
            <div style="font-size:11px; color:var(--text-3);">
                {{ auth()->user()->textoRol() }}
                <span class="is-notif-dot" style="margin-left:4px;"></span>
            </div>
        </div>
        <div class="is-avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        @endauth
    </div>
</nav>

{{-- ═══════════════════ LAYOUT PRINCIPAL ═══════════════════ --}}
<div class="is-app-layout" style="padding-top:62px;">

    {{-- SIDEBAR --}}
    <aside class="is-sidebar">

        <div class="is-sidebar-section">Principal</div>

        <a href="{{ route('casos.index') }}"
           class="is-sidebar-item {{ request()->routeIs('casos.*') ? 'active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none"
                 stroke="currentColor" stroke-width="1.5">
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
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none"
                 stroke="currentColor" stroke-width="1.5">
                <rect x="1" y="1" width="6" height="7" rx="1"/>
                <rect x="9" y="1" width="6" height="4" rx="1"/>
                <rect x="1" y="10" width="6" height="5" rx="1"/>
                <rect x="9" y="7" width="6" height="8" rx="1"/>
            </svg>
            Dashboard
        </a>

        @if(auth()->check() && auth()->user()->puedeGestionarUsuarios())
        <a href="{{ route('users.index') }}"
           class="is-sidebar-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none"
                 stroke="currentColor" stroke-width="1.5">
                <circle cx="6" cy="5" r="3"/>
                <path d="M1 13s1-3 5-3 5 3 5 3"/>
                <path d="M12 2l2 2-2 2M14 4h-3"/>
            </svg>
            Usuarios
        </a>
        @endif

        <div class="is-sidebar-section">Alertas</div>

        <a href="{{ route('casos.index', ['alerta' => 'prescripcion']) }}"
           class="is-sidebar-item {{ request('alerta') === 'prescripcion' ? 'active' : '' }}">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none"
                 stroke="currentColor" stroke-width="1.5">
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
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none"
                 stroke="currentColor" stroke-width="1.5">
                <path d="M4 2h6l4 4v9a1 1 0 01-1 1H3a1 1 0 01-1-1V3a1 1 0 011-1z"/>
                <path d="M9 2v4h4M6 9h4M6 12h4"/>
            </svg>
            Docs. pendientes
            @if(isset($casosDocsPendientes) && $casosDocsPendientes > 0)
                <span class="is-sidebar-badge blue">{{ $casosDocsPendientes }}</span>
            @endif
        </a>

        <div class="is-sidebar-section">Módulos</div>

        <a href="#" class="is-sidebar-item">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none"
                 stroke="currentColor" stroke-width="1.5">
                <path d="M3 8h10M3 4h10M3 12h7"/>
            </svg>
            Bitácoras
        </a>

        <a href="#" class="is-sidebar-item">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none"
                 stroke="currentColor" stroke-width="1.5">
                <path d="M2 12V4a2 2 0 012-2h8a2 2 0 012 2v8"/>
                <path d="M6 6h4M6 9h4M6 12h2"/>
            </svg>
            Reportes
        </a>

        {{-- Footer usuario --}}
        <div class="is-sidebar-footer">
            @auth
            <div style="display:flex; align-items:center; gap:9px; padding:10px 11px;
                        border-radius:8px; background:var(--bg-input);">
                <div class="is-avatar" style="width:30px;height:30px;font-size:11px;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:var(--text-1);">
                        {{ auth()->user()->name }}
                    </div>
                    <div style="font-size:10px;color:var(--text-3);">
                        {{ auth()->user()->textoRol() }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" style="margin-top:4px;">
                @csrf
                <button type="submit"
                        style="display:flex;align-items:center;gap:9px;width:100%;
                               padding:8px 11px;border-radius:8px;border:none;
                               background:transparent;cursor:pointer;
                               font-size:12px;font-weight:500;color:var(--color-danger,#E53935);
                               font-family:'DM Sans',sans-serif;transition:background .18s;"
                        onmouseover="this.style.background='rgba(229,57,53,0.08)'"
                        onmouseout="this.style.background='transparent'">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none"
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

{{-- ═══════════════════ SCRIPTS GLOBALES ═══════════════════ --}}
<script>
(function () {
    // ── Tema persistente ──────────────────────────────────
    const html      = document.documentElement;
    const btn       = document.getElementById('themeToggle');
    const icon      = document.getElementById('themeIcon');
    const STORAGE   = 'is_theme';

    function applyTheme(t) {
        html.setAttribute('data-theme', t);
        icon.textContent = t === 'dark' ? '☀️' : '🌙';
        localStorage.setItem(STORAGE, t);
    }

    // Restaurar preferencia guardada
    const saved = localStorage.getItem(STORAGE) ||
                  (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(saved);

    btn.addEventListener('click', function () {
        applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });

    // ── Fade-in de página ─────────────────────────────────
    document.querySelectorAll('.is-animate-rise').forEach(function (el, i) {
        el.style.animationDelay = (i * 0.06) + 's';
    });
})();
</script>

@stack('scripts')

</body>
</html>
