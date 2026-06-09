<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CategoryDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Cache;
use Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CategoryDataTable $dataTable)
    {
        //
        return $dataTable->render('admin.category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'icon'       => ['nullable'],
            'name'       => ['required', 'max:200', 'unique:categories,name'],
            'status'     => ['required'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $category = new Category();
        $category->icon       = $request->icon;
        $category->name       = $request->name;
        $category->slug       = Str::slug($request->name);
        $category->status     = $request->status;
        $category->sort_order = $request->sort_order;
        $category->save();

        Cache::forget('nav_categories');
        toastr('Categoria Creada Con exito');
        return redirect()->route('admin.category.index');

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
        $category = Category::findOrFail($id);
        return view('admin.category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'icon'       => ['nullable'],
            'name'       => ['required', 'max:200', 'unique:categories,name,' . $id],
            'status'     => ['required'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $category = Category::findOrFail($id);

        $category->icon       = $request->icon;
        $category->name       = $request->name;
        $category->slug       = Str::slug($request->name);
        $category->status     = $request->status;
        $category->sort_order = $request->sort_order;
        $category->save();

        Cache::forget('nav_categories');
        toastr('Actualizacion con exito', 'success');
        return redirect()->route('admin.category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $subCategory = Subcategory::where('category_id',$category->id)->count();
        if($subCategory > 0){
            return response(['status' => 'error', 'message' => 'Esta Categoria contiene Sub Categorias, Para eliminar esta Categoria elimina las Sub Categorias primero.']);
        }

        $category->delete();
        Cache::forget('nav_categories');

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request){

        $category = Category::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();

        Cache::forget('nav_categories');

        return response(['message' =>'Status Changed Successfully!']);

    }
}
