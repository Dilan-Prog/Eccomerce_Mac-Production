<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Traits\ImageUploadTrait;
use App\Models\Brand;
use App\Models\Product;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\BrandTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Str;

class BrandController extends Controller
{
    use ImageUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.brand.index');
    }

    /** JSON data source for the custom admin table (replaces BrandDataTable). */
    public function tableData(Request $request, BrandTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every brand matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, BrandTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Marcas',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('marcas.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "marcas.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar (mirrors destroy()'s side effects: image cleanup + "has products" guard). */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $brands = Brand::whereIn('id', $ids)->get();
            $deleted = 0;
            $skipped = 0;
            foreach ($brands as $brand) {
                if (Product::where('brand_id', $brand->id)->count() > 0) {
                    $skipped++;
                    continue;
                }
                $this->deleteImage($brand->logo);
                $brand->delete();
                $deleted++;
            }

            $message = "{$deleted} marca(s) eliminada(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} marca(s) no se eliminaron porque tienen productos asociados.";
            }

            return response()->json(['status' => $deleted > 0 ? 'success' : 'error', 'message' => $message]);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brand.create');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.brand._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin-ui.brand._form', compact('brand'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'logo'=>[Rule::requiredIf(!$request->filled('logo_from_library')), 'nullable', 'image', 'max:2000'],
            'logo_from_library'=>['nullable', 'string'],
            'name'=>['required' , 'max:50'],
            'is_featured'=>['required'],
            'status'=>['required']

        ]);


        $logoPath = $this->resolveOrCopyImage($request, 'logo', 'logo_from_library', 'uploads');
        $brand = new Brand();
        $brand->logo=$logoPath;
        $brand->name= $request->name;
        $brand->slug = Str::slug($request->title);
        $brand->is_featured = $request->is_featured;
        $brand->status = $request->status;
        $brand->save();

        Cache::forget('brand');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Marca creada con éxito.']);
        }

        toastr('Brand Creada Con exito');
        return redirect()->route('admin.brand.index');

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
        $brand = Brand::findOrFail($id);
        return view('admin.brand.edit',compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'logo'=>['nullable','image','max:2000'],
            'logo_from_library'=>['nullable', 'string'],
            'name'=>['required','max:50'],
            'is_featured'=>['required'],
            'status'=>['required']
        ]);

        $brand = Brand::findOrFail($id);

        $logoPath = $this->resolveOrCopyImage($request, 'logo', 'logo_from_library', 'uploads', $brand->logo);
        $brand->logo = empty(!$logoPath) ? $logoPath : $brand->logo;

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $brand->is_featured = $request->is_featured;
        $brand->status= $request->status;
        $brand->save();

        Cache::forget('brand');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Marca actualizada con éxito.']);
        }

        toastr('Actualizacion con exito','success');
        return redirect()->route('admin.brand.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
            if(Product::where('brand_id', $brand->id)->count() > 0){
                return response(['status' => 'error', 'message' => 'This brand have products you can\'t delete it.']);
            }
            $this->deleteImage($brand->logo);
            $brand->delete();
            return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    public function changeStatus(Request $request){

        $category = Brand::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();

        return response(['message' =>'Status Changed Successfully!']);

    }
}
