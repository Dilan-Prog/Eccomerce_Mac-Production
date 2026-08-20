<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * tiempo_entrega_general (encabezado del PDF, junto a "Fecha") pasa de texto
 * libre a fecha real — el vendedor pidió capturarla con selector de fecha en
 * vez de escribirla a mano, para que sea más fácil de llenar. Se hace con
 * drop+add (no ->change()) para no requerir doctrine/dbal, que este proyecto
 * no tiene instalado; es seguro porque la columna se agregó hoy mismo y no
 * hay datos de producción que dependan del formato de texto libre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('tiempo_entrega_general');
        });
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->date('tiempo_entrega_general')->nullable()->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('tiempo_entrega_general');
        });
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('tiempo_entrega_general', 255)->nullable()->after('total');
        });
    }
};
