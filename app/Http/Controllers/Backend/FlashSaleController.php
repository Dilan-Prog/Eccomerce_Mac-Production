<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Product;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\FlashSaleItemTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FlashSaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:ecommerce');
    }

    public function index(){
        $flashSaleDate = FlashSale::first();
        $products = Product::where('status', 1)->orderBy('id', 'DESC')->get();
        return view('admin-ui.flash-sale.index', compact('flashSaleDate', 'products'));
    }

    /** JSON data source for the custom admin table (replaces FlashSaleItemDataTable). */
    public function tableData(Request $request, FlashSaleItemTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every flash sale item matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, FlashSaleItemTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Flash Sale',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('flash-sale.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "flash-sale.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar (mirrors destroy(), which has no extra side effects). */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $count = FlashSaleItem::whereIn('id', $ids)->count();
            FlashSaleItem::whereIn('id', $ids)->delete();

            return response()->json(['status' => 'success', 'message' => $count . ' producto(s) eliminado(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    public function update(Request $request){
        $request->validate([
            'end_date' => ['required']
        ]);
        FlashSale::updateOrCreate(
            ['id' => 1],
            ['end_date' => $request->end_date]
        );
        
        toastr('Actualizado con extito', 'success', 'Success');
        Cache::forget('flash_sale_date');
        return redirect()->back();

        
    }

    public function addProduct(Request $request){

        $request->validate([
            'product' => ['required', 'unique:flash_sale_items,product_id'],
            'show_at_home' =>['required'],
            'status' =>['required']
        ],[
            'product.unique' => 'The product is already in flash sale'
        ]);

        $flashSaleDate = FlashSale::first();

        $flashSaleItem = new FlashSaleItem();
        $flashSaleItem->product_id = $request->product;
        $flashSaleItem->flash_sale_id = $flashSaleDate->id;
        $flashSaleItem->show_at_home = $request->show_at_home;
        $flashSaleItem->status = $request->status;
        $flashSaleItem->save();

        toastr('Producto agregado' , 'success' , 'Success');
        Cache::forget('flash_sale_items');
        return redirect()->back();
    }
    
    




    public function changeShowAtHomeStatus(Request $request) {

        $flashSaleItem = FlashSaleItem::findOrFail($request->id);
        $flashSaleItem->show_at_home = $request->status == 'true' ? 1 : 0;
        $flashSaleItem->save();

        return response(['message' =>'Status Changed Successfully!']);
        
    }

    public function changeStatus(Request $request){

        $flashSaleItem = FlashSaleItem::findOrFail($request->id);
        $flashSaleItem->status = $request->status == 'true' ? 1 : 0;
        $flashSaleItem->save();

        return response(['message' =>'Status Changed Successfully!']);
    }

    public function destroy(string $id){

        $flashSaleItem = FlashSaleItem::findOrFail($id);
        $flashSaleItem->delete();
        return response(['status' => 'success', 'message' => 'Borrado Exitosamente !']);
        
    }
}
