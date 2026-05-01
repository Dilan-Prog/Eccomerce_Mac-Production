@extends('frontend.layouts.master')
@section('title')
{{$settings->site_name}} || Procesando Compra
@endsection

@section('content')
    <!--============================
        CHECK OUT PAGE START
    ==============================-->
    <section id="wsus__cart_view">
        <div class="container">

                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="wsus__check_form">
                            <div class="d-flex">
                                <h5>Selecciona tu Direcci&oacute;n</h5>
                                <a href="javascript;:" style="margin-left: auto" data-bs-toggle="modal" data-bs-target="#exampleModal">Agregar
                                    Nueva Direcci&oacute;n</a>
                            </div>
                            <div class="row">
                                @foreach ($addresses as $address )

                                <div class="col-xl-6">
                                    <div class="wsus__checkout_single_address">
                                        <div class="form-check">
                                            <input class="form-check-input shipping_address" data-id="{{ $address->id }}" type="radio" name="flexRadioDefault"
                                                id="flexRadioDefault1" >
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                Seleccionar Direcci&oacute;n
                                            </label>
                                        </div>
                                        <ul>
                                            <li><span>Nombre :</span>{{$address->name}}</li>
                                            <li><span>N&uacute;mero de Telefono :</span>{{$address->phone}}</li>
                                            <li><span>Correo Electronico :</span>{{$address->email}}</li>
                                            <li><span>Codigo Postal :</span>{{$address->zip}}</li>
                                            <li><span>Estado :</span>{{$address->state}}</li>
                                            <li><span>Ciudad :</span>{{$address->city}}</li>
                                            <li><span>Colonia :</span>{{$address->col}}</li>
                                            <li><span>Calle :</span>{{$address->street}}</li>
                                            <li><span>N&uacute;mero Interior :</span>{{$address->street_number}}</li>
                                        </ul>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="wsus__order_details" id="sticky_sidebar">
                            <p class="wsus__product">Metodos de Env&iacute;o</p>
                            @foreach ( $shippingMethod as $method  )
                                @if ($method->type == 'min_cost' && getCartTotal() >= $method->min_cost)
                                    <div class="form-check">
                                        <input class="form-check-input shipping_method" type="radio" name="exampleRadios" id="exampleRadios1"
                                            value="{{ $method->id }}" data-id="{{ $method->cost }}" >
                                        <label class="form-check-label" for="exampleRadios1">
                                            {{ $method->name }}
                                            <span>Costo: {{ $settings->currency_icon }}{{ $method->cost }}</span>
                                        </label>
                                    </div>
                                @elseif ($method->type == 'flat_cost')
                                    <div class="form-check">
                                        <input class="form-check-input shipping_method" type="radio" name="exampleRadios" id="exampleRadios1"
                                            value="{{ $method->id }}" data-id="{{ $method->cost }}" >
                                        <label class="form-check-label" for="exampleRadios1">
                                            {{ $method->name }}
                                            <span>Costo: {{ $settings->currency_icon }}{{ $method->cost }}</span>
                                        </label>
                                    </div>
                                @endif
                            @endforeach

                            <div class="wsus__order_details_summery">
                                <p>subtotal: <span>{{ $settings->currency_icon }}{{ formatCurrency(getCartTotal()) }}</span></p>
                                <p>Descuento de Cupon:<span>{{ $settings->currency_icon }}{{ getCartDiscount() }} </span></p>
                                <p>Costo de envío: <span id="shipping_fee">{{ $settings->currency_icon }}0</span></p>
                                <p><b>total:</b> <span><b id="total_amount" data-id="{{ getMainCartTotal() }}">{{ $settings->currency_icon }}{{ formatCurrency(getMainCartTotal()) }}</b></span></p>
                            </div>
                            <div class="terms_area">
                                <div class="form-check">
                                    <input class="form-check-input agree_term" type="checkbox" value="" id="flexCheckChecked3"
                                        >
                                    <label class="form-check-label" for="flexCheckChecked3">
                                        He le&iacute;do y acepto los  <a href="{{route('Terminos-Condiciones')}}">t&eacute;rminos y condiciones del sitio web *</a>
                                    </label>
                                </div>
                            </div>
                            <form action="" id="checkOutForm">
                                <input type="hidden" name="shipping_method_id" value="" id="shipping_method_id">
                                <input type="hidden" name="shipping_address_id" value="" id="shipping_address_id">
                            </form>
                            <a href="" id="submitCheckoutForm" class="common_btn">Realizar Pedido</a>
                        </div>
                    </div>
                </div>

        </div>
    </section>

    <div class="wsus__popup_address">
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Agregar Direcci&oacute;n</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="wsus__check_form p-3">
                            <form id="checkoutFormAddress" action="{{ route('user.checkout.address.create') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-xl-12 col-md-12">
                                      <div class="wsus__add_address_single">
                                        <label>Nombre <b>*</b></label>
                                        <input type="text" placeholder="Nombre" name="name" value="{{ old('name') }}">
                                      </div>
                                    </div>
                                    <div class="col-xl-12 col-md-12">
                                      <div class="wsus__add_address_single">
                                        <label>Correo Electronico</label>
                                        <input type="email" placeholder="Email" name="email" value="{{ old('email') }}">
                                      </div>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                      <div class="wsus__add_address_single">
                                        <label>Telefono <b>*</b></label>
                                        <input type="text" placeholder="Telefono" name="phone" value="{{ old('phone') }}">
                                      </div>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                      <div class="wsus__add_address_single">
                                        <label>Codigo Postal<b>*</b></label>
                                        <input type="text" placeholder="Codigo Postal" name="zip" value="{{ old('zip') }}">
                                      </div>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                      <div class="wsus__add_address_single">
                                        <label>Estado <b>*</b></label>
                                        <div class="wsus__topbar_select">
                                          <select class="select_2" name="state">
                                            <option value="">Seleccionar...</option>
                                            @foreach (config('settings.state_list') as $state)
                                            <option {{ $state == old('state') ? 'selected' : '' }} value="{{$state}}">{{$state}}</option>

                                            @endforeach
                                          </select>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                      <div class="wsus__add_address_single">
                                        <label>Ciudad <b>*</b></label>
                                        <input type="text" placeholder="Ciudad" name="city" value="{{ old('city') }}">
                                      </div>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                      <div class="wsus__add_address_single">
                                        <label>Colonia <b>*</b></label>
                                        <input type="text" placeholder="Colonia" name="col" value="{{ old('col') }}">
                                      </div>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                      <div class="wsus__add_address_single">
                                        <label>Calle<b>*</b></label>
                                        <input type="text" placeholder="Calle" name="street" value="{{ old('street') }}">
                                      </div>
                                    </div>
                                    <div class="col-xl-6 col-md-6">
                                      <div class="wsus__add_address_single">
                                        <label>Numero Interior<b>(opcional)</b></label>
                                        <input type="text" placeholder="Numero Interior" name="street_number" value="{{ old('street_number') }}">
                                      </div>
                                    </div>
                                    <div class="col-xl-12 col-md-12">
                                      <div class="wsus__add_address_single">
                                        <label>¿Entre Que Calles Esta?</label>
                                        <div class="row">
                                            <div class="col-xl-12 col-md-12">
                                              <label>Calle 1<b>*</b></label>
                                                <input type="text" placeholder="Calle" name="street_1" value="{{ old('street_1') }}">
                                            </div>
                                            <div class="col-xl-12 col-md-12">
                                              <label>Calle 2<b>*</b></label>
                                                <input type="text" placeholder="Calle" name="street_2" value="{{ old('street_2') }}">
                                            </div>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-xl-12 col-md-12">
                                      <div class="wsus__add_address_single">
                                        <label>Indicaciones Adicionales De Esta Direccion<b>(opcional)</b></label>
                                        <textarea type="text" placeholder="Descripción del exterior del edificio, lugares destacados para ubicarlo, medidas de seguridad recomendadas, entre otros detalles relevantes." name="address">{{ old('address') }}</textarea>
                                      </div>
                                    </div>
                                    <div class="col-xl-6">
                                      <button type="submit" id="saveAddressButton" class="common_btn">Guardar Direcci&oacute;n</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--============================
        CHECK OUT PAGE END
    ==============================-->




@endsection

@push('scripts')

<script>
    $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('input[type="radio"]').prop('checked', false);
        $('#shipping_method_id').val("");
        $('#shipping_address_id').val("");

        function formatNumber(num) {
                return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }

       $('.shipping_method').on('click', function(){


            let shippingFee = parseFloat($(this).data('id'));
            let currentTotalAmount = parseFloat($('#total_amount').data('id'));
            let totalAmount = currentTotalAmount + shippingFee;

            // Actualizar los valores en el formulario
            $('#shipping_method_id').val($(this).val());
            $('#shipping_fee').text("{{ $settings->currency_icon }}" + formatNumber(shippingFee));
            $('#total_amount').text("{{ $settings->currency_icon }}" + formatNumber(totalAmount));
        });

        $('.shipping_address').on('click', function(){
            $('#shipping_address_id').val($(this).data('id'));
        })

        //submitCheckoutForm
        //headers: {
            // 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //         },
            // se agrega para volver a ahceer que coincidan los tokends
        $('#submitCheckoutForm').on('click', function(e){
            e.preventDefault();
            if($('#shipping_method_id').val() == ""){
                toastr.error('Agrega un metodo de envío')
            }else if($('#shipping_address_id').val() == ""){
                toastr.error('Agrega una direccion de entrega')
            }else if(!$('.agree_term').prop('checked')){
                toastr.error('Acepta Terminos y Condiciones')
            }else{
                $.ajax({
                    url: "{{ route('user.checkout.form-submit') }}",
                    method: 'POST',
                    data: $('#checkOutForm').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function(){
                        $('#submitCheckoutForm').html('<i class="fas fa-spinner fa-spin fa-1x"></i>').addClass('disabled')
                    },
                    success: function(data){
                        if(data.status === 'success'){
                            $('#submitCheckoutForm').text('Realizar Pedido');
                            // //redirect
                            window.location.href = data.redirect_url;
                        }

                    },
                    error: function(data){
                        console.log(data);
                    }
                })

            }

        })




        $('#saveAddressButton').on('click', function(e){
        e.preventDefault(); // Evita el envío del formulario de forma predeterminada

        var $button = $(this); // Obtiene el botón que fue clickeado
        if($button.hasClass('disabled')) {
            return; // Si el botón ya está desactivado, no hace nada
        }

        $button.addClass('disabled').text('Enviando...'); // Desactiva el botón y cambia el texto
        $(this).closest('form').submit(); // Envía el formulario

    });






    });
</script>

@endpush

