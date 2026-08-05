<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda la moneda nativa Aspel de una partida (lo que devuelva
 * CotizacionExchange::nativeCurrencyForSku()) y su precio CRUDO, sin
 * normalizar a MXN — necesario para que la partida pueda mostrarse tal cual
 * (sin ida y vuelta de conversión) cuando la moneda de la cotización
 * coincide exactamente con la moneda nativa del producto. Ver
 * Cotizacion::displayItemAmount(). Solo se llena en el camino de
 * precio_tier para producto simple (ver storeItem()) — precio_personalizado
 * y combinaciones dejan ambas columnas en NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            $table->string('moneda_origen', 10)->nullable()->after('precio_tier_label');
            $table->decimal('precio_unitario_origen', 12, 4)->nullable()->after('moneda_origen');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            $table->dropColumn(['moneda_origen', 'precio_unitario_origen']);
        });
    }
};
