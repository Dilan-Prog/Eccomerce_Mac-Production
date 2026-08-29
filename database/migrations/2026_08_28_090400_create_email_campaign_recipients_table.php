<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot congelado de los destinatarios de una campaña, tomado al
 * programarla (EmailCampaignController::schedule). Es una copia, no una
 * consulta en vivo a la lista: cambiar la lista después de programar la
 * campaña NO altera a quién se le va a enviar, y el historial de un envío
 * ya hecho queda intacto aunque la lista se edite o se vacíe más tarde.
 *
 * `attempts` solo cuenta: Laravel NO implementa política de reintentos ni
 * tope que bloquee. n8n es el dueño del timing y decide si vuelve a pedir el
 * render de un destinatario en 'failed'; aquí solo se registra el resultado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('contact_source')->default('manual');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aspel_client_id')->nullable()->constrained('aspel_clients')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->unique(['email_campaign_id', 'email'], 'ec_recipients_campaign_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
    }
};
