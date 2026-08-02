<?php

namespace App\Support\AdminTable\Queries;

use App\Models\ProductVariantItem;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces ProductVariantItemDataTable. Index is scoped to a single
 * ProductVariant (via {productId}/{variantId} route params), so the
 * controller must call forVariant() before paginate()/exportRows().
 */
class ProductVariantItemTableQuery extends AdminTableQuery
{
    private ?int $productId = null;
    private ?int $variantId = null;

    /** Scopes the listing to a single product/variant, mirroring the old query()'s request()->variantId filter. */
    public function forVariant(int $productId, int $variantId): static
    {
        $this->productId = $productId;
        $this->variantId = $variantId;

        return $this;
    }

    public function baseQuery(): Builder
    {
        $query = ProductVariantItem::query()->with('productVariant');

        if ($this->variantId !== null) {
            $query->where('product_variant_id', $this->variantId);
        }

        return $query;
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
                'label' => 'Opcion de Variante',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'variant_name',
                'label' => 'Nombre de Variante',
                'render' => fn (Model $row) => $row->productVariant->name ?? '',
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
            ],
        ];
    }

    public function filters(): array
    {
        return [
            ['key' => 'todas', 'label' => 'Todas', 'apply' => fn (Builder $q) => $q],
            ['key' => 'activos', 'label' => 'Activo', 'apply' => fn (Builder $q) => $q->where('status', 1)],
            ['key' => 'inactivos', 'label' => 'Inactivo', 'apply' => fn (Builder $q) => $q->where('status', 0)],
        ];
    }

    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhereHas('productVariant', fn (Builder $v) => $v->where('name', 'like', "%{$term}%"));
        });
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Editar',
                'modal' => [
                    'title' => 'Editar item de variante',
                    'subtitle' => 'ID ' . $row->id,
                    'icon' => 'fas fa-layer-group',
                    'fragmentUrl' => route('admin.products-variant-item.edit-fragment', $row->id),
                    'submitUrl' => route('admin.products-variant-item.update', $row->id),
                    'method' => 'PUT',
                ],
            ],
            [
                'label' => 'Eliminar',
                'url' => route('admin.products-variant-item.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'Estado', 'value' => $row->status ? 'Activo' : 'Inactivo'],
                ],
            ],
        ];
    }
}
