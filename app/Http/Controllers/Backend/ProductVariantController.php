<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\ProductVariantItem;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\ProductVariantTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $product = Product::findOrFail($request->product);

        return view('admin-ui.products-variant.index', compact('product'));
    }

    /** JSON data source for the custom admin table (replaces ProductVariantDataTable). */
    public function tableData(Request $request, ProductVariantTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every product variant matching the current filter/search. */
    public function export(Request $request, ProductVariantTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Variantes de Producto',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('variantes-producto.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "variantes-producto.{$extension}", $writerType);
    }

    /**
     * Bulk actions from the table's multi-select bar. Mirrors destroy()'s
     * side-effect check: variants that still have ProductVariantItem rows
     * are skipped instead of deleted.
     */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $variants = ProductVariant::whereIn('id', $ids)->get();
            $deleted = 0;
            $skipped = 0;
            foreach ($variants as $variant) {
                if (ProductVariantItem::where('product_variant_id', $variant->id)->count() > 0) {
                    $skipped++;
                    continue;
                }
                $variant->delete();
                $deleted++;
            }

            $message = $deleted . ' variante(s) eliminada(s).';
            if ($skipped > 0) {
                $message .= ' ' . $skipped . ' no se eliminaron por contener variantes relacionadas.';
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
        return view('admin.product.product-variant.create');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment(Request $request)
    {
        $product = Product::findOrFail($request->product);
        return view('admin-ui.products-variant._form', compact('product'));
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $variant = ProductVariant::findOrFail($id);
        return view('admin-ui.products-variant._form', compact('variant'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product' => ['integer', 'required'],
            'name' => ['required', 'max:200'],
            'status' => ['required']
        ]);

        $variant = new ProductVariant();
        $variant->product_id = $request->product;
        $variant->name = $request->name;
        $variant->status = $request->status;
        $variant->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Variante creada con éxito.']);
        }

        toastr('Creado con Exito');

        return redirect()->route('admin.products-variant.index', ['product' => $request->product]);


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
        $variant = ProductVariant::findOrFail($id);
        return view('admin.product.product-variant.edit', compact('variant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {   
        $request->validate([
            'name' => ['required' , 'max:2000'],
            'status' => ['required']
        ]);

        $variant = ProductVariant::findOrFail($id);
        $variant->name = $request->name;
        $variant->status = $request->status;
        $variant->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Variante actualizada con éxito.']);
        }

        toastr('Actualizado con exito');

        return redirect()->route('admin.products-variant.index', ['product' => $variant->product_id]);

       


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $variant = ProductVariant::findOrFail($id);
        $variantItemCheck = ProductVariantItem::where('product_variant_id', $variant->id)->count();
        if($variantItemCheck > 0){
            return response(['status' => 'error' , 'message' => "Este Item contiene variantes, primero elimina las primeras variantes" ]);
        }
        $variant->delete();

        return response(['status' => 'success' , 'message' => "Eliminado con exito" ]);
    }

    public function changeStatus(Request $request){

        $category = ProductVariant::findOrFail($request->id);
        $category->status = $request->status == 'true' ? 1 : 0;
        $category->save();

        return response(['message' =>'Status Changed Successfully!']);

    }


}
