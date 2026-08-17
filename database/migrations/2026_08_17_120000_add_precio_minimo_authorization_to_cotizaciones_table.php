<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-quote authorization to use the "Precio mínimo" tier
 * (cve_precio=2, see App\Support\CotizacionPricing::CVE_MINIMO). Only an
 * unrestricted admin (User::isUnrestrictedAdmin(), role==='admin' &&
 * role_id===null) can grant it, via
 * AdminCotizacionController::authorizeMinPrice(), and storeItem() only
 * allows that tier while it's set. nullOnDelete: if the granting user is
 * later deleted, the timestamp stays (historical fact) but attribution of
 * "who" is lost, without cascading the delete to the quote.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->timestamp('precio_minimo_autorizado_at')->nullable()->after('status');
            $table->unsignedBigInteger('precio_minimo_autorizado_por')->nullable()->after('precio_minimo_autorizado_at');

            $table->foreign('precio_minimo_autorizado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['precio_minimo_autorizado_por']);
            $table->dropColumn(['precio_minimo_autorizado_at', 'precio_minimo_autorizado_por']);
        });
    }
};
