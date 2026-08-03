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
}
