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
       Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->after('name');
            $table->string('company')->after('last_name');
            $table->string('rfc')->after('company')->unique();
            // llaver foranea para el tipo de precio
            // $table->enum('type_price', ['public', 'wholesale'])->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
