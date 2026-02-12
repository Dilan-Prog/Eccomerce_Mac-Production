@extends('associate.layouts.master')
@push('styles')
    <link rel="stylesheet" href="{{asset('frontend/css/associate/products-dataTable.css')}}">

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
                                    <th>Modelo</th>
                                    <th>Marca</th>
                                    <th>Precio</th>
                                    <th>Existencias</th>
                                    <th>Cantidad</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="dataTable-content">
                                <thead class="dataTable-subContent">
                                    <tr>
                                        <th class="dataTable-content-image">
                                            <div class="dataTable-image">
                                                <img src="{{ asset('uploads/image-png/media_6660077231c58.dc2500_converted.png') }}" alt="">
                                            </div>
                                        </th>
                                        <th class="dataTable-title">Control de Temperatura DC1010CT-100-0000-000</th>
                                        <th class="dataTable-model">DC1010CT-100-0000-000</th>
                                        <th class="dataTable-brand">Honelwell Process</th>
                                        <th class="dataTable-price">$4,600.00 MXN</th>
                                        <th class="dataTable-qty">5</th>
                                        <th class="dataTable-add-cart">Disabled</th>
                                        <th><button type="submit" class="dataTable-btn">Ver Producto</button></th>
                                    </tr>
                                </thead>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <label>Mostrar
                        <select id="per-page" class="form-select d-inline-block" style="width: auto;">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        entradas</label>
                    </div>
                    <nav>
                        <ul id="product-pagination" class="pagination mb-0">
                        <!-- pagination rendered by JS -->
                        </ul>
                    </nav>
                    </div>
                </div>
          </div>
        </div>
    </section>
    <!-- Product-Modal -->
    <div id="product-modal" class="product-modal" aria-hidden="true">
        <div class="modal-overlay" data-close="true"></div>
        <div class="modal-wrapper" role="dialog" aria-modal="true">
            <button type="button" class="modal-close" aria-label="Cerrar">&times;</button>
            <div class="modal-inner">
                <div class="modal-left">
                    <h3>Detalles Del Producto</h3>
                    <img src="" alt="Imagen del producto" class="modal-image">
                </div>
                <div class="modal-right">
                    <h2 class="modal-title">Título del producto</h2>
                    <p class="modal-price"></p>
                    <p class="modal-brand"></p>
                    <p class="modal-model"></p>
                    <hr>
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
        // Modal handling for product details
        (function($){
            function openProductModal(data){
                var $modal = $('#product-modal');
                $modal.find('.modal-image').attr('src', data.image || '');
                $modal.find('.modal-image').attr('alt', data.title || '');
                $modal.find('.modal-title').text(data.title || '');
                $modal.find('.modal-price').text(data.price || '');
                $modal.find('.modal-brand').text(data.brand || '');
                $modal.find('.modal-model').text(data.model || '');
                $modal.find('.modal-desc').text(data.desc || '');
                $modal.addClass('open').attr('aria-hidden','false');
                $('body').css('overflow','hidden');
            }

            function closeProductModal(){
                var $modal = $('#product-modal');
                $modal.removeClass('open').attr('aria-hidden','true');
                $('body').css('overflow','');
            }

            // Open modal when clicking the product button
            $(document).on('click', '.dataTable-btn', function(e){
                e.preventDefault();
                var $row = $(this).closest('tr');
                var data = {
                    image: $row.find('.dataTable-image img').attr('src'),
                    title: $row.find('.dataTable-title').text().trim(),
                    model: $row.find('.dataTable-model').text().trim(),
                    brand: $row.find('.dataTable-brand').text().trim(),
                    price: $row.find('.dataTable-price').text().trim(),
                    desc: $row.find('.dataTable-title').text().trim() // fallback, replace if available
                };
                openProductModal(data);
            });

            // Close handlers
            $(document).on('click', '#product-modal .modal-close, #product-modal .modal-overlay', function(){
                closeProductModal();
            });

            $(document).on('keydown', function(e){
                if(e.key === 'Escape') closeProductModal();
            });

        })(jQuery);
    </script>

@endpush
