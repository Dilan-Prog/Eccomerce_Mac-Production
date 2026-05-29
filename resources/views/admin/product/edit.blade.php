@extends('admin.layouts.master')

@section('content')
    <section class="section">
      <div class="section-header">
        <h1>Actualizar Producto</h1>
      </div>
      <div class="section-body">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Actualizar</h4>

              </div>
              <div class="card-body">
                <form action="{{route('admin.products.update', $product->id)}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Imagen Previa</label>
                        <br>
                        <img src="{{asset($product->thumb_image)}}" width="200px" alt="">
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <input type="file" class="form-control" name="image">
                    </div>
                    <!--Star for promotion text animations-->
                    <div class="form-group">
                        <label>Nombre del Producto</label>
                        <input type="text" class="form-control" name="name" value="{{$product->name}}">
                    </div>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputState">Categoria</label>
                              <select id="inputState" class="form-control main-category" name="category">
                                <option selected="" value="">Seleccionar</option>
                                @foreach ($categories as $category )
                                <option {{$category->id == $product->category_id ? 'selected' : ''}} value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                              </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputState">Sub Categoria</label>
                              <select id="inputState" class="form-control sub-category" name="sub_category">
                                <option selected="" value="">Seleccionar</option>
                                @foreach ($subCategories as $subCategory )
                                <option {{$subCategory->id == $product->sub_category_id ? 'selected' : ''}} value="{{$subCategory->id}}">{{$subCategory->name}}</option>
                                @endforeach
                              </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                            <label for="inputState">Categoria Secundaria</label>
                              <select id="inputState" class="form-control child-category" name="child_category">
                                <option selected="" value="">Seleccionar</option>
                                @foreach ($childCategories as $childCategory )
                                <option {{$childCategory->id == $product->child_category_id ? 'selected' : ''}} value="{{$childCategory->id}}">{{$childCategory->name}}</option>
                                @endforeach
                              </select>
                        </div>
                      </div>
                    </div>

                      <div class="form-group">
                          <label for="inputState">Marca</label>
                            <select id="inputState" class="form-control" name="brand">
                              <option selected="" value="">Seleccionar</option>
                              @foreach ($brands as $brand)
                              <option {{$brand->id == $product->brand_id ? 'selected' : ''}} value="{{$brand->id}}">{{$brand->name}}</option>

                              @endforeach
                            </select>
                      </div>
                    <div class="form-group position-relative">
                      <label>Sku</label><small id="sku-status" class="form-text d-block mt-2"></small>
                      <input type="text" class="form-control sku-input" name="sku" value="{{ old('sku', $product->sku) }}" placeholder="Escribe el SKU para buscar">
                      <div id="sku-dropdown" class="list-group" style="position: absolute; top: 100%; left: 0; right: 0; max-height: 250px; overflow-y: auto; display: none; z-index: 1000; margin-top: 2px;"></div>
                    </div>
                  <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" class="form-control" name="productModel" value="{{$product->productModel}}">
                  </div>
                  <div class="form-group">
                      <label for="inputState">Precio Personalizado</label>
                      <select id="inputState" class="form-control" name="price_personalizated">
                        <option {{$product->price_personalizated == 0 ? 'selected' : ''}} value="0">No</option>
                        <option {{$product->price_personalizated == 1 ? 'selected' : ''}} value="1">Si</option>
                      </select>
                  </div>
                  <div class="form-group">
                      <label>Precio</label>
                      <input type="text" class="form-control" name="price" value="{{$product->price}}">
                  </div>
                  <div class="form-group">
                      <label>Precio Aspel</label>
                      @php
                        $aspelCurrencyCode = $aspelCurrency->cve_moned ?? 'MXN';
                        $aspelExchangeRate = $aspelCurrency && $aspelCurrency->tipo_cambio ? (float) $aspelCurrency->tipo_cambio : 1;
                        $aspelSymbol = $aspelCurrency->simbolo ?? '$';
                        $aspelIsMXN = $aspelCurrencyCode === 'MXN';
                        $ivaPercent = (float) $ivaValue;
                      @endphp
                      @if(isset($aspelPriceOptions) && count($aspelPriceOptions))
                        <select class="form-control" name="aspel_price">
                          <option value="">Seleccionar...</option>
                          @foreach($aspelPriceOptions as $opt)
                            @php
                              $desc = optional($opt->precio_info)->descripcion ?? ('Precio ' . $opt->cve_precio);
                              $val = $opt->precio;
                              $convertedVal = $aspelIsMXN ? $val : $val * $aspelExchangeRate;
                              $priceWithIva = $convertedVal * (1 + $ivaPercent / 100);
                              $selectedAspel = false;
                              if ($product->aspel_price) {
                                $selectedAspel = (round($product->aspel_price, 2) == round($priceWithIva, 2));
                              }
                            @endphp
                            <option value="{{ $priceWithIva }}" {{ $selectedAspel ? 'selected' : '' }}>
                              {{ $desc }} — {{ $aspelSymbol }}{{ number_format($val, 2) }} {{ $aspelCurrencyCode }}
                              / Precio Con IVA MXN ${{ number_format($priceWithIva, 2) }} ({{ number_format($ivaPercent, 2) }}% IVA)
                            </option>
                          @endforeach
                        </select>
                      @else
                        <input type="text" class="form-control" name="aspel_price" value="{{$product->aspel_price}}" placeholder="Sin precios SAE para este SKU">
                      @endif
                  </div>
                  <div class="form-group">
                      <label for="inputState">Precio Oferta Personalizado</label>
                      <select id="inputState" class="form-control" name="price_offert_personalizated">
                        <option {{$product->price_offert_personalizated == 0 ? 'selected' : ''}} value="0">No</option>
                        <option {{$product->price_offert_personalizated == 1 ? 'selected' : ''}} value="1">Si</option>
                      </select>
                  </div>
                  <div class="form-group">
                      <label>Precio De Oferta</label>
                      <input type="text" class="form-control" name="offert_price" value="{{$product->offert_price}}">
                  </div>
                  <div class="form-group">
                      <label>Precio De Oferta Aspel</label>
                      @if(isset($aspelPriceOptions) && count($aspelPriceOptions))
                        <select class="form-control" name="aspel_offert_price">
                          <option value="">Seleccionar...</option>
                          @foreach($aspelPriceOptions as $opt)
                            @php
                              $desc = optional($opt->precio_info)->descripcion ?? ('Precio ' . $opt->cve_precio);
                              $val = $opt->precio;
                              $convertedVal = $aspelIsMXN ? $val : $val * $aspelExchangeRate;
                              $priceWithIva = $convertedVal * (1 + $ivaPercent / 100);
                              $selectedAspel = ($product->aspel_offert_price == $priceWithIva) || ($product->aspel_offert_price == $convertedVal) || ($product->aspel_offert_price == $val);
                            @endphp
                            <option value="{{ $priceWithIva }}" {{ $selectedAspel ? 'selected' : '' }}>
                              {{ $desc }} — {{ $aspelSymbol }}{{ number_format($val, 2) }} {{ $aspelCurrencyCode }}
                              @if(!$aspelIsMXN)
                                / Precio Con IVA MXN ${{ number_format($priceWithIva, 2) }} ({{ number_format($ivaPercent, 2) }}% IVA)
                              @else
                                / Precio Con IVA MXN ${{ number_format($priceWithIva, 2) }} ({{ number_format($ivaPercent, 2) }}% IVA)
                              @endif
                              (ID: {{ $opt->cve_precio }})
                            </option>
                          @endforeach
                        </select>
                      @else
                        <input type="text" class="form-control" name="aspel_offert_price" value="{{$product->aspel_offert_price}}" placeholder="Sin precios SAE para este SKU">
                      @endif
                  </div>
                  <div class="row">

                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="inputState">Fecha Inicial De Oferta</label>
                            <input type="text" class="form-control datepicker" name="offer_start_date" value="{{$product->offer_star_date}}">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                          <label for="inputState">Fecha Final De Oferta</label>
                            <input type="text" class="form-control datepicker" name="offer_end_date" value="{{$product->offer_end_date}}">
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                      <label for="inputState">Cantidad Personalizada</label>
                      <select id="inputState" class="form-control" name="qty_personalizated">
                        <option {{$product->qty_personalizated == 0 ? 'selected' : ''}} value="0">No</option>
                        <option {{$product->qty_personalizated == 1 ? 'selected' : ''}} value="1">Si</option>
                      </select>
                  </div>
                  <div class="form-group">
                      <label>Cantidad De Stock</label>
                      <input type="number" class="form-control" min="0" name="qty" value="{{$product->qty}}">
                  </div>
                  <div class="form-group">
                      <label>Cantidad Aspel</label>
                      @if(isset($aspelProductData) && $aspelProductData)
                        <div class="input-group">
                          <input type="number" class="form-control" min="0" name="qty_aspel" value="{{intval($aspelProductData->exist)}}">
                        </div>
                      @endif
                  </div>
                  <div class="form-group">
                    <label>Video Link (Formato embed https://www.youtube.com/embed/ AQUI CAMBIAR EL ID)</label>
                    <input type="text" class="form-control" name="video_link" value="{{ $product->video_link }}">
                    </div>
                    <div class="form-group">
                        <label>Url de drive para el PDF</label>
                        <input type="text" class="form-control" name="url_PDF" value="{{ $product->url_PDF }}">
                    </div>
                  <div class="form-group">
                      <label>Garantia</label>
                      <textarea type="text" name="short_description" class="form-control">{!! $product->short_description!!}</textarea>
                  </div>
                  <div class="form-group">
                      <label>Descripción Larga</label>
                      <textarea type="text" name="long_description" class="form-control ">{!! $product->long_description!!}</textarea>
                  </div>
                  <div class="form-group">
                      <label for="inputState">Tipo De Producto</label>
                        <select id="inputState" class="form-control" name="product_type">
                          <option value="">Seleccionar...</option>
                          <option {{$product->product_type == 'new_arrival' ? 'selected' : ''}} value="new_arrival">Nuevo</option>
                          <option {{$product->product_type == 'featured_product' ? 'selected' : ''}} value="featured_product" disabled>Producto Destacado</option>
                          <option {{$product->product_type == 'top_product' ? 'selected' : ''}} value="top_product">Más Buscado</option>
                          <option {{$product->product_type == 'best_product' ? 'selected' : ''}} value="best_product">Más Vendido</option>
                        </select>
                  </div>
                  <div class="form-group">
                      <label>Seo Titulo</label>
                      <input type="text" class="form-control" name="seo_title" value="{{$product->seo_title}}">
                  </div>
                  <div class="form-group">
                      <label>Seo Descripción</label>
                      <textarea type="text" class="form-control" name="seo_description" value="">{!! $product->seo_description!!}</textarea>
                  </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                          <select id="inputState" class="form-control" name="status">
                            <option selected="">Seleccionar...</option>
                            <option {{$product->status == 1 ? 'selected' : ''}} value="1">Activo</option>
                            <option {{$product->status == 0 ? 'selected' : ''}} value="0">Inactivo</option>
                          </select>
                    </div>
                    <div class="form-group">
                      <label>Es Url Canonica</label>
                      <select class="form-control" name="is_canonical">
                        <option selected="">Seleccionar</option>
                        <option {{$product->is_canonical == 1 ? 'selected' : ''}} value="1">Si</option>
                        <option {{$product->is_canonical == 0 ? 'selected' : ''}} value="0">No</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Url Canonica Personalizada</label>
                      <div class="wsus__topbar_select">
                          <input class="form-control" name="canonical_url" value="{{$product->canonical_url}}" placeholder="Poner URL Canonical en caso de no tener una afiliacion y ser unica dejar el campo vacio y posteriormente rellenarlo">
                      </div>
                    </div>


                    <button type="submit" class="btn btn-primary">Actualizar</button>
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
      $('.child-category').html('<option value="">Select</option>')
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
                              ${opt.desc} — ${res.symbol}${Number(opt.val).toFixed(2)} ${res.currency}
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
                              ${opt.desc} — ${res.symbol}${Number(opt.val).toFixed(2)} ${res.currency}
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
