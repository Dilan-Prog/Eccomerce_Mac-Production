<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Miembros de una lista de contactos. El correo se guarda desnormalizado (no
 * solo el user_id / aspel_client_id) a propósito: una lista debe poder tener
 * contactos manuales sin cuenta en el sitio, y el correo con el que se
 * importó no debe cambiar solo porque el cliente edite su perfil después.
 *
 * unique(lista, email) es la única defensa contra duplicados — los tres
 * importadores (clientes, Aspel, manual) se apoyan en ella.
 *
 * unsubscribed_at: baja lógica. Un miembro dado de baja se queda en la lista
 * (para no volver a importarlo por accidente) pero nunca entra al snapshot
 * de destinatarios de una campaña.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_contact_list_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_contact_list_id')->constrained('email_contact_lists')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('source')->default('manual');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aspel_client_id')->nullable()->constrained('aspel_clients')->nullOnDelete();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['email_contact_list_id', 'email'], 'ecl_members_list_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_contact_list_members');
    }
};
