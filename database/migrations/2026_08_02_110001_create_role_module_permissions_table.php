<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('module_key');
            $table->boolean('can_access')->default(true);
            $table->timestamps();

            $table->unique(['role_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_module_permissions');
    }
};
