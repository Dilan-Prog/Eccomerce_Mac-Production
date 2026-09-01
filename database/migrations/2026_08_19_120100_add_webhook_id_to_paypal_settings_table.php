<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paypal_settings', function (Blueprint $table) {
            $table->string('webhook_id')->nullable()->after('secret_key')
                ->comment('Webhook ID creado en el Dashboard de PayPal (evento PAYMENT.CAPTURE.COMPLETED), usado para verificar la firma vía v1/notifications/verify-webhook-signature');
        });
    }

    public function down(): void
    {
        Schema::table('paypal_settings', function (Blueprint $table) {
            $table->dropColumn('webhook_id');
        });
    }
};
