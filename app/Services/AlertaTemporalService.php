<?php

namespace App\Services;

use App\Models\Caso;
use Carbon\Carbon;

/**
 * AlertaTemporalService
 *
 * Genera alertas temporales por estado jurídico según el flujo SOAT.
 * Cada estado tiene su propia regla de tiempo y nivel de alerta.
 *
 * PLAZOS:
 *  - Impugnación de fallo negado : 3 días hábiles  (CRÍTICO)
 *  - Desacato sin cumplimiento   : 1 semana / 5 días hábiles (CRÍTICO)
 *  - Todos los demás procesos    : 1 mes / 30 días calendario (URGENTE → CRÍTICO)
 *
 * USO en Blade:
 *   @include('casos._alertas-temporales', ['caso' => $caso])
 *
 * USO en controlador:
 *   use App\Services\AlertaTemporalService;
 *   $alertas = AlertaTemporalService::calcular($caso);
 */
class AlertaTemporalService
{
    const NIVEL_CRITICO = 'critico';
    const NIVEL_URGENTE = 'urgente';
    const NIVEL_INFO    = 'info';

    // Días calendario considerados "1 mes" para la mayoría de procesos
    const MES_DIAS = 30;

    // Días hábiles de margen antes de empezar a alertar (evita alertas prematuras)
    const AVISO_PREVIO_DIAS = 7;

    /**
     * Retorna array de alertas activas para el caso.
     * Cada alerta: ['nivel', 'titulo', 'mensaje', 'dias', 'icono']
     */
    public static function calcular(Caso $caso): array
    {
        $estadosCerrados = [
            'Pagado',
            'Cerrado',
            'Caso cerrado en segunda instancia',
        ];

        if (in_array($caso->estado, $estadosCerrados)) {
            return [];
        }

        $alertas = [];
        $hoy     = Carbon::today();
        $estado  = $caso->estado ?? '';

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE A — FLUJO ASEGURADORA (solicitud → dictamen → apelación → tutela)
        // ══════════════════════════════════════════════════════════════════════

        // A1. Solicitud enviada sin respuesta — 1 mes hábil
        if (in_array($estado, [
            'Solicitud de calificación enviada',
            'Aseguradora negó - presentar tutela para calificación',
            'Aseguradora no respondió - presentar tutela para calificación',
        ]) && $caso->fecha_solicitud_aseguradora) {
            $dias = self::diasHabiles(Carbon::parse($caso->fecha_solicitud_aseguradora), $hoy);
            if ($dias >= 20) {
                $nivel = $dias >= 30 ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '⏰',
                    'Sin respuesta de la aseguradora',
                    "Han pasado {$dias} días hábiles desde la solicitud de calificación enviada.",
                    $dias >= 30
                        ? 'El plazo de 1 mes hábil se cumplió. Procede a presentar tutela.'
                        : 'El plazo es de 30 días hábiles. Prepara la tutela por si no responden.',
                    $dias
                );
            }
        }

        // A2. Dictamen recibido — plazo de 1 mes para manifestar inconformidad/apelar
        if ($estado === 'Dictamen de aseguradora recibido' && $caso->fecha_solicitud_aseguradora) {
            // Si el modelo tiene fecha_dictamen_aseguradora úsala; si no, usa la solicitud como proxy
            $fechaRef = property_exists($caso, 'fecha_dictamen_aseguradora') && $caso->fecha_dictamen_aseguradora
                        ? $caso->fecha_dictamen_aseguradora
                        : $caso->fecha_solicitud_aseguradora;
            $dias = Carbon::parse($fechaRef)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '📝',
                    'Dictamen recibido — manifestar inconformidad',
                    "Llevan {$dias} días desde el dictamen de la aseguradora.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes para manifestar inconformidad puede estar vencido.'
                        : 'Tienes 1 mes para presentar inconformidad o apelación del dictamen.',
                    $dias
                );
            }
        }

        // A3. Apelación presentada sin resultado — 1 mes
        if ($estado === 'Apelación de dictamen presentada' && $caso->fecha_apelacion) {
            $dias = Carbon::parse($caso->fecha_apelacion)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '🏛️',
                    'Apelación sin respuesta del ente calificador',
                    "Han pasado {$dias} días desde la apelación del dictamen.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes venció. Haz seguimiento urgente ante la junta o aseguradora.'
                        : 'El plazo esperado es de 1 mes.',
                    $dias
                );
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE B — FLUJO TUTELA
        // ══════════════════════════════════════════════════════════════════════

        // B1. Tutela presentada sin fallo — 10 días hábiles (juez debe fallar en 10 días)
        if (in_array($estado, [
            'Tutela para calificación presentada',
            'Tutela por debido proceso presentada',
        ]) && $caso->fecha_tutela) {
            $dias = self::diasHabiles(Carbon::parse($caso->fecha_tutela), $hoy);
            if ($dias >= 10) {
                $nivel = $dias >= 20 ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '📋',
                    'Tutela presentada — en espera de fallo',
                    "Han pasado {$dias} días hábiles desde la presentación de la tutela.",
                    $dias >= 10
                        ? '⚠️ El juez tiene 10 días hábiles para fallar. Haz seguimiento urgente con el juzgado.'
                        : 'Haz seguimiento periódico con el juzgado.',
                    $dias
                );
            }
        }

        // B2. Fallo negado — IMPUGNAR en 3 días hábiles (MÁS CRÍTICO)
        if ($estado === 'Fallo tutela negado - pendiente impugnación' && $caso->fecha_fallo_tutela) {
            $dias = self::diasHabiles(Carbon::parse($caso->fecha_fallo_tutela), $hoy);
            $restantes = max(0, 3 - $dias);
            $alertas[] = self::alerta(
                self::NIVEL_CRITICO,
                '🚨',
                '¡IMPUGNAR AHORA — 3 días hábiles!',
                "El fallo de tutela fue NEGADO hace {$dias} día(s) hábil(es).",
                $dias >= 3
                    ? '⚠️ El plazo de 3 días hábiles para impugnar puede estar VENCIDO. Actúa de inmediato.'
                    : "Quedan aproximadamente {$restantes} día(s) hábil(es) para impugnar. Es el plazo más corto del proceso.",
                $dias
            );
        }

        // B3. Fallo concedido — aseguradora debe cumplir en 1 mes
        if ($estado === 'Fallo tutela concedido - esperando cumplimiento' && $caso->fecha_fallo_tutela) {
            $dias = Carbon::parse($caso->fecha_fallo_tutela)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '⚖️',
                    'Aseguradora debe cumplir fallo concedido',
                    "Han pasado {$dias} días desde el fallo de tutela CONCEDIDO sin que la aseguradora cumpla.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes venció. Presenta incidente de desacato de inmediato.'
                        : 'Plazo de 1 mes para cumplir. Prepara el incidente de desacato.',
                    $dias
                );
            }
        }

        // B4. Fallo de tutela registrado (estado intermedio) — seguimiento
        if ($estado === 'Fallo de tutela registrado' && $caso->fecha_fallo_tutela) {
            $dias = Carbon::parse($caso->fecha_fallo_tutela)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $alertas[] = self::alerta(
                    self::NIVEL_INFO,
                    '📋',
                    'Fallo registrado — pendiente definir próximo paso',
                    "Han pasado {$dias} días desde el registro del fallo de tutela.",
                    'Verifica si el fallo fue concedido o negado y actualiza el estado del caso.',
                    $dias
                );
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE C — DESACATO
        // ══════════════════════════════════════════════════════════════════════

        // C1. Desacato presentado sin cumplimiento — 1 semana (5 días hábiles)
        if ($estado === 'Incidente de desacato presentado' && $caso->fecha_incidente_desacato) {
            $dias = self::diasHabiles(Carbon::parse($caso->fecha_incidente_desacato), $hoy);
            if ($dias >= 5) {
                $alertas[] = self::alerta(
                    self::NIVEL_CRITICO,
                    '🚨',
                    'Desacato sin cumplimiento — 1 semana',
                    "Han pasado {$dias} días hábiles desde el incidente de desacato sin cumplimiento.",
                    'La aseguradora debe cumplir en ~1 semana. Solicita al juez que ejecute el desacato.',
                    $dias
                );
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE D — SEGUNDA INSTANCIA
        // ══════════════════════════════════════════════════════════════════════

        // D1. Impugnación presentada sin fallo segunda instancia — 1 mes
        if ($estado === 'Impugnación presentada' && $caso->fecha_impugnacion) {
            $dias = Carbon::parse($caso->fecha_impugnacion)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '🏛️',
                    'Impugnación sin fallo de segunda instancia',
                    "Han pasado {$dias} días desde la impugnación sin fallo.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo esperado de 1 mes venció. Haz seguimiento con el tribunal.'
                        : 'Haz seguimiento periódico con el tribunal.',
                    $dias
                );
            }
        }

        // D2. Fallo segunda instancia registrado — seguimiento
        if ($estado === 'Fallo de segunda instancia registrado' && $caso->fecha_fallo_segunda_instancia) {
            $dias = Carbon::parse($caso->fecha_fallo_segunda_instancia)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $alertas[] = self::alerta(
                    self::NIVEL_INFO,
                    '📋',
                    'Fallo segunda instancia — actualizar estado',
                    "Han pasado {$dias} días desde el registro del fallo de segunda instancia.",
                    'Verifica el resultado y actualiza el estado del caso.',
                    $dias
                );
            }
        }

        // D3. Segunda instancia revoca — aseguradora debe calificar (1 mes)
        if ($estado === 'Segunda instancia revoca - aseguradora debe calificar'
            && $caso->fecha_fallo_segunda_instancia) {
            $dias = Carbon::parse($caso->fecha_fallo_segunda_instancia)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '⚖️',
                    'Segunda instancia revoca — aseguradora debe calificar',
                    "Han pasado {$dias} días desde que la segunda instancia revocó el fallo.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes venció. La aseguradora debe emitir calificación. Escala si no lo hace.'
                        : 'Plazo de 1 mes para que la aseguradora califique.',
                    $dias
                );
            }
        }

        // D4. Segunda instancia revoca — aseguradora debe pagar honorarios (1 mes)
        if ($estado === 'Segunda instancia revoca - aseguradora debe pagar honorarios'
            && $caso->fecha_fallo_segunda_instancia) {
            $dias = Carbon::parse($caso->fecha_fallo_segunda_instancia)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '💰',
                    'Segunda instancia revoca — pendiente pago honorarios',
                    "Han pasado {$dias} días desde que la segunda instancia revocó el fallo.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes venció. Haz seguimiento urgente del pago de honorarios.'
                        : 'Plazo de 1 mes para recibir pago de honorarios.',
                    $dias
                );
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE E — PENDIENTES DOCUMENTOS / ORTOPEDIA
        // ══════════════════════════════════════════════════════════════════════

        // E1. Tutela cumplida — pendiente dictamen aseguradora (1 mes)
        if ($estado === 'Tutela cumplida - pendiente dictamen aseguradora'
            && $caso->fecha_cumplimiento_tutela) {
            $dias = Carbon::parse($caso->fecha_cumplimiento_tutela)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '🏥',
                    'Cumplimiento tutela — pendiente dictamen aseguradora',
                    "Han pasado {$dias} días desde el cumplimiento de la tutela sin dictamen.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes venció. Exige a la aseguradora que emita el dictamen.'
                        : 'El plazo esperado es de 1 mes para el dictamen.',
                    $dias
                );
            }
        }

        // E2. Tutela cumplida — pendiente pago honorarios (1 mes)
        if ($estado === 'Tutela cumplida - pendiente pago honorarios'
            && $caso->fecha_cumplimiento_tutela) {
            $dias = Carbon::parse($caso->fecha_cumplimiento_tutela)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '💰',
                    'Cumplimiento tutela — pendiente pago honorarios a junta',
                    "Han pasado {$dias} días desde el cumplimiento de la tutela sin pago de honorarios.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes venció. Gestiona el pago de honorarios a la junta.'
                        : 'El plazo esperado para el pago de honorarios es de 1 mes.',
                    $dias
                );
            }
        }

        // E3. Pendiente alta por ortopedia — 1 mes como referencia
        if ($estado === 'Pendiente alta por ortopedia') {
            // Usamos updated_at como proxy de cuándo entró a este estado
            $fechaRef = $caso->updated_at ?? $caso->created_at ?? null;
            if ($fechaRef) {
                $dias = Carbon::parse($fechaRef)->diffInDays($hoy);
                if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                    $nivel = $dias >= self::MES_DIAS ? self::NIVEL_URGENTE : self::NIVEL_INFO;
                    $alertas[] = self::alerta(
                        $nivel,
                        '🩺',
                        'Pendiente alta por ortopedia',
                        "Llevan {$dias} días en espera del alta por ortopedia.",
                        'Haz seguimiento con el centro ortopédico para agilizar el alta.',
                        $dias
                    );
                }
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE F — FLUJO JUNTA
        // ══════════════════════════════════════════════════════════════════════

        // F1. Listo para solicitud a junta — sin envío en 1 mes
        if ($estado === 'Listo para solicitud a junta') {
            $fechaRef = $caso->fecha_pago_honorarios ?? $caso->updated_at ?? null;
            if ($fechaRef) {
                $dias = Carbon::parse($fechaRef)->diffInDays($hoy);
                if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                    $nivel = $dias >= self::MES_DIAS ? self::NIVEL_URGENTE : self::NIVEL_INFO;
                    $alertas[] = self::alerta(
                        $nivel,
                        '📬',
                        'Listo para junta — solicitud no enviada',
                        "Llevan {$dias} días en estado 'Listo para junta' sin enviar la solicitud.",
                        'Envía la solicitud a la junta médica.',
                        $dias
                    );
                }
            }
        }

        // F2. Solicitud enviada a junta — sin dictamen en 1 mes
        if ($estado === 'Solicitud enviada a junta' && $caso->fecha_envio_junta) {
            $dias = Carbon::parse($caso->fecha_envio_junta)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '🏥',
                    'Junta médica sin emitir dictamen',
                    "Han pasado {$dias} días desde el envío de la solicitud a la junta sin dictamen.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes venció. Contacta la junta para exigir el dictamen.'
                        : 'El plazo esperado es de 1 mes para el dictamen.',
                    $dias
                );
            }
        }

        // F3. Dictamen de junta recibido — sin cobro en 1 mes
        if ($estado === 'Dictamen de junta recibido' && $caso->fecha_dictamen_junta) {
            $dias = Carbon::parse($caso->fecha_dictamen_junta)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_URGENTE : self::NIVEL_INFO;
                $alertas[] = self::alerta(
                    $nivel,
                    '💼',
                    'Dictamen recibido — enviar cobro a aseguradora',
                    "Han pasado {$dias} días desde el dictamen de la junta sin enviar el cobro.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes pasó. Prepara y envía el cobro a la aseguradora.'
                        : 'Prepara el cobro para enviarlo a la aseguradora.',
                    $dias
                );
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE G — COBRO Y PAGO FINAL
        // ══════════════════════════════════════════════════════════════════════

        // G1. Listo para cobro a aseguradora — sin envío en 1 mes
        if ($estado === 'Listo para cobro a aseguradora') {
            $fechaRef = $caso->fecha_dictamen_junta ?? $caso->updated_at ?? null;
            if ($fechaRef) {
                $dias = Carbon::parse($fechaRef)->diffInDays($hoy);
                if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                    $nivel = $dias >= self::MES_DIAS ? self::NIVEL_URGENTE : self::NIVEL_INFO;
                    $alertas[] = self::alerta(
                        $nivel,
                        '📬',
                        'Cobro listo — aún no enviado a la aseguradora',
                        "Llevan {$dias} días en estado 'Listo para cobro' sin enviar.",
                        'Envía el cobro a la aseguradora para iniciar el pago.',
                        $dias
                    );
                }
            }
        }

        // G2. Cobro enviado sin pago — 1 mes
        if ($estado === 'Cobro a aseguradora enviado' && $caso->fecha_reclamacion_final) {
            $dias = Carbon::parse($caso->fecha_reclamacion_final)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO_DIAS) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '💳',
                    'Cobro enviado — sin pago de la aseguradora',
                    "Han pasado {$dias} días desde el envío del cobro sin que la aseguradora pague.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 1 mes venció. Considera presentar queja ante la Superintendencia Financiera.'
                        : 'El plazo esperado es de 1 mes para el pago.',
                    $dias
                );
            }
        }

        // Ordenar: crítico primero, luego urgente, luego info; mismo nivel → más días primero
        usort($alertas, function ($a, $b) {
            $orden = [self::NIVEL_CRITICO => 0, self::NIVEL_URGENTE => 1, self::NIVEL_INFO => 2];
            $cmp = ($orden[$a['nivel']] ?? 9) <=> ($orden[$b['nivel']] ?? 9);
            return $cmp !== 0 ? $cmp : ($b['dias'] <=> $a['dias']);
        });

        return $alertas;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private static function alerta(
        string $nivel,
        string $icono,
        string $titulo,
        string $contexto,
        string $accion,
        int    $dias
    ): array {
        return [
            'nivel'   => $nivel,
            'icono'   => $icono,
            'titulo'  => $titulo,
            'mensaje' => "{$contexto} {$accion}",
            'dias'    => $dias,
        ];
    }

    /**
     * Cuenta días hábiles (lunes a viernes) entre dos fechas, inclusive.
     */
    public static function diasHabiles(Carbon $desde, Carbon $hasta): int
    {
        if ($hasta->lt($desde)) return 0;

        $dias  = 0;
        $fecha = $desde->copy()->startOfDay();
        $fin   = $hasta->copy()->startOfDay();

        while ($fecha->lte($fin)) {
            if ($fecha->isWeekday()) {
                $dias++;
            }
            $fecha->addDay();
        }

        return max(0, $dias - 1);
    }
}
