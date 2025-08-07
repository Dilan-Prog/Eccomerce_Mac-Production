<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductVariantItemDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantItem;
use Illuminate\Http\Request;

class ProductVariantItemController extends Controller
{
    public function index(ProductVariantItemDataTable $dataTable, $productId, $variantId) {

        $product = Product::findOrFail($productId);
        $variant = ProductVariant::findOrFail($variantId);
        return $dataTable->render('admin.product.product-variant-item.index', compact('product', 'variant'));

    }

    public function create(string $productId, string $variantId){

        $product = Product::findOrFail($productId);
        $variant = ProductVariant::findOrFail($variantId);
        return view('admin.product.product-variant-item.create', compact('variant', 'product'));

    }

    public function store(Request $request){

        $request->validate([
            'variant_id' => ['required', 'integer'],
            'name' => ['required', 'max:2000'],
            // 'price' => ['required', 'integer'],
            // 'qty' => ['required', 'integer'],
            // 'is_default' => ['required'],
            'status' => ['required']
        ]);

        $variantItem = new ProductVariantItem();
        $variantItem->product_variant_id = $request->variant_id;
        $variantItem->name = $request->name;
        // $variantItem->price = $request->price;
        // $variantItem->qty = $request->qty;
        // $variantItem->is_default = $request->is_default;
        $variantItem->status = $request->status;
        $variantItem->save();

        toastr('Creado con exito', 'success', 'success');

        return redirect()->route('admin.products-variant-item.index', ['productId' => $request->product_id, 'variantId' => $request->variant_id ]);

    }

    public function edit(string $variantItemId) {
        $variantItem = ProductVariantItem::findOrFail($variantItemId);
        return view('admin.product.product-variant-item.edit', compact('variantItem'));
    }


    public function update(Request $request, string $variantItemId){
        $request->validate([
            'name' => ['required', 'max:2000'],
            // 'price' => ['required', 'integer'],
            // 'qty' => ['required', 'integer'],
            // 'is_default' => ['required'],
            'status' => ['required']
        ]);

        $variantItem = ProductVariantItem::findOrFail($variantItemId);
        $variantItem->name = $request->name;
        // $variantItem->price = $request->price;
        // $variantItem->qty = $request->qty;
        // $variantItem->is_default = $request->is_default;
        $variantItem->status = $request->status;
        $variantItem->save();

        toastr('Actualizado con exito', 'success', 'success');

        return redirect()->route('admin.products-variant-item.index', ['productId' => $variantItem->productVariant->product_id, 'variantId' => $variantItem->product_variant_id]);

    }

    public function destroy(string $variantItemId) {
        $variantItem = ProductVariantItem::findOrFail($variantItemId);
        $variantItem->delete();

        return response(['status' => 'success', 'message' => "Eliminado con exito"]);
    }

    public function changeStatus(Request $request){

        $variantItem = ProductVariantItem::findOrFail($request->id);
        $variantItem->status = $request->status == 'true' ? 1 : 0;
        $variantItem->save();

        return response(['message' =>'Status Changed Successfully!']);

    }

}
