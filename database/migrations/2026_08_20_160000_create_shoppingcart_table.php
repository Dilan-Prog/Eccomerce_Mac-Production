<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla que ya espera el paquete anayarojo/shoppingcart (Cart::store()/
 * restore()/erase()) para persistir el carrito entre navegadores — nunca se
 * había publicado en este proyecto. Mismo shape que el stub del paquete en
 * vendor/anayarojo/shoppingcart/src/Database/migrations/2018_12_23_120000_create_shoppingcart_table.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('cart.database.table'), function (Blueprint $table) {
            $table->string('identifier');
            $table->string('instance');
            $table->longText('content');
            $table->nullableTimestamps();
            $table->primary(['identifier', 'instance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('cart.database.table'));
    }
};
