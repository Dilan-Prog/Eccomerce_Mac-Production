<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un paso de una secuencia: qué plantilla se manda y cuántos días después de
 * la inscripción.
 *
 * wait_days se cuenta SIEMPRE desde enrolled_at (la fecha de inscripción),
 * nunca encadenado desde el paso anterior — así el calendario completo de una
 * inscripción se puede materializar de una sola vez al inscribirla, y un paso
 * que falle o se retrase no corre la fecha de los siguientes.
 * wait_days = 0 es válido: el paso queda debido de inmediato.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_sequence_id')->constrained('email_sequences')->cascadeOnDelete();
            $table->foreignId('email_template_id')->constrained('email_templates')->restrictOnDelete();
            $table->unsignedSmallInteger('step_order');
            $table->unsignedSmallInteger('wait_days')->default(0);
            $table->string('name')->nullable();
            $table->timestamps();

            $table->unique(['email_sequence_id', 'step_order'], 'es_steps_sequence_order_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sequence_steps');
    }
};
