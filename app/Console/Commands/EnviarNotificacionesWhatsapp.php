<?php

namespace App\Console\Commands;

use App\Models\Caso;
use App\Models\WhatsappContacto;
use App\Models\WhatsappNotificacionEnviada;
use App\Services\WhatsappService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EnviarNotificacionesWhatsapp extends Command
{
    protected $signature = 'whatsapp:notificar
                            {--dry-run : Simula el envío sin enviar mensajes reales}
                            {--caso= : Procesa solo un caso específico por ID}
                            {--debug : Muestra información detallada de cada caso procesado}';

    protected $description = 'Revisa todos los casos activos y envía notificaciones de WhatsApp según su estado jurídico.';

    /**
     * Mapa completo de alertas → días entre recordatorios.
     *
     *   0 = notifica UNA sola vez cuando entra en ese estado (cambio de estado).
     *   1 = recordatorio diario (urgencia extrema, ej: impugnación 3 días hábiles).
     *   3 = recordatorio cada 3 días.
     *   7 = recordatorio semanal.
     *
     * Flujo jurídico cubierto:
     *   Solicitud → Tutela → Fallo → Cumplimiento/Desacato/Impugnación
     *   → Segunda instancia → Junta → Cobro → Pago
     */
    private array $alertasActivas = [

        // ── EXTREMO URGENTE (recordatorio diario) ─────────────────────────────
        // Impugnación: solo 3 días hábiles para presentar
        'impugnacion_urgente'            => 1,

        // ── MUY URGENTE (cada 3 días) ──────────────────────────────────────────
        // Aseguradora negó/no respondió → presentar tutela ya
        'presentar_tutela'               => 3,
        // Prescripción: caso vence pronto
        'prescripcion_critica'           => 3,
        // Caso ya prescrito
        'prescrito'                      => 3,
        // Segunda instancia revoca: acción inmediata de la aseguradora
        'segunda_instancia_calificar'    => 3,
        'segunda_instancia_honorarios'   => 3,
        // Fallo concedido + aseguradora no cumplió → desacato
        'desacato_posible'               => 3,

        // ── URGENTE (semanal) ──────────────────────────────────────────────────
        // Sin respuesta aseguradora > 30 días → tutela
        'sin_respuesta'                  => 7,
        // alias que devuelve el sistema para sin_respuesta/tutela
        'tutela'                         => 7,
        // Tutela presentada > 30 días sin fallo
        'fallo_tutela_pendiente'         => 7,
        'seguimiento_tutela'             => 7,
        // Desacato presentado, seguimiento
        'desacato_seguimiento'           => 7,
        'desacato'                       => 7,
        // Impugnación presentada, espera segunda instancia
        'impugnacion_presentada'         => 7,
        'impugnacion'                    => 7,
        'segunda_instancia'              => 7,
        // Cobro enviado > 30 días sin pago → queja
        'pago_final_pendiente'           => 7,
        'queja'                          => 7,
        // Tutela cumplida, esperando dictamen aseguradora
        'dictamen_aseguradora_pendiente' => 7,
        // Tutela cumplida, pendiente pago honorarios junta
        'pago_honorarios_junta'          => 7,
        // Cumplimiento tutela (14 días)
        'cumplimiento_tutela'            => 3,
        'cumplimiento_segunda_instancia' => 7,

        // ── PENDIENTES (una sola notificación por estado) ──────────────────────
        // Documentación
        'documentacion_inicial'          => 0,
        'poder_pendiente'                => 0,
        'contrato_pendiente'             => 0,
        // Apelación de dictamen presentada a la aseguradora
        'apelacion_dictamen_pendiente'   => 0,
        // Honorarios junta pendientes de pago
        'honorarios_junta'               => 0,
        // Alta ortopedia pendiente
        'alta_ortopedia_pendiente'       => 0,
        // Solicitud a junta médica
        'solicitud_junta'                => 0,
        'solicitud_junta_urgente'        => 0,
        // Dictamen junta recibido → proceder con cobro
        'dictamen_junta_recibido'        => 0,
        // Listo para cobrar
        'cobro_listo'                    => 0,
        'reclamacion'                    => 0,
        'pago_pendiente'                 => 0,
        // Apelar dictamen
        'apelar_dictamen'                => 0,
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $casoId = $this->option('caso');
        $debug  = $this->option('debug');

        $this->info('=== INDEMNI-SOAT: Notificaciones WhatsApp ===');

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN — no se enviarán mensajes reales.');
        }

        // ── Verificar tablas ─────────────────────────────────────────────────
        if (!Schema::hasTable('whatsapp_contactos') || !Schema::hasTable('whatsapp_notificaciones_enviadas')) {
            $this->error('❌ Las tablas de WhatsApp no existen. Ejecuta: php artisan migrate');
            Log::error('WhatsApp scheduler: tablas no encontradas.');
            return Command::FAILURE;
        }

        // ── 1. Contactos activos ─────────────────────────────────────────────
        $contactos = WhatsappContacto::where('activo', true)->get();
        if ($contactos->isEmpty()) {
            $this->warn('⚠️  Sin contactos de WhatsApp activos.');
            return Command::SUCCESS;
        }
        $this->info("📱 Contactos activos: {$contactos->count()}");

        // ── 2. Casos activos ─────────────────────────────────────────────────
        $query = Caso::query()
            ->where(function ($q) {
                $q->where('estado', '!=', 'Pagado')
                  ->where('estado', '!=', 'Cerrado')
                  ->orWhereNull('estado');
            })
            ->whereNull('fecha_pago_final');

        if ($casoId) {
            $query->where('id', $casoId);
        }

        $casos = $query->get();
        $this->info("📂 Casos activos encontrados: {$casos->count()}");

        if ($casos->isEmpty()) {
            $this->info('Sin casos activos para procesar.');
            return Command::SUCCESS;
        }

        // ── 3. Resolver alertas de todos los casos ───────────────────────────
        $alertaResueltaPorCaso = $casos->mapWithKeys(
            fn($caso) => [$caso->id => $this->resolverAlertaCodigo($caso)]
        );

        // ── 4. Pre-cargar notificaciones ya enviadas (una sola query) ────────
        $casoIds        = $casos->pluck('id')->toArray();
        $numerosActivos = $contactos->pluck('numero')->toArray();

        $enviadas = WhatsappNotificacionEnviada::whereIn('caso_id', $casoIds)
            ->whereIn('numero_whatsapp', $numerosActivos)
            ->get()
            ->groupBy(fn($r) => "{$r->caso_id}|{$r->alerta_codigo}|{$r->numero_whatsapp}")
            ->map(fn($group) => $group->sortByDesc('enviada_at')->first());

        $servicio  = new WhatsappService();
        $enviados  = 0;
        $omitidos  = 0;
        $sinAlerta = 0;

        foreach ($casos as $caso) {
            $alertaCodigo = $alertaResueltaPorCaso->get($caso->id);

            if ($debug) {
                $estadoDisplay = substr((string)($caso->estado ?? 'sin estado'), 0, 55);
                $this->line("  [DEBUG] Caso {$caso->numero_caso} | estado: \"{$estadoDisplay}\" → alerta: " . ($alertaCodigo ?? 'null'));
            }

            if (!$alertaCodigo || !array_key_exists($alertaCodigo, $this->alertasActivas)) {
                $sinAlerta++;
                if ($debug && $alertaCodigo) {
                    $this->line("  [SKIP] Caso {$caso->numero_caso} | alerta '{$alertaCodigo}' no está en lista activa");
                }
                continue;
            }

            $diasRecordatorio = $this->alertasActivas[$alertaCodigo];

            foreach ($contactos as $contacto) {
                $clave              = "{$caso->id}|{$alertaCodigo}|{$contacto->numero}";
                $ultimaNotificacion = $enviadas->get($clave);

                if (!$this->debeEnviar($ultimaNotificacion, $diasRecordatorio)) {
                    $omitidos++;
                    if ($debug) {
                        $ultimoStr = $ultimaNotificacion
                            ? $ultimaNotificacion->enviada_at->format('d/m/Y')
                            : '-';
                        $this->line("  [SKIP] → {$contacto->nombre} | Caso {$caso->numero_caso} | último: {$ultimoStr}");
                    }
                    continue;
                }

                $mensaje = $servicio->construirMensaje($caso, $alertaCodigo);

                if ($dryRun) {
                    $this->line("  [DRY-RUN] → {$contacto->nombre} ({$contacto->numero}) | Caso {$caso->numero_caso} | {$alertaCodigo}");
                    $enviados++;
                    continue;
                }

                $ok = $servicio->enviar($contacto->numero, $mensaje);

                if ($ok) {
                    $nuevaNotif = WhatsappNotificacionEnviada::create([
                        'caso_id'         => $caso->id,
                        'alerta_codigo'   => $alertaCodigo,
                        'numero_whatsapp' => $contacto->numero,
                        'enviada_at'      => now(),
                    ]);
                    $enviadas->put($clave, $nuevaNotif);
                    $enviados++;
                    $this->line("  ✅ Enviado → {$contacto->nombre} | Caso {$caso->numero_caso} | {$alertaCodigo}");
                    Log::info('WhatsApp: notificación enviada', [
                        'caso'   => $caso->numero_caso,
                        'numero' => $contacto->numero,
                        'alerta' => $alertaCodigo,
                    ]);
                } else {
                    $this->error("  ❌ Error → {$contacto->nombre} ({$contacto->numero}) | Caso {$caso->numero_caso}");
                    Log::error('WhatsApp scheduler: fallo al enviar', [
                        'caso_id' => $caso->id,
                        'numero'  => $contacto->numero,
                        'alerta'  => $alertaCodigo,
                    ]);
                }

                usleep(700000); // 0.7 s entre mensajes
            }
        }

        $this->info('─────────────────────────────────────────');
        $this->info("✅ Enviados    : {$enviados}");
        $this->info("⏭️  Omitidos   : {$omitidos}  (ya notificados, aún en espera)");
        $this->info("⚪ Sin alerta  : {$sinAlerta} (casos sin acción requerida ahora)");
        $this->info('─────────────────────────────────────────');

        Log::info('WhatsApp scheduler: ciclo completado', [
            'enviados'   => $enviados,
            'omitidos'   => $omitidos,
            'sin_alerta' => $sinAlerta,
        ]);

        $this->limpiarNotificacionesObsoletas($alertaResueltaPorCaso);

        return Command::SUCCESS;
    }

    /**
     * Resuelve el código de alerta de un caso.
     *
     * Estrategia en orden de prioridad:
     *   1. alerta_valor del modelo (lógica temporal ya calculada por el sistema)
     *   2. Mapeo directo por texto del estado (para estados que siempre requieren acción)
     */
    private function resolverAlertaCodigo(Caso $caso): ?string
    {
        // ── Prioridad 1: alerta_valor del modelo ──────────────────────────────
        // El sistema ya calcula esto basado en fechas (sin_respuesta, prescripcion_critica, etc.)
        // Solo descartamos 'normal' porque significa "sin alerta temporal urgente aún"
        foreach (['alerta_valor', 'alerta_codigo'] as $atributo) {
            $valor = $caso->{$atributo} ?? null;
            if (!empty($valor) && $valor !== 'normal') {
                return (string) $valor;
            }
        }

        // ── Prioridad 2: Mapeo por estado del caso ────────────────────────────
        // Para estados donde la acción es requerida por el estado mismo,
        // independientemente de cuántos días han pasado.
        $estado = strtolower(trim((string)($caso->estado ?? '')));

        if (empty($estado)) {
            return null;
        }

        // ── URGENCIA EXTREMA: Impugnación (3 días hábiles por ley) ───────────
        if (str_contains($estado, 'negado') && str_contains($estado, 'impugnacion')) {
            return 'impugnacion_urgente';
        }

        // ── URGENTE: Aseguradora negó o no respondió → presentar tutela ──────
        if (str_contains($estado, 'aseguradora nego')) {
            return 'presentar_tutela';
        }
        if (str_contains($estado, 'aseguradora no respondio')) {
            return 'presentar_tutela';
        }
        if (str_contains($estado, 'no respondio') && str_contains($estado, 'presentar tutela')) {
            return 'presentar_tutela';
        }

        // ── URGENTE: Fallo concedido, aseguradora debe cumplir (14 días) ─────
        if (str_contains($estado, 'concedido') && str_contains($estado, 'cumplimiento')) {
            return 'desacato_posible';
        }

        // ── Segunda instancia revoca: acción inmediata ────────────────────────
        if (str_contains($estado, 'segunda instancia revoca')) {
            if (str_contains($estado, 'calificar')) {
                return 'segunda_instancia_calificar';
            }
            if (str_contains($estado, 'honorarios')) {
                return 'segunda_instancia_honorarios';
            }
        }

        // ── Desacato presentado: seguimiento ─────────────────────────────────
        if (str_contains($estado, 'desacato presentado')) {
            return 'desacato_seguimiento';
        }

        // ── Impugnación presentada: seguimiento segunda instancia ─────────────
        if (str_contains($estado, 'impugnacion presentada')) {
            return 'impugnacion_presentada';
        }

        // ── Tutela presentada sin fallo: seguimiento ─────────────────────────
        if (str_contains($estado, 'tutela para calificacion presentada')) {
            return 'fallo_tutela_pendiente';
        }
        if (str_contains($estado, 'tutela por debido proceso presentada')) {
            return 'fallo_tutela_pendiente';
        }

        // ── Tutela cumplida pero esperando acción aseguradora ────────────────
        if (str_contains($estado, 'tutela cumplida') && str_contains($estado, 'dictamen aseguradora')) {
            return 'dictamen_aseguradora_pendiente';
        }
        if (str_contains($estado, 'tutela cumplida') && str_contains($estado, 'pago honorarios')) {
            return 'pago_honorarios_junta';
        }

        // ── Apelación de dictamen pendiente ──────────────────────────────────
        if (str_contains($estado, 'apelacion de dictamen')) {
            return 'apelacion_dictamen_pendiente';
        }

        // ── Alta ortopedia pendiente ──────────────────────────────────────────
        if (str_contains($estado, 'pendiente alta por ortopedia')) {
            return 'alta_ortopedia_pendiente';
        }

        // ── Solicitud a junta médica: acción inmediata ───────────────────────
        if (str_contains($estado, 'listo para solicitud a junta')) {
            return 'solicitud_junta_urgente';
        }

        // ── Dictamen de junta recibido: proceder con cobro ───────────────────
        if (str_contains($estado, 'dictamen de junta recibido')) {
            return 'dictamen_junta_recibido';
        }

        // ── Listo para cobrar a aseguradora: acción inmediata ────────────────
        if (str_contains($estado, 'listo para cobro')) {
            return 'cobro_listo';
        }

        // ── Cobro enviado: seguimiento pago (30 días) ─────────────────────────
        if (str_contains($estado, 'cobro a aseguradora enviado')) {
            return 'pago_final_pendiente';
        }

        return null;
    }

    /**
     * Determina si hay que enviar la notificación.
     */
    private function debeEnviar(?WhatsappNotificacionEnviada $ultimaNotificacion, int $diasRecordatorio): bool
    {
        if ($ultimaNotificacion === null) {
            return true;
        }
        if ($diasRecordatorio === 0) {
            return false;
        }

        return $ultimaNotificacion->enviada_at->copy()->addDays($diasRecordatorio)->isPast();
    }

    /**
     * Limpia registros de notificaciones cuya alerta ya cambió (mismo mapa que el envío).
     */
    private function limpiarNotificacionesObsoletas(\Illuminate\Support\Collection $alertaResueltaPorCaso): void
    {
        $casoIdsConHistoria = WhatsappNotificacionEnviada::select('caso_id')
            ->distinct()
            ->pluck('caso_id');

        if ($casoIdsConHistoria->isEmpty()) {
            return;
        }

        $sinAlerta    = [];
        $cambioAlerta = [];

        foreach ($casoIdsConHistoria as $cid) {
            if (!$alertaResueltaPorCaso->has($cid)) {
                $sinAlerta[] = $cid;
                continue;
            }
            $alertaActual = $alertaResueltaPorCaso->get($cid);
            if (empty($alertaActual)) {
                $sinAlerta[] = $cid;
            } else {
                $cambioAlerta[$cid] = $alertaActual;
            }
        }

        if (!empty($sinAlerta)) {
            WhatsappNotificacionEnviada::whereIn('caso_id', $sinAlerta)->delete();
        }

        foreach ($cambioAlerta as $cid => $alertaActual) {
            WhatsappNotificacionEnviada::where('caso_id', $cid)
                ->where('alerta_codigo', '!=', $alertaActual)
                ->delete();
        }
    }
}
