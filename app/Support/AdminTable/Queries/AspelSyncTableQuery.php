<?php

namespace App\Support\AdminTable\Queries;

use App\Models\AspelSync;
use App\Models\PrecioXProductAspel;
use App\Support\AdminTable\AdminTableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Replaces App\DataTables\AspelSyncDataTable.
 *
 * Read-only listing of products synced from Aspel SAE (external inventory
 * system). There is no create/edit/delete UI for these rows in the admin
 * panel — the only writer is AspelSyncController::sync(), an API endpoint
 * called by the external Aspel sync tool — so this table has no "actions"
 * column, no rowActions(), and no bulk delete.
 */
class AspelSyncTableQuery extends AdminTableQuery
{
    private const CURRENCY_META = [
        '1' => 'MXN',
        '2' => 'USD',
    ];

    public function baseQuery(): Builder
    {
        // Mirrors AspelSyncDataTable::query() exactly: pivot the per-price-tier
        // rows in precio_x_product_aspel (cve_precio 1-4) into four columns.
        $priceSub = PrecioXProductAspel::select('cve_art')
            ->selectRaw('MAX(CASE WHEN cve_precio = 1 THEN precio END) AS precio_publico')
            ->selectRaw('MAX(CASE WHEN cve_precio = 2 THEN precio END) AS precio_minimo')
            ->selectRaw('MAX(CASE WHEN cve_precio = 3 THEN precio END) AS precio_liquidacion')
            ->selectRaw('MAX(CASE WHEN cve_precio = 4 THEN precio END) AS precio_mayorista')
            ->groupBy('cve_art');

        return AspelSync::query()
            ->select(
                'aspel_products.*',
                'pxp.precio_publico',
                'pxp.precio_minimo',
                'pxp.precio_liquidacion',
                'pxp.precio_mayorista'
            )
            ->leftJoinSub($priceSub, 'pxp', 'pxp.cve_art', '=', 'aspel_products.cve_art');
    }

    public function columns(): array
    {
        return [
            [
                'key' => 'cve_art',
                'label' => 'CVE_ART',
                'type' => 'mono',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'descr',
                'label' => 'Descripción',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'nombre',
                'label' => 'Nombre Alias',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'key' => 'precio_publico',
                'label' => 'Precio Público',
                'type' => 'mono',
                'sortable' => true,
                'render' => fn (Model $row) => number_format(round((float) $row->precio_publico, 2), 2, '.', ','),
            ],
            [
                'key' => 'precio_minimo',
                'label' => 'Precio Mínimo',
                'type' => 'mono',
                'sortable' => true,
                'render' => fn (Model $row) => number_format((float) $row->precio_minimo, 2, '.', ','),
            ],
            [
                'key' => 'precio_liquidacion',
                'label' => 'Precio Liquidación',
                'type' => 'mono',
                'sortable' => true,
                'render' => fn (Model $row) => number_format((float) $row->precio_liquidacion, 2, '.', ','),
            ],
            [
                'key' => 'precio_mayorista',
                'label' => 'Precio Mayorista',
                'type' => 'mono',
                'sortable' => true,
                'render' => fn (Model $row) => number_format((float) $row->precio_mayorista, 2, '.', ','),
            ],
            [
                'key' => 'num_mon',
                'label' => 'Moneda',
                'sortable' => true,
                'render' => fn (Model $row) => self::CURRENCY_META[$row->num_mon] ?? $row->num_mon,
            ],
            [
                'key' => 'exist',
                'label' => 'Stock',
                'type' => 'mono',
                'sortable' => true,
                'render' => fn (Model $row) => (string) (int) $row->exist,
            ],
        ];
    }

    /**
     * Mirrors AspelSyncDataTable's ->filter() closure exactly: free-text
     * search across cve_art, descr and nombre.
     */
    public function search(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('aspel_products.cve_art', 'like', "%{$term}%")
                ->orWhere('aspel_products.descr', 'like', "%{$term}%")
                ->orWhere('aspel_products.nombre', 'like', "%{$term}%");
        });
    }
}
