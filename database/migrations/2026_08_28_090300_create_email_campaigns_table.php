<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Envío masivo de UNA plantilla a UNA lista de contactos.
 *
 * Ciclo de vida de `status`:
 *   borrador -> programada -> enviando -> enviada | cancelada
 * - borrador: se puede editar/borrar libremente, todavía no hay
 *   destinatarios materializados.
 * - programada: ya se tomó el snapshot a email_campaign_recipients (ver
 *   EmailCampaignController::schedule) y la campaña es visible para n8n en
 *   GET /api/marketing/campaigns/due (si scheduled_at ya pasó, o si es null).
 * - enviando: n8n la reclamó (claimed_at) y está procesando destinatarios.
 * - enviada: ya no quedan destinatarios en `pending`.
 *
 * FK con restrict a plantilla y lista: no se permite borrar una plantilla o
 * una lista que alguna campaña esté usando (ver los destroy() con try/catch
 * de esos módulos, que traducen el error de FK a un mensaje amigable).
 *
 * claimed_at es el candado de concurrencia: el UPDATE condicional de
 * MarketingCampaignController::claim() garantiza que dos ejecuciones
 * simultáneas de n8n nunca tomen la misma campaña (la segunda recibe 409).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('email_template_id')->constrained('email_templates')->restrictOnDelete();
            $table->foreignId('email_contact_list_id')->constrained('email_contact_lists')->restrictOnDelete();
            $table->string('status')->default('borrador');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
