<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * nota_envio (agregada en 2026_08_20_130000) se reemplaza por un mecanismo
 * más útil: el vendedor elige "Envío gratis" o "Envío con costo" en el
 * builder, y si es con costo, el monto se agrega como una partida real de
 * la cotización (cotizacion_items) — no como texto libre en el pie del PDF.
 * La columna nunca llegó a usarse en producción (mismo día que se creó).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('nota_envio');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('nota_envio', 255)->nullable()->after('tiempo_entrega_general');
        });
    }
};
