<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_conversions', function (Blueprint $table) {
            // Eventos generados desde el Laboratorio de atribucion (modo prueba). Nunca se cuentan
            // en metricas ni se suben a Google Ads. Lo decide el servidor, no el navegador.
            $table->boolean('is_test')->default(false)->after('contact_folio');
            $table->index(['is_test', 'id'], 'track_conversions_is_test_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('track_conversions', function (Blueprint $table) {
            $table->dropIndex('track_conversions_is_test_id_index');
            $table->dropColumn('is_test');
        });
    }
};
