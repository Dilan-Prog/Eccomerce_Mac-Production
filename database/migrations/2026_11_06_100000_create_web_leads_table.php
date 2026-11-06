<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitudes de los formularios publicos (asociados, calibracion y puesta en marcha, medicion, cotizacion publica).
 * Antes enviaban directo a formsubmit.co (sin copia en la BD: si el servicio caia, el mensaje se perdia).
 * Ahora se guardan aqui primero y despues se avisa por correo / webhook.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_leads', function (Blueprint $table) {
            $table->id();
            $table->string('form_type', 40);                 // asociado | calibracion_puesta | medicion | cotizacion

            // Datos del formulario
            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('email', 190);
            $table->string('phone', 40)->nullable();
            $table->string('company', 190)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('operation', 190)->nullable();
            $table->text('message')->nullable();

            // Origen y atribucion (mismos nombres que track_conversions)
            $table->string('source_page', 500)->nullable();   // pagina donde se lleno el formulario
            $table->string('landing_page', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('session_id', 64)->nullable();
            $table->string('gclid', 255)->nullable();
            $table->string('gbraid', 255)->nullable();
            $table->string('wbraid', 255)->nullable();
            $table->string('fbclid', 255)->nullable();
            $table->string('utm_source', 190)->nullable();
            $table->string('utm_medium', 190)->nullable();
            $table->string('utm_campaign', 190)->nullable();
            $table->string('utm_term', 190)->nullable();
            $table->string('utm_content', 190)->nullable();

            // Seguimiento interno y avisos
            $table->string('status', 20)->default('nuevo');   // nuevo | contactado | descartado
            $table->timestamp('notified_at')->nullable();      // correo enviado
            $table->text('notify_error')->nullable();
            $table->timestamp('webhook_sent_at')->nullable();  // webhook (n8n) enviado
            $table->text('webhook_error')->nullable();

            $table->timestamps();

            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['form_type', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_leads');
    }
};
