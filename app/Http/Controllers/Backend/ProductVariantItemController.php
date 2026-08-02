<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantItem;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\ProductVariantItemTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ProductVariantItemController extends Controller
{
    public function index($productId, $variantId) {

        $product = Product::findOrFail($productId);
        $variant = ProductVariant::findOrFail($variantId);
        return view('admin-ui.products-variant-item.index', compact('product', 'variant'));

    }

    /** JSON data source for the custom admin table (replaces ProductVariantItemDataTable). */
    public function tableData(Request $request, ProductVariantItemTableQuery $table, $productId, $variantId)
    {
        $table->forVariant($productId, $variantId);

        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every variant item matching the current filter/search. */
    public function export(Request $request, ProductVariantItemTableQuery $table, $productId, $variantId)
    {
        $table->forVariant($productId, $variantId);

        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Items de Variante',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('items-de-variante.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "items-de-variante.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $count = ProductVariantItem::whereIn('id', $ids)->delete();
            return response()->json(['status' => 'success', 'message' => $count . ' elemento(s) eliminado(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    public function create(string $productId, string $variantId){

        $product = Product::findOrFail($productId);
        $variant = ProductVariant::findOrFail($variantId);
        return view('admin.product.product-variant-item.create', compact('variant', 'product'));

    }

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment(string $productId, string $variantId)
    {
        $product = Product::findOrFail($productId);
        $variant = ProductVariant::findOrFail($variantId);
        return view('admin-ui.products-variant-item._form', compact('product', 'variant'));
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $variantItemId)
    {
        $variantItem = ProductVariantItem::with('productVariant')->findOrFail($variantItemId);
        return view('admin-ui.products-variant-item._form', compact('variantItem'));
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

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Item de variante creado con éxito.']);
        }

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

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Item de variante actualizado con éxito.']);
        }

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
