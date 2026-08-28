<?php

namespace App\Support\AdminTable\Queries;

use App\Models\EmailTemplate;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return EmailTemplate::query()->with('category:id,name');
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
                'width' => 80,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'subject',
                'label' => 'Asunto',
                'searchable' => true,
            ],
            [
                'key' => 'category',
                'label' => 'Categoría',
                'render' => fn (Model $row) => $row->category->name ?? 'General / todas',
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->status
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Inactivo', 'tone' => 'critical'],
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
            ['key' => 'activas', 'label' => 'Activa', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'inactivas', 'label' => 'Inactiva', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'url' => route('admin.email-templates.edit', $row->id),
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.email-templates.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Categoría', 'value' => $row->category->name ?? 'General / todas'],
                ],
            ],
        ];
    }
}
