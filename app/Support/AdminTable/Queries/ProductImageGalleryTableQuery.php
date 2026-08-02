<?php

namespace App\Support\AdminTable\Queries;

use App\Models\ProductImageGallery;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces app/DataTables/ProductImageGalleryDataTable.php.
 *
 * Index is scoped to a single Product. The old route/controller actually
 * scope this via a `?product=` query string (see ProductImageGalleryController
 * and app/DataTables/ProductDataTable.php's "Galleria de Imagenes" link) —
 * NOT via the `products-image-gallery/{productId}` path route, which is
 * unreachable in practice (its route name collides with, and is shadowed by,
 * Route::resource()'s plain index route; `php artisan route:list` confirms
 * its real registered name is `admin.admin.products-image-gallery.index`).
 * forProduct() mirrors the old query()'s `request()->product` filter.
 *
 * Note: the old table rendered an actual `<img>` thumbnail for the `image`
 * column. The AdminTable column-type set (text/mono/badge/date/currency/
 * person/actions — see public/admin-ui/js/table/column-types.js) has no
 * image renderer, so the thumbnail is intentionally not ported as a column
 * here (same call made in BrandTableQuery for the `logo` column); the image
 * path is still shown as plain text.
 */
class ProductImageGalleryTableQuery extends AdminTableQuery
{
    private ?int $productId = null;

    /** Scopes the listing to a single product, mirroring the old query()'s request()->product filter. */
    public function forProduct(int $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function baseQuery(): Builder
    {
        $query = ProductImageGallery::query();

        if ($this->productId !== null) {
            $query->where('product_id', $this->productId);
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
                'width' => 80,
            ],
            [
                'key' => 'image',
                'label' => 'Imagen',
                'type' => 'mono',
                'searchable' => true,
            ],
            [
                'key' => 'actions',
                'label' => 'Acciones',
                'type' => 'actions',
            ],
        ];
    }

    public function rowActions(Model $row): array
    {
        return [
            [
                'label' => 'Borrar',
                'url' => route('admin.products-image-gallery.destroy', $row->id),
                'method' => 'DELETE',
                'tone' => 'critical',
                'confirm' => true,
                'summary' => [
                    ['label' => 'ID', 'value' => (string) $row->id],
                    ['label' => 'Imagen', 'value' => basename($row->image)],
                ],
            ],
        ];
    }
}
