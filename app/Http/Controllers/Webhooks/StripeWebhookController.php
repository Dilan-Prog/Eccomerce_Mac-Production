<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\StripeSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Red de seguridad de reconciliación, no reconstrucción automática de la
 * orden: si Stripe confirma un cobro (charge.succeeded) pero
 * PaymentController::storeOrder() nunca llegó a crear la Transaction
 * correspondiente (ej. el navegador se cayó justo después del cobro),
 * este endpoint lo detecta y avisa por correo para reconciliar a mano.
 * No se puede recrear la orden desde aquí: el carrito vive solo en la
 * sesión PHP al momento del cobro, nunca se manda como metadata a Stripe.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $stripeSetting = StripeSetting::first();

        // Se prueban los dos secretos configurados (live y sandbox), no solo
        // el del "Modo Activo" actual: un webhook llega según el modo en que
        // se hizo el cobro, no según qué modo tenga seleccionado el admin en
        // este momento — si alguien cambia a Sandbox para hacer una prueba
        // mientras un webhook real de Live sigue en la cola de reintentos de
        // Stripe, antes se rechazaba por "firma inválida" sin más aviso.
        $candidateSecrets = array_filter(array_unique([
            $stripeSetting?->webhook_secret,
            $stripeSetting?->sandbox_webhook_secret,
        ]));

        if (!$stripeSetting || empty($candidateSecrets)) {
            Log::warning('Stripe webhook recibido pero no hay webhook_secret configurado en Ajustes de Pago (ni live ni sandbox).');
            return response('Webhook secret not configured', 400);
        }

        $event = null;
        foreach ($candidateSecrets as $secret) {
            try {
                $event = Webhook::constructEvent(
                    $request->getContent(),
                    $request->header('Stripe-Signature'),
                    $secret
                );
                break;
            } catch (\UnexpectedValueException $e) {
                Log::warning('Stripe webhook: payload inválido - ' . $e->getMessage());
                return response('Invalid payload', 400);
            } catch (SignatureVerificationException $e) {
                continue;
            }
        }

        if (!$event) {
            Log::warning('Stripe webhook: firma inválida contra los secretos configurados (live y sandbox).');
            return response('Invalid signature', 400);
        }

        if ($event->type === 'charge.succeeded') {
            $charge = $event->data->object;

            if (Transaction::where('transaction_id', $charge->id)->doesntExist()) {
                Log::critical("Stripe webhook: charge {$charge->id} succeeded pero no existe ninguna orden registrada con ese ID — posible pago cobrado sin registrar.", [
                    'charge_id' => $charge->id,
                    'amount' => $charge->amount,
                    'currency' => $charge->currency,
                ]);

                $this->alertAdmin(
                    'Cobro de Stripe sin orden registrada',
                    "Stripe confirmó el cobro {$charge->id} por " . number_format($charge->amount / 100, 2) . ' ' . strtoupper($charge->currency) .
                        ' pero no existe ninguna orden registrada con ese ID de transacción. Revisar y reconciliar manualmente.'
                );
            }
        }

        return response('OK', 200);
    }

    private function alertAdmin(string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($message) use ($subject) {
                $message->to('dilanp270105@gmail.com')->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('No se pudo enviar la alerta de webhook de Stripe: ' . $e->getMessage());
        }
    }
}
