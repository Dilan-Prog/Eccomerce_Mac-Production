<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            // cve_precio elegido en precio_x_product_aspel (1=publico, 2=minimo,
            // 3=liquidacion, 4=mayorista) — null cuando el producto no tiene
            // niveles Aspel (se usó Product::effectivePrice() normal).
            $table->unsignedTinyInteger('precio_tier')->nullable()->after('precio_unitario');
            $table->string('precio_tier_label')->nullable()->after('precio_tier');

            // Partición automática de stock: cuando la cantidad pedida excede
            // el stock disponible, la partida original se divide en dos filas
            // (inmediata + pendiente) — ver AdminCotizacionController::storeItem().
            $table->boolean('es_pendiente')->default(false)->after('cantidad');
            $table->string('tiempo_entrega')->nullable()->after('es_pendiente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            $table->dropColumn(['precio_tier', 'precio_tier_label', 'es_pendiente', 'tiempo_entrega']);
        });
    }
};
