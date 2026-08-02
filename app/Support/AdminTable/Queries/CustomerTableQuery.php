<?php

namespace App\Support\AdminTable\Queries;

use App\Models\User;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces CustomerListDataTable (app/DataTables/CustomerListDataTable.php).
 * Row actions ("Ver CSF" / activar-desactivar / "Subir CSF") reuse the exact
 * same routes as the old Bootstrap view (admin.customer.status-change,
 * admin.customer.csf.upload, admin.customer.csf.view) — the interactive
 * behaviour for those three is wired up in
 * resources/views/admin-ui/customer/index.blade.php (custom click handlers +
 * a couple of lightweight modals reusing the au-modal-* component styles),
 * since none of it is a plain "navigate to a route" row action.
 */
class CustomerTableQuery extends AdminTableQuery
{
    private const TIPO_CLIENTE_LABELS = [
        'revendedor'  => 'Revendedor',
        'tecnico'     => 'Técnico',
        'empresa'     => 'Empresa',
        'contratista' => 'Contratista',
    ];

    public function baseQuery(): Builder
    {
        return User::query()->where('role', 'user');
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
                'render' => fn (Model $row) => [
                    'title' => $row->name ?? '',
                    'subtitle' => $row->email ?? '',
                ],
            ],
            [
                'key' => 'account_type',
                'label' => 'Tipo cuenta',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->account_type === 'b2b'
                    ? ['label' => 'B2B', 'tone' => 'info']
                    : ['label' => 'Personal', 'tone' => 'secondary'],
            ],
            [
                'key' => 'rfc',
                'label' => 'RFC',
                'type' => 'mono',
                'sortable' => true,
                'render' => fn (Model $row) => $row->rfc ? $row->rfc : '—',
            ],
            [
                'key' => 'tipo_cliente',
                'label' => 'Perfil',
                'sortable' => true,
                'render' => fn (Model $row) => $row->tipo_cliente
                    ? (self::TIPO_CLIENTE_LABELS[$row->tipo_cliente] ?? $row->tipo_cliente)
                    : '—',
            ],
            [
                'key' => 'csf_document',
                'label' => 'CSF',
                'type' => 'badge',
                'render' => function (Model $row) {
                    if (!$row->csf_path) {
                        return ['label' => 'Sin CSF', 'tone' => 'secondary'];
                    }
                    return $row->b2b_status === 'verified'
                        ? ['label' => 'Verificado', 'tone' => 'success']
                        : ['label' => 'Pendiente', 'tone' => 'warning'];
                },
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
                'key' => 'actions',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    public function filters(): array
    {
        return [
            ['key' => 'todos', 'label' => 'Todos', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activos', 'label' => 'Activos', 'apply' => fn (Builder $q) => $q->where('status', 'active')],
            ['key' => 'inactivos', 'label' => 'Inactivos', 'apply' => fn (Builder $q) => $q->where('status', 'inactive')],
            ['key' => 'b2b', 'label' => 'B2B', 'apply' => fn (Builder $q) => $q->where('account_type', 'b2b')],
            ['key' => 'csf_pendiente', 'label' => 'CSF Pendiente', 'apply' => fn (Builder $q) => $q->whereNotNull('csf_path')->where('b2b_status', 'pending')],
        ];
    }

    /** Mirrors the old Yajra default search across the searchable columns (id, name, email). */
    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('id', 'like', "%{$term}%");
        });
    }

    public function rowActions(Model $row): array
    {
        $actions = [];

        if ($row->csf_path) {
            $actions[] = [
                'label' => 'Ver CSF',
                'url' => route('admin.customer.csf.view', $row->id),
            ];
        }

        $actions[] = [
            'label' => $row->status === 'active' ? 'Desactivar' : 'Activar',
            'url' => '#toggle-status',
        ];

        $actions[] = [
            'label' => 'Subir CSF',
            'url' => '#upload-csf',
        ];

        return $actions;
    }
}
