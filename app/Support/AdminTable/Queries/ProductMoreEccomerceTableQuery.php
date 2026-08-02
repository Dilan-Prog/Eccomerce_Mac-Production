<?php

namespace App\Support\AdminTable\Queries;

use App\Models\ProductMoreEccomerce;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces ProductMoreEccomerceDataTable. Listing is always scoped to a
 * single parent product (the old class read `request()->product` directly;
 * here the controller passes it in explicitly via setProductId()).
 */
class ProductMoreEccomerceTableQuery extends AdminTableQuery
{
    private int $productId;

    public function setProductId(int $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function baseQuery(): Builder
    {
        return ProductMoreEccomerce::query()->where('product_id', $this->productId);
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'nameEccomerce',
                'label' => 'Nombre del Comercio',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'linkProduct',
                'label' => 'Link del Producto',
                'width' => 420,
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->status == 1
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
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activos', 'label' => 'Activos', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'inactivos', 'label' => 'Inactivos', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar opción de comercio',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-store',
                    'fragmentUrl' => route('admin.products-more-eccomerce.edit-fragment', $row->id),
                    'submitUrl' => route('admin.products-more-eccomerce.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.products-more-eccomerce.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Comercio', 'value' => $row->nameEccomerce],
                    ['label' => 'Link', 'value' => $row->linkProduct],
                ],
            ],
        ];
    }
}
