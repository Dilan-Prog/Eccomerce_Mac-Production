<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SubcategoryDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChildCategory;
use App\Models\Subcategory;
use App\Models\Category;
use Str;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SubcategoryDataTable $dataTable)
    {
        return $dataTable->render('admin.sub-category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $categories = Category::all();
        return view('admin.sub-category.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category'=>['required'],
            'name'=>['required','max:200', 'unique:subcategories,name'],
            'status'=>['required']

        ]);

        $subCategory = new Subcategory();
        $subCategory->category_id = $request->category;
        $subCategory->name = $request->name;
        $subCategory->slug = Str::slug($request->name);
        $subCategory->status = $request->status;
        $subCategory->save();
        toastr('Sub Categoria Creada Con exito');
        return redirect()->route('admin.sub-category.index');
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
        $categories = Category::all();
        $subCategory = Subcategory::findOrFail($id);
        return view('admin.sub-category.edit', compact('subCategory','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'category'=>['required'],
            'name'=>['required','max:200', 'unique:subcategories,name,'.$id],
            'status'=>['required']

        ]);

        $subCategory = Subcategory::findOrFail($id);

        $subCategory->category_id = $request->category;
        $subCategory->name = $request->name;
        $subCategory->slug = Str::slug($request->name);
        $subCategory->status = $request->status;
        $subCategory->save();
        toastr('Sub Categoria Actualizada Con exito');
        return redirect()->route('admin.sub-category.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $subCategory = Subcategory::findOrFail($id);
        $childCategory = ChildCategory::where('sub_category_id',$subCategory->id)->count();

        if($childCategory > 0){
            return response(['status' => 'error', 'message' => 'Esta Categoria contiene Sub Categorias, Para eliminar esta Categoria elimina las Sub Categorias primero.']);     
        }
        $subCategory->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
    public function changeStatus(Request $request){

        $category = Subcategory::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();

        return response(['message' =>'Status Changed Successfully!']);
    }
}
