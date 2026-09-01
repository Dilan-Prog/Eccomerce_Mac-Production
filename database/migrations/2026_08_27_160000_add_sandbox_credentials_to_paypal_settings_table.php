<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paypal_settings', function (Blueprint $table) {
            $table->string('sandbox_client_id')->nullable()->after('webhook_id')
                ->comment('Client ID de PayPal Developer para el entorno sandbox');
            $table->string('sandbox_secret_key')->nullable()->after('sandbox_client_id')
                ->comment('Secret key de PayPal Developer para el entorno sandbox');
            $table->string('sandbox_webhook_id')->nullable()->after('sandbox_secret_key')
                ->comment('Webhook ID del webhook de sandbox creado en el Dashboard de PayPal');
        });
    }

    public function down(): void
    {
        Schema::table('paypal_settings', function (Blueprint $table) {
            $table->dropColumn(['sandbox_client_id', 'sandbox_secret_key', 'sandbox_webhook_id']);
        });
    }
};
