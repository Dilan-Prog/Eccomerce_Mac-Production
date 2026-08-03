<?php

namespace App\Support\AdminTable\Queries;

use App\Models\User;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Unified "Personal / Staff" listing — replaces the create-only
 * UserManageController (admin.manage-user) and the admin-only
 * AdminListController (admin.admin-list) with a single table covering every
 * non-customer, non-legacy-vendor role: admin, associate, technician.
 * ('vendor' was removed here too — StaffUserController's STAFF_ROLES no
 * longer includes it and 0 real users hold that role, so this listing/tab
 * would only ever show an empty "Vendedores" bucket going forward.)
 *
 * The `rol_personalizado` column only means something for role=admin
 * (User::customRole(), a nullable FK to a non-system Role — see
 * app/Models/Role.php / RoleModulePermission.php from the separate Roles
 * module effort); it renders '—' for every other role.
 *
 * The old AdminListController/AdminListTableQuery hardcoded an `id == 1`
 * "protect the super-admin" check. That is NOT reused here — `is_protected`
 * is a real, backfilled column (see
 * database/migrations/2026_08_02_110002_add_role_id_and_is_protected_to_users_table.php)
 * and is the only thing consulted below.
 */
class StaffUserTableQuery extends AdminTableQuery
{
    private const ROLE_BADGES = [
        'admin' => ['label' => 'Administrador', 'tone' => 'critical'],
        'associate' => ['label' => 'Asociado', 'tone' => 'success'],
        'technician' => ['label' => 'Técnico', 'tone' => 'warning'],
    ];

    public function baseQuery(): Builder
    {
        return User::query()->whereIn('role', ['admin', 'associate', 'technician']);
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
                'width' => 60,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'type' => 'person',
                'sortable' => true,
                'searchable' => true,
                'render' => fn (Model $row) => [
                    'title' => $row->name ?? '',
                    'subtitle' => $row->email ?? '',
                ],
            ],
            [
                'key' => 'role',
                'label' => 'Rol',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => self::ROLE_BADGES[$row->role] ?? ['label' => ucfirst($row->role), 'tone' => 'neutral'],
            ],
            [
                'key' => 'rol_personalizado',
                'label' => 'Rol personalizado',
                'render' => fn (Model $row) => $row->customRole?->name ?? '—',
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->status === 'active'
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Inactivo', 'tone' => 'critical'],
            ],
            [
                'key' => 'acciones',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    public function filters(): array
    {
        return [
            ['key' => 'todos', 'label' => 'Todos', 'apply' => fn (Builder $q) => $q],
            ['key' => 'administradores', 'label' => 'Administradores', 'apply' => fn (Builder $q) => $q->where('role', 'admin')],
            ['key' => 'asociados', 'label' => 'Asociados', 'apply' => fn (Builder $q) => $q->where('role', 'associate')],
            ['key' => 'tecnicos', 'label' => 'Técnicos', 'apply' => fn (Builder $q) => $q->where('role', 'technician')],
        ];
    }

    /** Matches name/email/id, same shape as CustomerTableQuery's override. */
    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('id', 'like', "%{$term}%");
        });
    }

    /**
     * "Editar" is always available. "Borrar" is omitted entirely for
     * protected accounts (is_protected=true) — mirrors the old id==1 hide,
     * but driven by the real column instead of a hardcoded id.
     */
    public function rowActions(Model $row): array
    {
        $actions = [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar personal',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-user-tie',
                    'fragmentUrl' => route('admin.staff-users.edit-fragment', $row->id),
                    'submitUrl' => route('admin.staff-users.update', $row->id),
                    'method' => 'PUT',
                    'sidebar' => false,
                ],
            ],
        ];

        if (!$row->is_protected) {
            $actions[] = [
                'label' => 'Borrar',
                'url' => route('admin.staff-users.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Rol', 'value' => self::ROLE_BADGES[$row->role]['label'] ?? $row->role],
                ],
            ];
        }

        return $actions;
    }
}
