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
        // Crear tabla precios
        Schema::create('precios', function (Blueprint $table) {
            $table->id();
            $table->integer('cve_precio')->unique();
            $table->string('descripcion')->nullable();
            $table->string('cve_bita')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('precios');
    }
};
