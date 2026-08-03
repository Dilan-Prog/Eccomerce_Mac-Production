<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_system')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed the 5 existing system roles so users.role_id can reference them
        // for FK integrity, even though system roles are unrestricted (see
        // ModuleAccessMiddleware) and are never edited/deleted from the Roles UI.
        $now = now();
        DB::table('roles')->insert([
            ['name' => 'Administrador', 'slug' => 'admin', 'is_system' => true, 'description' => 'Acceso completo al panel administrativo.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Vendedor', 'slug' => 'vendor', 'is_system' => true, 'description' => 'Panel de vendedor.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cliente', 'slug' => 'user', 'is_system' => true, 'description' => 'Cuenta de cliente.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Asociado', 'slug' => 'associate', 'is_system' => true, 'description' => 'Panel de asociado.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Técnico', 'slug' => 'technician', 'is_system' => true, 'description' => 'Panel de técnico.', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
