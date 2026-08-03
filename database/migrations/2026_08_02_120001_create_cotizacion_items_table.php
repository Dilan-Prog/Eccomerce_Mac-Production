<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * New table backing the ERP-style admin quote builder's line items.
 * Purely additive: existing cotizaciones keep storing their snapshot in
 * `productos_json` untouched; this table is only populated going forward by
 * the new admin quote-builder flow (a separate task wires that up).
 *
 * product_id / product_variant_combination_id are nullable + nullOnDelete so
 * a line item survives even if the referenced catalog product/variant is
 * later deleted (the item keeps its own snapshot columns: nombre, sku,
 * modelo, marca, precio_unitario).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizacion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_combination_id')->nullable()
                ->constrained('product_variants_combinations')->nullOnDelete();
            $table->string('nombre');
            $table->string('sku')->nullable();
            $table->string('modelo')->nullable();
            $table->string('marca')->nullable();
            $table->decimal('precio_unitario', 12, 2);
            $table->unsignedInteger('cantidad');
            $table->decimal('subtotal', 12, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_items');
    }
};
