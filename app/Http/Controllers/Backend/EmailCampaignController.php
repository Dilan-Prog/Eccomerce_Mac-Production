<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailContactList;
use App\Models\EmailTemplate;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\EmailCampaignRecipientTableQuery;
use App\Support\AdminTable\Queries\EmailCampaignTableQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Campañas de correo: una plantilla enviada a una lista de contactos
 * (pestaña "Campañas" de EmailMarketingController).
 *
 * El envío real lo hace n8n contra las rutas
 * /api/marketing/campaigns/* (ver App\Http\Controllers\Api\MarketingCampaignController).
 * Este controlador solo prepara el trabajo y lo deja listo para que n8n lo
 * recoja cuando pase: en ningún momento Laravel decide cuándo se manda nada.
 */
class EmailCampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:marketing-integracion,view')->only(['tableData', 'show', 'recipientsTableData']);
        $this->middleware('can-access-module:marketing-integracion,create')->only(['createFragment', 'store']);
        $this->middleware('can-access-module:marketing-integracion,edit')->only(['editFragment', 'update', 'schedule', 'cancel']);
        $this->middleware('can-access-module:marketing-integracion,delete')->only(['destroy']);
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, EmailCampaignTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.email-campaigns._form', $this->formOptions());
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $emailCampaign = EmailCampaign::findOrFail($id);

        return view('admin-ui.email-campaigns._form', $this->formOptions() + compact('emailCampaign'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        EmailCampaign::create($data + [
            'status' => 'borrador',
            'created_by_admin_id' => Auth::id(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Campaña creada como borrador.']);
    }

    /**
     * Solo se puede editar mientras siga en borrador: a partir de
     * "programada" ya existe el snapshot de destinatarios y cambiarle la
     * plantilla o la lista dejaría el envío en curso inconsistente.
     */
    public function update(Request $request, string $id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        if ($campaign->status !== 'borrador') {
            return response()->json([
                'status' => 'error',
                'message' => 'Esta campaña ya no es un borrador, así que no se puede editar.',
            ]);
        }

        $campaign->update($this->validateData($request));

        return response()->json(['status' => 'success', 'message' => 'Campaña actualizada con éxito.']);
    }

    /**
     * Toma el snapshot de destinatarios y deja la campaña visible para n8n.
     *
     * El snapshot es una COPIA congelada de la lista en este instante (ver la
     * migración de email_campaign_recipients): editar la lista después no
     * cambia a quién se le va a enviar, y el historial de un envío ya hecho
     * queda intacto. Solo entran los contactos suscritos — un dado de baja
     * nunca llega al snapshot.
     */
    public function schedule(Request $request, string $id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        if ($campaign->status !== 'borrador') {
            return response()->json([
                'status' => 'error',
                'message' => 'Solo se puede programar una campaña que todavía sea borrador.',
            ]);
        }

        $members = EmailContactList::findOrFail($campaign->email_contact_list_id)
            ->members()
            ->whereNull('unsubscribed_at')
            ->get();

        if ($members->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'La lista de esta campaña no tiene contactos suscritos, así que no hay a quién enviarle.',
            ]);
        }

        DB::transaction(function () use ($campaign, $members) {
            $now = now();
            $rows = $members->map(fn ($member) => [
                'email_campaign_id' => $campaign->id,
                'email' => $member->email,
                'name' => $member->name,
                'company' => $member->company,
                'contact_source' => $member->source,
                'user_id' => $member->user_id,
                'aspel_client_id' => $member->aspel_client_id,
                'status' => 'pending',
                'sent_at' => null,
                'error_message' => null,
                'attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                EmailCampaignRecipient::insert($chunk);
            }

            $campaign->update([
                'status' => 'programada',
                'total_recipients' => count($rows),
                'sent_count' => 0,
                'failed_count' => 0,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Campaña programada con ' . $members->count() . ' destinatario(s). n8n la recogerá la próxima vez que pregunte por campañas pendientes.',
        ]);
    }

    /**
     * Cancela una campaña programada — solo mientras n8n NO la haya
     * reclamado. Después de claimed_at ya puede haber correos saliendo y
     * "cancelar" daría la falsa sensación de haber detenido algo que Laravel
     * no controla.
     *
     * El snapshot de destinatarios se conserva: es el registro de a quién se
     * le iba a enviar, y borrarlo sería perder esa información.
     */
    public function cancel(string $id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        if ($campaign->status !== 'programada' || $campaign->claimed_at !== null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Esta campaña ya no se puede cancelar: solo se cancelan campañas programadas que n8n todavía no haya tomado.',
            ]);
        }

        $campaign->update(['status' => 'cancelada', 'completed_at' => now()]);

        return response()->json(['status' => 'success', 'message' => 'Campaña cancelada.']);
    }

    /**
     * Solo se borran borradores y canceladas — una campaña enviada (o a
     * medio enviar) es historial de correos que ya salieron.
     */
    public function destroy(string $id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        if (!in_array($campaign->status, ['borrador', 'cancelada'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Solo se pueden borrar campañas en borrador o canceladas — las enviadas son historial.',
            ]);
        }

        $campaign->delete();

        return response()->json(['status' => 'success', 'message' => 'Campaña eliminada con éxito.']);
    }

    /** Monitor del envío: cabecera con contadores + tabla de destinatarios. */
    public function show(string $id)
    {
        $emailCampaign = EmailCampaign::with(['template:id,name,subject', 'list:id,name'])->findOrFail($id);

        return view('admin-ui.email-campaigns.show', compact('emailCampaign'));
    }

    /** JSON data source de la tabla de destinatarios, acotada a esta campaña. */
    public function recipientsTableData(Request $request, string $id, EmailCampaignRecipientTableQuery $table)
    {
        $campaign = EmailCampaign::findOrFail($id);

        return response()->json($table->forCampaign((int) $campaign->id)->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            // Se ofrecen TODAS las plantillas activas, no solo las marcadas
            // como type = 'campaign': el tipo es una etiqueta de
            // organización, no una restricción (ver la migración
            // add_type_to_email_templates_table).
            'templates' => EmailTemplate::where('status', 1)->orderBy('name')->get(['id', 'name', 'type']),
            'lists' => EmailContactList::where('status', 1)->orderBy('name')->get(['id', 'name']),
        ];
    }

    /** @return array<string, mixed> */
    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'max:200'],
            'email_template_id' => ['required', 'integer', 'exists:email_templates,id'],
            'email_contact_list_id' => ['required', 'integer', 'exists:email_contact_lists,id'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        // scheduled_at vacío (o ausente del formulario) = "en cuanto n8n
        // pase". No es un cron nuestro: la campaña simplemente aparece como
        // pendiente en GET /api/marketing/campaigns/due desde ya.
        $validated['scheduled_at'] = ($validated['scheduled_at'] ?? null) ?: null;

        return $validated;
    }
}
