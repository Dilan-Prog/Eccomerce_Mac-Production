@php
    $address = json_decode($order->order_address);
    $shipping = json_decode($order->shipping_method);
    $coupon = json_decode($order->coupon);
@endphp

@extends('frontend.dashboard.layouts.master')

@section('title')
    {{ $settings->site_name }} || Product
@endsection

@section('content')
    <!--=============================
        DASHBOARD START
      ==============================-->
    <section id="wsus__dashboard">
        <div class="container-fluid">


            <div class="row">
                <div class="col-xl-9 col-xxl-10 col-lg-9 ms-auto">
                    <div class="dashboard_content mt-2 mt-md-0">
                        <h3><i class="far fa-user"></i>Detalles De Pedido</h3>
                        <div class="wsus__dashboard_profile">
                        @include('frontend.dashboard.layouts.sidebar')
                            <!--============================
                            INVOICE PAGE START
                        ==============================-->
                            <section id="" class="invoice-print">
                                <div class="">
                                    <div class="wsus__invoice_area">
                                        <div class="wsus__invoice_header">
                                            <div class="wsus__invoice_content">
                                                <div class="row">
                                                    <div class="col-xl-5 col-md-5 mb-5 mb-md-0">
                                                        <div class="wsus__invoice_single">
                                                            <h5>Rastreo Del Pedido CP{{ $order->invocie_id }}</h5>
                                                            <h6>Enviado a: {{ $address->street }} #{{ $address->street_number }}</h6>

                                                            <p>{{ $address->col }}, {{ $address->zip }}</p>
                                                            <p>{{ $address->city }}, {{ $address->state }}, México</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-7 col-md-7 mb-5 mb-md-0">
                                                        <div>
                                                            <h5>Fecha Del Pedido: {{ date('d-M-Y', strtotime($order->created_at)) }}</h5>
                                                            <br>
                                                            <h6>Envío: {{ $shipping->name }}</h6>
                                                            <h6 class="mt-3">Estado del Pedido:</h6>
                                                            @php
                                                                switch ($order->order_status) {
                                                                    case 'pending':
                                                                        echo '<span class="badge bg-warning">Pendiente</span>';
                                                                        break;
                                                                    case 'processed_and_ready_to_ship':
                                                                        echo '<span class="badge bg-info">Procesado y listo <br> para enviar</span>';
                                                                        break;
                                                                    case 'dropped_off':
                                                                        echo '<span class="badge bg-info">Entregado al transportista</span>';
                                                                        break;
                                                                    case 'shipped':
                                                                        echo '<span class="badge bg-info">Enviado</span>';
                                                                        break;
                                                                    case 'out_for_delivery':
                                                                        echo '<span class="badge bg-primary">En ruta de entrega</span>';
                                                                        break;
                                                                    case 'delivered':
                                                                        echo '<span class="badge bg-success">Entregado</span>';
                                                                        break;
                                                                    case 'canceled':
                                                                        echo '<span class="badge bg-danger">Cancelado</span>';
                                                                        break;
                                                                    default:
                                                                        echo '<span class="badge bg-secondary">Desconocido</span>';
                                                                        break;
                                                                }
                                                            @endphp
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wsus__invoice_description">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <tr>
                                                            <th class="name">
                                                                Producto
                                                            </th>
                                                            <th class="name">
                                                                Clave
                                                            </th>
                                                            <th class="name">
                                                                Modelo
                                                            </th>

                                                            <th class="amount">
                                                                Precio x Unidad
                                                            </th>

                                                            <th class="quentity">
                                                                Cantidad
                                                            </th>
                                                            <th class="total">
                                                                Total
                                                            </th>
                                                        </tr>
                                                        @foreach ($order->orderProducts as $product)
                                                                <tr>
                                                                    <td class="name">
                                                                        <p>{{ $product->product_name }}</p>

                                                                    </td>
                                                                    <td class="name">
                                                                        <p>{{ $product->sku }}</p>
                                                                    </td>
                                                                    <td class="name">
                                                                        <p>{{ $product->productModel }}</p>
                                                                    </td>

                                                                    <td class="amount">
                                                                        {{ $settings->currency_icon }}{{ formatCurrency($product->unit_price) }}Mxn
                                                                    </td>

                                                                    <td class="quentity">
                                                                        {{ $product->qty }}
                                                                    </td>
                                                                    <td class="total">
                                                                        {{ $settings->currency_icon }}{{formatCurrency($product->unit_price * $product->qty) }}Mxn
                                                                    </td>
                                                                </tr>

                                                        @endforeach

                                                    </table>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="wsus__invoice_footer">

                                            <p><span>Sub Total:</span> {{ @$settings->currency_icon }} {{ formatCurrency($order->sub_total) }}</p>
                                            <p><span>Envío(+):</span>{{ @$settings->currency_icon }} {{formatCurrency(@$shipping->cost)}} </p>
                                            <p><span>Coupon(-):</span>{{ @$settings->currency_icon }} {{formatCurrency(@$coupon->discount ? $coupon->discount : 0)}}</p>
                                            <p><span>Monto Total:</span>{{ @$settings->currency_icon }} {{formatCurrency(@$order->amount)}} Mxn</p>


                                        </div>
                                    </div>
                                </div>
                            </section>
                            <!--============================
                            INVOICE PAGE END
                        ==============================-->
                        {{-- <div class="col">
                            <div class="mt-2 float-end">
                                <button class="btn btn-warning print_invoice">print</button>
                            </div>
                        </div> --}}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--=============================
        DASHBOARD START
      ==============================-->
@endsection

@push('scripts')
    <script>
        $('.print_invoice').on('click', function() {
            let printBody = $('.invoice-print');
            let originalContents = $('body').html();

            $('body').html(printBody.html());

            window.print();

            $('body').html(originalContents);

        })
    </script>
@endpush
