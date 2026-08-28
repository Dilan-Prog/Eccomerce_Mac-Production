<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Espejo de Aspel SAE PAR_FACTF01 (partidas de factura). `cve_doc` es FK
 * lógica a aspel_sales.cve_doc (sin FK real de BD: aspel_sales puede no
 * haber llegado todavía en el mismo lote de sync). `cve_art` cruza con
 * aspel_products.cve_art (y de ahí, products.sku = aspel_products.cve_art
 * — ver SyncAspelQty.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspel_sale_items', function (Blueprint $table) {
            $table->id();
            $table->string('cve_doc', 20)->index();
            $table->integer('num_par');
            $table->string('cve_art', 16)->index();
            $table->double('cant')->nullable();
            $table->double('prec')->nullable();
            $table->double('tot_partida')->nullable();
            $table->string('descr_art', 40)->nullable();
            $table->timestamps();

            $table->unique(['cve_doc', 'num_par']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspel_sale_items');
    }
};
