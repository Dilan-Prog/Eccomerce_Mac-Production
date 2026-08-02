<?php

namespace App\Support\AdminTable\Queries;

use App\Models\User;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces App\DataTables\AdminListDataTable — lists users with role=admin.
 *
 * The old view rendered the "status" column as a live toggle-switch (checked
 * = active) that PUT-ed to admin.admin-list.status-change on change, and hid
 * both the status switch and the delete button entirely for user id 1 (the
 * protected super-admin). The shared admin-table column types
 * (public/admin-ui/js/table/column-types.js) don't include an inline toggle
 * control, so status is now shown as a read-only badge (Activo/Inactivo);
 * the status-change route/endpoint itself is untouched and still reachable.
 * The id-1 protection is preserved for the row action (no delete button
 * rendered for id 1), matching the old template exactly.
 */
class AdminListTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return User::query()->where('role', 'admin');
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'text',
                'sortable' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'type' => 'text',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'text',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'role',
                'label' => 'Rol',
                'type' => 'text',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->status === 'active'
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Inactivo', 'tone' => 'warning'],
            ],
            [
                'key' => 'action',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activos', 'label' => 'Activos', 'apply' => fn (Builder $q) => $q->where('status', 'active')],
            // Matches the "Inactivo" badge grouping below: the users.status enum also allows
            // 'blocked' (never surfaced as a distinct state by the old checkbox UI either,
            // which only ever rendered checked-for-'active' / unchecked-for-anything-else),
            // so any non-active status counts as "Inactivos" here too.
            ['key' => 'inactivos', 'label' => 'Inactivos', 'apply' => fn (Builder $q) => $q->where('status', '!=', 'active')],
        ];
    }

    /** Protected super-admin (id 1) never gets a delete button, matching the old Blade/DataTable template. */
    public function rowActions(Model $row): array
    {
        if ($row->id == 1) {
            return [];
        }

        return [
            [
                'label' => 'Borrar',
                'url' => route('admin.admin-list.destory', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
            ],
        ];
    }
}
