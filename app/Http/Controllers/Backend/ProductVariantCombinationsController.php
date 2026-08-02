<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductVariantCombinationsDataTables;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantCombinations;
use App\Models\ProductVariantItem;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\ProductVariantCombinationsTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductVariantCombinationsController extends Controller
{

public function index(Request $request)
    {
        $product = Product::findOrFail($request->product);
        return view('admin-ui.products-variant-combinations.index', compact('product'));
    }

    /** JSON data source for the custom admin table (replaces ProductVariantCombinationsDataTables). */
    public function tableData(Request $request, ProductVariantCombinationsTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every combinacion matching the current filter/search. */
    public function export(Request $request, ProductVariantCombinationsTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Combinaciones de Variante',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('combinaciones-de-variante.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "combinaciones-de-variante.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $combinations = ProductVariantCombinations::whereIn('id', $ids)->get();
            foreach ($combinations as $combination) {
                $combination->delete();
            }
            return response()->json(['status' => 'success', 'message' => count($combinations) . ' combinacion(es) eliminada(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    public function create(Request $request)
{
    $product = Product::with(['variants.productVariantItems'])->findOrFail($request->product);
    $variantItems = $product->variants->flatMap(function($variant) {
        return $variant->productVariantItems;
    });

   return view('admin.product.product-variant-combinations.create', compact('product', 'variantItems'));
}

    /** Bare form fragment for the admin-ui Crear modal (AU.FormModal) — no page layout. */
    public function createFragment(Request $request)
    {
        $product = Product::with(['variants.productVariantItems'])->findOrFail($request->product);
        $variantItems = $product->variants->flatMap(function ($variant) {
            return $variant->productVariantItems;
        });

        return view('admin-ui.products-variant-combinations._form', compact('product', 'variantItems'));
    }

    /** Bare form fragment for the admin-ui Editar modal, pre-filled. */
    public function editFragment(string $id)
    {
        $productVariantCombination = ProductVariantCombinations::findOrFail($id);
        $product = Product::with(['variants.productVariantItems'])->findOrFail($productVariantCombination->product_id);
        $variantItems = $product->variants->flatMap(function ($variant) {
            return $variant->productVariantItems;
        });

        return view('admin-ui.products-variant-combinations._form', compact('productVariantCombination', 'product', 'variantItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'variants_item_ids' => ['required', 'array'],
            'variants_item_ids.*' => ['integer'],
            'product_id' => [ 'required', 'integer'],
            'sku' => ['required','string','max:255'],
            'price' => ['required'],
            'qty' => ['required', 'integer', 'max:255'],
            'is_default' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        $productVariantCombination = new ProductVariantCombinations();
        $productVariantCombination->name = $request->name;
        $productVariantCombination->variants_item_ids = json_encode($request->variants_item_ids);
        $productVariantCombination->product_id = $request->product_id;
        $productVariantCombination->sku = $request->sku;
        $productVariantCombination->price = $request->price;
        $productVariantCombination->qty = $request->qty;
        $productVariantCombination->is_default = $request->is_default;
        $productVariantCombination->status = $request->status;

        $productVariantCombination->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Combinación de variantes creada con éxito.']);
        }

        toastr('Combinacion de Variantes Creada con Exito');

        return redirect()->route('admin.products-variant-combinations.index', ['product' => $productVariantCombination->product_id]);
    }

    public function edit($id)
    {
        $productVariantCombination = ProductVariantCombinations::findOrFail($id);
        $product = Product::with(['variants.productVariantItems'])->findOrFail($productVariantCombination->product_id);
        $variantItems = $product->variants->flatMap(function($variant) {
            return $variant->productVariantItems;
        });

        return view('admin.product.product-variant-combinations.edit', compact('productVariantCombination', 'product', 'variantItems'));
    }


    public function update(Request $request,string $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'variants_item_ids' => ['required', 'array'],
            'variants_item_ids.*' => ['integer'],
            'product_id' => [ 'required', 'integer'],
            'sku' => ['required','string','max:255'],
            'price' => ['required'],
            'qty' => ['required', 'integer', 'max:255'],
            'is_default' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        $productVariantCombination = ProductVariantCombinations::findOrFail($id);
        $productVariantCombination->name = $request->name;
        $productVariantCombination->variants_item_ids = json_encode($request->variants_item_ids);
        $productVariantCombination->product_id = $request->product_id;
        $productVariantCombination->sku = $request->sku;
        $productVariantCombination->price = $request->price;
        $productVariantCombination->qty = $request->qty;
        $productVariantCombination->is_default = $request->is_default;
        $productVariantCombination->status = $request->status;

        $productVariantCombination->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Combinación de variantes actualizada con éxito.']);
        }

        toastr('Combinacion de Variantes Actualizada con Exito');

        return redirect()->route('admin.products-variant-combinations.index', ['product' => $productVariantCombination->product_id]);
    }

    public function destroy(string $id)
    {
        $productVariantCombination = ProductVariantCombinations::findOrFail($id);
        $productVariantCombination->delete();
        toastr('Combinacion de Variantes Eliminada con Exito');
        return response(['status' => 'success', 'message' => "Eliminado con exito"]);
    }


}
