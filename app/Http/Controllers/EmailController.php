<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Models\Caso;
use App\Models\Bitacora;
use App\Models\OAuthToken;
use App\Services\MultiImapService;
use App\Services\MicrosoftDeviceCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class EmailController extends Controller
{
    public const TYPE_COLORS = [
        'solicitud_enviada'   => '#4B78FF',
        'respuesta_positiva'  => '#1DBD7F',
        'respuesta_negativa'  => '#F26F6F',
        'en_proceso'          => '#FFB800',
        'requiere_documentos' => '#8B5CF6',
        'citacion'            => '#EC4899',
        'otro'                => '#64748B',
    ];

    public const TYPE_LABELS = [
        'solicitud_enviada'   => 'Solicitud',
        'respuesta_positiva'  => 'Positiva',
        'respuesta_negativa'  => 'Negativa',
        'en_proceso'          => 'En Proceso',
        'requiere_documentos' => 'Documentos',
        'citacion'            => 'Citación',
        'otro'                => 'Otro',
    ];

    // ────────────────────────────────────────────────────────────────────────
    // index()
    // ────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $stats = [
            'emails_today'   => EmailLog::whereDate('created_at', today())->count(),
            'cases_updated'  => EmailLog::whereDate('created_at', today())
                                        ->whereNotNull('caso_id')
                                        ->distinct('caso_id')
                                        ->count('caso_id'),
            'overdue_cases'  => Caso::where('estado', 'Solicitud enviada a aseguradora')
                                    ->where('fecha_envio_solicitud', '<', now()->subDays(
                                        $this->getConfig('dias_sin_respuesta', 30)
                                    ))->count(),
            'pending_alerts' => Caso::where('estado', 'Sin respuesta - Requerimiento')
                                    ->whereDate('updated_at', today())
                                    ->count(),
            'total_cases'      => Caso::count(),
            'auto_cases_today' => Caso::where('auto_created', true)
                                      ->whereDate('created_at', today())
                                      ->count(),
        ];

        $recentEmails      = EmailLog::with('caso')
            ->orderBy('email_date', 'desc')
            ->limit(20)
            ->get();

        $emailIntegrations = $this->getStoredAccounts();

        $config = [
            'dias_sin_respuesta' => $this->getConfig('dias_sin_respuesta', 30),
            'frecuencia'         => $this->getConfig('frecuencia_revision', '6h'),
        ];

        // Tokens OAuth activos por email
        $oauthTokens = OAuthToken::pluck('expires_at', 'email')->all();

        return view('emails.index', compact(
            'emailIntegrations', 'stats', 'recentEmails', 'config', 'oauthTokens'
        ));
    }

    // ────────────────────────────────────────────────────────────────────────
    // sync()  →  POST /emails/sync
    // ────────────────────────────────────────────────────────────────────────
    public function sync(Request $request)
    {
        try {
            $service = new MultiImapService();
            $results = $service->processAllAccounts();

            $totalProcessed = $results['total_processed'] ?? 0;
            $accountResults = $results['results'] ?? [];

            $overdueMarked = $this->checkOverdueCases();

            $lines     = [];
            $hasErrors = false;

            foreach ($accountResults as $account => $result) {
                if ($result['success'] ?? false) {
                    $lines[] = "• {$account}: {$result['message']}";
                } else {
                    $lines[] = "• {$account}: Error — " . ($result['message'] ?? 'desconocido');
                    $hasErrors = true;
                }
            }

            if ($totalProcessed > 0) {
                $autoCasesCount = Caso::where('auto_created', true)
                    ->where('created_at', '>', now()->subMinutes(5))
                    ->count();

                if ($autoCasesCount > 0) {
                    array_unshift($lines, "{$autoCasesCount} caso(s) nuevos creados automáticamente");
                }

                if ($overdueMarked > 0) {
                    $lines[] = "{$overdueMarked} caso(s) marcados como 'Sin respuesta'";
                }

                array_unshift($lines, "Se procesaron {$totalProcessed} correos en total:");
                return redirect()->route('emails.index')->with('success', implode("\n", $lines));
            }

            if ($hasErrors) {
                array_unshift($lines, "Error al conectar con los correos (conecta las cuentas con OAuth):");
                return redirect()->route('emails.index')->with('error', implode("\n", $lines));
            }

            $infoMsg = 'No hay correos nuevos para procesar.';
            if (!empty($lines)) {
                $infoMsg .= ' (' . implode(', ', $lines) . ')';
            }
            if ($overdueMarked > 0) {
                $infoMsg .= " Se marcaron {$overdueMarked} caso(s) sin respuesta.";
            }

            return redirect()->route('emails.index')->with('info', $infoMsg);

        } catch (\Exception $e) {
            return redirect()->route('emails.index')
                ->with('error', 'Error al procesar correos: ' . $e->getMessage());
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // oauthSetup()  →  GET /emails/oauth/setup?email=xxx
    // Inicia el Device Code Flow y muestra la página de autorización
    // ────────────────────────────────────────────────────────────────────────
    public function oauthSetup(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return redirect()->route('emails.index')->with('error', 'Correo no especificado.');
        }

        try {
            $service        = new MicrosoftDeviceCodeService();
            $deviceCodeData = $service->initiateDeviceCode();

            // Guardar el device_code en sesión para el polling
            session([
                "oauth_device_code_{$email}"    => $deviceCodeData['device_code'],
                "oauth_device_expires_{$email}" => now()->addSeconds($deviceCodeData['expires_in'] ?? 900)->toIso8601String(),
            ]);

            return view('emails.oauth_setup', [
                'email'           => $email,
                'userCode'        => $deviceCodeData['user_code'],
                'verificationUri' => $deviceCodeData['verification_uri'] ?? 'https://microsoft.com/devicelogin',
                'expiresIn'       => $deviceCodeData['expires_in'] ?? 900,
            ]);

        } catch (\Exception $e) {
            return redirect()->route('emails.index')
                ->with('error', 'Error iniciando OAuth con Microsoft: ' . $e->getMessage());
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // oauthPoll()  →  GET /emails/oauth/poll?email=xxx  (AJAX)
    // El JS lo llama cada 5 segundos para saber si ya autorizó
    // ────────────────────────────────────────────────────────────────────────
    public function oauthPoll(Request $request)
    {
        $email      = $request->query('email');
        $deviceCode = session("oauth_device_code_{$email}");

        if (!$deviceCode) {
            return response()->json(['status' => 'expired']);
        }

        // Verificar si el código venció
        $expiresAt = session("oauth_device_expires_{$email}");
        if ($expiresAt && now()->greaterThan(\Carbon\Carbon::parse($expiresAt))) {
            session()->forget(["oauth_device_code_{$email}", "oauth_device_expires_{$email}"]);
            return response()->json(['status' => 'expired']);
        }

        try {
            $service   = new MicrosoftDeviceCodeService();
            $tokenData = $service->pollForToken($deviceCode);

            if ($tokenData) {
                $service->saveToken($email, $tokenData);
                session()->forget(["oauth_device_code_{$email}", "oauth_device_expires_{$email}"]);
                return response()->json(['status' => 'authorized']);
            }

            return response()->json(['status' => 'pending']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // oauthRevoke()  →  DELETE /emails/oauth/revoke?email=xxx
    // Desconecta una cuenta (borra el token)
    // ────────────────────────────────────────────────────────────────────────
    public function oauthRevoke(Request $request)
    {
        $email = $request->query('email');
        if ($email) {
            (new MicrosoftDeviceCodeService())->revokeToken($email);
        }
        return redirect()->route('emails.index')
            ->with('info', "Cuenta {$email} desconectada de OAuth.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // addAccount()  →  POST /emails/add-account
    // ────────────────────────────────────────────────────────────────────────
    public function addAccount(Request $request)
    {
        $request->validate([
            'email_address'  => 'required|email|max:255',
            'email_provider' => 'required|in:outlook,gmail,imap',
            'password'       => 'nullable|string|max:255',
            'imap_host'      => 'nullable|string|max:255',
        ], [
            'email_address.required' => 'El correo electrónico es obligatorio.',
            'email_address.email'    => 'Introduce un correo válido.',
            'email_provider.in'      => 'Proveedor no válido.',
        ]);

        $accounts = $this->getStoredAccounts(asArray: true);

        $exists = collect($accounts)->contains(fn($a) => $a['email_address'] === $request->email_address);
        if ($exists) {
            return redirect()->route('emails.index')
                ->withErrors(['email_address' => 'Esa cuenta ya está registrada.'])
                ->withInput();
        }

        $accounts[] = [
            'email_address'  => $request->email_address,
            'email_provider' => $request->email_provider,
            'imap_host'      => $request->imap_host,
            'password'       => $request->filled('password')
                                    ? Crypt::encryptString($request->password)
                                    : null,
            'is_active'      => true,
            'added_at'       => now()->toDateTimeString(),
        ];

        Cache::forever('email_accounts', $accounts);

        return redirect()->route('emails.index')
            ->with('success', "Cuenta {$request->email_address} agregada correctamente.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // removeAccount()  →  DELETE /emails/account/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function removeAccount(int $id)
    {
        $accounts = $this->getStoredAccounts(asArray: true);

        if (!isset($accounts[$id])) {
            return redirect()->route('emails.index')->with('error', 'Cuenta no encontrada.');
        }

        $removed = $accounts[$id]['email_address'];
        array_splice($accounts, $id, 1);
        Cache::forever('email_accounts', $accounts);

        return redirect()->route('emails.index')->with('success', "Cuenta {$removed} eliminada.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // saveConfig()  →  POST /emails/save-config
    // ────────────────────────────────────────────────────────────────────────
    public function saveConfig(Request $request)
    {
        $request->validate([
            'dias_sin_respuesta'  => 'required|integer|min:15|max:90',
            'frecuencia_revision' => 'required|in:1h,6h,24h',
        ], [
            'dias_sin_respuesta.min' => 'El mínimo es 15 días.',
            'dias_sin_respuesta.max' => 'El máximo es 90 días.',
            'frecuencia_revision.in' => 'Frecuencia no válida.',
        ]);

        Cache::forever('email_config', [
            'dias_sin_respuesta'  => (int) $request->dias_sin_respuesta,
            'frecuencia_revision' => $request->frecuencia_revision,
        ]);

        return redirect()->route('emails.index')
            ->with('success', 'Configuración guardada correctamente.');
    }

    // ────────────────────────────────────────────────────────────────────────
    // testConnection()  →  GET /emails/test-connection
    // ────────────────────────────────────────────────────────────────────────
    public function testConnection()
    {
        $service = new MultiImapService();
        $results = $service->testAllConnections();
        return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // ════════════════════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS
    // ════════════════════════════════════════════════════════════════════════

    private function getStoredAccounts(bool $asArray = false)
    {
        $raw = Cache::get('email_accounts', []);

        if (empty($raw)) {
            $raw = [
                [
                    'email_address'  => 'gestionsoat365@outlook.com',
                    'email_provider' => 'outlook',
                    'imap_host'      => null,
                    'password'       => null,
                    'is_active'      => true,
                    'added_at'       => null,
                ],
                [
                    'email_address'  => 'reclamacionessoat@hotmail.com',
                    'email_provider' => 'outlook',
                    'imap_host'      => null,
                    'password'       => null,
                    'is_active'      => true,
                    'added_at'       => null,
                ],
            ];
        }

        if ($asArray) {
            return $raw;
        }

        return collect($raw)->map(fn($a) => (object) $a);
    }

    private function getConfig(string $key, mixed $default = null): mixed
    {
        $config = Cache::get('email_config', []);
        return $config[$key] ?? $default;
    }

    private function checkOverdueCases(): int
    {
        $dias = $this->getConfig('dias_sin_respuesta', 30);

        $overdueCases = Caso::where('estado', 'Solicitud enviada a aseguradora')
            ->where('fecha_envio_solicitud', '<', now()->subDays($dias))
            ->get();

        foreach ($overdueCases as $caso) {
            $caso->estado = 'Sin respuesta - Requerimiento';
            $caso->save();

            Bitacora::create([
                'caso_id'      => $caso->id,
                'titulo'       => 'Alerta automática: Caso sin respuesta',
                'descripcion'  => "Han pasado {$dias} días sin respuesta de la aseguradora.",
                'fecha_evento' => now(),
            ]);
        }

        return $overdueCases->count();
    }
}
