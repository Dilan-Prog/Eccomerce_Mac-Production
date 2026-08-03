<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only meaningful for role='admin' users: null = full/legacy admin
            // (every admin that exists today), set = scoped to a custom role's
            // module_key permissions via ModuleAccessMiddleware.
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();

            // Replaces the id==1 magic-number check previously hardcoded in
            // AdminListController/AdminListTableQuery.
            $table->boolean('is_protected')->default(false)->after('status');
        });

        // Protect the earliest-created admin account rather than assuming id 1
        // exists — on this project's data, id 1 has since been deleted, so the
        // pre-existing "id==1 is the super-admin" convention was already
        // silently broken. The oldest surviving admin row is the closest
        // real-world equivalent of "the original super-admin."
        $originalAdminId = DB::table('users')->where('role', 'admin')->min('id');
        if ($originalAdminId) {
            DB::table('users')->where('id', $originalAdminId)->update(['is_protected' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('is_protected');
        });
    }
};
