<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\CategoryTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.category.index');
    }

    /** JSON data source for the custom admin table (replaces CategoryDataTable). */
    public function tableData(Request $request, CategoryTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every category matching the current filter/search. */
    public function export(Request $request, CategoryTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Categorias',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('categorias.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "categorias.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $categories = Category::whereIn('id', $ids)->get();
            $deleted = 0;
            $blocked = 0;
            foreach ($categories as $category) {
                $subCategory = Subcategory::where('category_id', $category->id)->count();
                if ($subCategory > 0) {
                    $blocked++;
                    continue;
                }
                $category->delete();
                $deleted++;
            }

            Cache::forget('nav_categories');
            Cache::forget('categories_filter_tree');

            if ($blocked > 0) {
                return response()->json([
                    'status' => $deleted > 0 ? 'success' : 'error',
                    'message' => "{$deleted} categoría(s) eliminada(s). {$blocked} no se pudieron eliminar por contener Sub Categorias.",
                ]);
            }

            return response()->json(['status' => 'success', 'message' => "{$deleted} categoría(s) eliminada(s)."]);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.category._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $category = Category::findOrFail($id);
        return view('admin-ui.category._form', compact('category'));
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
        Cache::forget('categories_filter_tree');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Categoría creada con éxito.']);
        }

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
        Cache::forget('categories_filter_tree');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Categoría actualizada con éxito.']);
        }

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
        Cache::forget('categories_filter_tree');

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request){

        $category = Category::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();

        Cache::forget('nav_categories');
        Cache::forget('categories_filter_tree');

        return response(['message' =>'Status Changed Successfully!']);

    }
}
