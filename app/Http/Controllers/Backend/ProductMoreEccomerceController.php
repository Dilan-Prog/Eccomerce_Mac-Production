<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductMoreEccomerceDataTable;
use App\Http\Controllers\Controller;
use App\Models\ProductMoreEccomerce;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductMoreEccomerceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProductMoreEccomerceDataTable $dataTable)
    {
        $product = Product::findOrFail($request->product);
        return $dataTable->render('admin.product.more-eccomerce.index' , compact('product'));

    }


    public function create()
    {
        
        return view('admin.product.more-eccomerce.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        request()->validate([
            'nameEccomerce' => ['required', 'max:200'],
            'linkProduct' => ['required'],
            'status' => ['required']
        ]);
        
        $product = Product::findOrFail($request->product);
        $productMoreEccomerce = new ProductMoreEccomerce();
        $productMoreEccomerce->nameEccomerce = $request->nameEccomerce;
        $productMoreEccomerce->linkProduct = $request->linkProduct;
        $productMoreEccomerce->product_id = $request->product;
        $productMoreEccomerce->status = $request->status;
        $productMoreEccomerce->save();

        toastr('Creado con Exito');

        return redirect()->route('admin.products-more-eccomerce.index', ['product' => $product->id]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $productMoreEccomerce = ProductMoreEccomerce::findOrFail($id);
        return view('admin.product.more-eccomerce.edit', compact('productMoreEccomerce'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    request()->validate([
        'nameEccomerce' => ['required', 'max:200'],
        'linkProduct' => ['required'],
        'status' => ['required']
    ]);

    $productMoreEccomerce = ProductMoreEccomerce::findOrFail($id);
    $productMoreEccomerce->nameEccomerce = $request->nameEccomerce;
    $productMoreEccomerce->linkProduct = $request->linkProduct;
    $productMoreEccomerce->status = $request->status;
    $productMoreEccomerce->save();

    toastr('Actualizado con Exito');

    return redirect()->route('admin.products-more-eccomerce.index', ['product' => $productMoreEccomerce->product_id]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productMoreEccomerce = ProductMoreEccomerce::findOrFail($id);
         $productMoreEccomerce->delete();

        toastr('Eliminado con Exito');

        return redirect()->route('admin.products-more-eccomerce.index', ['product' => $productMoreEccomerce->product_id]);
    }
    //Change Status
    public function changeStatus(Request $request){

        $category = ProductMoreEccomerce::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();

        return response(['message' =>'Estado Cambido con Exito']);

    }

}
