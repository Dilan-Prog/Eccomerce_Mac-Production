<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `nombre` era un mirror puro de `descr` (AspelSyncController::sync() lo
 * llenaba siempre con `$item['descr']`, nunca con un valor propio) — se
 * mostraba en el admin como "Nombre Alias" pero no aportaba información
 * distinta. Se quita del todo: columna, $fillable, sync() y la tabla admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspel_products', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('aspel_products', function (Blueprint $table) {
            $table->string('nombre')->nullable()->comment('Alias de descr (compatibilidad)');
        });
    }
};
