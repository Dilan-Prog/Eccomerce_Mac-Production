<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atribucion por folio de contacto.
 *
 *  - contact_folio: folio corto ("MN" + 5 caracteres) que viaja en el mensaje de WhatsApp / asunto
 *    del correo. Indice NO unico a proposito: un UNIQUE perderia el segundo evento con el mismo
 *    folio (y el catch del 23000 del controlador lo tragaria en silencio). Una colision se
 *    resuelve con `ambiguous` en el endpoint de consulta, no descartando datos.
 *  - event_at: hora del evento segun el navegador (captured_at NO cambia de significado: es la
 *    hora del ultimo toque de atribucion).
 *  - indice en created_at: lo usa la consulta por ventana de tiempo (cruce de llamadas).
 *
 * Aditiva y reversible; no toca datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_conversions', function (Blueprint $table) {
            $table->string('contact_folio', 12)->nullable()->after('event_id');
            $table->timestamp('event_at')->nullable()->after('captured_at');

            $table->index('contact_folio', 'track_conversions_contact_folio_index');
            $table->index('created_at', 'track_conversions_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('track_conversions', function (Blueprint $table) {
            $table->dropIndex('track_conversions_contact_folio_index');
            $table->dropIndex('track_conversions_created_at_index');

            $table->dropColumn(['contact_folio', 'event_at']);
        });
    }
};
