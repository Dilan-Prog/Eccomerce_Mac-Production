<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_image_galleries', function (Blueprint $table) {
            $table->text('image_laptop')->nullable();
            $table->text('image_tablet')->nullable();
            $table->text('image_phone')->nullable();
            $table->text('image_carrusel')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('product_image_galleries', function (Blueprint $table) {
            $table->dropColumn(['image_laptop', 'image_tablet', 'image_phone', 'image_carrusel']);
        });
    }
};
