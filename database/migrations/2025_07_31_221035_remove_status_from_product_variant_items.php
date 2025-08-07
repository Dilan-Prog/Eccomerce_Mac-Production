<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variant_items', function (Blueprint $table) {
             $table->dropColumn('price');
             $table->dropColumn('is_default');
             $table->dropColumn('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variant_items', function (Blueprint $table) {
             $table->decimal('price', 10, 2)->nullable(); // o el tipo que tenías
             $table->boolean('is_default')->default(false);
             $table->integer('qty')->default(0);
        });
    }
};
