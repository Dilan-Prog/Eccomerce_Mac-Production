@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Detalles De Carrito
@endsection



@section('content')
    <section id="wsus__cart_view">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 ">
                    <div class="wsus__cart_list">
                        <div class="table-responsive">
                            <table>
                                <tbody>
                                    <tr class="d-flex">
                                        <th class="wsus__pro_img">
                                            Producto
                                        </th>

                                        <th class="wsus__pro_name">
                                            Detalles Del Producto
                                        </th>


                                        <th class="wsus__pro_tk">
                                            Precio Unitario
                                        </th>

                                        <th class="wsus__pro_tk">
                                            Total
                                        </th>

                                        <th class="wsus__pro_select">
                                            Cantidad
                                        </th>


                                        <th class="wsus__pro_icon">
                                            <a href="#" class="common_btn clear_cart">Limpiar Carrito</a>
                                        </th>
                                    </tr>
                                    @foreach ($cartItems as $item )
                                    <tr class="d-flex">

                                        <td class="wsus__pro_img"><img src="{{asset($item->options->image)}}" alt="product"
                                                class="img-fluid w-100">
                                        </td>

                                        <td class="wsus__pro_name text-center">
                                            <p>{!!$item->name!!}</p>
                                            <small>Marca:{{$item->options->brand_name}}</small>
                                            <small>Modelo:{{ $item->options->productModel }}</small>
                                        </td>


                                        <td class="wsus__pro_tk">
                                            <h6>{{ $settings->currency_icon . number_format($item->price, 2, '.', ',') }}</h6>
                                        </td>

                                        <td class="wsus__pro_tk">
                                            <h6 id="{{$item->rowId}}">{{ $settings->currency_icon . number_format($item->price * $item->qty, 2, '.', ',') }}</h6>
                                        </td>

                                        <td class="wsus__pro_select">
                                            <form class="product_qty_wrapper">
                                                <button type="button" class="btn product-decrement" style="background-color:#00468c; color: white;" >-</button>
                                                <input class="product-qty" data-rowid='{{$item->rowId}}' type="text" min="1" max="100" value="{{ $item->qty }}" readonly />
                                                <button type="button" class="btn product-increment" style="background-color:#00468c; color: white;">+</button>
                                            </form>
                                        </td>

                                        <td class="wsus__pro_icon">
                                            <a href="{{route('cart.remove-product', $item->rowId)}}"><i class="far fa-times"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach

                                    @if (count($cartItems) == 0)
                                    <tr class="d-flex">
                                        <td class="wsus_pro_icon m-2" rowspan="2" style="width: 100%">
                                            Tu carrito esta vacio!
                                        </td>
                                    </tr>
                                    @endif

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 ">
                    <div class="wsus__cart_list_footer_button" id="sticky_sidebar">
                        <h6>Total Del Carrito</h6>
                        <p>subtotal: <span id="sub_total">{{ $settings->currency_icon }}{{ formatCurrency(getCartTotal()) }}</span></p>
                        <p>Envio: <span>$00.00</span></p>
                        <p>Descuento: <span id="discount">{{ $settings->currency_icon }}{{ formatCurrency(getCartDiscount()) }}</span></p>
                        <p class="total"><span >Total:</span> <span id="cart_total">{{ $settings->currency_icon }}{{ formatCurrency(getMainCartTotal()) }}</span></p>

                        <form id="coupon_form">
                            <input type="text" placeholder="Cupon" name="coupon_code" value="{{ session()->has('coupon') ? session()->get('coupon')['coupon_code'] : '' }} ">
                            <button type="submit" class="common_btn">Aplicar</button>
                        </form>
                        <a class="common_btn mt-4 w-100 text-center" href="{{ route('user.checkout') }}">Continuar Compra</a>
                        <a class="common_btn mt-1 w-100 text-center" href="{{ route('index') }}"><i class="fab fa-shopify"></i>Seguir Comprando</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--============================
          CART VIEW PAGE END
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

        $('.product-increment').on('click', function(){
            let input = $(this).siblings('.product-qty');
            let quantity = parseInt(input.val()) + 1;
            let rowId = input.data('rowid');
            input.val(quantity);

            $.ajax({
                url: "{{route('cart.update-quantity')}}",
                method: 'POST',
                data: {
                    rowId: rowId,
                    quantity: quantity
                },
                success: function(data){
                    if(data.status === 'success'){
                    let productId = '#'+rowId;
                    let totalAmount = "{{ $settings->currency_icon }}"+formatNumber(data.product_total);
                    renderCartSubTotal()
                    calculateCouponDescount()
                    $(productId).text(totalAmount)
                        toastr.success(data.message)
                    }else if (data.status === 'error'){
                        toastr.error(data.message);
                    }
                },
                error: function(data){

                }
            })
        })

        //Decrement Product Quantity
        $('.product-decrement').on('click', function(){
            let input = $(this).siblings('.product-qty');
            let quantity = parseInt(input.val()) - 1;
            let rowId = input.data('rowid');
            if(quantity < 1){
                quantity = 1;
            }
            input.val(quantity);

            $.ajax({
                url: "{{route('cart.update-quantity')}}",
                method: 'POST',
                data: {
                    rowId: rowId,
                    quantity: quantity
                },
                success: function(data){
                    if(data.status == 'success'){
                    let productId = '#'+rowId;
                    let totalAmount = "{{ $settings->currency_icon }}"+formatNumber(data.product_total);
                    $(productId).text(totalAmount)
                    renderCartSubTotal()
                    calculateCouponDescount()
                        toastr.success(data.message)
                    }else if (data.status === 'error'){
                        toastr.error(data.message);
                    }
                },
                error: function(data){

                }
            })
        })

        //Clear cart
        $('.clear_cart').on('click', function(e){
            e.preventDefault();
            Swal.fire({
                    title: 'Estas seguro?',
                    text: "This action will clear your cart!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, clear it!'
                    }).then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            type: 'get',
                            url: "{{route('clear.cart')}}",
                            success: function(data){
                                if(data.status === 'success'){
                                    window.location.reload();
                                }
                            },
                            error: function(xhr, status, error){
                                console.log(error);
                            }
                        })
                    }
                })
        })
        //get subtotal (increment and decrement)
        function renderCartSubTotal(){
            $.ajax({
                method: 'GET',
                url: "{{route('cart.sidebar-product-total')}}",
                success: function(data) {
                    let subtotal = parseFloat(data);
                    let formattedSubtotal = formatNumber(subtotal);
                    $('#sub_total').text("{{$settings->currency_icon}}"+formattedSubtotal)
                },
                error: function(data) {
                    console.log(data);
                }
            })


        }



        // applay coupon on cart

        $('#coupon_form').on('submit', function(e){
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                method: 'GET',
                url: "{{ route('apply-coupon') }}",
                data: formData,
                success: function(data) {
                   if(data.status === 'error'){
                    toastr.error(data.message)
                   }else if (data.status === 'success'){
                    calculateCouponDescount()
                    toastr.success(data.message)
                   }
                },
                error: function(data) {
                    console.log(data);
                }
            })

        })

        // calculate discount amount
        function calculateCouponDescount(){
            $.ajax({
                method: 'GET',
                url: "{{ route('coupon-calculation') }}",
                success: function(data) {
                    let cartTotal = parseFloat(data.cart_total);
                    let discount = parseFloat(data.discount);

                    let formattedCartTotal = formatNumber(cartTotal);
                    let formattedDiscount = formatNumber(discount);
                    if(data.status === 'success'){
                        $('#discount').text('{{$settings->currency_icon}}' + formattedDiscount);
                        $('#cart_total').text('{{$settings->currency_icon}}' + formattedCartTotal);
                    }
                },
                error: function(data) {
                    console.log(data);
                }
            })
        }


    })

    function formatNumber(num) {
    return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

</script>

@endpush
