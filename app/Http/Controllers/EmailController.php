<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Models\Caso;
use App\Models\Bitacora;
use App\Services\MicrosoftGraphService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class EmailController extends Controller
{
    // ────────────────────────────────────────────────────────────────────────
    // Colores y etiquetas de tipos de correo (usados también en la vista)
    // ────────────────────────────────────────────────────────────────────────
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
        // Estadísticas
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
            'total_cases'       => Caso::count(),
            'auto_cases_today'  => Caso::where('auto_created', true)
                                       ->whereDate('created_at', today())
                                       ->count(),
        ];

        // Correos recientes
        $recentEmails = EmailLog::with('caso')
            ->orderBy('email_date', 'desc')
            ->limit(20)
            ->get();

        // Cuentas guardadas en cache/config
        $emailIntegrations = $this->getStoredAccounts();

        // Configuración de alertas
        $config = [
            'dias_sin_respuesta' => $this->getConfig('dias_sin_respuesta', 30),
            'frecuencia'         => $this->getConfig('frecuencia_revision', '6h'),
        ];

        return view('emails.index', compact('emailIntegrations', 'stats', 'recentEmails', 'config'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // sync()  →  POST /emails/sync
    // ────────────────────────────────────────────────────────────────────────
    public function sync(Request $request)
    {
        try {
            $service = new MicrosoftGraphService();
            $results = $service->processAllAccounts();

            $totalProcessed  = $results['total_processed'] ?? 0;
            $accountResults  = $results['results'] ?? [];

            // Verificar y marcar casos vencidos
            $overdueMarked = $this->checkOverdueCases();

            if ($totalProcessed > 0) {
                $lines = ["✅ Se procesaron {$totalProcessed} correos:"];

                foreach ($accountResults as $account => $result) {
                    if ($result['success'] ?? false) {
                        $lines[] = "• {$account}: {$result['processed']} correos";
                    } else {
                        $lines[] = "• {$account}: Error — " . ($result['message'] ?? 'desconocido');
                    }
                }

                // Casos creados automáticamente en los últimos 5 minutos
                $autoCasesCount = Caso::where('auto_created', true)
                    ->where('created_at', '>', now()->subMinutes(5))
                    ->count();

                if ($autoCasesCount > 0) {
                    $lines[] = "🎉 {$autoCasesCount} caso(s) nuevos creados automáticamente";
                }

                if ($overdueMarked > 0) {
                    $lines[] = "⚠️ {$overdueMarked} caso(s) marcados como 'Sin respuesta'";
                }

                return redirect()->route('emails.index')
                    ->with('success', implode("\n", $lines));
            }

            $infoMsg = 'No hay correos nuevos para procesar.';
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

        // Evitar duplicados
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
            // La contraseña se guarda cifrada — NUNCA en texto plano
            'password'       => $request->filled('password')
                                    ? Crypt::encryptString($request->password)
                                    : null,
            'is_active'      => true,
            'added_at'       => now()->toDateTimeString(),
        ];

        Cache::forever('email_accounts', $accounts);

        return redirect()->route('emails.index')
            ->with('success', "✅ Cuenta {$request->email_address} agregada correctamente.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // removeAccount()  →  DELETE /emails/account/{id}
    // ────────────────────────────────────────────────────────────────────────
    public function removeAccount(int $id)
    {
        $accounts = $this->getStoredAccounts(asArray: true);

        if (!isset($accounts[$id])) {
            return redirect()->route('emails.index')
                ->with('error', 'Cuenta no encontrada.');
        }

        $removed = $accounts[$id]['email_address'];
        array_splice($accounts, $id, 1);
        Cache::forever('email_accounts', $accounts);

        return redirect()->route('emails.index')
            ->with('success', "Cuenta {$removed} eliminada.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // saveConfig()  →  POST /emails/save-config
    // ────────────────────────────────────────────────────────────────────────
    public function saveConfig(Request $request)
    {
        $request->validate([
            'dias_sin_respuesta' => 'required|integer|min:15|max:90',
            'frecuencia_revision'=> 'required|in:1h,6h,24h',
        ], [
            'dias_sin_respuesta.min' => 'El mínimo es 15 días.',
            'dias_sin_respuesta.max' => 'El máximo es 90 días.',
            'frecuencia_revision.in' => 'Frecuencia no válida.',
        ]);

        Cache::forever('email_config', [
            'dias_sin_respuesta' => (int) $request->dias_sin_respuesta,
            'frecuencia_revision'=> $request->frecuencia_revision,
        ]);

        return redirect()->route('emails.index')
            ->with('success', '✅ Configuración guardada correctamente.');
    }

    // ════════════════════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Devuelve las cuentas guardadas en caché.
     * Si $asArray = true devuelve array plano; si no, devuelve Collection de objetos.
     */
    private function getStoredAccounts(bool $asArray = false)
    {
        $raw = Cache::get('email_accounts', []);

        // Cuentas base fijas (las que ya tenías en producción)
        // Puedes eliminar este bloque cuando uses la DB o el cache sea la fuente de verdad.
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

        // Convertir a colección de objetos para que el blade use $integration->email_address
        return collect($raw)->map(fn($a) => (object) $a);
    }

    /**
     * Lee un valor de la configuración de alertas guardada en caché.
     */
    private function getConfig(string $key, mixed $default = null): mixed
    {
        $config = Cache::get('email_config', []);
        return $config[$key] ?? $default;
    }

    /**
     * Marca como 'Sin respuesta - Requerimiento' los casos que llevan más de N días
     * en estado 'Solicitud enviada a aseguradora'.
     * Retorna el número de casos actualizados.
     */
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

    /**
     * Actualiza el estado del caso según el tipo de correo detectado
     * y registra el evento en la bitácora.
     */
    private function updateCaseStatus(Caso $caso, string $emailType, array $email): void
    {
        $descripcion = "Correo recibido de {$email['from']}: {$email['subject']}";

        $map = [
            'solicitud_enviada'   => ['estado' => 'Solicitud enviada a aseguradora',    'fecha' => 'fecha_envio_solicitud'],
            'respuesta_positiva'  => ['estado' => 'Respuesta favorable de aseguradora', 'fecha' => 'fecha_respuesta_aseguradora'],
            'respuesta_negativa'  => ['estado' => 'Respuesta negativa - Preparar tutela','fecha' => 'fecha_respuesta_aseguradora'],
            'en_proceso'          => ['estado' => 'En estudio por aseguradora',          'fecha' => null],
            'requiere_documentos' => ['estado' => 'Requiere documentos adicionales',     'fecha' => null],
            'citacion'            => ['estado' => 'Citación programada',                 'fecha' => null],
        ];

        if (isset($map[$emailType])) {
            $caso->estado = $map[$emailType]['estado'];
            if ($map[$emailType]['fecha']) {
                $caso->{$map[$emailType]['fecha']} = now();
            }
            $caso->save();
        }

        Bitacora::create([
            'caso_id'      => $caso->id,
            'titulo'       => 'Correo automático: ' . (self::TYPE_LABELS[$emailType] ?? $emailType),
            'descripcion'  => $descripcion,
            'fecha_evento' => $email['date'] ?? now(),
        ]);
    }

    /**
     * Busca un caso relacionado escaneando el asunto y cuerpo del correo.
     */
    private function findRelatedCase(string $subject, string $body): ?Caso
    {
        $text = $subject . ' ' . $body;

        $patterns = [
            '/caso[:\s#]+([A-Z0-9\-]+)/i',
            '/expediente[:\s#]+([A-Z0-9\-]+)/i',
            '/radicado[:\s#]+([A-Z0-9\-]+)/i',
            '/([A-Z]{2,4}\d{4,6})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $caso = Caso::where('numero_caso', 'LIKE', "%{$matches[1]}%")->first();
                if ($caso) {
                    return $caso;
                }
            }
        }

        return null;
    }

    /**
     * Extrae fechas y montos mencionados en el cuerpo del correo.
     */
    private function extractData(string $subject, string $body): array
    {
        $data = [];

        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $body, $matches)) {
            $data['fecha_mencionada'] = $matches[0];
        }

        if (preg_match('/\$?\s*(\d{1,3}(?:\.\d{3})*(?:,\d{2})?)/', $body, $matches)) {
            $data['monto_mencionado'] = $matches[1];
        }

        return $data;
    }
}
