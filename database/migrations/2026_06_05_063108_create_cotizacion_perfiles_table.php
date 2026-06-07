<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizacion_perfiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo_persona', ['empresa', 'fisica']);
            $table->string('razon_social')->nullable();
            $table->string('curp')->nullable();
            $table->string('rfc');
            $table->text('direccion_fiscal');
            $table->string('cif_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_perfiles');
    }
};
