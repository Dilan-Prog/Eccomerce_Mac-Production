@extends('associate.layouts.master')
@push('styles')
    <link rel="stylesheet" href="{{asset('frontend/css/associate/products-dataTable.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">\
@endpush
@section('content')
    <section class="section">
      <div class="section-header">
        <h1>Productos</h1>
      </div>
      <div class="section-body">
        <div class="row">
          <div class="col-12">
                <div class="dataTable-content" id="products-table" >
                    <div class="content-search-dataTable">
                        <input id="product-search" class="input-search-dataTable" placeholder="Buscar por nombre, sku, marca" />
                    </div>
                    <div class="table-responsive">
                        <table class="table-main">
                            <thead class="table-col">
                                <tr>
                                    <th></th>
                                    <th>Producto</th>
                                    <th>Clave Prod.</th>
                                    <th>Modelo</th>
                                    <th>Marca</th>
                                    <th>Precio</th>
                                    <th>Existencias</th>
                                    <th>Cantidad</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="dataTable-content">
                                @foreach ($productAssociate as $product)
                                    @php
                                        $precio = $product->price_personalizated == 1
                                            ? $product->price
                                            : ($aspelPrecio[$product->sku] ?? $product->price);

                                        $mon = (isset($aspelMoneda[$product->sku]) && (int)$aspelMoneda[$product->sku] === 2) ? 'USD' : 'MXN';
                                    @endphp
                                    <tr class="dataTable-row">
                                        <td class="dataTable-content-image">
                                            <div class="dataTable-image">
                                            <img src="{{ asset($product->thumb_image) }}" alt="">
                                            </div>
                                        </td>
                                        <td class="dataTable-title">{{ $product->name }}</td>
                                        <td class="dataTable-sku">{{ $product->sku }}</td>
                                        <td class="dataTable-model">{{ $product->productModel }}</td>
                                        <td class="dataTable-brand">{{ $product->brand->name }}</td>
                                        <td class="dataTable-price">
                                            {{ number_format($precio, 2, ',', '.') }} {{ $mon }}
                                        </td>
                                        <td class="dataTable-qty">{{ $product->qty_personalizated == 1 ? $product->qty : $product->qty_aspel }}</td>
                                        <td class="dataTable-add-cart" aria-disabled="true">Disabled</td>
                                        <td><button type="button" class="dataTable-btn" data-product-id="{{ $product->id }}">Ver Producto</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container">
                        <div class="pagination-dataTable">
                            <button class="button-left"><i class="bi bi-chevron-left"></i></button>
                            <button class="button-right"><i class="bi bi-chevron-right"></i></button>
                            <p>1-100</p>

                        </div>
                    </div>
                </div>
          </div>
        </div>
    </section>
    <!-- Product-Modal -->
    <div id="product-modal" class="product-modal" aria-hidden="true">
    <div class="modal-overlay"></div>
        <div class="modal-wrapper" role="dialog" aria-modal="true">
            <button type="button" class="modal-close">&times;</button>

            <div class="modal-inner">

                <div class="modal-left">
                    <div class="modal-details-title">
                    <h3>Detalles Del Producto</h3>
                    </div>

                    <div class="modal-image-container">
                    <img src="" alt="" id="modal-main-image">
                    </div>

                    <div class="modal-gallery-images">
                    <!-- JS insertará thumbnails aquí -->
                    </div>
                </div>

                <div class="modal-right">
                    <p class="modal-type">{{ $product->type }}</p>
                    <h2 class="modal-title">{{ $product->name }}</h2>
                    <p class="modal-price"></p>
                    <p class="modal-shipping"></p>
                    <p class="modal-brand">{{ $product->brand->name ?? 'Sin marca' }}</p>
                    <p class="modal-model">{{ $product->productModel }}</p>
                    <p class="modal-line-brand">Linea:</p>

                    <hr>

                    <p class="modal-ficha">Ficha Técnica</p>
                    <a href="#" class="modal-ficha-link">
                    <i class="bi bi-file-earmark modal-ficha-icon"></i>
                    </a>

                    <hr>

                    <p class="modal-desc-title">Descripción:</p>
                    <p class="modal-desc"></p>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
  <script>
    // legacy change-status handler preserved (uses jQuery)
    $(document).on('click', '.change-status', function(){
      let isChecked = $(this).is(':checked');
      let id = $(this).data('id');
      $.ajax({
        url:"{{route('admin.product.change-status')}}",
        method:'PUT',
        data:{ status:isChecked, id:id },
        success:function(data){ toastr.success(data.message); }
      });
    });
  </script>

  <script>
    (function($){

                function openProductModal(){
            var $modal = $('#product-modal');
            $modal.addClass('open').attr('aria-hidden','false');
            $('body').css('overflow','hidden');
        }

        function closeProductModal(){
            var $modal = $('#product-modal');
            $modal.removeClass('open').attr('aria-hidden','true');
            $('body').css('overflow','');
        }

        // Helper para mapear tipos a texto legible
        function mapType(t){
            switch(t){
            case 'new_arrival': return 'Nuevo';
            case 'featured_product': return 'Producto Favorito';
            case 'top_product': return 'Producto Top';
            case 'best_product': return 'Más Vendido';
            default: return '';
            }
        }

        // Click en botón "Ver Producto"
        $(document).on('click', '.dataTable-btn', function(e){
            e.preventDefault();

            // OJO: tu botón tiene data-product-id
            var id = $(this).data('product-id');

            if(!id){
            console.warn('No se encontró data-product-id en el botón');
            return;
            }

            $.get("{{ url('associate/products') }}/" + id, function(data){

            // Texto
            $('#product-modal .modal-type').text(mapType(data.type || ''));
            $('#product-modal .modal-title').text(data.name || '');
            $('#product-modal .modal-model').text('Modelo: ' + (data.model || ''));
            $('#product-modal .modal-brand').text('Marca: ' + (data.brand || 'Sin marca'));
            $('#product-modal .modal-desc').text(data.description || 'Sin descripción');
            $('#product-modal .modal-shipping').text(data.shipping_text || '');
            // Imagen principal
            var mainImg = data.thumb || '';
            $('#product-modal .modal-image-container img').attr('src', mainImg);

            // Precio (si viene)
            if (data.price !== null && data.price !== undefined && data.price !== '') {
                var formatted = Number(data.price).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
                });
                $('#product-modal .modal-price').text('$' + formatted + ' ' + (data.currency || 'MXN'));
            } else {
                $('#product-modal .modal-price').text('');
            }

            // Galería
            var $gallery = $('#product-modal .modal-gallery-images');
            $gallery.empty();

            if (Array.isArray(data.gallery) && data.gallery.length > 0) {
                data.gallery.forEach(function(src, idx){
                // thumb
                $gallery.append(
                    '<div class="gallery-thumb" data-src="'+src+'">' +
                    '<img src="'+src+'" alt="thumb">' +
                    '</div>'
                );

                // si no hay thumb principal, usa la primera imagen de galería
                if(!mainImg && idx === 0){
                    $('#product-modal .modal-image-container img').attr('src', src);
                }
                });
            }

            openProductModal();

            }).fail(function(){
            if (window.toastr) toastr.error('No se pudo cargar el producto.');
            });
        });

        // Click en thumbnail: cambia imagen principal
        $(document).on('click', '#product-modal .gallery-thumb', function(){
            var src = $(this).data('src');
            if(src){
            $('#product-modal .modal-image-container img').attr('src', src);
            }
        });

        // Cerrar modal
        $(document).on('click', '#product-modal .modal-close, #product-modal .modal-overlay', function(){
            closeProductModal();
        });

        $(document).on('keydown', function(e){
            if(e.key === 'Escape') closeProductModal();
        });

    })(jQuery);
</script>


@endpush
