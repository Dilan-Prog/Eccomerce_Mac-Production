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
        // Crear tabla precio_x_product_aspel
        Schema::create('precio_x_product_aspel', function (Blueprint $table) {
            $table->id();
            $table->string('cve_art');
            $table->integer('cve_precio')->unique();
            $table->decimal('precio', 15, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precio_x_product_aspel');
    }
};
