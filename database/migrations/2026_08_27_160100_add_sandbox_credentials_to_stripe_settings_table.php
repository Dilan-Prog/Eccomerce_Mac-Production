<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stripe_settings', function (Blueprint $table) {
            $table->string('sandbox_client_id')->nullable()->after('webhook_secret')
                ->comment('Publishable/client key de Stripe para el modo de prueba (test mode)');
            $table->string('sandbox_secret_key')->nullable()->after('sandbox_client_id')
                ->comment('Secret key de Stripe para el modo de prueba (test mode)');
            $table->string('sandbox_webhook_secret')->nullable()->after('sandbox_secret_key')
                ->comment('Signing secret del webhook de test mode creado en el Dashboard de Stripe');
        });
    }

    public function down(): void
    {
        Schema::table('stripe_settings', function (Blueprint $table) {
            $table->dropColumn(['sandbox_client_id', 'sandbox_secret_key', 'sandbox_webhook_secret']);
        });
    }
};
