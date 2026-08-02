<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_image_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duplicate_image_group_id')->constrained('duplicate_image_groups')->cascadeOnDelete();
            $table->char('path_hash', 32);
            $table->text('physical_path');
            $table->char('image_hash', 16);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->unique(['duplicate_image_group_id', 'path_hash'], 'dup_group_members_group_path_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_image_group_members');
    }
};
