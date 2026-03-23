<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — INDEMNISOAT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<div class="app-mesh"></div>

{{-- Theme toggle flotante --}}
<button id="themeToggle" type="button"
        style="position:fixed;top:18px;right:18px;z-index:500;
               width:38px;height:38px;border-radius:8px;
               border:1px solid var(--border-2);background:var(--bg-input);
               display:flex;align-items:center;justify-content:center;
               cursor:pointer;font-size:16px;color:var(--text-2);
               transition:all .2s;">
    <span id="themeIcon">☀️</span>
</button>

<div style="min-height:100vh;display:flex;align-items:center;
            justify-content:center;padding:40px 20px;position:relative;z-index:1;">

    {{-- Split card --}}
    <div class="is-animate-rise"
         style="display:grid;grid-template-columns:1fr 420px;
                max-width:900px;width:100%;
                background:var(--bg-card);
                border:1px solid var(--border-2);
                border-radius:24px;
                overflow:hidden;
                box-shadow:var(--shadow-md);">

        {{-- ── Panel izquierdo ── --}}
        <div class="is-login-left">
            <div style="position:relative;z-index:1;">

                {{-- Badge --}}
                <div style="display:inline-flex;align-items:center;gap:6px;
                            padding:5px 13px;border-radius:20px;
                            background:rgba(255,255,255,0.1);
                            border:1px solid rgba(255,255,255,0.15);
                            font-size:10px;font-weight:700;
                            color:rgba(255,255,255,0.7);
                            letter-spacing:1px;text-transform:uppercase;
                            margin-bottom:28px;">
                    <svg width="10" height="10" viewBox="0 0 48 48" fill="none">
                        <path d="M24 3C13 3 6 9 6 18L6 30C6 38 14 44 24 46C34 44 42 38 42 30L42 18C42 9 35 3 24 3Z"
                              fill="rgba(255,255,255,0.6)"/>
                    </svg>
                    Software Jurídico Certificado
                </div>

                {{-- Heading --}}
                <div style="font-family:'Playfair Display',serif;
                            font-size:30px;font-weight:700;color:#fff;
                            letter-spacing:-0.5px;line-height:1.25;margin-bottom:14px;">
                    Gestión inteligente<br>de reclamaciones SOAT
                </div>

                <p style="font-size:13px;color:rgba(255,255,255,0.5);
                          line-height:1.65;max-width:320px;">
                    Centralizamos todo el proceso jurídico: desde la primera consulta
                    hasta el cobro exitoso de la indemnización ante la aseguradora.
                </p>

                {{-- Features --}}
                <div style="margin-top:36px;display:flex;flex-direction:column;gap:14px;">

                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <div style="width:32px;height:32px;border-radius:8px;flex-shrink:0;
                                    background:rgba(255,255,255,0.1);
                                    display:flex;align-items:center;justify-content:center;font-size:14px;">
                            ⚖️
                        </div>
                        <div>
                            <strong style="display:block;font-size:13px;
                                          font-weight:600;color:rgba(255,255,255,0.9);">
                                Seguimiento jurídico completo
                            </strong>
                            <span style="font-size:11px;color:rgba(255,255,255,0.42);line-height:1.4;">
                                Control de poderes, juntas, calificaciones y estados
                            </span>
                        </div>
                    </div>

                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <div style="width:32px;height:32px;border-radius:8px;flex-shrink:0;
                                    background:rgba(255,255,255,0.1);
                                    display:flex;align-items:center;justify-content:center;font-size:14px;">
                            🛡️
                        </div>
                        <div>
                            <strong style="display:block;font-size:13px;
                                          font-weight:600;color:rgba(255,255,255,0.9);">
                                Alertas de prescripción
                            </strong>
                            <span style="font-size:11px;color:rgba(255,255,255,0.42);line-height:1.4;">
                                Notificaciones automáticas para casos en riesgo
                            </span>
                        </div>
                    </div>

                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <div style="width:32px;height:32px;border-radius:8px;flex-shrink:0;
                                    background:rgba(255,255,255,0.1);
                                    display:flex;align-items:center;justify-content:center;font-size:14px;">
                            💰
                        </div>
                        <div>
                            <strong style="display:block;font-size:13px;
                                          font-weight:600;color:rgba(255,255,255,0.9);">
                                Control de cobros y honorarios
                            </strong>
                            <span style="font-size:11px;color:rgba(255,255,255,0.42);line-height:1.4;">
                                Trazabilidad total desde el estimado hasta el pago
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer panel --}}
            <div style="position:relative;z-index:1;
                        padding-top:24px;margin-top:32px;
                        border-top:1px solid rgba(255,255,255,0.1);
                        font-size:11px;color:rgba(255,255,255,0.3);">
                © {{ date('Y') }} INDEMNISOAT · Datos confidenciales y protegidos
            </div>
        </div>

        {{-- ── Panel derecho (formulario) ── --}}
        <div style="padding:48px 40px;
                    display:flex;flex-direction:column;justify-content:center;
                    background:var(--bg-card);">

            {{-- Logo + nombre --}}
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px;">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none">
                    <defs>
                        <linearGradient id="ls1" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#4B78FF"/>
                            <stop offset="100%" stop-color="#1B4FFF"/>
                        </linearGradient>
                        <linearGradient id="lg1" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#D4AA48"/>
                            <stop offset="100%" stop-color="#B8922A"/>
                        </linearGradient>
                    </defs>
                    <path d="M24 3C13 3 6 9 6 18L6 30C6 38 14 44 24 46C34 44 42 38 42 30L42 18C42 9 35 3 24 3Z"
                          fill="url(#ls1)"/>
                    <path d="M24 7C15 7 10 12 10 19L10 29C10 36 16 41 24 43C32 41 38 36 38 29L38 19C38 12 33 7 24 7Z"
                          fill="rgba(255,255,255,0.07)"/>
                    <line x1="12" y1="22" x2="36" y2="22" stroke="url(#lg1)" stroke-width="2" stroke-linecap="round"/>
                    <line x1="24" y1="22" x2="24" y2="32" stroke="url(#lg1)" stroke-width="2" stroke-linecap="round"/>
                    <polygon points="24,17 21,22 27,22" fill="url(#lg1)"/>
                    <line x1="14" y1="22" x2="14" y2="27" stroke="url(#lg1)" stroke-width="1.4" stroke-linecap="round"/>
                    <ellipse cx="14" cy="28.5" rx="5" ry="2" fill="none" stroke="url(#lg1)" stroke-width="1.4"/>
                    <line x1="34" y1="22" x2="34" y2="27" stroke="url(#lg1)" stroke-width="1.4" stroke-linecap="round"/>
                    <ellipse cx="34" cy="28.5" rx="5" ry="2" fill="none" stroke="url(#lg1)" stroke-width="1.4"/>
                </svg>
                <span style="font-family:'Playfair Display',serif;
                             font-size:18px;font-weight:700;color:var(--text-1);">
                    INDEMNI<span style="color:#1B4FFF;">SOAT</span>
                </span>
            </div>

            <div style="font-family:'Playfair Display',serif;
                        font-size:22px;font-weight:700;
                        color:var(--text-1);margin-bottom:5px;">
                Bienvenido de vuelta
            </div>
            <p style="font-size:13px;color:var(--text-2);margin-bottom:28px;">
                Ingresa tus credenciales para acceder al sistema
            </p>

            {{-- Alertas --}}
            @if(session('success'))
                <div style="background:rgba(5,150,105,0.1);border:1px solid rgba(5,150,105,0.25);
                            border-radius:8px;padding:11px 14px;margin-bottom:18px;
                            font-size:13px;color:#1DBD7F;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:rgba(229,57,53,0.08);border:1px solid rgba(229,57,53,0.22);
                            border-radius:8px;padding:11px 14px;margin-bottom:18px;
                            font-size:13px;color:#F26F6F;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                {{-- Email --}}
                <div style="margin-bottom:16px;">
                    <label class="is-form-label" for="email">
                        Correo electrónico
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="usuario@indemnisoat.co"
                           autocomplete="email"
                           autofocus
                           class="is-input"
                           style="{{ $errors->has('email') ? 'border-color:rgba(229,57,53,0.55);' : '' }}">
                </div>

                {{-- Password --}}
                <div style="margin-bottom:20px;">
                    <label class="is-form-label" for="password">
                        Contraseña
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="••••••••••"
                           autocomplete="current-password"
                           class="is-input"
                           style="{{ $errors->has('password') ? 'border-color:rgba(229,57,53,0.55);' : '' }}">
                </div>

                {{-- Recordarme --}}
                <label style="display:flex;align-items:center;gap:9px;
                              margin-bottom:24px;cursor:pointer;" id="rememberLabel">
                    <div class="is-checkbox-box {{ old('recordar') ? 'checked' : '' }}"
                         id="rememberBox">
                        <svg width="9" height="7" viewBox="0 0 9 7" fill="none"
                             style="{{ old('recordar') ? '' : 'display:none' }}" id="rememberCheck">
                            <path d="M1 3.5l2.5 2.5L8 1" stroke="#fff"
                                  stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input type="checkbox" name="recordar" value="1"
                           id="rememberInput"
                           style="display:none;"
                           {{ old('recordar') ? 'checked' : '' }}>
                    <span style="font-size:13px;color:var(--text-2);">
                        Mantener sesión activa en este dispositivo
                    </span>
                </label>

                {{-- Botón --}}
                <button type="submit" class="is-btn-primary"
                        style="width:100%;justify-content:center;
                               padding:13px;font-size:14px;">
                    Iniciar sesión →
                </button>

            </form>

            {{-- Badge SSL --}}
            <div style="display:flex;align-items:center;justify-content:center;gap:6px;
                        margin-top:18px;padding:8px 12px;
                        background:var(--bg-input);
                        border:1px solid var(--border);
                        border-radius:8px;font-size:11px;color:var(--text-3);">
                <span style="width:6px;height:6px;border-radius:50%;
                             background:#059669;flex-shrink:0;display:inline-block;"></span>
                Conexión cifrada SSL · Datos protegidos
            </div>

            <div style="text-align:center;margin-top:14px;
                        font-size:12px;color:var(--text-3);">
                ¿Problemas?
                <a href="#" style="color:#1B4FFF;text-decoration:none;font-weight:600;">
                    Contactar soporte
                </a>
            </div>

        </div>{{-- fin panel derecho --}}
    </div>{{-- fin split card --}}
</div>

<script>
(function () {
    // ── Tema ──────────────────────────────────────────────
    const html    = document.documentElement;
    const btn     = document.getElementById('themeToggle');
    const icon    = document.getElementById('themeIcon');
    const STORAGE = 'is_theme';

    function applyTheme(t) {
        html.setAttribute('data-theme', t);
        icon.textContent = t === 'dark' ? '☀️' : '🌙';
        localStorage.setItem(STORAGE, t);
    }

    const saved = localStorage.getItem(STORAGE) ||
                  (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(saved);

    btn.addEventListener('click', function () {
        applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });

    // ── Checkbox personalizado ────────────────────────────
    const box   = document.getElementById('rememberBox');
    const input = document.getElementById('rememberInput');
    const check = document.getElementById('rememberCheck');

    document.getElementById('rememberLabel').addEventListener('click', function (e) {
        e.preventDefault();
        const isChecked = input.checked;
        input.checked = !isChecked;
        if (!isChecked) {
            box.style.background    = '#059669';
            box.style.borderColor   = '#059669';
            check.style.display     = '';
        } else {
            box.style.background    = '';
            box.style.borderColor   = '';
            check.style.display     = 'none';
        }
    });
})();
</script>

</body>
</html>
