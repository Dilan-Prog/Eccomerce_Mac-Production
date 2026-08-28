<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generaliza email_templates para el motor de namespaces de marcadores
 * ({{contact.*}}, {{quote.*}}, {{cart.*}}, {{deal.*}} — ver
 * App\Support\EmailTemplateRenderer) y para poder proteger plantillas
 * futuras que dependan del código (ej. "cotización enviada").
 *
 * - builder_mode: solo informativo, indica qué editor se usó por última vez
 *   para guardar esta plantilla ('code' o 'blocks'); no cambia cómo se
 *   renderiza (eso ya lo decide blocks_json ser null o no).
 * - is_system / system_key: plantillas protegidas por código — no se pueden
 *   borrar desde el admin (ver EmailTemplateController::destroy()), pero sí
 *   editar. Ninguna plantilla existente hoy es de sistema, por eso el
 *   default false/null no rompe filas actuales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->string('builder_mode')->default('blocks')->after('blocks_json');
            $table->boolean('is_system')->default(false)->after('status');
            $table->string('system_key')->nullable()->unique()->after('is_system');
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropUnique(['system_key']);
            $table->dropColumn(['builder_mode', 'is_system', 'system_key']);
        });
    }
};
