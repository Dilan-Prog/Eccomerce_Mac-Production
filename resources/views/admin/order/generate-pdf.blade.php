@php
    $address = json_decode($order->order_address);
    $shipping = json_decode($order->shipping_method);
    $coupon = json_decode($order->coupon);
@endphp
@extends('admin.layouts.masterpdf')

@section('content')
      <!-- Main Content -->
        <section class="section">

            <div class="container" style="overflow-x: hidden;">
                <div class="section-body">
                  <div class="invoice">
                    <div class="invoice-print">



                      <div class="row">
                        <div class="col-lg-12">
                          <div class="invoice-title">
                              <br>
                            <div class="invoice-number">Order #{{ $order->invocie_id }}</div>
                          </div>
                          <hr>
                          <div class="row">
                            <div class="col-md-6">
                              <address>
                                <strong>Facturado a:</strong><br>
                                <b>Nombre:</b> {{$address->name}}<br>
                                <b>Correo:</b> {{$address->email}}<br>
                                <b>Telefono:</b> {{$address->phone}}<br>
                                <b>Calle:</b> {{$address->street}} #{{ $address->street_number }}<br>
                                <b>Dirección:</b> {{ $address->col }}, {{$address->city}}, {{ $address->zip }}<br>
                                 {{$address->state}}<br>

                              </address>
                            </div>
                            <div class="col-md-6 text-md-right">
                              <address>
                                  <strong>Facturado a:</strong><br>
                                  <b>Nombre:</b> {{$address->name}}<br>
                                  <b>Correo:</b> {{$address->email}}<br>
                                  <b>Telefono:</b> {{$address->phone}}<br>
                                  <b>Calle:</b> {{$address->street}} #{{ $address->street_number }}<br>
                                  <b>Dirección:</b> {{ $address->col }}, {{$address->city}}, {{ $address->zip }}<br>
                                   {{$address->state}}<br>
                              </address>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-6">
                              <address>
                                <strong>Información De Pago:</strong><br>
                                <b>Metodo De Pago:</b> {{ $order->payment_method }}<br>
                                <b>Transacción ID:</b> {{ @$order->transaction->transaction_id }} <br>
                                <b>Estado:</b>{{ $order->payment_status == 1 ? 'Completada' : 'Pendiente' }}
                              </address>
                            </div>
                            <div class="col-md-6 text-md-right">
                              <address>
                                <strong>Fecha De Orden:</strong><br>
                                {{ date('d M, Y', strtotime($order->created_at)) }}<br><br>
                              </address>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row mt-4">
                        <div class="col-md-12">
                          <div class="section-title">Resumen Del Pedido</div>
                          <p class="section-lead">Todos los elementos aqu&iacute; no se pueden eliminar.</p>
                          <div class="table-responsive">
                            <table class="table table-striped table-hover table-md">
                              <tr>
                                  <th data-width="40">#</th>
                                  <th>Articulo</th>
                                  <th>Modelo</th>
                                  <th class="text-center">Precio</th>
                                  <th class="text-center">Cantidad</th>
                                  <th class="text-right">Total</th>
                              </tr>
                              @foreach ($order->orderProducts as $product )
                              <tr>
                                  <td>{{ ++$loop->index }}</td>
                                  <td>{{ $product->product_name }}</td>
                                  <td>{{ $product->sku }}</td>
                                  <td class="text-center">{{ $settings->currency_icon }}{{ $product->unit_price }}</td>
                                  <td class="text-center">{{ $product->qty }}</td>
                                  <td class="text-right">{{ $settings->currency_icon }}{{ $product->unit_price * $product->qty }}</td>
                              </tr>
                              @endforeach

                            </table>
                          </div>
                          <div class="row mt-4">
                            <div class="col-lg-8">
                              <div class="col-md-4">
                                  <div class="form-group">
                                      <label for="">Estado De Pago</label>
                                        @if ($order->payment_status == 0)
                                        <div>Pendiente</div>
                                        @else
                                        <div>Completado</div>
                                        @endif
                                  </div>

                                  <div class="form-group">
                                   <label for="">Estado De Orden</label>
                                    <div data-id="{{$order->id}}">
                                        @switch($order->order_status)
                                            @case('pending')
                                                <div>Pendiente</div>
                                                @break
                                            @case('processed_and_ready_to_ship')
                                                <div>Procesado y listo para enviar</div>
                                                @break
                                            @case('dropped_off')
                                                <div>Entregado al transportista</div>
                                                @break
                                            @case('shipped')
                                                <div>Enviado</div>
                                                @break
                                            @case('delivered')
                                                <div>Entregado</div>
                                                @break
                                            @case('canceled')
                                                <div>Cancelado</div>
                                                @break
                                            @default

                                        @endswitch
                                    </div>
                                  </div>
                              </div>
                            </div>
                            <div class="col-lg-4 text-right">
                              <div class="invoice-detail-item">
                                <div class="invoice-detail-name">Subtotal</div>
                                <div class="invoice-detail-value">{{ $settings->currency_icon }}{{ $order->sub_total }}</div>
                              </div>
                              <div class="invoice-detail-item">
                                <div class="invoice-detail-name">Descuento</div>
                                <div class="invoice-detail-value">{{ $settings->currency_icon }}{{ @$coupon->discount ? @$coupon->discount : 0 }}</div>
                              </div>
                              <div class="invoice-detail-item">
                                <div class="invoice-detail-name">Envio</div>
                                <div class="invoice-detail-value">{{ $settings->currency_icon }}{{ @$shipping->cost }}</div>
                              </div>
                              <hr class="mt-2 mb-2">
                              <div class="invoice-detail-item">
                                <div class="invoice-detail-name">Total</div>
                                <div class="invoice-detail-value invoice-detail-value-lg">{{ $settings->currency_icon }}{{ $order->amount }}</div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <hr>
                    <div class="text-md-right">

                      <button class="btn btn-warning btn-icon icon-left" id="download_pdf">
                          <a href="{{ route('admin.invocie.pdf', ['id' => $order->id]) }}" style="color: white; text-decoration: none;">
                              <i class="fas fa-file-pdf"></i> Descargar PDF
                          </a>
                      </button>
                    </div>
                  </div>
                </div>
            </div>
        </section>

{{-- @push('scripts')
    <script>
        $(document).ready(function(){

            // $('#order_status').on('change', function(){
            //     let status = $(this).val();
            //     let id = $(this).data('id');

            //     $.ajax({
            //         method: 'GET',
            //         url: "{{route('admin.order.status')}}",
            //         data: {status: status, id:id},
            //         success: function(data){
            //             if(data.status === 'success'){
            //                 toastr.success(data.message)
            //             }
            //         },
            //         error: function(data){
            //             console.log(data);
            //         }
            //     })
            // })

            $('#payment_status').on('change', function(){
                let status = $(this).val();
                let id = $(this).data('id');

                $.ajax({
                    method: 'GET',
                    url: "{{ route('admin.payment.status') }}",
                    data: {status: status, id:id},
                    success: function(data){
                        if(data.status === 'success'){
                            toastr.success(data.message)
                        }
                    },
                    error: function(data){
                        console.log(data);
                    }
                })
            })


            //Imprimir Documento

            // $('.print_invoice').on('click', function(){
            //     let printBody = $('.invoice-print');
            //     let originalContents = $('body').html();

            //     $('body').html(printBody.html());

            //     window.print();

            //     $('body').html(originalContents);

            // })


        })
    </script>
@endpush --}}

