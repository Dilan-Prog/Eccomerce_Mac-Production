<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\OrderDataTable;
use App\DataTables\PendingOrderDataTable;
use App\DataTables\processedOrderDataTable;
use App\DataTables\droppedOffOrderDataTable;
use App\DataTables\shippedOrderDataTable;
use App\DataTables\outForDeliveryOrderDataTable;
use App\DataTables\deliveredOrderDataTable;
use App\DataTables\canceledOrderDataTable;
use App\Http\Controllers\Controller;
use App\Models\Order;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(OrderDataTable $dataTable)
    {
        return $dataTable->render('admin.order.index');
    }

    /**Peding Order */
    public function pendingOrders(PendingOrderDataTable $dataTable)
    {
        return $dataTable->render('admin.order.pending-order');
    }

    public function processedOrders(processedOrderDataTable $dataTable)
    {
        return $dataTable->render('admin.order.processed-orders');
    }

    public function droppedOfOrders(droppedOffOrderDataTable $dataTable)
    {
        return $dataTable->render('admin.order.dropped-off-order');
    }

    public function shippedOrders(shippedOrderDataTable $dataTable)
    {
        return $dataTable->render('admin.order.shipped-order');
    }

    public function outForDeliveryOrders(outForDeliveryOrderDataTable $dataTable)
    {
        return $dataTable->render('admin.order.out-for-delivery-order');
    }

    public function deliveredOrders(deliveredOrderDataTable $dataTable)
    {
        return $dataTable->render('admin.order.delivered-order');
    }

    public function canceledOrders(canceledOrderDataTable $dataTable)
    {
        return $dataTable->render('admin.order.canceled-order');
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
        return view('admin.order.show', compact('order'));
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
