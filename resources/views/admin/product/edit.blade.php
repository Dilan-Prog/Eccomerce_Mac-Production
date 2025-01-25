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

                    <div class="form-group">
                      <label>Sku</label>
                      <input type="text" class="form-control" name="sku" value="{{$product->sku}}">
                  </div>
                  <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" class="form-control" name="productModel" value="{{$product->productModel}}">
                  </div>
                  <div class="form-group">
                      <label>Precio</label>
                      <input type="text" class="form-control" name="price" value="{{$product->price}}">
                  </div>
                  <div class="form-group">
                      <label>Precio De Oferta</label>
                      <input type="text" class="form-control" name="offert_price" value="{{$product->offert_price}}">
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
                      <label>Cantidad De Stock</label>
                      <input type="number" class="form-control" min="0" name="qty" value="{{$product->qty}}">
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

  })
</script>

@endpush
