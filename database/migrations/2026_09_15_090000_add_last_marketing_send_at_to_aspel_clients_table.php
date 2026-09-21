<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rastreo de envios de campana de email marketing a nivel de base de datos —
 * reemplaza $getWorkflowStaticData() de n8n, que no persiste de forma
 * confiable entre ejecuciones manuales en esta instalacion self-hosted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspel_clients', function (Blueprint $table) {
            $table->timestamp('last_marketing_send_at')->nullable()->after('sync_hash');
        });
    }

    public function down(): void
    {
        Schema::table('aspel_clients', function (Blueprint $table) {
            $table->dropColumn('last_marketing_send_at');
        });
    }
};
