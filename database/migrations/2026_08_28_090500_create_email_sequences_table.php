<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Secuencia de seguimiento automatizada de cotizaciones: N pasos, cada uno
 * con su plantilla y sus días de espera desde la inscripción.
 *
 * Solo las secuencias con status = 1 inscriben cotizaciones nuevas (ver
 * App\Support\SequenceProcessor). Apagar una secuencia NO cancela las
 * inscripciones que ya estaban en curso — eso sería destruir historial; solo
 * deja de tomar cotizaciones nuevas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sequences');
    }
};
