<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * App\Support\AspelPricing (and the cotizaciones PDF's IVA split) already
 * read general_settings.iva_mexico via a raw DB::table() query, but this
 * column was only ever added by hand directly on some databases — no
 * tracked migration ever created it, which breaks a fresh install/test DB.
 * Written defensively (skips if already present) since it already exists on
 * environments where someone added it manually.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('general_settings', 'iva_mexico')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->decimal('iva_mexico', 5, 2)->default(16.00)->after('currency_icon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('general_settings', 'iva_mexico')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->dropColumn('iva_mexico');
            });
        }
    }
};
