<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajustes del Laboratorio de atribucion editables desde el panel (numero de WhatsApp de prueba,
        // URL del webhook de n8n de pruebas). Clave/valor; sin ellos se usa el .env como respaldo.
        Schema::create('attribution_lab_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribution_lab_settings');
    }
};
