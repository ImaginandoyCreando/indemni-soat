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
     * Alertas que disparan notificación y cuántos días esperar antes del siguiente recordatorio.
     *   - 0  = solo se envía UNA vez mientras siga la misma alerta
     *   - N  = se reenvía cada N días mientras la alerta persista
     */
    private array $alertasActivas = [
        // Críticas — recordatorio semanal
        'prescripcion_critica'           => 7,
        'sin_respuesta'                  => 7,
        'seguimiento_tutela'             => 7,
        'tutela'                         => 7,  // alias real que usa el sistema
        'queja'                          => 7,
        'desacato'                       => 7,
        'cumplimiento_segunda_instancia' => 7,

        // Urgentes — recordatorio cada 3 días
        'prescrito'                      => 3,
        'impugnacion'                    => 3,
        'segunda_instancia'              => 3,
        'cumplimiento_tutela'            => 3,

        // Pendientes — solo una notificación inicial
        'documentacion_inicial'          => 0,
        'poder_pendiente'                => 0,
        'contrato_pendiente'             => 0,
        'apelar_dictamen'                => 0,
        'honorarios_junta'               => 0,
        'alta_ortopedia_pendiente'       => 0,
        'solicitud_junta'                => 0,
        'reclamacion'                    => 0,
        'pago_pendiente'                 => 0,
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

        // ── Verificar tablas necesarias ──────────────────────────────────────
        if (!Schema::hasTable('whatsapp_contactos') || !Schema::hasTable('whatsapp_notificaciones_enviadas')) {
            $this->error('❌ Las tablas de WhatsApp no existen. Ejecuta: php artisan migrate');
            Log::error('WhatsApp scheduler: tablas no encontradas. Ejecutar php artisan migrate.');
            return Command::FAILURE;
        }

        // ── 1. Cargar contactos activos ──────────────────────────────────────
        $contactos = WhatsappContacto::where('activo', true)->get();
        if ($contactos->isEmpty()) {
            $this->warn('⚠️  Sin contactos de WhatsApp activos. Agrega al menos uno en el módulo WhatsApp.');
            return Command::SUCCESS;
        }
        $this->info("📱 Contactos activos: {$contactos->count()}");

        // ── 2. Cargar casos activos ──────────────────────────────────────────
        $query = Caso::query()
            ->where(function ($q) {
                $q->where('estado', '!=', 'Pagado')
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

        // ── 3. Resolver alertas de todos los casos de una vez ────────────────
        // Indexamos: caso_id => codigo_alerta_resuelto
        // Usamos este mapa tanto para decidir el envío como para la limpieza,
        // garantizando consistencia entre ambas operaciones.
        $alertaResueltaPorCaso = $casos->mapWithKeys(
            fn($caso) => [$caso->id => $this->resolverAlertaCodigo($caso)]
        );

        // ── 4. Pre-cargar notificaciones ya enviadas (una sola query) ────────
        $casoIds        = $casos->pluck('id')->toArray();
        $numerosActivos = $contactos->pluck('numero')->toArray();

        // Índice: "casoId|alertaCodigo|numero" => modelo WhatsappNotificacionEnviada (último)
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
                $this->line("  [DEBUG] Caso {$caso->numero_caso} → alerta = " . ($alertaCodigo ?? 'null'));
            }

            if (!$alertaCodigo || !array_key_exists($alertaCodigo, $this->alertasActivas)) {
                $sinAlerta++;
                if ($debug && $alertaCodigo) {
                    $this->line("  [SKIP] Caso {$caso->numero_caso} | alerta '{$alertaCodigo}' no está en lista de alertas activas");
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
                        $this->line("  [SKIP] → {$contacto->nombre} | Caso {$caso->numero_caso} | último envío: {$ultimoStr}");
                    }
                    continue;
                }

                // Llamada de instancia (compatible con static y non-static en el servidor)
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

                usleep(700000); // 0.7 s entre mensajes para no saturar UltraMsg
            }
        }

        $this->info('─────────────────────────────────────────');
        $this->info("✅ Enviados    : {$enviados}");
        $this->info("⏭️  Omitidos   : {$omitidos}  (ya notificados, aún en espera)");
        $this->info("⚪ Sin alerta  : {$sinAlerta} (casos sin alerta activa mapeada)");
        $this->info('─────────────────────────────────────────');

        Log::info('WhatsApp scheduler: ciclo completado', [
            'enviados'   => $enviados,
            'omitidos'   => $omitidos,
            'sin_alerta' => $sinAlerta,
        ]);

        // Limpieza usando el MISMO mapa de alertas resueltas → sin inconsistencias
        $this->limpiarNotificacionesObsoletas($alertaResueltaPorCaso);

        return Command::SUCCESS;
    }

    /**
     * Resuelve el código de alerta de un caso probando distintos atributos/métodos.
     * El orden de prioridad va de más específico a más genérico para evitar falsos positivos.
     */
    private function resolverAlertaCodigo(Caso $caso): ?string
    {
        // Opción 1: accessor alerta_valor definido en el modelo Caso
        $valor = $caso->alerta_valor ?? null;
        if (!empty($valor)) {
            return (string) $valor;
        }

        // Opción 2: columna alerta_codigo directa en la tabla casos
        $codigo = $caso->alerta_codigo ?? null;
        if (!empty($codigo)) {
            return (string) $codigo;
        }

        // Opción 3: inferir desde el estado/sub-estado del caso
        // (fallback para cuando el modelo no expone un atributo de alerta normalizado)
        $estado    = strtolower((string)($caso->estado ?? ''));
        $subestado = strtolower((string)($caso->sub_estado ?? $caso->subestado ?? ''));
        $textoRef  = $estado . ' ' . $subestado;

        if (str_contains($textoRef, 'sin respuesta')) {
            return 'sin_respuesta';
        }
        if (str_contains($textoRef, 'desacato')) {
            return 'desacato';
        }
        if (str_contains($textoRef, 'tutela')) {
            return 'seguimiento_tutela';
        }
        if (str_contains($textoRef, 'prescri')) {
            return 'prescripcion_critica';
        }
        if (str_contains($textoRef, 'queja')) {
            return 'queja';
        }
        if (str_contains($textoRef, 'impugna')) {
            return 'impugnacion';
        }

        return null;
    }

    /**
     * Determina si hay que enviar la notificación.
     *
     * @param  WhatsappNotificacionEnviada|null $ultimaNotificacion  Último registro guardado (o null si nunca se envió)
     * @param  int                              $diasRecordatorio     0 = solo una vez; N = reenviar cada N días
     */
    private function debeEnviar(?WhatsappNotificacionEnviada $ultimaNotificacion, int $diasRecordatorio): bool
    {
        if ($ultimaNotificacion === null) {
            return true; // Nunca se envió → enviar
        }
        if ($diasRecordatorio === 0) {
            return false; // Política "una sola vez" → no reenviar
        }

        // copy() garantiza que no mutamos el Carbon original del modelo
        return $ultimaNotificacion->enviada_at->copy()->addDays($diasRecordatorio)->isPast();
    }

    /**
     * Elimina en lote los registros de notificaciones cuya alerta ya cambió.
     * Recibe el mapa ya calculado (caso_id => alerta_resuelta) para ser
     * consistente con la lógica de envío — evita borrar historia válida.
     *
     * Usa una sola query SET-based por lote en lugar de un delete por caso.
     */
    private function limpiarNotificacionesObsoletas(\Illuminate\Support\Collection $alertaResueltaPorCaso): void
    {
        // Casos que tienen notificaciones registradas
        $casoIdsConHistoria = WhatsappNotificacionEnviada::select('caso_id')
            ->distinct()
            ->pluck('caso_id');

        if ($casoIdsConHistoria->isEmpty()) {
            return;
        }

        // Agrupar por tipo de limpieza
        $sinAlerta    = [];  // caso ya no tiene alerta activa → borrar todo
        $cambioAlerta = [];  // caso cambió de alerta → [caso_id => nueva_alerta]

        foreach ($casoIdsConHistoria as $cid) {
            if (!$alertaResueltaPorCaso->has($cid)) {
                // Caso ya no está en la lista activa (eliminado o pagado)
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

        // Borrar todo para casos sin alerta activa (una sola query)
        if (!empty($sinAlerta)) {
            WhatsappNotificacionEnviada::whereIn('caso_id', $sinAlerta)->delete();
        }

        // Borrar solo las filas de alertas anteriores (una query por caso con alerta diferente)
        // En la práctica son pocos casos los que cambian de alerta en un día
        foreach ($cambioAlerta as $cid => $alertaActual) {
            WhatsappNotificacionEnviada::where('caso_id', $cid)
                ->where('alerta_codigo', '!=', $alertaActual)
                ->delete();
        }
    }
}
