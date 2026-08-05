<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tipos de cambio EXCLUSIVOS del módulo de Cotizaciones — deliberadamente
 * aislados de monedas_aspel/AspelPricing (esos siguen sirviendo solo al
 * catálogo de Productos). Nullable: quedan vacíos hasta que el admin los
 * capture en Configuración General; no se siembran con ningún valor de
 * Aspel a propósito. Ver App\Support\CotizacionExchange.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->decimal('tipo_cambio_usd_mxn', 10, 4)->nullable()->after('iva_mexico'); // pesos por dólar
            $table->decimal('tipo_cambio_mxn_usd', 10, 4)->nullable()->after('tipo_cambio_usd_mxn'); // dólares por peso
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['tipo_cambio_usd_mxn', 'tipo_cambio_mxn_usd']);
        });
    }
};
