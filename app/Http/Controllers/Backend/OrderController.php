<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\AdminTable\AdminTableExport;
use App\Support\AdminTable\AdminTableRequest;
use App\Support\AdminTable\Queries\OrderTableQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can-access-module:orders');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin-ui.order.index');
    }

    /** JSON data source for the custom admin table (replaces OrderDataTable). */
    public function tableData(Request $request, OrderTableQuery $table)
    {
        return response()->json($table->paginate(AdminTableRequest::fromRequest($request)));
    }

    /** Excel/CSV/PDF export of every order matching the current filter/search (replaces the Yajra-Buttons export). */
    public function export(Request $request, OrderTableQuery $table)
    {
        $adminRequest = AdminTableRequest::fromRequest($request);
        $headings = $table->exportHeadings();
        $rows = $table->exportRows($adminRequest)->map(fn ($row) => $table->exportRow($row))->all();
        $format = $request->input('format', 'xlsx');

        if ($format === 'pdf') {
            return Pdf::loadView('admin-ui.exports.table-pdf', [
                'title' => 'Ordenes',
                'headings' => $headings,
                'rows' => $rows,
                'generatedAt' => now()->format('d/m/Y H:i'),
            ])->download('ordenes.pdf');
        }

        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $extension = $format === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(new AdminTableExport($headings, $rows), "ordenes.{$extension}", $writerType);
    }

    /** Bulk actions from the table's multi-select bar. */
    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No se seleccionó ningún elemento.']);
        }

        if ($request->input('action') === 'delete') {
            $orders = Order::whereIn('id', $ids)->get();
            foreach ($orders as $order) {
                $order->orderProducts()->delete();
                $order->transaction()->delete();
                $order->delete();
            }
            return response()->json(['status' => 'success', 'message' => count($orders) . ' orden(es) eliminada(s).']);
        }

        return response()->json(['status' => 'error', 'message' => 'Acción no soportada.']);
    }

    /**
     * The 8 status-filtered views (Pendientes, Procesadas, ...) used to be
     * separate DataTable-backed pages; they now redirect into the unified
     * table with the equivalent tab pre-selected. Route names kept as-is
     * since resources/views/admin/layouts/sidebar.blade.php (still used by
     * not-yet-migrated modules) links to them directly.
     */
    public function pendingOrders()
    {
        return redirect()->route('admin.order.index', ['filter' => 'pendiente']);
    }

    public function processedOrders()
    {
        return redirect()->route('admin.order.index', ['filter' => 'procesado']);
    }

    public function droppedOfOrders()
    {
        return redirect()->route('admin.order.index', ['filter' => 'entregado_transportista']);
    }

    public function shippedOrders()
    {
        return redirect()->route('admin.order.index', ['filter' => 'enviado']);
    }

    public function outForDeliveryOrders()
    {
        return redirect()->route('admin.order.index', ['filter' => 'en_ruta']);
    }

    public function deliveredOrders()
    {
        return redirect()->route('admin.order.index', ['filter' => 'entregado']);
    }

    public function canceledOrders()
    {
        return redirect()->route('admin.order.index', ['filter' => 'cancelado']);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::findOrFail($id);
        return view('admin-ui.order.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);

        // delete order products
        $order->orderProducts()->delete();
        // delete transaction
        $order->transaction()->delete();

        $order->delete();

        return response(['status' => 'success', 'message' => 'Borrado Exitosamente!']);
    }


    /**change order status */
    public function changeOrderStatus(Request $request)
    {
        $order = Order::findOrFail($request->id);
        $order->order_status = $request->status;
        $order->save();

        return response(['status' => 'success', 'message' => 'Estado de Orden Actualizado']);
    }
    /**Change payment status */
    public function changePaymentStatus(Request $request)
    {
        $paymentStatus = Order::findOrFail($request->id);
        $paymentStatus->payment_status = $request->status;
        $paymentStatus->save();

        return response(['status' => 'success', 'message' => 'Updated Payment Status Successfully']);
    }



    //Generate PDF

    // public function generatePdf(string $id){
    //     $order = Order::findOrFail($id);
    //     return view('admin.order.generate-pdf', compact('order'));
    // }


    // public function pdf(string $id)
    // {
    //     // Obtener la orden específica según el ID proporcionado
    //     $order = Order::findOrFail($id);

    //     // Generar el PDF utilizando la vista 'show' y los datos de la orden
    //     $pdf = PDF::loadView('admin.order.generate-pdf', compact('order'));
    //     $pdf->setPaper('a4', 'portrait'); // Configurar tamaño de papel y orientación
    //     // Descargar el PDF con el nombre 'invoice.pdf'
    //     // return $pdf->download($order->invocie_id);
    //     return $pdf->stream();
    // }


}
