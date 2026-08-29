<?php

namespace App\Support;

use App\Models\Cotizacion;
use App\Models\EmailSequence;
use App\Models\EmailSequenceEnrollment;
use App\Models\EmailSequenceStepSend;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Housekeeping de las secuencias de seguimiento de cotizaciones.
 *
 * DECISIÓN DE ARQUITECTURA — quién manda el reloj: n8n, no Laravel. Este
 * proceso NO está registrado en app/Console/Kernel.php ni tiene una
 * frecuencia propia; se ejecuta en línea dentro de
 * MarketingSequenceController::due(), es decir, cada vez que n8n pregunta
 * "¿qué hay listo para mandar?". Laravel nunca decide cuándo revisar ni
 * cuándo reintentar — solo responde con la verdad del momento en que le
 * preguntan. El comando app:process-email-sequences existe únicamente para
 * poder dispararlo a mano al probar.
 *
 * Por eso los cuatro pasos son ESTRICTAMENTE IDEMPOTENTES: se pueden
 * ejecutar mil veces seguidas, en cualquier orden de llamadas, y el
 * resultado es el mismo (las inscripciones se protegen con
 * whereDoesntHave + el unique de la tabla; los cambios de estado son
 * transiciones de un estado a otro, nunca acumulativas).
 */
class SequenceProcessor
{
    /**
     * Corre los cuatro pasos, en este orden (importa: sacar por compra antes
     * de vencer pasos evita marcar como "debido" un paso de alguien que ya
     * compró, y cerrar completadas al final ve el efecto de los tres
     * anteriores).
     *
     * @return array{enrolled: int, exited: int, promoted: int, completed: int}
     */
    public function process(): array
    {
        return [
            'enrolled' => $this->enrollNewQuotes(),
            'exited' => $this->exitPurchasers(),
            'promoted' => $this->promoteDueSteps(),
            'completed' => $this->closeFinishedEnrollments(),
        ];
    }

    /**
     * Paso 1 — inscribe en cada secuencia activa las cotizaciones que
     * todavía no tienen inscripción en ella.
     *
     * Elegibilidad: TODAS las cotizaciones, incluidas las de status
     * 'borrador' (decisión explícita del dueño del negocio). Lo único que se
     * exige es que la cotización tenga user_id — sin cliente no hay a quién
     * escribirle ni de dónde sacar los datos de {{contact.*}}.
     *
     * Al inscribir se materializa de una vez el calendario completo: una
     * fila en email_sequence_step_sends por cada paso, con due_at ya
     * calculado desde enrolled_at + wait_days (nunca encadenado desde el
     * paso anterior, ver la migración de email_sequence_steps).
     */
    private function enrollNewQuotes(): int
    {
        $sequences = EmailSequence::query()
            ->where('status', 1)
            ->with('steps')
            ->get();

        $enrolled = 0;

        foreach ($sequences as $sequence) {
            // Una secuencia sin pasos no inscribe nada — inscribir ahí
            // crearía inscripciones que nacen ya "completadas" y solo
            // ensucian el monitoreo.
            if ($sequence->steps->isEmpty()) {
                continue;
            }

            Cotizacion::query()
                ->whereNotNull('user_id')
                ->whereDoesntHave('sequenceEnrollments', function ($query) use ($sequence) {
                    $query->where('email_sequence_id', $sequence->id);
                })
                ->orderBy('id')
                ->chunkById(200, function ($cotizaciones) use ($sequence, &$enrolled) {
                    foreach ($cotizaciones as $cotizacion) {
                        // La transacción cubre inscripción + calendario:
                        // nunca puede quedar una inscripción sin sus pasos.
                        DB::transaction(function () use ($sequence, $cotizacion, &$enrolled) {
                            $enrolledAt = now();

                            $enrollment = EmailSequenceEnrollment::create([
                                'email_sequence_id' => $sequence->id,
                                'cotizacion_id' => $cotizacion->id,
                                'user_id' => $cotizacion->user_id,
                                'status' => 'active',
                                'enrolled_at' => $enrolledAt,
                            ]);

                            foreach ($sequence->steps as $step) {
                                EmailSequenceStepSend::create([
                                    'email_sequence_enrollment_id' => $enrollment->id,
                                    'email_sequence_step_id' => $step->id,
                                    'status' => 'pending',
                                    'due_at' => $enrolledAt->copy()->addDays((int) $step->wait_days),
                                ]);
                            }

                            $enrolled++;
                        });
                    }
                });
        }

        return $enrolled;
    }

    /**
     * Paso 2 — saca del seguimiento a quien ya compró.
     *
     * Consulta de SOLO LECTURA sobre `orders` (mismo criterio de "compra
     * válida" que ya usa el resto del sistema: payment_status = 1), por
     * user_id y con created_at posterior al enrolled_at. Deliberadamente NO
     * se engancha nada en App\Http\Controllers\Frontend\PaymentController:
     * ese archivo pertenece a otro frente de trabajo en curso y esta
     * detección no necesita tocarlo — preguntarle a la tabla cada vez que
     * n8n pasa da el mismo resultado sin acoplar los dos módulos.
     */
    private function exitPurchasers(): int
    {
        $exited = 0;

        EmailSequenceEnrollment::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->chunkById(200, function ($enrollments) use (&$exited) {
                foreach ($enrollments as $enrollment) {
                    $hasPurchase = Order::query()
                        ->where('user_id', $enrollment->user_id)
                        ->where('payment_status', 1)
                        ->when($enrollment->enrolled_at, fn ($q) => $q->where('created_at', '>', $enrollment->enrolled_at))
                        ->exists();

                    if (!$hasPurchase) {
                        continue;
                    }

                    DB::transaction(function () use ($enrollment, &$exited) {
                        $enrollment->update([
                            'status' => 'exited_purchase',
                            'exited_at' => now(),
                            'exit_reason' => 'El cliente realizó una compra después de inscribirse.',
                        ]);

                        // Solo los pasos que todavía no se mandaron: un
                        // 'sent' o un 'failed' ya son historial y no se
                        // reescriben nunca.
                        EmailSequenceStepSend::query()
                            ->where('email_sequence_enrollment_id', $enrollment->id)
                            ->whereIn('status', ['pending', 'due'])
                            ->update(['status' => 'skipped', 'updated_at' => now()]);

                        $exited++;
                    });
                }
            });

        return $exited;
    }

    /**
     * Paso 3 — pasa a 'due' los pasos cuyo due_at ya venció, de
     * inscripciones que sigan activas. Es lo que los vuelve visibles para
     * n8n en GET /api/marketing/sequences/due.
     */
    private function promoteDueSteps(): int
    {
        return EmailSequenceStepSend::query()
            ->where('status', 'pending')
            ->where('due_at', '<=', now())
            ->whereHas('enrollment', fn ($q) => $q->where('status', 'active'))
            ->update(['status' => 'due', 'updated_at' => now()]);
    }

    /**
     * Paso 4 — cierra las inscripciones activas a las que ya no les queda
     * ningún paso por mandar (todos en sent/failed/skipped).
     *
     * Un paso en 'failed' cuenta como terminado a efectos de cierre: n8n es
     * el dueño de la política de reintentos y, si decide reintentar, vuelve
     * a pedir el render y a reportar — no se deja la inscripción abierta
     * para siempre esperando algo que Laravel no controla.
     */
    private function closeFinishedEnrollments(): int
    {
        return EmailSequenceEnrollment::query()
            ->where('status', 'active')
            ->whereDoesntHave('stepSends', fn ($q) => $q->whereIn('status', ['pending', 'due']))
            ->update(['status' => 'completed', 'exited_at' => now(), 'updated_at' => now()]);
    }
}
