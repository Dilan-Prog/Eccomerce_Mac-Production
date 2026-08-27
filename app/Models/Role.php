<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_system',
        'description',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(RoleModulePermission::class);
    }

    /** Module keys this role can access (only meaningful for non-system roles — see ModuleAccessMiddleware). */
    public function allowedModuleKeys(): array
    {
        return $this->permissions()->where('can_access', true)->pluck('module_key')->all();
    }

    /**
     * Whether this role can perform $action on $moduleKey. For module keys
     * outside RoleModulePermission::GRANULAR_MODULE_KEYS, $action is
     * ignored and this degrades to the legacy single can_access gate (same
     * result allowedModuleKeys()/canAccessModule() would give).
     */
    public function canPerformAction(string $moduleKey, string $action): bool
    {
        $permission = $this->permissions()->where('module_key', $moduleKey)->first();

        if (!$permission || !$permission->can_access) {
            return false;
        }

        if (!in_array($moduleKey, RoleModulePermission::GRANULAR_MODULE_KEYS, true)) {
            return true;
        }

        $column = 'can_' . $action;

        return (bool) ($permission->{$column} ?? true);
    }
}
