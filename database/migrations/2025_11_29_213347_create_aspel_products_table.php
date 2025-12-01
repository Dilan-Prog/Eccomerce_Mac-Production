<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aspel_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('nombre')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock');
            $table->timestamp('remote_updated_at')->nullable();
            $table->string('sync_hash')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aspel_products');
    }
};
