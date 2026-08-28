<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editor visual por bloques para las plantillas de correo (ver
 * App\Support\BlockEmailRenderer). `blocks_json` guarda la estructura de
 * bloques armada por el editor visual (JS/Blade, agente en paralelo);
 * `body` sigue siendo el HTML final — cuando hay bloques, se regenera
 * automáticamente a partir de `blocks_json` en cada guardado (ver
 * EmailTemplateController::store()/update()), y sirve de todos modos como
 * respaldo para plantillas que nunca usaron el editor visual (blocks_json
 * null).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->json('blocks_json')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('blocks_json');
        });
    }
};
