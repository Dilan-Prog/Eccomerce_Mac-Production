<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Calendario materializado de una inscripción: una fila por cada paso de la
 * secuencia, creada toda de golpe al inscribir (con su due_at ya calculado
 * desde enrolled_at + wait_days del paso).
 *
 * status:
 * - pending: todavía no llega su due_at.
 * - due: ya venció y está listo para que n8n lo recoja en
 *   GET /api/marketing/sequences/due.
 * - sent / failed: resultado reportado por n8n.
 * - skipped: la inscripción salió antes (ej. el cliente compró) y este paso
 *   ya no se manda nunca.
 *
 * Igual que en las campañas, `attempts` solo cuenta — Laravel no reintenta
 * por su cuenta ni bloquea al llegar a un tope; el timing y la política de
 * reintento son de n8n.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sequence_step_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_sequence_enrollment_id')->constrained('email_sequence_enrollments')->cascadeOnDelete();
            $table->foreignId('email_sequence_step_id')->constrained('email_sequence_steps')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->unique(['email_sequence_enrollment_id', 'email_sequence_step_id'], 'es_step_sends_enrollment_step_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sequence_step_sends');
    }
};
