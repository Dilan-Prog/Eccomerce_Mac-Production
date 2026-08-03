<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('thumb_image_laptop')->nullable();
            $table->text('thumb_image_tablet')->nullable();
            $table->text('thumb_image_phone')->nullable();
            $table->text('thumb_image_carrusel')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['thumb_image_laptop', 'thumb_image_tablet', 'thumb_image_phone', 'thumb_image_carrusel']);
        });
    }
};
