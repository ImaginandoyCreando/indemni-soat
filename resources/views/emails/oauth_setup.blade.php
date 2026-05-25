@extends('layouts.app')

@section('title', 'Conectar cuenta con Microsoft')

@section('content')

<div class="is-animate-rise" style="max-width:560px;margin:0 auto;padding:40px 20px;">

    {{-- Cabecera --}}
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:32px;">
        <a href="{{ route('emails.index') }}"
           style="width:38px;height:38px;border-radius:6px;border:1px solid var(--border-2);
                  background:var(--bg-input);display:flex;align-items:center;justify-content:center;
                  color:var(--text-2);text-decoration:none;transition:all .2s;flex-shrink:0;"
           onmouseover="this.style.background='var(--bg-hover)'"
           onmouseout="this.style.background='var(--bg-input)'">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <div>
            <div class="is-page-title">Conectar cuenta Microsoft</div>
            <div style="font-size:12px;color:var(--text-2);margin-top:3px;">{{ $email }}</div>
        </div>
    </div>

    {{-- Card principal --}}
    <div id="card-pending"
         style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:32px;text-align:center;">

        {{-- Icono Microsoft --}}
        <div style="width:64px;height:64px;margin:0 auto 24px;border-radius:12px;
                    background:linear-gradient(135deg,#f25022,#7fba00,#00a4ef,#ffb900);
                    display:flex;align-items:center;justify-content:center;">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <rect x="2" y="2" width="13" height="13" fill="white"/>
                <rect x="17" y="2" width="13" height="13" fill="white"/>
                <rect x="2" y="17" width="13" height="13" fill="white"/>
                <rect x="17" y="17" width="13" height="13" fill="white"/>
            </svg>
        </div>

        <div style="font-size:18px;font-weight:700;color:var(--text-1);margin-bottom:8px;">
            Autorización requerida
        </div>
        <div style="font-size:13px;color:var(--text-2);margin-bottom:28px;line-height:1.6;">
            Para leer los correos de <strong>{{ $email }}</strong>, necesitas autorizar el acceso
            una sola vez en Microsoft. El sistema guardará el acceso automáticamente.
        </div>

        {{-- Pasos --}}
        <div style="background:var(--bg-input);border-radius:8px;padding:20px;margin-bottom:24px;text-align:left;">
            <div style="font-size:12px;font-weight:700;color:var(--text-3);margin-bottom:16px;letter-spacing:.05em;">
                INSTRUCCIONES
            </div>

            <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:14px;">
                <div style="width:24px;height:24px;border-radius:50%;background:#4B78FF;color:white;
                            display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">1</div>
                <div style="font-size:13px;color:var(--text-1);padding-top:3px;">
                    Abre este enlace en el navegador:
                    <a href="{{ $verificationUri }}" target="_blank"
                       style="color:#4B78FF;font-weight:600;display:block;margin-top:4px;">
                        {{ $verificationUri }}
                    </a>
                </div>
            </div>

            <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:14px;">
                <div style="width:24px;height:24px;border-radius:50%;background:#4B78FF;color:white;
                            display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">2</div>
                <div style="font-size:13px;color:var(--text-1);padding-top:3px;">
                    Ingresa este código cuando Microsoft lo solicite:
                    <div style="font-size:28px;font-weight:800;letter-spacing:.25em;color:#4B78FF;
                                margin-top:8px;font-family:monospace;">
                        {{ $userCode }}
                    </div>
                </div>
            </div>

            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div style="width:24px;height:24px;border-radius:50%;background:#4B78FF;color:white;
                            display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">3</div>
                <div style="font-size:13px;color:var(--text-1);padding-top:3px;">
                    Inicia sesión con <strong>{{ $email }}</strong> y acepta los permisos.
                    Esta página detectará la autorización automáticamente.
                </div>
            </div>
        </div>

        {{-- Estado de espera --}}
        <div id="status-waiting" style="display:flex;align-items:center;justify-content:center;gap:10px;
             color:var(--text-2);font-size:13px;padding:14px;">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="animation:spin 1.2s linear infinite;">
                <circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="1.5" stroke-dasharray="22 16" stroke-linecap="round"/>
            </svg>
            Esperando autorización... (el código expira en <span id="countdown">{{ $expiresIn }}</span>s)
        </div>

        <div id="status-success" style="display:none;background:rgba(29,189,127,0.12);border:1px solid #1DBD7F;
             border-radius:8px;padding:16px;color:#1DBD7F;font-weight:600;font-size:14px;">
            Cuenta conectada correctamente. Redirigiendo...
        </div>

        <div id="status-expired" style="display:none;background:rgba(242,111,111,0.12);border:1px solid #F26F6F;
             border-radius:8px;padding:16px;color:#F26F6F;font-size:13px;">
            El código expiró.
            <a href="{{ route('emails.oauthSetup', ['email' => $email]) }}" style="color:#F26F6F;font-weight:700;">
                Intentar de nuevo
            </a>
        </div>

        {{-- Temporizador de expiración --}}
        <div style="margin-top:16px;font-size:11px;color:var(--text-3);">
            El código expirará automáticamente. Si ya autorizaste, espera unos segundos.
        </div>
    </div>

</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
(function () {
    const email      = @json($email);
    const pollUrl    = "{{ route('emails.oauthPoll') }}?email=" + encodeURIComponent(email);
    const successUrl = "{{ route('emails.index') }}";
    let seconds      = {{ $expiresIn }};
    let done         = false;

    // Cuenta regresiva
    const cdEl = document.getElementById('countdown');
    const countdownTimer = setInterval(() => {
        seconds--;
        if (cdEl) cdEl.textContent = seconds;
        if (seconds <= 0) clearInterval(countdownTimer);
    }, 1000);

    // Polling cada 5 segundos
    function poll() {
        if (done) return;

        fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'authorized') {
                    done = true;
                    clearInterval(countdownTimer);
                    document.getElementById('status-waiting').style.display = 'none';
                    document.getElementById('status-success').style.display = 'block';
                    setTimeout(() => { window.location.href = successUrl + '?oauth=ok'; }, 1500);

                } else if (data.status === 'expired') {
                    done = true;
                    clearInterval(countdownTimer);
                    document.getElementById('status-waiting').style.display = 'none';
                    document.getElementById('status-expired').style.display = 'block';

                } else {
                    // pending → seguir esperando
                    setTimeout(poll, 5000);
                }
            })
            .catch(() => {
                if (!done) setTimeout(poll, 5000);
            });
    }

    // Iniciar polling luego de 5 segundos (esperar que el usuario abra el link)
    setTimeout(poll, 5000);
})();
</script>

@endsection
