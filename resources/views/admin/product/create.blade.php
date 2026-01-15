@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nuevo Productos</h1>
      </div>
      <div class="section-body">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Crear Nuevo Productos</h4>
              </div>
              <div class="card-body">
                <form action="{{route('admin.products.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Imagen</label>
                        <input type="file" class="form-control" name="image">
                    </div>
                    <!--Star for promotion text animations-->
                    <div class="form-group">
                        <label>Nombre del Producto</label>
                        <input type="text" class="form-control" name="name" value="">
                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputState">Categoria</label>
                              <select id="inputState" class="form-control main-category" name="category">
                                <option value="">Seleccionar</option>
                                @foreach ($categories as $category )
                                <option value="{{$category->id}}" {{ old('category') == $category->id ? 'selected' : '' }}>{{$category->name}}</option>
                                @endforeach
                              </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputState">Sub Categoria</label>
                              <select id="inputState" class="form-control sub-category" name="sub_category">
                                <option value="">Seleccionar</option>
                              </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputState">Categoria Secundaria</label>
                              <select id="inputState" class="form-control child-category" name="child_category">
                                <option value="">Seleccionar</option>
                              </select>
                        </div>
                      </div>
                    </div>

                      <div class="form-group">
                          <label for="inputState">Marca</label>
                            <select id="inputState" class="form-control" name="brand">
                              <option selected="">Seleccionar</option>
                              @foreach ($brands as $brand)
                              <option value="{{$brand->id}}" {{ old('brand') == $brand->id ? 'selected' : '' }}>{{$brand->name}}</option>
                              @endforeach
                            </select>
                      </div>

                    <div class="form-group position-relative">
                      <label>Sku</label><small id="sku-status" class="form-text d-block mt-2"></small>
                      <input type="text" class="form-control sku-input" name="sku" value="{{old('sku')}}" placeholder="Escribe el SKU para buscar">
                      <div id="sku-dropdown" class="list-group" style="position: absolute; top: 100%; left: 0; right: 0; max-height: 250px; overflow-y: auto; display: none; z-index: 1000; margin-top: 2px;"></div>
                    </div>
                  <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" class="form-control" name="productModel" value="{{old('productModel')}}">
                  </div>
                  <div class="form-group">
                      <label for="inputState">Precio Personalizado</label>
                      <select id="inputState" class="form-control" name="price_personalizated">
                        <option value="0" {{ old('price_personalizated') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('price_personalizated') == '1' ? 'selected' : '' }}>Si</option>
                      </select>
                  </div>
                  <div class="form-group">
                      <label>Precio</label>
                      <input type="text" class="form-control" name="price" value="{{old('price')}}">
                  </div>
                  <div class="form-group">
                    <label>Precio Aspel</label>
                        <select class="form-control" name="aspel_price" id="aspel-price-select">
                            <option value="">Sin precios SAE para este SKU</option>
                        </select>
                </div>

                  <div class="form-group">
                      <label for="inputState">Precio Oferta Personalizado</label>
                      <select id="inputState" class="form-control" name="price_offert_personalizated">
                        <option value="0" {{ old('price_offert_personalizated') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('price_offert_personalizated') == '1' ? 'selected' : '' }}>Si</option>
                      </select>
                  </div>
                  <div class="form-group">
                      <label>Precio De Oferta</label>
                      <input type="text" class="form-control" name="offert_price" value="{{old('offert_price')}}">
                  </div>
                  <div class="form-group">
                      <label>Precio De Oferta Aspel</label>
                        <select class="form-control" name="aspel_offert_price" id="aspel-offert-price-select">
                          <option value="" >Sin precios SAE para este SKU</option>
                        </select>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="inputState">Fecha De Inicio De Oferta</label>
                            <input type="text" class="form-control datepicker" name="offer_start_date" value="{{old('offer_start_date')}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="inputState">Fecha De Final De Oferta</label>
                            <input type="text" class="form-control datepicker" name="offer_end_date" value="{{old('offer_end_date')}}">
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                      <label for="inputState">Cantidad Personalizada</label>
                      <select id="inputState" class="form-control" name="qty_personalizated">
                        <option value="0" {{ old('qty_personalizated') == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('qty_personalizated') == '1' ? 'selected' : '' }}>Si</option>
                      </select>
                  </div>
                  <div class="form-group">
                      <label>Cantidad En Stock</label>
                      <input type="number" class="form-control" min="0" name="qty" value="{{old('qty')}}">
                  </div>
                  <div class="form-group">
                      <label>Cantidad Aspel</label>
                      <input type="number" class="form-control" min="0" name="qty_aspel" value="{{old('qty_aspel')}}" placeholder="Ingrese cantidad manual">
                  </div>
                  <div class="form-group">
                      <label>Video Link (Formato embed https://www.youtube.com/embed/ AQUI CAMBIAR EL ID)</label>
                      <input type="text" class="form-control" name="video_link" value="{{old('video_link')}}">
                  </div>
                  <div class="form-group">
                    <label>Url de drive para el PDF</label>
                    <input type="text" class="form-control" name="url_PDF" value="{{old('url_PDF')}}">
                  </div>
                  <div class="form-group">
                      <label>Garantia</label>
                      <textarea type="text" name="short_description"  class="form-control"></textarea>
                  </div>
                  <div class="form-group">
                      <label>Descripción Larga</label>
                      <textarea type="text" name="long_description" class="form-control "></textarea>
                  </div>


                  <div class="form-group">
                      <label for="inputState">Tipo De Producto</label>
                        <select id="inputState" class="form-control" name="product_type">
                          <option value="0" {{ old('product_type') == '0' ? 'selected' : '' }}>Seleccionar...</option>
                          <option value="new_arrival" {{ old('product_type') == 'new_arrival' ? 'selected' : '' }}>Nuevo</option>
                          <option value="featured_product" disabled>Producto Destacado</option>
                          <option value="top_product" {{ old('product_type') == 'top_product' ? 'selected' : '' }}>Más Buscado</option>
                          <option value="best_product" {{ old('product_type') == 'best_product' ? 'selected' : '' }}>Más Vendido</option>

                        </select>
                  </div>


                  <div class="form-group">
                      <label>Seo Titulo</label>
                      <input type="text" class="form-control" name="seo_title" value="{{old('seo_title')}}">
                  </div>
                  <div class="form-group">
                      <label>Seo Descripción</label>
                      <textarea type="text" class="form-control" name="seo_description" value="{{old('seo_description')}}"></textarea>
                  </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                          <select id="inputState" class="form-control" name="status">
                            <option value="none" {{ old('status') == 'none' ? 'selected' : '' }} >Seleccionar...</option>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                          </select>
                    </div>

                  <div class="form-group">
                    <label>Es Url Canonica (Solo Marketing)</label>
                    <select class="form-control" name="is_canonical">
                      <option value="">Seleccionar</option>
                      <option value="1" {{ old('is_canonical') == '1' ? 'selected' : '' }}>Si</option>
                      <option value="0" {{ old('is_canonical') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Url Canonica Personalizada (Solo Marketing)</label>
                    <div class="wsus__topbar_select">
                        <input class="form-control" name="canonical_url" value="{{old('canonical_url')}}" placeholder="Poner URL Canonical en caso de no tener una afiliacion y ser unica dejar el campo vacio y posteriormente rellenarlo">
                    </div>
                  </div>


                {{-- Base Search Canonical URL --}}

                  {{-- <div class="form-group">
                    <label>Url Canonica Personalizada</label>
                    <div class="wsus__topbar_select">
                        <select class="form-control" name="canonical_url" id="product-search">


                        </select>
                    </div>
                </div>
                 --}}

                 {{-- End Base Search Canonical URL --}}


                    <button type="submit" class="btn btn-primary" id="submit-btn">Crear</button>
                </form>
              </div>

            </div>
          </div>

        </div>

      </div>
    </section>




@endsection
@push('scripts')
<script>
  $(document).ready(function(){

    $('body').on('change', '.main-category',function(e){
      let id = $(this).val();
      $.ajax({
        method: 'GET',
        url:"{{route('admin.product.get-subcategories')}}",
        data: {
          id:id
        },
        success: function(data){

          $('.sub-category').html('<option value="">Select</option>')
          $.each(data, function(i, item){

            $('.sub-category').append(`<option value="${item.id}">${item.name}</option>`)

          })
        },
        error:function(xhr,status,error){
        console.log(error);
      }

      })

    })
    /**get child categories*/
    $('body').on('change', '.sub-category',function(e){
      let id = $(this).val();
      $.ajax({
        method: 'GET',
        url:"{{route('admin.product.get-child-categories')}}",
        data: {
          id:id
        },
        success: function(data){

          $('.child-category').html('<option value="">Select</option>')
          $.each(data, function(i, item){

            $('.child-category').append(`<option value="${item.id}">${item.name}</option>`)

          })
        },
        error:function(xhr,status,error){
        console.log(error);
      }

      })

    })

    // Búsqueda automática de SKU con dropdown
    let skuSearchTimeout;
    let selectedSkuData = null;
    
    $('.sku-input').on('keyup', function(){
      let sku = $(this).val().trim();
      let dropdown = $('#sku-dropdown');
      let statusElement = $('#sku-status');
      
      clearTimeout(skuSearchTimeout);
      
      if(sku.length < 1){
        dropdown.hide();
        statusElement.html('');
        selectedSkuData = null;
        return;
      }
      
      statusElement.text('Buscando...').removeClass('text-success text-warning text-danger').addClass('text-muted');
      
      skuSearchTimeout = setTimeout(function(){
        $.ajax({
          method: 'GET',
          url: "{{route('admin.product.search-sku')}}",
          data: { sku: sku },
          success: function(data){
            dropdown.html('');
            
            // Si hay resultados
            if(data && data.length > 0){
              data.forEach(function(item, index){
                let displayText = item.cve_art + ' - ' + (item.descr || 'Sin descripción');
                let warningClass = item.exists_in_products ? ' border-warning' : '';
                let option = $('<a href="#" class="list-group-item list-group-item-action sku-option'+warningClass+'" data-index="'+index+'">'+displayText+'</a>');
                dropdown.append(option);
              });
              dropdown.show();
              statusElement.html('');
              // Guardar los datos para usar después
              window.skuResults = data;
            } else {
              statusElement.html('<div class="text-danger">No se encontraron resultados</div>');
              dropdown.hide();
              selectedSkuData = null;
            }
          },
          error: function(xhr, status, error){
            console.log(error);
            statusElement.html('<div class="text-danger">Error al buscar</div>');
            dropdown.hide();
            selectedSkuData = null;
          }
        });
      }, 300);
    });
    
    // Al hacer click en una opción del dropdown
    $(document).on('click', '.sku-option', function(e){
      e.preventDefault();
      let index = $(this).data('index');
      let data = window.skuResults[index];
      
      // Establecer el valor en el input
      $('.sku-input').val(data.cve_art);
      $('#sku-dropdown').hide();
      selectedSkuData = data;
      
      // Auto-llenar datos de Aspel
      if(data){
        // Auto-llenar cantidad Aspel
        $('input[name="qty_aspel"]').val(data.exist);
        
        // Si hay precios de Aspel, mostrar en los campos correspondientes
        if(data.aspel_prices && data.aspel_prices.length > 0){
          // Para precio normal
          let precioInput = $('input[name="aspel_price"]');
          precioInput.val(data.aspel_prices[0].precio);
          
          // Para precio oferta (si existe más de un precio)
          if(data.aspel_prices.length > 1){
            let ofertaInput = $('input[name="aspel_offert_price"]');
            ofertaInput.val(data.aspel_prices[1].precio);
          }
        }
      }
      
      let statusElement = $('#sku-status');
      
      if(data.exists_in_products){
        // Si el SKU ya existe, solo mostrar el error
        statusElement.html('<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> <strong>Este SKU ya existe en productos. No se puede crear.</strong></div>');
        $('#submit-btn').prop('disabled', true).addClass('disabled');
      } else {
        // Si el SKU no existe, mostrar el mensaje de éxito
        let html = '<div class="text-success"><i class="fas fa-check-circle"></i> '+data.cve_art+' seleccionado</div>';
        statusElement.html(html);
        $('#submit-btn').prop('disabled', false).removeClass('disabled');
      }

      // Después de selectedSkuData = data;
      $.ajax({
          url: "{{ route('admin.product.aspel-prices') }}",
          method: "GET",
          data: { sku: data.cve_art },
          success: function(res) {
              // Llenar aspel_price
              let select = $('select[name="aspel_price"]');
              select.html('');
              if(res.prices && res.prices.length > 0){
                  select.append('<option value="">Seleccionar...</option>');
                  res.prices.forEach(function(opt){
                      select.append(
                          `<option value="${opt.priceWithIva}">
                              ${opt.desc}--${res.symbol}${Number(opt.val).toFixed(2)} ${res.currency}
                              / Precio Con IVA MXN $${Number(opt.priceWithIva).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (${Number(res.iva).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}% IVA)
                          </option>`
                      );
                  });
              } else {
                  select.append('<option value="">Sin precios SAE para este SKU</option>');
              }
              select.prop('disabled', false);

              // Llenar aspel_offert_price igual
              let selectOffert = $('select[name="aspel_offert_price"]');
              selectOffert.html('');
              if(res.prices && res.prices.length > 0){
                  selectOffert.append('<option value="">Seleccionar...</option>');
                  res.prices.forEach(function(opt){
                      selectOffert.append(
                          `<option value="${opt.priceWithIva}">
                              ${opt.desc}--${res.symbol}${Number(opt.val).toFixed(2)} ${res.currency}
                              / Precio Con IVA MXN $${Number(opt.priceWithIva).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (${Number(res.iva).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}% IVA)
                          </option>`
                      );
                  });
              } else {
                  selectOffert.append('<option value="">Sin precios SAE para este SKU</option>');
              }
              selectOffert.prop('disabled', false);
          }
      });
    });
    
    // Cerrar dropdown al hacer click fuera
    $(document).on('click', function(e){
      if(!$(e.target).closest('.form-group').length){
        $('#sku-dropdown').hide();
      }
    });



    // Validación al enviar el formulario
    $('form').on('submit', function(e){
      if(selectedSkuData && selectedSkuData.exists_in_products){
        e.preventDefault();
        alert('⚠️ Este SKU ya existe en productos. Por favor seleccione un SKU diferente.');
        $('.sku-input').focus();
        return false;
      }
    });

    // Función para actualizar estado de Precio Personalizado
    function updatePrecioPersonalizadoState(){
      let val = $('select[name="price_personalizated"]').val();
      if(val == '0'){ // Si es personalizado (Si)
        $('input[name="price"]').prop('disabled', true);
        $('select[name="aspel_price"]').prop('disabled', false);
        $('input[name="aspel_price"]').prop('disabled', false);
      } else { // Si no es personalizado (No)
        $('input[name="price"]').prop('disabled', false);
        $('select[name="aspel_price"]').prop('disabled', true);
        $('input[name="aspel_price"]').prop('disabled', true);
      }
    }

    // Función para actualizar estado de Precio Oferta Personalizado
    function updatePrecioOfertaPersonalizadoState(){
      let val = $('select[name="price_offert_personalizated"]').val();
      if(val == '0'){ // Si es personalizado (Si)
        $('input[name="offert_price"]').prop('disabled', true);
        $('select[name="aspel_offert_price"]').prop('disabled', false);
        $('input[name="aspel_offert_price"]').prop('disabled', false);
      } else { // Si no es personalizado (No)
        $('input[name="offert_price"]').prop('disabled', false);
        $('select[name="aspel_offert_price"]').prop('disabled', true);
        $('input[name="aspel_offert_price"]').prop('disabled', true);
      }
    }

    // Función para actualizar estado de Cantidad Personalizada
    function updateCantidadPersonalizadaState(){
      let val = $('select[name="qty_personalizated"]').val();
      if(val == '0'){ // Si es personalizado (Si)
        $('input[name="qty"]').prop('disabled', true);
        $('input[name="qty_aspel"]').prop('disabled', false);
      } else { // Si no es personalizado (No)
        $('input[name="qty"]').prop('disabled', false);
        $('input[name="qty_aspel"]').prop('disabled', true);
      }
    }

    // Validación de Precio Personalizado
    $('body').on('change', 'select[name="price_personalizated"]', function(e){
      updatePrecioPersonalizadoState();
    });

    // Validación de Precio Oferta Personalizado
    $('body').on('change', 'select[name="price_offert_personalizated"]', function(e){
      updatePrecioOfertaPersonalizadoState();
    });

    // Validación de Cantidad Personalizada
    $('body').on('change', 'select[name="qty_personalizated"]', function(e){
      updateCantidadPersonalizadaState();
    });

    // Inicializar estado al cargar
    updatePrecioPersonalizadoState();
    updatePrecioOfertaPersonalizadoState();
    updateCantidadPersonalizadaState();




  })

  
</script>

@endpush
