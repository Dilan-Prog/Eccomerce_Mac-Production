<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos de atribucion completos para el rastreo de conversiones
 * (click ids de Google/Meta, UTM extendidos, referrer, sesion e idempotencia).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_conversions', function (Blueprint $table) {
            $table->string('gbraid', 255)->nullable()->after('landing_page');
            $table->string('wbraid', 255)->nullable()->after('gbraid');
            $table->string('fbclid', 255)->nullable()->after('wbraid');
            $table->string('utm_term', 255)->nullable()->after('fbclid');
            $table->string('utm_content', 255)->nullable()->after('utm_term');
            $table->string('referrer', 500)->nullable()->after('utm_content');
            $table->string('page_url', 500)->nullable()->after('referrer');
            $table->string('session_id', 64)->nullable()->after('page_url');
            $table->string('event_id', 64)->nullable()->after('session_id');
            $table->timestamp('captured_at')->nullable()->after('event_id');

            $table->index('gclid', 'track_conversions_gclid_index');
            $table->index('session_id', 'track_conversions_session_id_index');
            $table->unique('event_id', 'track_conversions_event_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('track_conversions', function (Blueprint $table) {
            $table->dropIndex('track_conversions_gclid_index');
            $table->dropIndex('track_conversions_session_id_index');
            $table->dropUnique('track_conversions_event_id_unique');

            $table->dropColumn([
                'gbraid',
                'wbraid',
                'fbclid',
                'utm_term',
                'utm_content',
                'referrer',
                'page_url',
                'session_id',
                'event_id',
                'captured_at',
            ]);
        });
    }
};
