<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens compartidos que autentican las rutas POST /api/aspel/* (ver
 * App\Http\Middleware\AspelApiTokenMiddleware). Administrados desde el
 * módulo "Integración" del panel admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspel_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('token', 80)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aspel_api_tokens');
    }
};
