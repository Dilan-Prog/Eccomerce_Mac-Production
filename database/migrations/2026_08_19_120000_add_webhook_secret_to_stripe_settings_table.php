<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stripe_settings', function (Blueprint $table) {
            $table->string('webhook_secret')->nullable()->after('secret_key')
                ->comment('Signing secret del webhook creado en el Dashboard de Stripe (evento charge.succeeded)');
        });
    }

    public function down(): void
    {
        Schema::table('stripe_settings', function (Blueprint $table) {
            $table->dropColumn('webhook_secret');
        });
    }
};
