<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\AssociateProductDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class AssociateProductController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productAssociate = Product::with(['brand', 'productImageGalleries'])
            ->where('status', 1)
            ->paginate(25);

        $skus = collect($productAssociate->items())
            ->pluck('sku')
            ->filter()
            ->values();

        $aspelPrecio = DB::table('precio_x_product_aspel')
            ->whereIn('cve_art', $skus)
            ->where('cve_precio', 2)
            ->pluck('precio', 'cve_art'); // [sku => precio]

        $aspelMoneda = DB::table('aspel_products')
            ->whereIn('cve_art', $skus)
            ->pluck('num_mon', 'cve_art'); // [sku => num_mon]

        return view('associate.product.index', compact('productAssociate', 'aspelPrecio', 'aspelMoneda'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['brand', 'productImageGalleries']);

        $sku = $product->sku;

        // 1) Precio Aspel por SKU
        $aspelPrecio = DB::table('precio_x_product_aspel')
            ->where('cve_art', $sku)
            ->where('cve_precio', 2)
            ->value('precio'); // solo 1 valor

        // 2) Moneda Aspel por SKU
        $numMon = DB::table('aspel_products')
            ->where('cve_art', $sku)
            ->value('num_mon');

        $moneda = ((int)$numMon === 2) ? 'USD' : 'MXN';

        // 3) Misma regla que tu @php
        $precioFinal = ($product->price_personalizated == 1)
            ? $product->price
            : ($aspelPrecio ?? $product->price);

        $stockUsado = ((int)$product->qty_personalizated === 1)
        ? (int)$product->qty
        : (int)$product->qty_aspel;

        // 5) Mensaje (sin mostrar el número)
        $shippingText = $stockUsado > 0
            ? 'Disponible para envío inmediato'
            : 'Tiempo de Entrega: 3 a 4 semanas';

        return response()->json([
            'id' => $product->id,
            'type' => $product->product_type,
            'name' => $product->name,
            'sku' => $sku,
            'model' => $product->productModel,
            'brand' => $product->brand->name,
            'qty' => $stockUsado,
            'price' => (float)$precioFinal,
            'price_formatted' => number_format((float)$precioFinal, 2, ',', '.'),
            'currency' => $moneda,
            'shipping_text' => $shippingText,
            'thumb' => asset($product->thumb_image),
            'description' => $product->long_description ?? $product->name,
            'gallery' => $product->productImageGalleries
                ->map(fn($g) => asset($g->image))
                ->values(),
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }
}
