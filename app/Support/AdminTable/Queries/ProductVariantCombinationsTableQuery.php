<?php

namespace App\Support\AdminTable\Queries;

use App\Models\ProductVariantCombinations;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces App\DataTables\ProductVariantCombinationsDataTables.
 *
 * Note: the old DataTable class accepted a productId (via setProductId(),
 * called from the controller with the ?product= query param) but never
 * actually used it in query() — the table always listed EVERY combination
 * for EVERY product, unfiltered. That behavior is preserved as-is here
 * (baseQuery() has no product scoping) per the "port bugs/business rules
 * exactly" migration rule; the $product model is still resolved in the
 * controller purely for the page header / "+ Crear Nuevo" link context.
 */
class ProductVariantCombinationsTableQuery extends AdminTableQuery
{
    public function baseQuery(): Builder
    {
        return ProductVariantCombinations::query()->with('product');
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'Id',
                'type' => 'mono',
                'sortable' => true,
                'width' => 60,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre Combinacion',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'sku',
                'label' => 'Clave SKU',
                'type' => 'mono',
                'sortable' => true,
                'searchable' => true,
                'width' => 125,
            ],
            [
                'key' => 'price',
                'label' => 'Precio',
                'type' => 'currency',
                'sortable' => true,
                'width' => 100,
            ],
            [
                'key' => 'qty',
                'label' => 'Stock',
                'sortable' => true,
                'width' => 100,
            ],
            [
                'key' => 'is_default',
                'label' => 'Por Defecto',
                'render' => fn (Model $row) => $row->is_default ? 'Si' : 'No',
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'render' => fn (Model $row) => $row->status
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Inactivo', 'tone' => 'critical'],
            ],
            [
                'key' => 'actions',
                'label' => 'Acciones',
                'type' => 'actions',
                'width' => 250,
            ],
        ];
    }

    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar combinación',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-layer-group',
                    'fragmentUrl' => route('admin.products-variant-combinations.edit-fragment', $row->id),
                    'submitUrl' => route('admin.products-variant-combinations.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Eliminar',
                'url' => route('admin.products-variant-combinations.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'SKU', 'value' => $row->sku],
                ],
            ],
        ];
    }
}
