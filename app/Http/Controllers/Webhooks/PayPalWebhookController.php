<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\PaypalSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

/**
 * Misma red de seguridad de reconciliación que StripeWebhookController,
 * para PayPal: si PAYMENT.CAPTURE.COMPLETED llega pero no existe una
 * Transaction con ese capture ID, avisa por correo en vez de perderlo
 * en silencio. No reconstruye la orden — mismo motivo que en Stripe.
 */
class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $paypalSetting = PaypalSetting::first();

        // Igual que StripeWebhookController: se intenta con las credenciales
        // de AMBOS modos (live y sandbox), no solo el "Modo Activo" actual —
        // un webhook llega según el modo en que se hizo el cobro, no según
        // qué modo tenga seleccionado el admin en este momento.
        $candidates = [];
        if ($paypalSetting?->webhook_id && $paypalSetting?->client_id && $paypalSetting?->secret_key) {
            $candidates[] = [
                'mode' => 'live',
                'webhook_id' => $paypalSetting->webhook_id,
                'client_id' => $paypalSetting->client_id,
                'client_secret' => $paypalSetting->secret_key,
            ];
        }
        if ($paypalSetting?->sandbox_webhook_id && $paypalSetting?->sandbox_client_id && $paypalSetting?->sandbox_secret_key) {
            $candidates[] = [
                'mode' => 'sandbox',
                'webhook_id' => $paypalSetting->sandbox_webhook_id,
                'client_id' => $paypalSetting->sandbox_client_id,
                'client_secret' => $paypalSetting->sandbox_secret_key,
            ];
        }

        if (!$paypalSetting || empty($candidates)) {
            Log::warning('PayPal webhook recibido pero no hay webhook_id configurado en Ajustes de Pago (ni live ni sandbox).');
            return response('Webhook ID not configured', 400);
        }

        $payload = $request->json()->all();
        $verification = null;

        foreach ($candidates as $candidate) {
            $config = [
                'mode'    => $candidate['mode'],
                'sandbox' => [
                    'client_id'     => $candidate['client_id'],
                    'client_secret' => $candidate['client_secret'],
                    'app_id'        => 'APP-80W284485P519543T',
                ],
                'live' => [
                    'client_id'     => $candidate['client_id'],
                    'client_secret' => $candidate['client_secret'],
                    'app_id'        => '',
                ],
                'payment_action' => 'Sale',
                'currency'       => $paypalSetting->currency_name,
                'notify_url'     => '',
                'locale'         => 'en_US',
                'validate_ssl'   => true,
            ];

            try {
                $provider = new PayPalClient($config);
                $provider->getAccessToken();

                $attempt = $provider->verifyWebHook([
                    'transmission_id'   => $request->header('Paypal-Transmission-Id'),
                    'transmission_time' => $request->header('Paypal-Transmission-Time'),
                    'cert_url'          => $request->header('Paypal-Cert-Url'),
                    'auth_algo'         => $request->header('Paypal-Auth-Algo'),
                    'transmission_sig'  => $request->header('Paypal-Transmission-Sig'),
                    'webhook_id'        => $candidate['webhook_id'],
                    'webhook_event'     => $payload,
                ]);
            } catch (\Exception $e) {
                Log::warning("PayPal webhook: error al verificar firma en modo {$candidate['mode']} - " . $e->getMessage());
                continue;
            }

            if (($attempt['verification_status'] ?? null) === 'SUCCESS') {
                $verification = $attempt;
                break;
            }
        }

        if (!$verification) {
            Log::warning('PayPal webhook: firma no válida contra las credenciales configuradas (live y sandbox).');
            return response('Invalid signature', 400);
        }

        if (($payload['event_type'] ?? null) === 'PAYMENT.CAPTURE.COMPLETED') {
            $captureId = $payload['resource']['id'] ?? null;

            if ($captureId && Transaction::where('transaction_id', $captureId)->doesntExist()) {
                $amount = $payload['resource']['amount']['value'] ?? '?';
                $currency = $payload['resource']['amount']['currency_code'] ?? '?';

                Log::critical("PayPal webhook: captura {$captureId} completada pero no existe ninguna orden registrada con ese ID — posible pago cobrado sin registrar.", [
                    'capture_id' => $captureId,
                    'amount' => $amount,
                    'currency' => $currency,
                ]);

                $this->alertAdmin(
                    'Cobro de PayPal sin orden registrada',
                    "PayPal confirmó la captura {$captureId} por {$amount} {$currency} pero no existe ninguna orden registrada con ese ID de transacción. Revisar y reconciliar manualmente."
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
            Log::error('No se pudo enviar la alerta de webhook de PayPal: ' . $e->getMessage());
        }
    }
}
