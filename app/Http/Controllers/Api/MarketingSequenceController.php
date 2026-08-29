<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailSequenceStepSend;
use App\Support\BlockEmailRenderer;
use App\Support\EmailTemplateRenderer;
use App\Support\SequenceProcessor;
use Illuminate\Http\Request;

/**
 * Pasos de secuencia listos para enviarse, para el flujo de n8n. Mismo grupo
 * `marketing.token` que MarketingDataController y MarketingCampaignController.
 *
 * AQUÍ ESTÁ EL RELOJ DEL MÓDULO, y es de n8n: due() corre primero el
 * housekeeping completo (App\Support\SequenceProcessor) y después responde.
 * Es decir, "inscribir cotizaciones nuevas / sacar a los que ya compraron /
 * vencer pasos / cerrar inscripciones terminadas" ocurre exactamente cuando
 * n8n pregunta, ni antes ni después. Laravel no tiene cron propio para esto
 * (app/Console/Kernel.php no se tocó) ni política de reintentos: si un envío
 * falla, n8n decide si vuelve a pedir el render y a reportar.
 *
 * Flujo típico de un ciclo de n8n:
 *   GET  /api/marketing/sequences/due
 *   POST /api/marketing/sequences/due/{id}/claim     -> 409 si otro ya lo tomó
 *   GET  /api/marketing/sequences/due/{id}/render
 *   POST /api/marketing/sequences/due/{id}/report
 *
 * En todas ellas {id} es el id de una fila de email_sequence_step_sends: la
 * unidad de trabajo es "este paso, para esta cotización".
 */
class MarketingSequenceController extends Controller
{
    /**
     * GET /api/marketing/sequences/due
     *
     * Corre el housekeeping y devuelve los pasos vencidos de inscripciones
     * activas.
     *
     * Incluye los que ya tienen claimed_at (y lo expone en la respuesta): si
     * un ciclo de n8n se cortó entre el claim y el report, el paso sigue
     * siendo visible en vez de desaparecer para siempre. report() no exige
     * haber reclamado, así que n8n siempre puede cerrar esos casos.
     */
    public function due(SequenceProcessor $processor)
    {
        $housekeeping = $processor->process();

        $sends = EmailSequenceStepSend::query()
            ->where('status', 'due')
            ->whereHas('enrollment', fn ($q) => $q->where('status', 'active'))
            ->with([
                'step:id,email_sequence_id,email_template_id,step_order,wait_days,name',
                'step.sequence:id,name',
                'step.template:id,name',
                'enrollment:id,email_sequence_id,cotizacion_id,user_id,enrolled_at',
                'enrollment.user:id,name,last_name,email,company',
                'enrollment.cotizacion:id,folio,total,currency',
            ])
            ->orderBy('due_at')
            ->orderBy('id')
            ->get()
            ->map(fn (EmailSequenceStepSend $send) => [
                'id' => $send->id,
                'due_at' => optional($send->due_at)->toDateTimeString(),
                'claimed_at' => optional($send->claimed_at)->toDateTimeString(),
                'attempts' => (int) $send->attempts,
                'sequence' => [
                    'id' => $send->step->email_sequence_id ?? null,
                    'name' => $send->step->sequence->name ?? null,
                ],
                'step' => [
                    'id' => $send->email_sequence_step_id,
                    'order' => $send->step->step_order ?? null,
                    'name' => $send->step->name,
                    'wait_days' => (int) ($send->step->wait_days ?? 0),
                    'template' => [
                        'id' => $send->step->email_template_id ?? null,
                        'name' => $send->step->template->name ?? null,
                    ],
                ],
                'enrollment' => [
                    'id' => $send->email_sequence_enrollment_id,
                    'enrolled_at' => optional($send->enrollment->enrolled_at)->toDateTimeString(),
                ],
                'quote' => [
                    'id' => $send->enrollment->cotizacion_id ?? null,
                    'folio' => $send->enrollment->cotizacion->folio ?? null,
                ],
                'recipient' => [
                    'user_id' => $send->enrollment->user_id ?? null,
                    'email' => $send->enrollment->user->email ?? null,
                    'name' => trim(($send->enrollment->user->name ?? '') . ' ' . ($send->enrollment->user->last_name ?? '')),
                ],
            ])
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $sends,
            // Se devuelve el resultado del housekeeping para que se pueda ver
            // desde n8n qué hizo esta llamada (cuántas inscripciones nuevas,
            // cuántas salidas por compra, etc.) sin tener que mirar la base.
            'housekeeping' => $housekeeping,
        ]);
    }

    /**
     * POST /api/marketing/sequences/due/{id}/claim
     *
     * Candado de concurrencia, mismo patrón que las campañas: las
     * condiciones van dentro del WHERE del UPDATE, así que la base de datos
     * elige al ganador de forma atómica y el segundo llamador recibe 409.
     */
    public function claim(string $id)
    {
        $send = EmailSequenceStepSend::findOrFail($id);

        $affected = EmailSequenceStepSend::query()
            ->where('id', $send->id)
            ->where('status', 'due')
            ->whereNull('claimed_at')
            ->update(['claimed_at' => now(), 'updated_at' => now()]);

        if ($affected === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Este paso ya fue reclamado por otra ejecución, o ya no está pendiente de envío.',
            ], 409);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Paso reclamado.',
            'data' => ['id' => $send->id, 'claimed_at' => now()->toDateTimeString()],
        ]);
    }

    /**
     * GET /api/marketing/sequences/due/{id}/render
     *
     * HTML y asunto de ese paso para esa cotización. A diferencia de las
     * campañas, aquí SÍ hay objetos reales detrás: el contacto es el
     * App\Models\User dueño de la cotización y la cotización es un
     * App\Models\Cotizacion — así que los marcadores {{contact.*}} y
     * {{quote.*}} se resuelven todos, sin ningún cambio en el renderer.
     */
    public function render(string $id)
    {
        $send = EmailSequenceStepSend::with([
            'step.template',
            'enrollment.user',
            'enrollment.cotizacion',
        ])->findOrFail($id);

        $template = $send->step->template ?? null;
        $user = $send->enrollment->user ?? null;

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'El paso no tiene una plantilla válida asociada.',
            ], 422);
        }

        if (!$user || empty($user->email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La cotización de este paso no tiene un cliente con correo al cual enviarle.',
            ], 422);
        }

        $placeholderData = [
            'nombre_cliente' => trim(($user->name ?? '') . ' ' . ($user->last_name ?? '')),
            'contact' => $user,
            'quote' => $send->enrollment->cotizacion,
        ];

        // Misma mecánica que MarketingDataController::email(): con editor
        // visual, el body efectivo se arma desde los bloques.
        $effectiveTemplate = $template;
        if (!empty($template->blocks_json['blocks'] ?? [])) {
            $effectiveTemplate = $template->replicate();
            $effectiveTemplate->body = app(BlockEmailRenderer::class)->render($template->blocks_json, $placeholderData);
        }

        $rendered = app(EmailTemplateRenderer::class)->render($effectiveTemplate, $placeholderData);

        return response()->json([
            'html' => $rendered['html'],
            'subject' => $rendered['subject'],
            'recipient_email' => $user->email,
            'step_send_id' => $send->id,
            'quote_folio' => $send->enrollment->cotizacion->folio ?? null,
        ]);
    }

    /**
     * POST /api/marketing/sequences/due/{id}/report
     * Body: { "status": "sent"|"failed", "error_message": "..." }
     *
     * Anota el resultado. NO exige haber reclamado el paso: si un ciclo
     * previo de n8n se cortó después del claim, esto es lo que permite
     * cerrarlo.
     *
     * Cerrar la inscripción cuando ya no le quedan pasos NO se hace aquí:
     * de eso se encarga SequenceProcessor la próxima vez que n8n llame a
     * due(), que es el único lugar donde vive el housekeeping.
     */
    public function report(Request $request, string $id)
    {
        $send = EmailSequenceStepSend::findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'in:sent,failed'],
            'error_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $send->update([
            'status' => $data['status'],
            'sent_at' => $data['status'] === 'sent' ? now() : $send->sent_at,
            'error_message' => $data['status'] === 'failed' ? ($data['error_message'] ?? null) : null,
            'attempts' => $send->attempts + 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Resultado registrado.',
            'data' => [
                'id' => $send->id,
                'step_status' => $send->status,
                'attempts' => (int) $send->attempts,
            ],
        ]);
    }
}
