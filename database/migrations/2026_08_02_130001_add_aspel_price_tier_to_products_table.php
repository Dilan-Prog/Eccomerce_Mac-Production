<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * products.aspel_price/aspel_offert_price already store the computed price
 * (tipo de cambio + IVA applied) chosen by the admin from one of the price
 * tiers in precio_x_product_aspel (público/mínimo/liquidación/mayorista).
 * That's a snapshot with no memory of *which* tier was picked, so nothing
 * can ever refresh it once Aspel's own prices change. These two columns
 * store the cve_precio of the tier chosen for each, so a scheduled job
 * (app:sync-aspel-prices) can recompute the current price for that same
 * tier later, the same way sync-aspel-qty already refreshes qty_aspel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('aspel_price_tier')->nullable()->after('aspel_price');
            $table->unsignedInteger('aspel_offert_price_tier')->nullable()->after('aspel_offert_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['aspel_price_tier', 'aspel_offert_price_tier']);
        });
    }
};
