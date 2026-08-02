<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_image_groups', function (Blueprint $table) {
            $table->id();
            $table->char('group_key', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('member_count')->default(0);
            $table->unsignedBigInteger('total_bytes')->nullable();
            $table->unsignedBigInteger('recoverable_bytes')->nullable();
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamp('discarded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_image_groups');
    }
};
