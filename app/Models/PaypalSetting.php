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
}
