<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Support\BlockEmailRenderer;
use App\Support\EmailTemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Envío de campañas para el flujo de n8n. Vive en el mismo grupo
 * `marketing.token` que MarketingDataController — mismo universo de tokens,
 * aislado del de Aspel.
 *
 * REPARTO DE RESPONSABILIDADES (decisión de arquitectura del módulo):
 * n8n es el dueño del reloj y de la política de reintentos. Laravel no
 * tiene ningún cron ni cola propia para esto; solo responde tres preguntas
 * — qué hay pendiente, dame el HTML de este destinatario, y anota este
 * resultado. Si un envío falla, Laravel NO reintenta ni bloquea al llegar a
 * un tope: si n8n decide reintentar, vuelve a pedir el render y a reportar.
 *
 * Flujo típico de un ciclo de n8n:
 *   GET  /api/marketing/campaigns/due
 *   POST /api/marketing/campaigns/{id}/claim              -> 409 si otro ya la tomó
 *   GET  /api/marketing/campaigns/{id}/recipients?status=pending
 *   GET  /api/marketing/campaigns/{id}/recipients/{rid}/render
 *   POST /api/marketing/campaigns/{id}/recipients/{rid}/report
 */
class MarketingCampaignController extends Controller
{
    /**
     * GET /api/marketing/campaigns/due
     *
     * Campañas listas para empezar a enviarse: programadas, todavía sin
     * reclamar, y cuya fecha programada ya pasó (o que no tienen fecha, lo
     * que significa "en cuanto n8n pase").
     *
     * También devuelve las que ya están en 'enviando' y siguen teniendo
     * destinatarios pendientes: así, si un ciclo de n8n se corta a la mitad,
     * el siguiente la vuelve a ver y puede terminar el trabajo.
     */
    public function due()
    {
        $campaigns = EmailCampaign::query()
            ->with(['template:id,name,subject', 'list:id,name'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'programada')
                        ->whereNull('claimed_at')
                        ->where(function ($inner) {
                            $inner->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
                        });
                })->orWhere(function ($q) {
                    $q->where('status', 'enviando')
                        ->whereHas('recipients', fn ($r) => $r->where('status', 'pending'));
                });
            })
            ->orderBy('id')
            ->get()
            ->map(fn (EmailCampaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
                'template' => [
                    'id' => $campaign->email_template_id,
                    'name' => $campaign->template->name ?? null,
                ],
                'list' => [
                    'id' => $campaign->email_contact_list_id,
                    'name' => $campaign->list->name ?? null,
                ],
                'scheduled_at' => optional($campaign->scheduled_at)->toDateTimeString(),
                'claimed_at' => optional($campaign->claimed_at)->toDateTimeString(),
                'total_recipients' => (int) $campaign->total_recipients,
                'sent_count' => (int) $campaign->sent_count,
                'failed_count' => (int) $campaign->failed_count,
                'pending_count' => $campaign->recipients()->where('status', 'pending')->count(),
            ])
            ->values();

        return response()->json(['status' => 'success', 'data' => $campaigns]);
    }

    /**
     * POST /api/marketing/campaigns/{id}/claim
     *
     * Candado de concurrencia. El UPDATE lleva las condiciones dentro del
     * WHERE, así que la base de datos decide el ganador de forma atómica:
     * si dos ejecuciones de n8n corren a la vez, solo una afecta filas y la
     * otra recibe 409. Una campaña se reclama UNA sola vez — el segundo
     * intento siempre da 409.
     *
     * Si un ciclo de n8n se corta a la mitad, no hace falta volver a
     * reclamar para terminar el trabajo: recipients(), render() y report()
     * no exigen el claim. La campaña sigue apareciendo en due() mientras le
     * queden destinatarios pendientes, con su claimed_at a la vista.
     */
    public function claim(string $id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        $affected = EmailCampaign::query()
            ->where('id', $campaign->id)
            ->where('status', 'programada')
            ->whereNull('claimed_at')
            ->update([
                'status' => 'enviando',
                'claimed_at' => now(),
                'started_at' => now(),
                'updated_at' => now(),
            ]);

        if ($affected === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Esta campaña ya fue reclamada por otra ejecución, o ya no está programada.',
            ], 409);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Campaña reclamada.',
            'data' => ['id' => $campaign->id, 'claimed_at' => now()->toDateTimeString()],
        ]);
    }

    /**
     * GET /api/marketing/campaigns/{id}/recipients[?status=pending][&page=N]
     *
     * Destinatarios del snapshot, paginados (100). Sin `status` devuelve
     * todos; el uso normal de n8n es ?status=pending.
     */
    public function recipients(Request $request, string $id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        $query = $campaign->recipients()->orderBy('id');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $recipients = $query->paginate(100);

        $data = $recipients->getCollection()->map(fn (EmailCampaignRecipient $recipient) => [
            'id' => $recipient->id,
            'email' => $recipient->email,
            'name' => $recipient->name,
            'company' => $recipient->company,
            'status' => $recipient->status,
            'attempts' => (int) $recipient->attempts,
            'sent_at' => optional($recipient->sent_at)->toDateTimeString(),
        ])->values();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'current_page' => $recipients->currentPage(),
                'per_page' => $recipients->perPage(),
                'total' => $recipients->total(),
                'last_page' => $recipients->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/marketing/campaigns/{id}/recipients/{recipientId}/render
     *
     * HTML y asunto ya resueltos para ESE destinatario. n8n lo toma tal cual
     * y lo pasa a la API transaccional de correo.
     *
     * El contacto se arma como arreglo plano {name, email, company} a partir
     * del snapshot, no como App\Models\User: un destinatario puede ser un
     * contacto manual o de Aspel sin cuenta en el sitio. EmailTemplateRenderer
     * acepta las dos formas.
     *
     * Los marcadores de otros namespaces ({{quote.*}}, {{cart.*}}, ...) no
     * aplican a una campaña masiva y, por la regla de siempre del renderer,
     * se quedan tal cual sin romper nada.
     */
    public function render(string $id, string $recipientId)
    {
        $campaign = EmailCampaign::with('template')->findOrFail($id);
        $recipient = $campaign->recipients()->findOrFail($recipientId);

        if (!$campaign->template) {
            return response()->json([
                'status' => 'error',
                'message' => 'La campaña no tiene una plantilla válida asociada.',
            ], 422);
        }

        $placeholderData = [
            'nombre_cliente' => $recipient->name ?: '',
            'contact' => [
                'name' => $recipient->name ?: '',
                'email' => $recipient->email,
                'company' => $recipient->company ?: '',
            ],
        ];

        // Misma mecánica que MarketingDataController::email(): si la
        // plantilla se armó con el editor visual, el body efectivo es el que
        // genera BlockEmailRenderer a partir de los bloques, no la copia
        // guardada en la columna `body`.
        $template = $campaign->template;
        if (!empty($template->blocks_json['blocks'] ?? [])) {
            $template = $template->replicate();
            $template->body = app(BlockEmailRenderer::class)->render($campaign->template->blocks_json, $placeholderData);
        }

        $rendered = app(EmailTemplateRenderer::class)->render($template, $placeholderData);

        return response()->json([
            'html' => $rendered['html'],
            'subject' => $rendered['subject'],
            'recipient_email' => $recipient->email,
            'recipient_id' => $recipient->id,
            'campaign_id' => $campaign->id,
        ]);
    }

    /**
     * POST /api/marketing/campaigns/{id}/recipients/{recipientId}/report
     * Body: { "status": "sent"|"failed", "error_message": "..." }
     *
     * Anota el resultado de UN envío. Idempotente en el sentido que importa:
     * los contadores de la campaña se recalculan desde la tabla de
     * destinatarios (no se incrementan a mano), así que reportar dos veces
     * el mismo destinatario nunca desalinea los totales.
     *
     * Al terminar, si ya no quedan pendientes, la campaña se cierra sola en
     * 'enviada' — Laravel no espera ningún reintento, porque la política de
     * reintentos es de n8n.
     */
    public function report(Request $request, string $id, string $recipientId)
    {
        $campaign = EmailCampaign::findOrFail($id);
        $recipient = $campaign->recipients()->findOrFail($recipientId);

        $data = $request->validate([
            'status' => ['required', 'in:sent,failed'],
            'error_message' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($campaign, $recipient, $data) {
            $recipient->update([
                'status' => $data['status'],
                'sent_at' => $data['status'] === 'sent' ? now() : $recipient->sent_at,
                'error_message' => $data['status'] === 'failed' ? ($data['error_message'] ?? null) : null,
                'attempts' => $recipient->attempts + 1,
            ]);

            $counts = $campaign->recipients()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $pending = (int) ($counts['pending'] ?? 0);

            $campaign->update([
                'sent_count' => (int) ($counts['sent'] ?? 0),
                'failed_count' => (int) ($counts['failed'] ?? 0),
                'status' => $pending === 0 ? 'enviada' : $campaign->status,
                'completed_at' => $pending === 0 ? now() : null,
            ]);
        });

        $campaign->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Resultado registrado.',
            'data' => [
                'campaign_status' => $campaign->status,
                'sent_count' => (int) $campaign->sent_count,
                'failed_count' => (int) $campaign->failed_count,
                'total_recipients' => (int) $campaign->total_recipients,
            ],
        ]);
    }
}
