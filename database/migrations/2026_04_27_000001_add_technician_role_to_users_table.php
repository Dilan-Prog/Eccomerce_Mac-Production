<?php
// database/migrations/2026_04_27_000001_add_technician_role_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','vendor','user','associate','technician') DEFAULT 'user';");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','vendor','user','associate') DEFAULT 'user';");
    }
};
