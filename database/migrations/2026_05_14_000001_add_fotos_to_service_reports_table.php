<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->json('fotos')->nullable()->after('mediciones');
        });
    }

    public function down(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->dropColumn('fotos');
        });
    }
};
