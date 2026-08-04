<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A list of bank accounts shown in the cotizaciones PDF (above the brand
 * logos), configurable from the admin panel. Deliberately separate from the
 * existing Transfer model, which is a single shared account used by the
 * customer-facing checkout/payment flow — this is quote-specific and needs
 * to support more than one account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('banco');
            $table->string('titular');
            $table->string('numero_cuenta')->nullable();
            $table->string('numero_tarjeta')->nullable();
            $table->string('clabe')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
