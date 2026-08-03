<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Additive alteration of the existing `cotizaciones` table to support an
 * ERP-style admin quote builder on top of the existing customer
 * self-service flow. No existing column is removed and no existing row's
 * observable behavior changes:
 *  - `source` defaults to 'cliente', which is exactly what every existing
 *    row already represents (the current, unchanged customer flow).
 *  - `created_by_admin_id` defaults to NULL for every existing row.
 *  - `cotizacion_perfil_id` becomes nullable so an admin-authored quote can
 *    exist without a fiscal profile; `user_id` stays required, since every
 *    quote (client self-service or admin-authored) always references a
 *    real registered User.
 *  - `status` is converted from ENUM('generada') to a plain VARCHAR so
 *    future statuses ('borrador', 'finalizada', ...) never require another
 *    raw ENUM ALTER. Validation of allowed values happens in application
 *    code, not at the DB level.
 *
 * doctrine/dbal is NOT installed in this project (confirmed via
 * `composer show doctrine/dbal`), so Blueprint::change() cannot be used for
 * the cotizacion_perfil_id nullability change or the status type change.
 * Both use raw DB::statement(), mirroring the style used in
 * 2026_04_27_000001_add_technician_role_to_users_table.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('created_by_admin_id')->nullable()->after('cotizacion_perfil_id')
                ->constrained('users')->nullOnDelete();

            // 'cliente' = today's existing customer self-service flow (unchanged
            // behavior, and the correct default for every existing + future row
            // created by that flow); 'admin' = a staff member built this quote
            // by hand via the ERP quote builder.
            $table->string('source')->default('cliente')->after('created_by_admin_id');
        });

        // cotizacion_perfil_id: NOT NULL foreign key -> nullable foreign key.
        // The existing foreign key constraint (cascadeOnDelete to
        // cotizacion_perfiles) is preserved by a MODIFY; only nullability changes.
        DB::statement('ALTER TABLE `cotizaciones` MODIFY `cotizacion_perfil_id` BIGINT UNSIGNED NULL');

        // status: ENUM('generada') DEFAULT 'generada' -> VARCHAR(255) NOT NULL DEFAULT 'generada'.
        // Existing rows are unaffected (their only possible value, 'generada',
        // is preserved verbatim). New allowed application-level values going
        // forward: 'generada' (unchanged meaning), 'borrador' (admin quote
        // being built), 'finalizada' (admin quote locked + PDF generated).
        DB::statement("ALTER TABLE `cotizaciones` MODIFY `status` VARCHAR(255) NOT NULL DEFAULT 'generada'");
    }

    public function down(): void
    {
        // Revert status back to the original single-value ENUM.
        DB::statement("ALTER TABLE `cotizaciones` MODIFY `status` ENUM('generada') NOT NULL DEFAULT 'generada'");

        // Revert cotizacion_perfil_id back to NOT NULL. Note: if any admin-authored
        // row was created with a NULL cotizacion_perfil_id while this migration was
        // active, this rollback will fail until such rows are fixed or removed -
        // that is intentional, since silently coercing them to a fake profile id
        // would corrupt data.
        DB::statement('ALTER TABLE `cotizaciones` MODIFY `cotizacion_perfil_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['created_by_admin_id']);
            $table->dropColumn(['created_by_admin_id', 'source']);
        });
    }
};
