<?php

namespace App\Support\AdminTable\Queries;

use App\Models\Product;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces App\DataTables\ProductDataTable for the main Productos listing
 * (app/Http/Controllers/Backend/ProductController::index()).
 */
class ProductTableQuery extends AdminTableQuery
{
    /** Mirrors ProductDataTable::addColumn('type', ...) badge mapping exactly (labels kept verbatim). */
    private const TYPE_META = [
        'new_arrival' => ['label' => 'Nuevo', 'tone' => 'success'],
        'featured_product' => ['label' => 'Producto Favorito', 'tone' => 'warning'],
        'top_product' => ['label' => 'Producto Top', 'tone' => 'info'],
        'best_product' => ['label' => 'Mas Vendido', 'tone' => 'critical'],
    ];

    public function baseQuery(): Builder
    {
        return Product::query()->with(['category', 'brand']);
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'id',
                'label' => 'ID',
                'type' => 'mono',
                'sortable' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Nombre del Producto',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'sku',
                'label' => 'SKU',
                'type' => 'mono',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'productModel',
                'label' => 'Modelo',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'price',
                'label' => 'Precio',
                'type' => 'currency',
                'sortable' => true,
                // Mirrors ProductDataTable::addColumn('price', ...): manual price if
                // price_personalizated, otherwise Aspel price falling back to price.
                'render' => fn (Model $row) => $row->price_personalizated == 1
                    ? (float) $row->price
                    : (float) ($row->aspel_price ?? $row->price),
            ],
            [
                'key' => 'price_personalizated',
                'label' => 'Precio Personalizado(SAE)',
                'render' => fn (Model $row) => $row->price_personalizated == 1 ? 'Si' : 'No',
            ],
            [
                'key' => 'product_type',
                'label' => 'Tipo',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => self::TYPE_META[$row->product_type] ?? ['label' => 'Ninguno', 'tone' => 'purple'],
            ],
            [
                'key' => 'status',
                'label' => 'Estado',
                'type' => 'badge',
                'sortable' => true,
                'render' => fn (Model $row) => $row->status == 1
                    ? ['label' => 'Activo', 'tone' => 'success']
                    : ['label' => 'Inactivo', 'tone' => 'critical'],
            ],
            [
                'key' => 'category',
                'label' => 'Categoría',
                'render' => fn (Model $row) => $row->category->name ?? 'N/A',
            ],
            [
                'key' => 'brand',
                'label' => 'Marca',
                'render' => fn (Model $row) => $row->brand->name ?? 'N/A',
            ],
            [
                'key' => 'qty',
                'label' => 'Cantidad',
                'sortable' => true,
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
                    'title' => 'Editar producto',
                    'subtitle' => $row->name . ' (ID ' . $row->id . ')',
                    'icon' => 'fas fa-box',
                    'fragmentUrl' => route('admin.products.edit-fragment', $row->id),
                    'submitUrl' => route('admin.products.update', $row->id),
                    'method' => 'PUT',
                    'sidebar' => true,
                ],
            ],
            [
                'label' => 'Galeria de Imagenes',
                'url' => route('admin.products-image-gallery.index', ['product' => $row->id]),
            ],
            [
                'label' => 'Variantes de Producto',
                'url' => route('admin.products-variant.index', ['product' => $row->id]),
            ],
            [
                'label' => 'Combinaciones del producto',
                'url' => route('admin.products-variant-combinations.index', ['product' => $row->id]),
            ],
            [
                'label' => 'Agregar Mas Comercios',
                'url' => route('admin.products-more-eccomerce.index', ['product' => $row->id]),
            ],
            [
                'label' => 'Borrar',
                'url' => route('admin.products.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'Nombre', 'value' => $row->name],
                    ['label' => 'SKU', 'value' => $row->sku ?: 'N/A'],
                ],
            ],
        ];
    }
}
