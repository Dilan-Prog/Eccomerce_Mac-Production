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
       Schema::create('monedas_aspel', function (Blueprint $table) {
            $table->id();
            $table->integer('num_moneda')->primaryKey();
            $table->string('descripcion');
            $table->string('simbolo');
            $table->string('tipo_cambio');
            $table->boolean('status');
            $table->string('cve_moned');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monedas_aspel');
    }
};
