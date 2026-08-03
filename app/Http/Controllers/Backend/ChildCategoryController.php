<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ChildCategoryDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChildCategory;
use App\Models\Subcategory;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\ChildCategoryTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Str;

class ChildCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:categories');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.child-category.index');
    }

    /** JSON data source for the custom admin table (replaces ChildCategoryDataTable). */
    public function tableData(Request $request, ChildCategoryTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every child category matching the current filter/search. */
    public function export(Request $request, ChildCategoryTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Categorias Secundarias',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('categorias-secundarias.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "categorias-secundarias.{$extension}", $writerType);
    }

    /**
     * Bulk actions from the table's multi-select bar. Mirrors destroy()'s
     * side-effect check: rows still referenced by a Product are skipped
     * instead of deleted.
     */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $childCategories = ChildCategory::whereIn('id', $ids)->get();
            $deleted = 0;
            $skipped = 0;
            foreach ($childCategories as $childCategory) {
                if (Product::where('child_category_id', $childCategory->id)->count() > 0) {
                    $skipped++;
                    continue;
                }
                $childCategory->delete();
                $deleted++;
            }
            Cache::forget('nav_categories');
            Cache::forget('categories_filter_tree');

            $message = $deleted . ' categoria(s) secundaria(s) eliminada(s).';
            if ($skipped > 0) {
                $message .= ' ' . $skipped . ' no se eliminaron por tener productos relacionados.';
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
        return view('admin.child-category.create', compact('categories'));
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        $categories = Category::all();
        $subCategories = Subcategory::all();
        return view('admin-ui.child-category._form', compact('categories', 'subCategories'));
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $childCategory = ChildCategory::findOrFail($id);
        $categories = Category::all();
        $subCategories = Subcategory::all();
        return view('admin-ui.child-category._form', compact('childCategory', 'categories', 'subCategories'));
    }

    /**
     * Get Sub Categories
     */
    public function getSubCategories(Request $request){
        $subCategories = SubCategory::where('category_id' , $request->id)->where('status',1)->get();
        return $subCategories;

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => ['required'],
            'sub_category'=> ['required'],
            'name' => ['required','max:200','unique:child_categories,name'],
            'status' => ['required']
        ]);

        $childCategory = new ChildCategory();
        $childCategory->category_id = $request->category;
        $childCategory->sub_category_id = $request->sub_category;
        $childCategory->name = $request->name;
        $childCategory->slug = Str::slug($request->name);
        $childCategory->status = $request->status;
        $childCategory->save();
        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Categoria Secundaria creada con éxito.']);
        }

        toastr('Categoria Secundaria Creada Con Exito');
        return redirect()->route('admin.child-category.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $categories = Category::all();
        $childCategory = ChildCategory::findOrFail($id);
        $subCategories = Subcategory::where('category_id', $childCategory->category_id)->get();
        return view('admin.child-category.edit',compact('childCategory','categories' , 'subCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'category' => ['required'],
            'sub_category'=> ['required'],
            'name' => ['required','max:200','unique:child_categories,name,'.$id],
            'status' => ['required']
        ]);

        $childCategory = ChildCategory::findOrFail($id);

        $childCategory->category_id = $request->category;
        $childCategory->sub_category_id = $request->sub_category;
        $childCategory->name = $request->name;
        $childCategory->slug = Str::slug($request->name);
        $childCategory->status = $request->status;
        $childCategory->save();
        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Categoria Secundaria actualizada con éxito.']);
        }

        toastr('Child Categoria Actualizada Con Exito');
        return redirect()->route('admin.child-category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $childCategory = ChildCategory::findOrFail($id);
        if(Product::where('child_category_id', $childCategory->id)->count() > 0){
            return response(['status' => 'error', 'message' => 'This item contain relation can\'t delete it.']);
        }
        $childCategory->delete();
        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }
    public function changeStatus(Request $request){

        $category = ChildCategory::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();
        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        return response(['message' =>'Status Changed Successfully!']);
    }
}
