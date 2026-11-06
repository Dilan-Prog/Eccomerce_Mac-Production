<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWebLeadRequest;
use App\Models\WebLead;
use App\Support\WebLeads\WebLeadNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Recibe los formularios publicos (POST /solicitudes-web). Guarda primero en web_leads y DESPUES avisa
 * por correo/webhook; un fallo al avisar nunca rompe la respuesta (la solicitud ya esta guardada).
 */
class WebLeadController extends Controller
{
    public function store(StoreWebLeadRequest $request): RedirectResponse|JsonResponse
    {
        $formType = (string) $request->input('form_type');

        // Robot (honeypot / demasiado rapido): misma respuesta de exito, sin guardar ni avisar.
        if ($request->isBot()) {
            // Sin datos personales: solo deja rastro de que se descarto un envio (por si un lead real cayera aqui).
            Log::info('WebLead: envio descartado por anti-spam', ['form_type' => $formType]);

            return $this->success($request, $formType);
        }

        $lead = WebLead::create($request->leadData());

        // El aviso (correo SMTP + webhook) se manda DESPUES de responder al cliente: un SMTP lento no lo hace esperar,
        // y la solicitud ya esta guardada pase lo que pase.
        dispatch(function () use ($lead) {
            try {
                app(WebLeadNotifier::class)->notify($lead);
            } catch (\Throwable $e) {
                // Sin datos personales en el log: solo el id y el tipo de error.
                Log::warning('WebLead: fallo al avisar de una solicitud ya guardada', [
                    'web_lead_id' => $lead->id,
                    'exception' => get_class($e),
                ]);
            }
        })->afterResponse();

        return $this->success($request, $formType);
    }

    private function success(StoreWebLeadRequest $request, string $formType): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'form' => $formType]);
        }

        return back()
            ->with('web_lead_status', 'ok')
            ->with('web_lead_form', $formType);
    }
}
