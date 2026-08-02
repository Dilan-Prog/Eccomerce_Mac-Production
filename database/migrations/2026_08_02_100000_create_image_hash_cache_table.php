<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_hash_cache', function (Blueprint $table) {
            $table->id();
            $table->char('path_hash', 32);
            $table->text('physical_path');
            $table->unsignedBigInteger('file_size');
            $table->unsignedBigInteger('file_mtime');
            $table->char('image_hash', 16);
            $table->unsignedTinyInteger('hash_algo_version')->default(1);
            $table->timestamp('last_hashed_at');
            $table->timestamps();

            $table->unique('path_hash');
            $table->index('image_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_hash_cache');
    }
};
