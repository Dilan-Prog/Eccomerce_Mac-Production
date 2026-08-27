<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permisos por acción (Ver/Crear/Editar/Borrar/Exportar), hoy solo
 * consultados/editables para los módulos en
 * App\Models\RoleModulePermission::GRANULAR_MODULE_KEYS ('aspel',
 * 'aspel-integracion', 'cotizaciones') — el resto de los módulos sigue
 * usando únicamente `can_access` como antes. Se agregan a todas las filas
 * (no solo esos 3 módulos) por simplicidad; default(true) evita que roles
 * ya existentes con can_access=true pierdan acceso de golpe al migrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_module_permissions', function (Blueprint $table) {
            $table->boolean('can_view')->default(true)->after('can_access');
            $table->boolean('can_create')->default(true)->after('can_view');
            $table->boolean('can_edit')->default(true)->after('can_create');
            $table->boolean('can_delete')->default(true)->after('can_edit');
            $table->boolean('can_export')->default(true)->after('can_delete');
        });
    }

    public function down(): void
    {
        Schema::table('role_module_permissions', function (Blueprint $table) {
            $table->dropColumn(['can_view', 'can_create', 'can_edit', 'can_delete', 'can_export']);
        });
    }
};
