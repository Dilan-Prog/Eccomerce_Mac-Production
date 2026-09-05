<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaypalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'mode',
        'country_name',
        'currency_name',
        'currency_rate',
        'client_id',
        'secret_key',
        'webhook_id',
        'sandbox_client_id',
        'sandbox_secret_key',
        'sandbox_webhook_id',
    ];

    /**
     * Resuelve la credencial que corresponde al modo activo (mode: 0 =
     * sandbox, 1 = live) — única fuente de verdad usada por
     * PaymentController, para que un cambio de modo en
     * /admin/payment-settings baste para cambiar de credenciales sin
     * tener que reescribir client_id/secret_key/webhook_id a mano.
     */
    public function activeClientId(): ?string
    {
        return $this->mode == 1 ? $this->client_id : $this->sandbox_client_id;
    }

    public function activeSecretKey(): ?string
    {
        return $this->mode == 1 ? $this->secret_key : $this->sandbox_secret_key;
    }

    public function activeWebhookId(): ?string
    {
        return $this->mode == 1 ? $this->webhook_id : $this->sandbox_webhook_id;
    }

    /** Base de la API REST de PayPal según el modo activo. */
    public function apiBase(): string
    {
        return $this->mode == 1
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Client token de PayPal, necesario para el formulario de tarjeta
     * incrustado (paypal.CardFields de Expanded Checkout). Va como atributo
     * data-client-token en la etiqueta del SDK.
     *
     * Sin este token, CardFields().isEligible() devuelve false AUNQUE la
     * cuenta tenga activada la capacidad "Advanced/Expanded Credit and Debit
     * Card Payments" — el token es lo que habilita el componente, no solo el
     * client-id. Los botones normales no lo necesitan, por eso funcionan sin
     * él.
     *
     * Devuelve null si algo falla (credenciales vacías, PayPal caído, etc.):
     * el checkout entonces se queda con los botones de siempre en vez de
     * romperse. Ver resources/views/frontend/pages/checkout.blade.php.
     *
     * Se cachea 25 minutos porque el token dura ~1 hora y generarlo cuesta
     * dos llamadas a PayPal en CADA carga del checkout.
     */
    public function generateClientToken(): ?string
    {
        $clientId = $this->activeClientId();
        $secret = $this->activeSecretKey();

        if (empty($clientId) || empty($secret)) {
            return null;
        }

        $cacheKey = 'paypal_client_token_' . $this->mode . '_' . substr(sha1($clientId), 0, 12);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(25), function () use ($clientId, $secret) {
            try {
                $auth = \Illuminate\Support\Facades\Http::withBasicAuth($clientId, $secret)
                    ->asForm()
                    ->post($this->apiBase() . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

                if (!$auth->successful() || !$auth->json('access_token')) {
                    \Illuminate\Support\Facades\Log::warning('PayPal: no se pudo obtener access_token para el client token.', [
                        'status' => $auth->status(),
                    ]);
                    return null;
                }

                // El cuerpo JSON vacío es obligatorio: sin él (o sin
                // Content-Type) PayPal responde 500 con el cuerpo vacío, que
                // parece un problema de la cuenta pero es solo el formato de
                // la petición. Con "{}" responde 200 y el client_token.
                $token = \Illuminate\Support\Facades\Http::withToken($auth->json('access_token'))
                    ->asJson()
                    ->post($this->apiBase() . '/v1/identity/generate-token', new \stdClass());

                if (!$token->successful()) {
                    \Illuminate\Support\Facades\Log::warning('PayPal: generate-token falló.', [
                        'status' => $token->status(),
                        'body' => $token->body(),
                    ]);
                    return null;
                }

                return $token->json('client_token');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('PayPal: excepción al generar el client token.', [
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }
}
