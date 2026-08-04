<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cotizaciones.currency already exists (default 'MXN') but was never
 * exposed/read anywhere. Together with this new column it becomes a real,
 * manually-entered per-quote currency + exchange rate: item prices always
 * stay in MXN (see Cotizacion::displayAmount()), this rate is only applied
 * at display/PDF time when currency = 'USD'. Deliberately not sourced from
 * Aspel's monedas_aspel — that's the isolation the cotizaciones module needs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('exchange_rate');
        });
    }
};
