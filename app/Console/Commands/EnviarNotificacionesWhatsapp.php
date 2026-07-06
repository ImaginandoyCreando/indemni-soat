<?php

namespace App\Console\Commands;

use App\Models\Caso;
use App\Models\WhatsappContacto;
use App\Models\WhatsappNotificacionEnviada;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnviarNotificacionesWhatsapp extends Command
{
    protected $signature = 'whatsapp:notificar
                            {--dry-run : Solo muestra lo que se enviaria, sin enviar}
                            {--caso=   : Procesa solo el caso con este ID}';

    protected $description = 'Envia notificaciones WhatsApp de alertas activas a todos los contactos activos';

    private WhatsappService $servicio;

    public function __construct(WhatsappService $servicio)
    {
        parent::__construct();
        $this->servicio = $servicio;
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $soloId = $this->option('caso');

        $this->info($dryRun ? '[DRY-RUN] Simulando envios...' : 'Iniciando notificaciones WhatsApp...');

        // ── 1. Contactos activos ──────────────────────────────────────────────
        $contactos = WhatsappContacto::where('activo', true)->get();

        if ($contactos->isEmpty()) {
            $this->warn('No hay contactos activos. Registra al menos uno en la seccion WhatsApp.');
            return Command::SUCCESS;
        }

        // ── 2. Casos con alerta activa ────────────────────────────────────────
        $alertaSQL = <<<'SQL'
            CASE
              WHEN (estado = 'Pagado' OR fecha_pago_final IS NOT NULL)
                THEN 'pagado'
              WHEN (fecha_prescripcion IS NOT NULL AND fecha_prescripcion < CURRENT_DATE)
                THEN 'prescrito'
              WHEN (fecha_prescripcion IS NOT NULL
                    AND fecha_prescripcion >= CURRENT_DATE
                    AND fecha_prescripcion <= CURRENT_DATE + INTERVAL '90 days'
                    AND estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL)
                THEN 'prescripcion_critica'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND (tiene_poder = false OR tiene_contrato = false))
                THEN 'documentacion_inicial'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_entrega_poder IS NOT NULL AND fecha_poder_firmado IS NULL)
                THEN 'poder_pendiente'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_entrega_contrato IS NOT NULL AND fecha_contrato_firmado IS NULL)
                THEN 'contrato_pendiente'
              WHEN (fecha_fallo_segunda_instancia IS NOT NULL
                    AND resultado_fallo_segunda_instancia = 'confirma')
                THEN 'caso_cerrado'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_fallo_segunda_instancia IS NOT NULL
                    AND resultado_fallo_segunda_instancia = 'revoca'
                    AND fecha_cumplimiento_tutela IS NULL)
                THEN 'cumplimiento_segunda_instancia'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_fallo_tutela IS NOT NULL AND resultado_fallo_tutela = 'concedido'
                    AND fecha_incidente_desacato IS NULL AND fecha_cumplimiento_tutela IS NULL
                    AND fecha_pago_honorarios IS NULL
                    AND fecha_fallo_tutela < CURRENT_DATE - INTERVAL '14 days')
                THEN 'desacato'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_fallo_tutela IS NOT NULL AND resultado_fallo_tutela = 'concedido'
                    AND fecha_cumplimiento_tutela IS NULL AND fecha_incidente_desacato IS NULL
                    AND fecha_pago_honorarios IS NULL
                    AND fecha_fallo_tutela >= CURRENT_DATE - INTERVAL '14 days')
                THEN 'cumplimiento_tutela'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_fallo_tutela IS NOT NULL
                    AND resultado_fallo_tutela IN ('negado', 'parcial')
                    AND fecha_impugnacion IS NULL)
                THEN 'impugnacion'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_impugnacion IS NOT NULL AND fecha_fallo_segunda_instancia IS NULL)
                THEN 'segunda_instancia'
              WHEN (estado IS DISTINCT FROM 'Pagado'
                    AND fecha_reclamacion_final IS NOT NULL AND fecha_pago_final IS NULL
                    AND fecha_reclamacion_final < CURRENT_DATE - INTERVAL '30 days')
                THEN 'queja'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_tutela IS NOT NULL AND fecha_fallo_tutela IS NULL
                    AND fecha_tutela < CURRENT_DATE - INTERVAL '30 days')
                THEN 'seguimiento_tutela'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_tutela IS NOT NULL AND fecha_fallo_tutela IS NULL)
                THEN 'tutela'
              WHEN (estado IS DISTINCT FROM 'Pagado'
                    AND fecha_reclamacion_final IS NOT NULL AND fecha_pago_final IS NULL)
                THEN 'pago_pendiente'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_dictamen_junta IS NOT NULL AND furpen_completo = false
                    AND fecha_reclamacion_final IS NULL)
                THEN 'furpen_pendiente'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_dictamen_junta IS NOT NULL AND furpen_completo = true
                    AND fecha_reclamacion_final IS NULL)
                THEN 'reclamacion'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_pago_honorarios IS NOT NULL AND alta_ortopedia = false
                    AND fecha_envio_junta IS NULL)
                THEN 'alta_ortopedia_pendiente'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_pago_honorarios IS NOT NULL AND alta_ortopedia = true
                    AND fecha_envio_junta IS NULL)
                THEN 'solicitud_junta'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_apelacion IS NOT NULL AND fecha_pago_honorarios IS NULL)
                THEN 'honorarios_junta'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND tipo_respuesta_aseguradora = 'emitio_dictamen'
                    AND fecha_respuesta_aseguradora IS NOT NULL AND fecha_apelacion IS NULL)
                THEN 'apelar_dictamen'
              WHEN (estado IS DISTINCT FROM 'Pagado' AND fecha_pago_final IS NULL
                    AND fecha_solicitud_aseguradora IS NOT NULL
                    AND tipo_respuesta_aseguradora IS NULL
                    AND fecha_solicitud_aseguradora < CURRENT_DATE - INTERVAL '30 days')
                THEN 'sin_respuesta'
              ELSE 'normal'
            END
        SQL;

        $query = Caso::selectRaw("casos.id, casos.numero_caso, casos.nombres, casos.apellidos, ({$alertaSQL}) AS alerta_codigo")
            ->having(DB::raw("({$alertaSQL})"), '!=', 'pagado')
            ->having(DB::raw("({$alertaSQL})"), '!=', 'normal')
            ->having(DB::raw("({$alertaSQL})"), '!=', 'caso_cerrado');

        if ($soloId) {
            $query->where('casos.id', (int) $soloId);
        }

        $casos = $query->get();

        if ($casos->isEmpty()) {
            $this->info('No hay casos con alertas activas hoy.');
            return Command::SUCCESS;
        }

        $this->info("Casos con alertas activas: {$casos->count()}");

        // ── 3. Pre-cargar log de notificaciones enviadas ──────────────────────
        // Una sola query en lugar de N×M queries individuales
        $casosIds    = $casos->pluck('id')->toArray();
        $numerosWa   = $contactos->map(fn($c) => $c->numero_limpio)->toArray();

        $yaEnviadas = WhatsappNotificacionEnviada::whereIn('caso_id', $casosIds)
            ->whereIn('numero_whatsapp', $numerosWa)
            ->get()
            ->groupBy(fn($r) => "{$r->caso_id}|{$r->alerta_codigo}|{$r->numero_whatsapp}");

        // ── 4. Enviar / decidir ───────────────────────────────────────────────
        $enviados = 0;
        $omitidos = 0;

        foreach ($casos as $caso) {
            $alertaCodigo = $caso->alerta_codigo;
            $prioridad    = $this->servicio->prioridadAlerta($alertaCodigo);
            $diasReenvio  = $this->servicio->diasReenvio($prioridad);
            $mensaje      = $this->servicio->construirMensaje($caso, $alertaCodigo);

            foreach ($contactos as $contacto) {
                $numero = $contacto->numero_limpio;
                $clave  = "{$caso->id}|{$alertaCodigo}|{$numero}";

                $registro = $yaEnviadas->get($clave)?->first();

                // Verificar si debe enviar
                $debeEnviar = false;
                if (!$registro) {
                    $debeEnviar = true;
                } elseif ($diasReenvio !== null) {
                    $diasDesde = Carbon::parse($registro->enviado_en)->diffInDays(now());
                    $debeEnviar = $diasDesde >= $diasReenvio;
                }

                if (!$debeEnviar) {
                    $omitidos++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY-RUN] Caso {$caso->numero_caso} → {$contacto->nombre} ({$numero}) | {$alertaCodigo}");
                    $enviados++;
                    continue;
                }

                $resultado = $this->servicio->enviar($numero, $mensaje);

                if ($resultado['ok']) {
                    // Upsert: si ya existe actualiza enviado_en; si no, crea
                    WhatsappNotificacionEnviada::updateOrCreate(
                        [
                            'caso_id'         => $caso->id,
                            'alerta_codigo'   => $alertaCodigo,
                            'numero_whatsapp' => $numero,
                        ],
                        ['enviado_en' => now()]
                    );
                    $this->line("✅ Caso {$caso->numero_caso} → {$contacto->nombre} ({$alertaCodigo})");
                    $enviados++;
                } else {
                    $this->warn("❌ Fallo caso {$caso->numero_caso} → {$contacto->nombre}: " . json_encode($resultado['respuesta']));
                }

                // Respetar rate limit de UltraMsg (evitar bloqueos)
                usleep(500_000); // 0.5 segundos entre mensajes
            }
        }

        // ── 5. Limpiar log de alertas que ya no aplican ───────────────────────
        if (!$dryRun) {
            $codigosActivos = $casos->pluck('alerta_codigo', 'id')->toArray();

            $registros = WhatsappNotificacionEnviada::whereIn('caso_id', $casosIds)->get();
            foreach ($registros as $reg) {
                $alertaActual = $codigosActivos[$reg->caso_id] ?? null;
                if ($alertaActual !== $reg->alerta_codigo) {
                    $reg->delete();
                }
            }
        }

        $this->info("Listo. Enviados: {$enviados} | Omitidos (ya notificados): {$omitidos}");
        return Command::SUCCESS;
    }
}
