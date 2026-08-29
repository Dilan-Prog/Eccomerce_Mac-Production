<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clasifica cada plantilla según para qué flujo se escribió:
 * - 'individual': la que ya existía — oferta 1 a 1 que arma
 *   MarketingDataController::email() (GET /api/marketing/email/{userId}).
 * - 'campaign': cuerpo de un envío masivo a una lista de contactos.
 * - 'sequence': cuerpo de un paso de una secuencia automatizada.
 *
 * Es solo una etiqueta de organización/filtrado en el admin: el renderizado
 * (EmailTemplateRenderer + BlockEmailRenderer) es idéntico para las tres y
 * NINGÚN endpoint filtra por este campo — así las plantillas ya existentes
 * (que caen todas en 'individual' por el default) siguen funcionando igual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->string('type')->default('individual')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
