<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens compartidos que autentican las rutas GET /api/marketing/* (ver
 * App\Http\Middleware\MarketingApiTokenMiddleware). Sistema aislado del de
 * Aspel (aspel_api_tokens) — mismo patrón (key_id + secret_hash, Bearer
 * "{key_id}.{secret}") pero para el flujo de n8n/email marketing, sin
 * mezclar ambos universos de autenticación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key_id', 20)->unique();
            $table->string('secret_hash');
            $table->string('secret_last_four', 4)->nullable();
            $table->boolean('status')->default(1);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_api_tokens');
    }
};
