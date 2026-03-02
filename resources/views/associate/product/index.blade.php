@extends('associate.layouts.master')
@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('frontend/css/associate/products-dataTable.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
                        <i class="bi bi-search"></i><input id="product-search" class="input-search-dataTable" placeholder="Buscar por nombre, sku, marca" />
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
                            <tbody id="products-tbody" >
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
                                            <img src="{{ asset($product->thumb_image) }}" loading="lazy" alt="">
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
                                        <td class="dataTable-add-cart" >Disabled</td>
                                        <td><button type="button" class="dataTable-btn" data-product-id="{{ $product->id }}">Ver Producto</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container" id="products-pager">
                        <div class="pagination-dataTable">
                            <button class="button-left" type="button"
                                data-page="{{ $productAssociate->currentPage() - 1 }}"
                                @disabled($productAssociate->onFirstPage())
                            >
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <button class="button-right" type="button"
                                data-page="{{ $productAssociate->currentPage() + 1 }}"
                                @disabled(!$productAssociate->hasMorePages())
                            >
                                <i class="bi bi-chevron-right"></i>
                            </button>

                            <p>
                                {{ $productAssociate->firstItem() }}-{{ $productAssociate->lastItem() }}
                                de {{ $productAssociate->total() }}
                            </p>
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
                    </div>
                </div>

                <div class="modal-right">
                    <p class="modal-type"></p>
                    <h2 class="modal-title"></h2>
                    <p class="modal-price"></p>
                    <p class="modal-shipping"></p>
                    <p class="modal-brand"></p>
                    <p class="modal-model"></p>
                    <p class="modal-line-brand">Linea:</p>
                    <hr>
                    <p class="modal-ficha">Ficha Técnica</p>
                    <a href="#" class="modal-ficha-link" target="_blank">
                        <i class="bi bi-file-earmark modal-ficha-icon"></i>
                    </a>
                    <hr>
                    <p class="modal-desc-title">Descripción:</p>
                    <div class="modal-desc" ></div>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
  <script>
(function($){
  var currentSearch = '';
  var typingTimer = null;
  var debounceMs = 350;
  var lastRequestId = 0;

  function setLoading(isLoading){
    // opcional: si quieres un estilo mientras carga (opacidad)
    $('#products-table').toggleClass('is-loading', isLoading);
  }

  function tbodySkeleton(){
    // opcional: skeleton simple mientras carga
    var cols = 9;
    var rows = 6;
    var html = '';
    for(var r=0; r<rows; r++){
      html += '<tr class="dataTable-row skeleton-row">';
      for(var c=0; c<cols; c++){
        html += '<td><div class="skel skel-line"></div></td>';
      }
      html += '</tr>';
    }
    return html;
  }

  // =========================
  // AJAX LOAD PAGE (WITH SEARCH)
  // =========================
  function loadPage(page){
    lastRequestId++;
    var reqId = lastRequestId;

    setLoading(true);
    $('#products-tbody').html(tbodySkeleton());

    $.get("{{ route('associate.products.index') }}", {
      page: page,
      search: currentSearch
    }, function(res){

      // si llegó una respuesta vieja, ignorarla
      if(reqId !== lastRequestId) return;

      // animación suave al renderizar
      var $tbody = $('#products-tbody');
      $tbody.stop(true,true).css('opacity', 0);

      $tbody.html(res.tbody);
      $('#products-pager').html(res.pager);

      $tbody.animate({opacity: 1}, 160);
      $('.table-responsive').scrollTop(0);

    }).fail(function(){
      if(window.toastr) toastr.error('No se pudo cargar la página.');
    }).always(function(){
      if(reqId === lastRequestId) setLoading(false);
    });
  }

  // =========================
  // PAGINATION CLICK
  // =========================
  $(document).on('click', '#products-pager .button-left, #products-pager .button-right', function(){
    var page = $(this).data('page');
    if(page) loadPage(page);
  });

  // =========================
  // LIVE SEARCH (DEBOUNCE)
  // =========================
  $(document).on('input', '#product-search', function(){
    var val = $(this).val() || '';
    clearTimeout(typingTimer);

    currentSearch = val;

    typingTimer = setTimeout(function(){
      // cuando cambias búsqueda, vuelves a página 1
      loadPage(1);
    }, debounceMs);
  });

  // =========================
  // MODAL FUNCTIONS
  // =========================
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

  function mapType(t){
    switch(t){
      case 'new_arrival': return 'Nuevo';
      case 'featured_product': return 'Producto Favorito';
      case 'top_product': return 'Producto Top';
      case 'best_product': return 'Más Vendido';
      default: return '';
    }
  }

  // =========================
  // OPEN PRODUCT MODAL (AJAX)
  // =========================
  $(document).on('click', '.dataTable-btn', function(e){
    e.preventDefault();

    var id = $(this).data('product-id');

    if(!id){
      console.warn('No se encontró data-product-id en el botón');
      return;
    }

    $.get("{{ url('associate/products') }}/" + id, function(data){

      $('#product-modal .modal-type').text(mapType(data.type || ''));
      $('#product-modal .modal-title').text(data.name || '');
      $('#product-modal .modal-model').text('Modelo: ' + (data.model || ''));
      $('#product-modal .modal-brand').text('Marca: ' + (data.brand || 'Sin marca'));
      $('#product-modal .modal-desc').text(data.description || 'Sin descripción');
      $('#product-modal .modal-shipping').text(data.shipping_text || '');

      // PDF link
      if (data.pdf_url && data.pdf_url !== '') {
        $('#product-modal .modal-ficha-link').attr('href', data.pdf_url).show();
      } else {
        $('#product-modal .modal-ficha-link').attr('href', '#').hide();
      }

      // Imagen principal
      var mainImg = data.thumb || '';
      $('#product-modal .modal-image-container img').attr('src', mainImg);

      // Precio
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
          $gallery.append(
            '<div class="gallery-thumb" data-src="'+src+'">' +
              '<img src="'+src+'" alt="thumb">' +
            '</div>'
          );

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

  // Cambiar imagen al click en thumb
  $(document).on('click', '#product-modal .gallery-thumb', function(){
    var src = $(this).data('src');
    if(src){
      $('#product-modal .modal-image-container img').attr('src', src);
    }
  });

  // Close modal
  $(document).on('click', '#product-modal .modal-close, #product-modal .modal-overlay', function(){
    closeProductModal();
  });

  $(document).on('keydown', function(e){
    if(e.key === 'Escape') closeProductModal();
  });

})(jQuery);
</script>


@endpush
