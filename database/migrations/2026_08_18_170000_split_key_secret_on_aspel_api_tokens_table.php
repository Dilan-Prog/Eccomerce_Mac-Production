<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separa identidad (key_id, público, siempre visible) de secreto
 * (secret_hash, nunca se guarda en claro — solo su hash y sus últimos 4
 * caracteres para mostrar). El Bearer token pasa a ser "{key_id}.{secret}"
 * (ver App\Http\Middleware\AspelApiTokenMiddleware). Cualquier token creado
 * antes de esta migración queda inválido — hay que regenerarlo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspel_api_tokens', function (Blueprint $table) {
            $table->dropColumn('token');
            $table->string('key_id', 20)->unique()->after('name');
            $table->string('secret_hash')->after('key_id');
            $table->string('secret_last_four', 4)->nullable()->after('secret_hash');
        });
    }

    public function down(): void
    {
        Schema::table('aspel_api_tokens', function (Blueprint $table) {
            $table->dropColumn(['key_id', 'secret_hash', 'secret_last_four']);
            $table->string('token', 80)->unique()->after('name');
        });
    }
};
