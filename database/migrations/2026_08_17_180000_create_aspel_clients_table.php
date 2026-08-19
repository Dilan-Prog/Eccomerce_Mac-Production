<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Espejo aislado de Aspel SAE CLIE01 (clientes). `user_id` es la única
 * relación con `users`, y siempre en este sentido (aspel_clients -> users) —
 * `users` nunca sabe de Aspel. Se enlaza/crea vía
 * AdminCotizacionController::resolveAspelClient(), llamado desde el picker
 * de cliente del cotizador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspel_clients', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 10)->unique();
            $table->char('status', 1)->nullable();
            $table->string('nombre', 254)->nullable();
            $table->string('nombre_comercial', 254)->nullable();
            $table->string('rfc', 15)->nullable();
            $table->char('tipo_empresa', 1)->nullable();
            $table->string('uso_cfdi', 5)->nullable();
            $table->string('regimen_fiscal', 4)->nullable();
            $table->string('email', 512)->nullable();
            $table->string('telefono', 25)->nullable();
            $table->string('calle', 80)->nullable();
            $table->string('num_ext', 15)->nullable();
            $table->string('colonia', 50)->nullable();
            $table->string('municipio', 50)->nullable();
            $table->string('estado', 50)->nullable();
            $table->string('codigo_postal', 5)->nullable();
            $table->integer('lista_precio')->nullable();
            $table->double('descuento')->nullable();
            $table->string('cve_vendedor', 5)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('remote_updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('sync_hash')->nullable();
            $table->timestamps();

            $table->index('rfc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspel_clients');
    }
};
