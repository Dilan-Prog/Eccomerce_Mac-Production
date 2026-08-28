<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleModulePermission extends Model
{
    /** Módulos con permisos por acción (los demás solo usan can_access). */
    public const GRANULAR_MODULE_KEYS = ['aspel', 'aspel-integracion', 'cotizaciones', 'marketing-integracion'];

    public const GRANULAR_ACTIONS = ['view', 'create', 'edit', 'delete', 'export'];

    public const GRANULAR_ACTION_LABELS = [
        'view' => 'Ver',
        'create' => 'Crear',
        'edit' => 'Editar',
        'delete' => 'Borrar',
        'export' => 'Exportar',
    ];

    protected $fillable = [
        'role_id',
        'module_key',
        'can_access',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'can_export',
    ];

    protected $casts = [
        'can_access' => 'boolean',
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_export' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
