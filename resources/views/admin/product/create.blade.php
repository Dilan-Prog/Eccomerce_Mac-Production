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
                                <option value="{{$category->id}}">{{$category->name}}</option>
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
                              <option value="{{$brand->id}}">{{$brand->name}}</option>

                              @endforeach

                            </select>
                      </div>

                    <div class="form-group">
                      <label>Sku</label>
                      <input type="text" class="form-control" name="sku" value="{{old('sku')}}">
                  </div>
                  <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" class="form-control" name="productModel" value="{{old('productModel')}}">
                  </div>
                
                  <div class="form-group">
                      <label>Precio</label>
                      <input type="text" class="form-control" name="price" value="{{old('price')}}">
                  </div>
                  <div class="form-group">
                      <label>Precio De Oferta</label>
                      <input type="text" class="form-control" name="offert_price" value="{{old('offert_price')}}">
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
                      <label>Cantidad En Stock</label>
                      <input type="number" class="form-control" min="0" name="qty" value="{{old('qty')}}">
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
                          <option value="0">Seleccionar...</option>
                          <option value="new_arrival">Nuevo</option>
                          <option value="featured_product" disabled>Producto Destacado</option>
                          <option value="top_product">Más Buscado</option>
                          <option value="best_product">Más Vendido</option>

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
                            <option value="none">Seleccionar...</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                          </select>
                    </div>

                  <div class="form-group">
                    <label>Es Url Canonica (Solo Marketing)</label>
                    <select class="form-control" name="is_canonical">
                      <option value="">Seleccionar</option>
                      <option value="1">Si</option>
                      <option value="0">No</option>
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
                

                    <button type="submit" class="btn btn-primary">Crear</button>
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



    // Base Search Canonical URL

    // $('#product-search').select2({
    //     placeholder: 'Buscar...',
    //     ajax: {
    //         url: "{{ route('admin.products.search') }}", // RUTA DE LA BÚSQUEDA
    //         dataType: 'json',
    //         delay: 250, // Retardo para evitar peticiones excesivas
    //         data: function (params) {
    //             return {
    //                 query: params.term // El término que el usuario ingresa
    //             };
    //         },
    //         success: function (data) {
    //         console.log('Success:', data); // Verifica los datos recibidos en la consola
    //         },
    //         error: function (xhr, status, error) {
    //             console.error('Error:', error); // Muestra el error si algo sale mal
    //             alert('Hubo un problema al cargar los productos. Por favor, intenta de nuevo más tarde.');
    //         },
    //         processResults: function (data) {
    //           console.log(data); // Verifica los datos recibidos
    //           return {
    //               results: $.map(data.results, function (product) {
    //                   return {
    //                       id: product.id,
    //                       text: product.text
    //                   };
    //               })
    //           };
    //       },
            
    //     },
    //     minimumInputLength: 2 // Mínimo de caracteres para comenzar la búsqueda
    // });

    // End Base Search Canonical URL

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

    




  })
</script>

@endpush
