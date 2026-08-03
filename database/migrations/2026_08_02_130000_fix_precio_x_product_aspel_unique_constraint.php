<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * The original migration marked cve_precio unique across the whole table, but
 * cve_precio is a shared price-tier code (público/mínimo/liquidación/mayorista)
 * meant to repeat once per product — PrecioXProductoController::precioXProducto()
 * already upserts on [cve_art, cve_precio] together, the schema just never
 * matched that. With the old constraint, only the first product to claim a
 * given tier could ever have a price row for it; every other product's price
 * sync for that tier silently failed (unique violation caught and logged).
 *
 * Written defensively (inspecting live indexes instead of assuming Laravel's
 * default index-naming convention) because some environments already had this
 * hand-fixed directly in the database without a matching migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        $indexes = Schema::getIndexes('precio_x_product_aspel');
        $hasComposite = collect($indexes)->contains(
            fn ($i) => $i['unique'] && $i['columns'] === ['cve_art', 'cve_precio']
        );

        if (!$hasComposite) {
            foreach ($indexes as $index) {
                if ($index['unique'] && $index['columns'] === ['cve_precio']) {
                    Schema::table('precio_x_product_aspel', function ($table) use ($index) {
                        $table->dropUnique($index['name']);
                    });
                }
            }

            Schema::table('precio_x_product_aspel', function ($table) {
                $table->unique(['cve_art', 'cve_precio']);
            });
        }
    }

    public function down(): void
    {
        $indexes = Schema::getIndexes('precio_x_product_aspel');
        foreach ($indexes as $index) {
            if ($index['unique'] && $index['columns'] === ['cve_art', 'cve_precio']) {
                Schema::table('precio_x_product_aspel', function ($table) use ($index) {
                    $table->dropUnique($index['name']);
                });
            }
        }

        Schema::table('precio_x_product_aspel', function ($table) {
            $table->unique('cve_precio');
        });
    }
};
