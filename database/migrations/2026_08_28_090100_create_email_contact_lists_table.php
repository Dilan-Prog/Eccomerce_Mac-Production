<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Listas de contactos del módulo de Email Marketing — el "a quién" de una
 * campaña (el "qué" es una email_template). Una lista se llena importando
 * clientes del sitio (users), clientes de Aspel (aspel_clients) o a mano.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_contact_lists', function (Blueprint $table) {
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
        Schema::dropIfExists('email_contact_lists');
    }
};
