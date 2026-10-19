<?php

namespace App\Support\AdminTable\Queries;

use App\Models\MarketingApiToken;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MarketingApiTokenTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return MarketingApiToken::query();
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'name',
                'label' => 'Nombre',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'key_id',
                'label' => 'Key ID',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'scope',
                'label' => 'Uso',
                'type' => 'badge',
                'render' => fn (Model $row) => [
                    'label' => $row->useLabel(),
                    'tone' => $row->scope === MarketingApiToken::SCOPE_ATTRIBUTION ? 'warning' : 'info',
                ],
            ],
            [
                'key' => 'secret_last_four',
                'label' => 'Secreto',
                'type' => 'mono',
                'render' => fn (Model $row) => '••••' . $row->secret_last_four,
            ],
            [
                'key' => 'last_used_at',
                'label' => 'Último uso',
                'type' => 'date',
                'sortable' => true,
                'render' => fn (Model $row) => $row->last_used_at ? $row->last_used_at->format('d/m/Y H:i') : 'Nunca',
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->status
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Revocado', 'tone' => 'critical'],
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
        $filters = [
            ['key' => 'todos', 'label' => 'Todos', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activos', 'label' => 'Activo', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'revocados', 'label' => 'Revocado', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];

        // Los filtros por uso solo existen si la columna `scope` ya está migrada: el listado calcula el
        // conteo de TODOS los filtros en cada carga y, sin la columna, se caería (500) antes de migrar.
        if (Schema::hasColumn('marketing_api_tokens', 'scope')) {
            $filters[] = ['key' => 'marketing', 'label' => 'Email Marketing', 'apply' => fn (Builder $q) => $q->forUse(MarketingApiToken::SCOPE_MARKETING)];
            $filters[] = ['key' => 'atribucion', 'label' => 'Atribución', 'apply' => fn (Builder $q) => $q->forUse(MarketingApiToken::SCOPE_ATTRIBUTION)];
        }

        return $filters;
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar token',
                    'subtitle' => $row->name,
                    'icon' => 'fas fa-key',
                    'fragmentUrl' => route('admin.marketing-tokens.edit-fragment', $row->id),
                    'submitUrl' => route('admin.marketing-tokens.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Regenerar',
                'url' => route('admin.marketing-tokens.regenerate', $row->id),
                'method' => 'POST',
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.marketing-tokens.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                ],
            ],
        ];
    }
}
