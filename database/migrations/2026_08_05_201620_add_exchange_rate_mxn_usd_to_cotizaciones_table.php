<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `exchange_rate` (ya existente) es "pesos por dólar" — USD→MXN — y ahora
 * también se usa para normalizar a MXN el precio de productos nativos-USD al
 * agregarlos a la cotización (ver AdminCotizacionController::storeItem()),
 * no solo para mostrar en pantalla. Esta columna nueva es la dirección
 * inversa (MXN→USD, dólares por peso), usada únicamente por
 * Cotizacion::displayAmount() al mostrar/generar PDF cuando currency='USD'.
 * Nullable + con fallback a la fórmula vieja (dividir por exchange_rate) en
 * displayAmount() para no regresionar cotizaciones ya finalizadas en USD.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->decimal('exchange_rate_mxn_usd', 10, 4)->nullable()->after('exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('exchange_rate_mxn_usd');
        });
    }
};
