<?php

namespace App\Services;

use App\Models\Caso;
use Carbon\Carbon;

/**
 * AlertaTemporalService — v2 CORREGIDA
 *
 * CAMBIOS APLICADOS:
 *  1. BUG FIX: Solicitud a aseguradora ahora usa días CALENDARIO (no hábiles).
 *     Antes mostraba ~24 (días hábiles). Ahora mostrará ~31-32 días correctos.
 *
 *  2. BUG FIX: Cada estado del flujo muestra su propia alerta dinámica.
 *     Ya no se agrupan "Aseguradora negó" / "no respondió" con "Solicitud enviada".
 *     Cada uno muestra la acción concreta que corresponde.
 *
 *  3. NUEVA LÓGICA: Alertas visibles desde el día 1 para estados críticos
 *     (negó, no respondió, tutela presentada, fallo negado, etc.).
 *     El contador es en tiempo real y siempre visible en estos estados.
 *
 * PLAZOS:
 *  - Impugnación de fallo negado : 3 días hábiles  (CRÍTICO desde día 0)
 *  - Desacato sin cumplimiento   : 5 días hábiles  (CRÍTICO)
 *  - Tutela presentada           : 10 días hábiles (juez debe fallar)
 *  - Todos los demás procesos    : 30 días calendario (URGENTE → CRÍTICO)
 */
class AlertaTemporalService
{
    const NIVEL_CRITICO = 'critico';
    const NIVEL_URGENTE = 'urgente';
    const NIVEL_INFO    = 'info';

    // Días calendario considerados "1 mes"
    const MES_DIAS     = 30;

    // Días antes del vencimiento para empezar a alertar (aviso previo)
    const AVISO_PREVIO = 7;

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
        // BLOQUE A — FLUJO ASEGURADORA
        // ══════════════════════════════════════════════════════════════════════

        // A1. Solicitud enviada — en espera de respuesta
        //     CORRECCIÓN: usa diffInDays (calendario), NO diasHabiles.
        //     Mayo 25 → Junio 26 = 32 días calendario ✓ (antes daba ~24 hábiles)
        //     Muestra desde día 1 con nivel INFO, sube a URGENTE/CRITICO al acercarse al mes.
        if ($estado === 'Solicitud de calificación enviada'
            && $caso->fecha_solicitud_aseguradora) {

            $dias = (int) Carbon::parse($caso->fecha_solicitud_aseguradora)->diffInDays($hoy);

            $nivel = match(true) {
                $dias >= self::MES_DIAS               => self::NIVEL_CRITICO,
                $dias >= self::MES_DIAS - self::AVISO_PREVIO => self::NIVEL_URGENTE,
                default                               => self::NIVEL_INFO,
            };

            $alertas[] = self::alerta(
                $nivel,
                '⏰',
                'Sin respuesta de la aseguradora',
                "Han pasado {$dias} días desde la solicitud de calificación.",
                $dias >= self::MES_DIAS
                    ? 'El plazo de 30 días se cumplió. Procede a presentar tutela de inmediato.'
                    : ($dias >= self::MES_DIAS - self::AVISO_PREVIO
                        ? 'El plazo de 30 días está próximo a vencer. Prepara la tutela.'
                        : 'Esperando respuesta de la aseguradora (plazo: 30 días).'),
                $dias
            );
        }

        // A1b. Aseguradora NEGÓ la solicitud → presentar tutela para calificación
        //      NUEVO: estado propio con alerta CRÍTICO visible desde el día 1.
        if ($estado === 'Aseguradora negó - presentar tutela para calificación') {
            $fechaRef = $caso->fecha_respuesta_aseguradora
                        ?? $caso->fecha_solicitud_aseguradora
                        ?? null;
            if ($fechaRef) {
                $dias = (int) Carbon::parse($fechaRef)->diffInDays($hoy);
                $alertas[] = self::alerta(
                    self::NIVEL_CRITICO,
                    '🚫',
                    'Aseguradora NEGÓ — presentar tutela para calificación',
                    "La aseguradora negó la solicitud (hace {$dias} día(s)).",
                    'Presenta la tutela para calificación ante el juzgado competente.',
                    $dias
                );
            }
        }

        // A1c. Aseguradora NO RESPONDIÓ → presentar tutela para calificación
        //      NUEVO: estado propio con alerta CRÍTICO desde el día 1.
        if ($estado === 'Aseguradora no respondió - presentar tutela para calificación'
            && $caso->fecha_solicitud_aseguradora) {

            $dias = (int) Carbon::parse($caso->fecha_solicitud_aseguradora)->diffInDays($hoy);
            $alertas[] = self::alerta(
                self::NIVEL_CRITICO,
                '⚠️',
                'Aseguradora NO RESPONDIÓ — presentar tutela',
                "Han pasado {$dias} días desde la solicitud sin respuesta de la aseguradora.",
                'Presenta la tutela para calificación ante el juzgado competente.',
                $dias
            );
        }

        // A2. Dictamen recibido → manifestar inconformidad / apelar (30 días)
        if ($estado === 'Dictamen de aseguradora recibido'
            && $caso->fecha_solicitud_aseguradora) {

            $fechaRef = $caso->fecha_respuesta_aseguradora
                        ?? $caso->fecha_solicitud_aseguradora;
            $dias = (int) Carbon::parse($fechaRef)->diffInDays($hoy);

            $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
            $alertas[] = self::alerta(
                $nivel,
                '📝',
                'Dictamen recibido — manifestar inconformidad o apelar',
                "Han pasado {$dias} días desde el dictamen de la aseguradora.",
                $dias >= self::MES_DIAS
                    ? 'El plazo de 30 días venció. Presenta la apelación o inconformidad de inmediato.'
                    : 'Tienes 30 días para presentar inconformidad o apelación del dictamen.',
                $dias
            );
        }

        // A3. Apelación presentada sin resultado — 30 días
        if ($estado === 'Apelación de dictamen presentada'
            && $caso->fecha_apelacion) {

            $dias = (int) Carbon::parse($caso->fecha_apelacion)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '🏛️',
                    'Apelación sin respuesta del ente calificador',
                    "Han pasado {$dias} días desde la apelación del dictamen.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días venció. Haz seguimiento urgente.'
                        : 'El plazo esperado es de 30 días.',
                    $dias
                );
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE B — FLUJO TUTELA
        // ══════════════════════════════════════════════════════════════════════

        // B1. Tutela presentada — a la espera del fallo del juez (10 días hábiles)
        //     NUEVO: visible desde el día 1 (no sólo después de 10 días).
        if (in_array($estado, [
            'Tutela para calificación presentada',
            'Tutela por debido proceso presentada',
        ]) && $caso->fecha_tutela) {

            $diasHab = self::diasHabiles(Carbon::parse($caso->fecha_tutela), $hoy);
            $diasCal = (int) Carbon::parse($caso->fecha_tutela)->diffInDays($hoy);

            $nivel = match(true) {
                $diasHab >= 20 => self::NIVEL_CRITICO,
                $diasHab >= 10 => self::NIVEL_URGENTE,
                default        => self::NIVEL_INFO,
            };

            $alertas[] = self::alerta(
                $nivel,
                '📋',
                'Tutela presentada — a la espera del fallo',
                "Han pasado {$diasHab} días hábiles ({$diasCal} calendario) desde la presentación.",
                $diasHab >= 10
                    ? '⚠️ El juez debía fallar en 10 días hábiles. Haz seguimiento urgente con el juzgado.'
                    : 'El juez tiene 10 días hábiles para fallar. Haz seguimiento periódico.',
                $diasHab
            );
        }

        // B2. Fallo negado — IMPUGNAR en 3 días hábiles (MÁS CRÍTICO — visible desde día 0)
        if ($estado === 'Fallo tutela negado - pendiente impugnación'
            && $caso->fecha_fallo_tutela) {

            $dias      = self::diasHabiles(Carbon::parse($caso->fecha_fallo_tutela), $hoy);
            $restantes = max(0, 3 - $dias);
            $alertas[] = self::alerta(
                self::NIVEL_CRITICO,
                '🚨',
                '¡IMPUGNAR AHORA — 3 días hábiles!',
                "El fallo fue NEGADO hace {$dias} día(s) hábil(es).",
                $dias >= 3
                    ? '⚠️ El plazo de 3 días hábiles para impugnar puede estar VENCIDO. Actúa de inmediato.'
                    : "Quedan aprox. {$restantes} día(s) hábil(es) para impugnar.",
                $dias
            );
        }

        // B3. Fallo concedido — aseguradora debe cumplir en 30 días
        if ($estado === 'Fallo tutela concedido - esperando cumplimiento'
            && $caso->fecha_fallo_tutela) {

            $dias = (int) Carbon::parse($caso->fecha_fallo_tutela)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '⚖️',
                    'Aseguradora debe cumplir fallo concedido',
                    "Han pasado {$dias} días desde el fallo CONCEDIDO sin cumplimiento.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días venció. Presenta incidente de desacato de inmediato.'
                        : 'Plazo de 30 días para cumplir. Prepara el incidente de desacato.',
                    $dias
                );
            }
        }

        // B4. Fallo de tutela registrado (estado intermedio) — seguimiento
        if ($estado === 'Fallo de tutela registrado' && $caso->fecha_fallo_tutela) {
            $dias = (int) Carbon::parse($caso->fecha_fallo_tutela)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
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

        // C1. Desacato presentado sin cumplimiento — 5 días hábiles (~1 semana)
        if ($estado === 'Incidente de desacato presentado'
            && $caso->fecha_incidente_desacato) {

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

        // D1. Impugnación presentada sin fallo de segunda instancia — 30 días
        if ($estado === 'Impugnación presentada' && $caso->fecha_impugnacion) {
            $dias = (int) Carbon::parse($caso->fecha_impugnacion)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '🏛️',
                    'Impugnación sin fallo de segunda instancia',
                    "Han pasado {$dias} días desde la impugnación sin fallo.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo esperado de 30 días venció. Haz seguimiento con el tribunal.'
                        : 'Haz seguimiento periódico con el tribunal.',
                    $dias
                );
            }
        }

        // D2. Fallo segunda instancia registrado — seguimiento
        if ($estado === 'Fallo de segunda instancia registrado'
            && $caso->fecha_fallo_segunda_instancia) {

            $dias = (int) Carbon::parse($caso->fecha_fallo_segunda_instancia)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
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

        // D3. Segunda instancia revoca — aseguradora debe calificar (30 días)
        if ($estado === 'Segunda instancia revoca - aseguradora debe calificar'
            && $caso->fecha_fallo_segunda_instancia) {

            $dias = (int) Carbon::parse($caso->fecha_fallo_segunda_instancia)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '⚖️',
                    'Segunda instancia revoca — aseguradora debe calificar',
                    "Han pasado {$dias} días desde que la segunda instancia revocó el fallo.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días venció. Exige a la aseguradora que emita calificación.'
                        : 'Plazo de 30 días para que la aseguradora califique.',
                    $dias
                );
            }
        }

        // D4. Segunda instancia revoca — aseguradora debe pagar honorarios (30 días)
        if ($estado === 'Segunda instancia revoca - aseguradora debe pagar honorarios'
            && $caso->fecha_fallo_segunda_instancia) {

            $dias = (int) Carbon::parse($caso->fecha_fallo_segunda_instancia)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '💰',
                    'Segunda instancia revoca — pendiente pago honorarios',
                    "Han pasado {$dias} días desde que la segunda instancia revocó el fallo.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días venció. Haz seguimiento urgente del pago de honorarios.'
                        : 'Plazo de 30 días para recibir pago de honorarios.',
                    $dias
                );
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE E — CUMPLIMIENTO TUTELA / ORTOPEDIA
        // ══════════════════════════════════════════════════════════════════════

        // E1. Tutela cumplida — pendiente dictamen aseguradora (30 días)
        if ($estado === 'Tutela cumplida - pendiente dictamen aseguradora'
            && $caso->fecha_cumplimiento_tutela) {

            $dias = (int) Carbon::parse($caso->fecha_cumplimiento_tutela)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '🏥',
                    'Cumplimiento tutela — pendiente dictamen aseguradora',
                    "Han pasado {$dias} días desde el cumplimiento de la tutela sin dictamen.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días venció. Exige a la aseguradora que emita el dictamen.'
                        : 'El plazo esperado es de 30 días para el dictamen.',
                    $dias
                );
            }
        }

        // E2. Tutela cumplida — pendiente pago honorarios (30 días)
        if ($estado === 'Tutela cumplida - pendiente pago honorarios'
            && $caso->fecha_cumplimiento_tutela) {

            $dias = (int) Carbon::parse($caso->fecha_cumplimiento_tutela)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '💰',
                    'Cumplimiento tutela — pendiente pago honorarios a junta',
                    "Han pasado {$dias} días desde el cumplimiento de la tutela sin pago de honorarios.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días venció. Gestiona el pago de honorarios a la junta.'
                        : 'El plazo esperado para el pago de honorarios es de 30 días.',
                    $dias
                );
            }
        }

        // E3. Pendiente alta por ortopedia — 30 días de referencia
        if ($estado === 'Pendiente alta por ortopedia') {
            $fechaRef = $caso->updated_at ?? $caso->created_at ?? null;
            if ($fechaRef) {
                $dias = (int) Carbon::parse($fechaRef)->diffInDays($hoy);
                if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
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

        // F1. Listo para solicitud a junta — sin envío en 30 días
        if ($estado === 'Listo para solicitud a junta') {
            $fechaRef = $caso->fecha_pago_honorarios ?? $caso->updated_at ?? null;
            if ($fechaRef) {
                $dias = (int) Carbon::parse($fechaRef)->diffInDays($hoy);
                if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
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

        // F2. Solicitud enviada a junta — sin dictamen en 30 días
        if ($estado === 'Solicitud enviada a junta' && $caso->fecha_envio_junta) {
            $dias = (int) Carbon::parse($caso->fecha_envio_junta)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '🏥',
                    'Junta médica sin emitir dictamen',
                    "Han pasado {$dias} días desde el envío de la solicitud a la junta sin dictamen.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días venció. Contacta la junta para exigir el dictamen.'
                        : 'El plazo esperado es de 30 días para el dictamen.',
                    $dias
                );
            }
        }

        // F3. Dictamen de junta recibido — sin cobro en 30 días
        if ($estado === 'Dictamen de junta recibido' && $caso->fecha_dictamen_junta) {
            $dias = (int) Carbon::parse($caso->fecha_dictamen_junta)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_URGENTE : self::NIVEL_INFO;
                $alertas[] = self::alerta(
                    $nivel,
                    '💼',
                    'Dictamen recibido — enviar cobro a aseguradora',
                    "Han pasado {$dias} días desde el dictamen de la junta sin enviar el cobro.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días pasó. Prepara y envía el cobro a la aseguradora.'
                        : 'Prepara el cobro para enviarlo a la aseguradora.',
                    $dias
                );
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // BLOQUE G — COBRO Y PAGO FINAL
        // ══════════════════════════════════════════════════════════════════════

        // G1. Listo para cobro a aseguradora — sin envío en 30 días
        if ($estado === 'Listo para cobro a aseguradora') {
            $fechaRef = $caso->fecha_dictamen_junta ?? $caso->updated_at ?? null;
            if ($fechaRef) {
                $dias = (int) Carbon::parse($fechaRef)->diffInDays($hoy);
                if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
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

        // G2. Cobro enviado sin pago — 30 días
        if ($estado === 'Cobro a aseguradora enviado' && $caso->fecha_reclamacion_final) {
            $dias = (int) Carbon::parse($caso->fecha_reclamacion_final)->diffInDays($hoy);
            if ($dias >= self::MES_DIAS - self::AVISO_PREVIO) {
                $nivel = $dias >= self::MES_DIAS ? self::NIVEL_CRITICO : self::NIVEL_URGENTE;
                $alertas[] = self::alerta(
                    $nivel,
                    '💳',
                    'Cobro enviado — sin pago de la aseguradora',
                    "Han pasado {$dias} días desde el envío del cobro sin que la aseguradora pague.",
                    $dias >= self::MES_DIAS
                        ? 'El plazo de 30 días venció. Considera presentar queja ante la Superintendencia Financiera.'
                        : 'El plazo esperado es de 30 días para el pago.',
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
     * Cuenta días hábiles (lunes a viernes) entre dos fechas.
     * Usado sólo donde la ley mide en días hábiles (tutela, impugnación, desacato).
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
