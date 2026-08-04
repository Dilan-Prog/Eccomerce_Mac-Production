<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bank_accounts', 'logo')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->string('logo')->nullable()->after('moneda');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bank_accounts', 'logo')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->dropColumn('logo');
            });
        }
    }
};
