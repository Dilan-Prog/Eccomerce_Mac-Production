<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductMoreEccomerce;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\ProductMoreEccomerceTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Product;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ProductMoreEccomerceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $product = Product::findOrFail($request->product);
        return view('admin-ui.products-more-eccomerce.index', compact('product'));
    }

    /** JSON data source for the custom admin table (replaces ProductMoreEccomerceDataTable). */
    public function tableData(Request $request, $product, ProductMoreEccomerceTableQuery $table)
    {
        $table->setProductId((int) $product);

        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every row matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, $product, ProductMoreEccomerceTableQuery $table)
    {
        $table->setProductId((int) $product);

        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Producto More Eccomerce',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('producto-more-eccomerce.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "producto-more-eccomerce.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request, $product)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $items = ProductMoreEccomerce::whereIn('id', $ids)->get();
            foreach ($items as $item) {
                $item->delete();
            }
            return response()->json(['status' => 'success', 'message' => count($items) . ' elemento(s) eliminado(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }


    public function create()
    {

        return view('admin.product.more-eccomerce.create');
    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment()
    {
        return view('admin-ui.products-more-eccomerce._form');
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $productMoreEccomerce = ProductMoreEccomerce::findOrFail($id);
        return view('admin-ui.products-more-eccomerce._form', compact('productMoreEccomerce'));
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

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Opción de comercio creada con éxito.']);
        }

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

    if ($request->wantsJson()) {
        return response()->json(['status' => 'success', 'message' => 'Opción de comercio actualizada con éxito.']);
    }

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
