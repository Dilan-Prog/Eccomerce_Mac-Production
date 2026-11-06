<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\WebLead;
use App\Models\WebLeadSetting;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\WebLeadTableQuery;
use App\Support\WebLeads\WebLeadNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Panel de "Solicitudes web": lista y gestiona lo que los visitantes envian desde los formularios
 * publicos (asociados, calibracion y puesta en marcha, medicion, cotizacion publica). Los datos vienen
 * de visitantes anonimos: en las vistas SIEMPRE se escapan (Blade {{ }}) y en JS se usa textContent.
 *
 * El alta de solicitudes NO esta aqui: la hace App\Http\Controllers\Frontend\WebLeadController (publico).
 * Todo este controlador exige el modulo 'site' ("Administrar Sitio Web"); el grupo de rutas ya esta bajo
 * web + auth + role:admin.
 */
class WebLeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:site');
    }

    public function index()
    {
        return view('admin-ui.web-leads.index', [
            'formTypes' => WebLead::FORM_TYPES,
            'notifyTo' => WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO) ?? '',
            'recipients' => WebLeadNotifier::recipients(),
            'recipientsSource' => WebLeadNotifier::recipientsSource(),
        ]);
    }

    /**
     * Guarda los correos que reciben el aviso de cada solicitud (tiene prioridad sobre WEB_LEADS_NOTIFY_TO del .env).
     * Vacio = vuelve a usar el .env.
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'notify_to' => ['nullable', 'string', 'max:1000'],
        ], [
            'notify_to.max' => 'La lista de correos es demasiado larga.',
        ]);

        [$valid, $invalid] = WebLeadNotifier::parseEmails((string) ($data['notify_to'] ?? ''));

        if ($invalid !== []) {
            return response()->json([
                'status' => 'error',
                'message' => 'Estos correos no son válidos: ' . implode(', ', array_slice($invalid, 0, 5)) . '.',
            ], 422);
        }
        if (count($valid) > WebLeadNotifier::MAX_RECIPIENTS) {
            return response()->json([
                'status' => 'error',
                'message' => 'Máximo ' . WebLeadNotifier::MAX_RECIPIENTS . ' correos.',
            ], 422);
        }

        try {
            WebLeadSetting::put(WebLeadSetting::KEY_NOTIFY_TO, $valid === [] ? null : implode(', ', $valid));
        } catch (Throwable $e) {
            Log::warning('WebLead: no se pudo guardar el ajuste de correos', ['exception' => get_class($e)]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $source = WebLeadNotifier::recipientsSource();

        return response()->json([
            'status' => 'success',
            'message' => $valid === []
                ? 'Se quitó la lista del panel; se usará WEB_LEADS_NOTIFY_TO del .env (si existe).'
                : 'Correos guardados. Los próximos avisos llegarán a: ' . implode(', ', $valid) . '.',
            'recipients' => WebLeadNotifier::recipients(),
            'source' => $source,
        ]);
    }

    /** JSON data source for the custom admin table. */
    public function tableData(Request $request, WebLeadTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Detalle completo en fragmento (sin layout) para el modal de la tabla. */
    public function show(string $id)
    {
        $lead = WebLead::findOrFail($id);

        return view('admin-ui.web-leads._details', compact('lead'));
    }

    /** Cambia el seguimiento interno: nuevo | contactado | descartado. */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(WebLead::STATUSES))],
        ], [
            'status.required' => 'Indica el estado.',
            'status.in' => 'El estado indicado no es válido.',
        ]);

        $lead = WebLead::findOrFail($id);
        $lead->status = $data['status'];
        $lead->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Estado actualizado a "' . WebLead::STATUSES[$lead->status] . '".',
            'lead_status' => $lead->status,
        ]);
    }

    /**
     * Reintenta el aviso (correo + webhook) con el notificador. El notificador guarda en la propia
     * solicitud notified_at / notify_error / webhook_sent_at / webhook_error; aqui se relee y se informa.
     */
    public function resend(string $id): JsonResponse
    {
        $lead = WebLead::findOrFail($id);

        try {
            app(WebLeadNotifier::class)->notify($lead);
        } catch (Throwable $e) {
            // El notificador no deberia lanzar (guarda sus errores), pero un fallo aqui no debe dar 500 en blanco.
            Log::warning('WebLead resend fallo', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);
            $lead->refresh();
            if (!$lead->notify_error) {
                $lead->notify_error = mb_substr($e->getMessage(), 0, 500);
                $lead->save();
            }
        }

        $lead = $lead->fresh();
        $hasError = filled($lead->notify_error) || filled($lead->webhook_error);

        return response()->json([
            'status' => $hasError ? 'error' : 'success',
            'message' => $this->noticeSummary($lead),
            'notice' => [
                'notified_at' => $lead->notified_at?->toIso8601String(),
                'notify_error' => $lead->notify_error,
                'webhook_sent_at' => $lead->webhook_sent_at?->toIso8601String(),
                'webhook_error' => $lead->webhook_error,
            ],
        ]);
    }

    /** Borra solo esa solicitud. */
    public function destroy(string $id): JsonResponse
    {
        $lead = WebLead::findOrFail($id);
        $lead->delete();

        return response()->json(['status' => 'success', 'message' => 'Solicitud eliminada con éxito.']);
    }

    /** Resumen legible del estado de los avisos tras un reenvio. */
    private function noticeSummary(WebLead $lead): string
    {
        $mail = $lead->notify_error
            ? 'Correo con error'
            : ($lead->notified_at ? 'Correo enviado' : 'Correo pendiente (sin destinatario configurado o no enviado)');

        $webhookConfigured = filled(config('webleads.webhook_url'));
        $webhook = $lead->webhook_error
            ? 'Webhook con error'
            : ($lead->webhook_sent_at ? 'Webhook enviado' : ($webhookConfigured ? 'Webhook pendiente' : 'Webhook no configurado'));

        return $mail . ' · ' . $webhook . '.';
    }
}
