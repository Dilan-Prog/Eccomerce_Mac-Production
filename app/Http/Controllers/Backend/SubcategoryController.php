<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChildCategory;
use App\Models\Subcategory;
use App\Models\Category;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\SubcategoryTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Str;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.sub-category.index');
    }

    /** JSON data source for the custom admin table (replaces SubcategoryDataTable). */
    public function tableData(Request $request, SubcategoryTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every sub categoria matching the current filter/search. */
    public function export(Request $request, SubcategoryTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Sub Categorias',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('sub-categorias.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "sub-categorias.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $subCategories = Subcategory::whereIn('id', $ids)->get();
            $deleted = 0;
            $blocked = 0;
            foreach ($subCategories as $subCategory) {
                $childCategoryCount = ChildCategory::where('sub_category_id', $subCategory->id)->count();
                if ($childCategoryCount > 0) {
                    $blocked++;
                    continue;
                }
                $subCategory->delete();
                $deleted++;
            }
            Cache::forget('nav_categories');
            Cache::forget('categories_filter_tree');

            if ($blocked > 0 && $deleted === 0) {
                return response()->json(['status' => 'error', 'message' => 'Ninguna Sub Categoria pudo eliminarse porque contienen Categorias hijas.']);
            }

            $message = $deleted . ' sub categoria(s) eliminada(s).';
            if ($blocked > 0) {
                $message .= ' ' . $blocked . ' omitida(s) por contener Categorias hijas.';
            }

            return response()->json(['status' => 'success', 'message' => $message]);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $categories = Category::all();
        return view('admin.sub-category.create',compact('categories'));
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        $categories = Category::all();
        return view('admin-ui.sub-category._form', compact('categories'));
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $categories = Category::all();
        $subCategory = Subcategory::findOrFail($id);
        return view('admin-ui.sub-category._form', compact('subCategory', 'categories'));
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
        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Sub Categoria creada con éxito.']);
        }

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
        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Sub Categoria actualizada con éxito.']);
        }

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
        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
    public function changeStatus(Request $request){

        $category = Subcategory::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();
        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        return response(['message' =>'Status Changed Successfully!']);
    }
}
