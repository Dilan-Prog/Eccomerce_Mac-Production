<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Espejo de `monedas_aspel`, pero exclusivo de Cotizaciones. `monedas_aspel`
 * sigue sirviendo solo al catálogo/ecommerce (AspelPricing, SyncAspelPrices,
 * ProductController) tal cual está, sembrada manualmente, sin sincronizar —
 * eso no cambia. Esta tabla nueva recibe el push diario completo de Aspel
 * (mismo shape que monedas_aspel) para que Cotizaciones tenga su propio tipo
 * de cambio dinámico, aislado, vía App\Support\CotizacionExchange.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizacion_monedas_aspel', function (Blueprint $table) {
            $table->id();
            $table->integer('num_moneda')->unique();
            $table->string('descripcion')->nullable();
            $table->string('simbolo')->nullable();
            $table->decimal('tipo_cambio', 12, 6)->nullable();
            $table->boolean('status')->nullable();
            $table->string('cve_moned', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_monedas_aspel');
    }
};
