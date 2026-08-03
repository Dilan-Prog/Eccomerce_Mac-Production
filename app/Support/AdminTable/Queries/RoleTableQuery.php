<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Role;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Powers the "Roles y Permisos" admin-ui listing (custom roles + the 5
 * system roles seeded alongside the roles/permissions feature).
 */
class RoleTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return Role::query();
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
                'width' => 100,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'tipo',
                'label' => 'Tipo',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->is_system
                    ? ['label' => 'Sistema', 'tone' => 'info']
                    : ['label' => 'Personalizado', 'tone' => 'neutral'],
            ],
            [
                'key' => 'modulos',
                'label' => 'Módulos',
                'render' => function (Model $row) {
                    if ($row->is_system) {
                        return 'Acceso completo';
                    }
                    $labels = config('admin-modules');
                    return collect($row->allowedModuleKeys())
                        ->map(fn ($key) => $labels[$key] ?? $key)
                        ->implode(', ');
                },
            ],
            [
                'key' => 'acciones',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    public function rowActions(Model $row): array
    {
        $actions = [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar rol',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-user-shield',
                    'fragmentUrl' => route('admin.roles.edit-fragment', $row->id),
                    'submitUrl' => route('admin.roles.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
        ];

        if (!$row->is_system) {
            $actions[] = [
                'label' => 'Borrar',
                'url' => route('admin.roles.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Tipo', 'value' => 'Personalizado'],
                ],
            ];
        }

        return $actions;
    }
}
