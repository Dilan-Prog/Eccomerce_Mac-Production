<script>
    $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        //Shopping cart form
        $(document).on('submit', '.shopping-cart-form', function(e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                method: 'POST',
                data: formData,
                url: "{{ route('add-to-cart') }}",
                success: function(data) {
                    if(data.status === 'success'){
                        getCartCount()
                        fetchSidebarCartProducts()
                        $('.mini_cart_actions').removeClass('d-none');
                        toastr.success(data.message);
                    }else if (data.status === 'error'){
                        toastr.error(data.message);
                    }
                },
                error: function(data) {

                }
            })
        })

        function getCartCount(){
            $.ajax({
                method:'GET',
                url: "{{route('cart-count')}}",
                success: function(data){
                    $('#cart-count').text(data);
                },
                error: function(data){

                }
            })
        }

        function fetchSidebarCartProducts(){
            $.ajax({
                method: 'GET',
                url: "{{route('cart-products')}}",
                success: function(data){
                    console.log(data);
                    $('.mini_cart_wrapper').html("");
                    var html = '';
                    for(let item in data){
                        let product = data[item];                                                    //{{--aqui puede ser {{asset(${product.options.image})}}--}}
                        let formattedPrice = formatNumber(parseFloat(product.price));
                        html += `
                        <li id="mini_cart_${product.rowId}">
                            <div class="wsus__cart_img">
                                <a href="{{ url('product-detail') }}/${product.options.slug}"><img src="${product.options.image}" alt="product" class="img-fluid w-100"></a>
                                <a class="wsis__del_icon remove_sidebar_product" data-Id="${product.rowId}" href=""><i class="fas fa-minus-circle"></i></a>
                            </div>
                            <div class="wsus__cart_text">
                                <a class="wsus__cart_title" href="{{url('product-detail/')}}${product.options.slug}">${product.name}</a>
                                <h6>"modelo"</h6>
                                <p>{{$settings->currency_icon}}${formattedPrice}</p>
                                <small>Modelo:<br> ${product.options.productModel}</small>
                                <br>
                                <small>Cantidad:${product.qty}</small>
                            </div>
                        </li>`
                    }
                    $('.mini_cart_wrapper').html(html);
                    getSidebarCartSubtotal();
                },
                error: function(data){

                }
            })
        }
        //remove
        $('body').on('click', '.remove_sidebar_product', function(e) {
            e.preventDefault()
            let rowId = $(this).data('id');
            $.ajax({
                method: 'POST',
                url: "{{ route('cart.remove-sidebar-product') }}",
                data: {
                    rowId: rowId
                },
                success: function(data) {
                    let productId = '#mini_cart_' + rowId;
                    $(productId).remove()
                    getCartCount()
                    getSidebarCartSubtotal();

                    if ($('.mini_cart_wrapper').find('li').length === 0) {
                        $('.mini_cart_actions').addClass('d-none');
                        $('.mini_cart_wrapper').html(
                            '<li class="text-center">Carrito Vacio!</li>');
                    }
                    toastr.success(data.message);
                    
                },
                error: function(data) {
                    console.log(data);
                }
            })
        })

        // get sidebar cart sub total
        function getSidebarCartSubtotal() {
            $.ajax({
                method: 'GET',
                url: "{{route('cart.sidebar-product-total')}}",
                success: function(data) {
                    let productTotal = parseFloat(data);
                    let formatProductTotal = formatNumber(productTotal);
                    $('#mini_cart_subtotal').text("{{ $settings->currency_icon }}" + formatProductTotal);
                },
                error: function(data) {
                    // Manejo del error si es necesario
                }
            })
        }


        function formatNumber(num) {
        return num.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    //Recarga automaticamente
        window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Si la página fue cargada desde el historial (no una recarga completa)
            window.location.reload();
        }
    });

    


    })


</script>
