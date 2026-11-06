<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajustes editables desde el panel (Administrar Sitio Web > Solicitudes web), en clave/valor.
 * Clave en uso: 'notify_to' (correos que reciben el aviso de cada solicitud; tiene prioridad sobre WEB_LEADS_NOTIFY_TO del .env).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_lead_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_lead_settings');
    }
};
