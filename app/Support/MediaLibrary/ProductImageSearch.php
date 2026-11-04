<?php

namespace App\Support\MediaLibrary;

use App\Models\Product;
use App\Models\ProductImageGallery;

/**
 * Busca imagenes de la biblioteca de medios POR PRODUCTO: dado un texto ("DC1010 honeywell", un SKU, un modelo...)
 * devuelve las rutas relativas de las imagenes (la principal y las de la galeria) de los productos cuyo nombre, SKU,
 * modelo (productModel) o marca coinciden. Cada palabra del texto debe coincidir con algun campo (AND entre palabras,
 * OR entre campos), asi "dc1010 honeywell" encuentra el DC1010 aunque la marca y el modelo esten en campos distintos.
 *
 * Lo usa MediaLibraryController::data() para que el buscador del selector de imagenes (y el de la biblioteca) no se
 * limite al nombre del archivo, que casi nunca lleva el nombre del producto.
 */
class ProductImageSearch
{
    /** Maximo de palabras del texto y de productos considerados (evita consultas enormes con textos de una letra). */
    public const MAX_TERMS = 6;
    public const MAX_PRODUCTS = 300;

    /**
     * @return array<string,array{name:?string,sku:?string,brand:?string,model:?string}> ruta relativa => producto
     */
    public function match(string $search): array
    {
        $terms = $this->terms($search);
        if ($terms === []) {
            return [];
        }

        $query = Product::query()
            ->with('brand:id,name')
            ->select(['id', 'name', 'sku', 'productModel', 'brand_id', 'thumb_image']);

        foreach ($terms as $term) {
            $like = '%' . $this->escapeLike($term) . '%';
            // ESCAPE '!' (no '\'): funciona igual en MySQL y SQLite, y deja que un % o _ del texto valgan como letras.
            $query->where(function ($w) use ($like) {
                $w->whereRaw("name LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("sku LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("productModel LIKE ? ESCAPE '!'", [$like])
                    ->orWhereHas('brand', fn ($b) => $b->whereRaw("name LIKE ? ESCAPE '!'", [$like]));
            });
        }

        $products = $query->orderByDesc('id')->limit(self::MAX_PRODUCTS)->get();
        if ($products->isEmpty()) {
            return [];
        }

        $hits = [];
        $info = fn (Product $p) => [
            'name' => $p->name,
            'sku' => $p->sku,
            'brand' => $p->brand?->name,
            'model' => $p->productModel,
        ];

        foreach ($products as $p) {
            $path = $this->relativePath((string) $p->thumb_image);
            if ($path !== '' && !isset($hits[$path])) {
                $hits[$path] = $info($p);
            }
        }

        $byId = $products->keyBy('id');
        foreach (ProductImageGallery::whereIn('product_id', $byId->keys())->get(['product_id', 'image']) as $row) {
            $path = $this->relativePath((string) $row->image);
            if ($path !== '' && !isset($hits[$path]) && isset($byId[$row->product_id])) {
                $hits[$path] = $info($byId[$row->product_id]);
            }
        }

        return $hits;
    }

    /**
     * Ruta relativa a la carpeta de uploads de un valor guardado en la BD. Productos guardan la URL COMPLETA
     * (p. ej. http://localhost/uploads/product/x.webp, a veces de otro host) y la galeria una ruta relativa: en
     * ambos casos se toma solo la parte de la ruta, sin host, sin "/" inicial ni query.
     */
    public function relativePath(string $stored): string
    {
        $stored = trim($stored);
        if ($stored === '') {
            return '';
        }
        $path = parse_url($stored, PHP_URL_PATH);

        return ltrim(is_string($path) ? rawurldecode($path) : $stored, '/');
    }

    /** @return list<string> */
    public function terms(string $search): array
    {
        $parts = preg_split('/\s+/u', mb_strtolower(trim($search))) ?: [];
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));

        return array_slice($parts, 0, self::MAX_TERMS);
    }

    private function escapeLike(string $value): string
    {
        // Caracter de escape '!' (ver ESCAPE '!' arriba): '!' primero, para no duplicar el escape de los demas.
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}
