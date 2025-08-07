<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductVariantCombinationsDataTables;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantCombinations;
use App\Models\ProductVariantItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductVariantCombinationsController extends Controller
{

public function index(Request $request, ProductVariantCombinationsDataTables $dataTable)
    {
        $product = Product::findOrFail($request->product);
        $dataTable->setProductId($request->product);
        return $dataTable->render('admin.product.product-variant-combinations.index', compact('product'));
    }

    public function create(Request $request)
{
    $product = Product::with(['variants.productVariantItems'])->findOrFail($request->product);
    $variantItems = $product->variants->flatMap(function($variant) {
        return $variant->productVariantItems;
    });

   return view('admin.product.product-variant-combinations.create', compact('product', 'variantItems'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'variants_item_ids' => ['required', 'array'],
            'variants_item_ids.*' => ['integer'],
            'product_id' => [ 'required', 'integer'],
            'sku' => ['required','string','max:255'],
            'price' => ['required'],
            'qty' => ['required', 'integer', 'max:255'],
            'is_default' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        $productVariantCombination = new ProductVariantCombinations();
        $productVariantCombination->name = $request->name;
        $productVariantCombination->variants_item_ids = json_encode($request->variants_item_ids);
        $productVariantCombination->product_id = $request->product_id;
        $productVariantCombination->sku = $request->sku;
        $productVariantCombination->price = $request->price;
        $productVariantCombination->qty = $request->qty;
        $productVariantCombination->is_default = $request->is_default;
        $productVariantCombination->status = $request->status;

        $productVariantCombination->save();

        toastr('Combinacion de Variantes Creada con Exito');

        return redirect()->route('admin.products-variant-combinations.index', ['product' => $productVariantCombination->product_id]);
    }

    public function edit($id)
    {
        $productVariantCombination = ProductVariantCombinations::findOrFail($id);
        $product = Product::with(['variants.productVariantItems'])->findOrFail($productVariantCombination->product_id);
        $variantItems = $product->variants->flatMap(function($variant) {
            return $variant->productVariantItems;
        });

        return view('admin.product.product-variant-combinations.edit', compact('productVariantCombination', 'product', 'variantItems'));
    }


    public function update(Request $request,string $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'variants_item_ids' => ['required', 'array'],
            'variants_item_ids.*' => ['integer'],
            'product_id' => [ 'required', 'integer'],
            'sku' => ['required','string','max:255'],
            'price' => ['required'],
            'qty' => ['required', 'integer', 'max:255'],
            'is_default' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        $productVariantCombination = ProductVariantCombinations::findOrFail($id);
        $productVariantCombination->name = $request->name;
        $productVariantCombination->variants_item_ids = json_encode($request->variants_item_ids);
        $productVariantCombination->product_id = $request->product_id;
        $productVariantCombination->sku = $request->sku;
        $productVariantCombination->price = $request->price;
        $productVariantCombination->qty = $request->qty;
        $productVariantCombination->is_default = $request->is_default;
        $productVariantCombination->status = $request->status;

        $productVariantCombination->save();

        toastr('Combinacion de Variantes Actualizada con Exito');

        return redirect()->route('admin.products-variant-combinations.index', ['product' => $productVariantCombination->product_id]);
    }

    public function destroy(string $id)
    {
        $productVariantCombination = ProductVariantCombinations::findOrFail($id);
        $productVariantCombination->delete();
        toastr('Combinacion de Variantes Eliminada con Exito');
        return response(['status' => 'success', 'message' => "Eliminado con exito"]);
    }


}
