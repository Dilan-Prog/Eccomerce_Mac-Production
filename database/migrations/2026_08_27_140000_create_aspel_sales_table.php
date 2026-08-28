<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Espejo de Aspel SAE FACTF01 (encabezado de facturas), solo TIP_DOC='F'
 * (facturas reales, ya emitidas — filtrado por quien manda el payload, no
 * aquí). `cve_clpv` cruza con `aspel_clients.clave`.
 *
 * `fecha_cancela` SÍ se incluye aunque no estaba en la lista original de
 * columnas del plan: MarketingOfferBuilder::resolveDominantCategory()
 * necesita filtrar `aspel_sales.fecha_cancela IS NULL` para excluir
 * facturas canceladas (ver instrucción explícita de esa sección), así que
 * sin esta columna ese filtro no sería posible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspel_sales', function (Blueprint $table) {
            $table->id();
            $table->string('cve_doc', 20)->unique();
            $table->string('cve_clpv', 10)->index();
            $table->timestamp('fecha_doc')->nullable();
            $table->timestamp('fecha_cancela')->nullable();
            $table->double('importe')->nullable();
            $table->string('rfc', 15)->nullable();
            $table->integer('num_moned')->nullable();
            $table->double('tipcamb')->nullable();
            $table->string('uuid', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspel_sales');
    }
};
