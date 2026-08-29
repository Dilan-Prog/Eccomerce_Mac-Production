<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inscripción de UNA cotización en UNA secuencia. La unidad de seguimiento
 * es la cotización, no el cliente: un mismo cliente con tres cotizaciones
 * abiertas recibe el seguimiento de cada una por separado.
 *
 * unique(secuencia, cotizacion) es lo que hace idempotente el paso de
 * inscripción de SequenceProcessor: se puede llamar mil veces seguidas y
 * nunca duplica inscripciones.
 *
 * status:
 * - active: en curso.
 * - completed: ya no le quedan pasos por mandar.
 * - exited_purchase: el cliente compró después de inscribirse — se corta el
 *   seguimiento y sus pasos pendientes pasan a 'skipped' (ver
 *   SequenceProcessor::exitPurchasers).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sequence_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_sequence_id')->constrained('email_sequences')->cascadeOnDelete();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('exited_at')->nullable();
            $table->string('exit_reason')->nullable();
            $table->timestamps();

            $table->unique(['email_sequence_id', 'cotizacion_id'], 'es_enrollments_sequence_quote_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sequence_enrollments');
    }
};
