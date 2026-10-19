<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Uso" (scope) de cada token de marketing_api_tokens:
 *  - marketing   -> autentica GET/POST /api/marketing/* (el comportamiento de siempre).
 *  - attribution -> autentica GET /api/attribution/lookup (n8n / Google Ads).
 * Los tokens que ya existen quedan en 'marketing' por el default, asi que siguen valiendo igual.
 * Un token de un uso NO autentica las rutas del otro (se valida en los dos middlewares).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_api_tokens', function (Blueprint $table) {
            $table->string('scope', 20)->default('marketing')->after('secret_last_four');
            $table->index('scope');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_api_tokens', function (Blueprint $table) {
            $table->dropIndex(['scope']);
            $table->dropColumn('scope');
        });
    }
};
