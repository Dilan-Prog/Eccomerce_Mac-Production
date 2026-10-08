<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_conversions', function (Blueprint $table) {
            // Datos extra del evento (sku, producto, cantidad, ubicación del botón, folio de cotización…).
            $table->json('meta')->nullable()->after('captured_at');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('track_conversions', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('meta');
        });
    }
};
