<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aspel SAE tiene un mecanismo de "campos libres" por producto
 * (INVE_CLIB01.CAMPLIB1..CAMPLIB6, unidos por CVE_PROD = INVE01.CVE_ART).
 * En esta instalación, PARAM_CAMPOSLIBRES01 confirma que solo CAMPLIB3
 * (varchar 25) fue reetiquetado como "MODELO" — los otros 5 siguen sin usar.
 * El script externo que sincroniza aspel_products hace el LEFT JOIN a
 * INVE_CLIB01 y envía ese valor como 'campo_libre_modelo'. No se llama
 * "modelo" a secas para no chocar con la columna preexistente `prefijo`
 * ("Modelo o prefijo", sin uso, campo distinto y no relacionado con este
 * campo libre).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspel_products', function (Blueprint $table) {
            $table->string('campo_libre_modelo', 25)->nullable()->after('prefijo')
                ->comment('INVE_CLIB01.CAMPLIB3 (relabeleado "MODELO" en PARAM_CAMPOSLIBRES01), via LEFT JOIN en el script de sync');
        });
    }

    public function down(): void
    {
        Schema::table('aspel_products', function (Blueprint $table) {
            $table->dropColumn('campo_libre_modelo');
        });
    }
};
