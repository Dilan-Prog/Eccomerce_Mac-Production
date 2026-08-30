<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extiende la restriccion de cupon a categoria (2026_08_27_090000) para
 * soportar tambien subcategoria y categoria hija -- mismo patron (nullable +
 * FK larga, nullOnDelete). NULL en los 3 sigue siendo "cupon global", igual
 * que antes. Un cupon puede restringirse a un nivel sin restringirse a los
 * mas especificos (ej. solo category_id, sub_category_id/child_category_id
 * en NULL = aplica a toda la categoria).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_category_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('child_category_id')->nullable()->after('sub_category_id');

            $table->foreign('sub_category_id')->references('id')->on('subcategories')->nullOnDelete();
            $table->foreign('child_category_id')->references('id')->on('child_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['sub_category_id']);
            $table->dropForeign(['child_category_id']);
            $table->dropColumn(['sub_category_id', 'child_category_id']);
        });
    }
};
