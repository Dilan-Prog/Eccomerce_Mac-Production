@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Editar Categoria Secundaria</h1>
        <div class="section-header-breadcrumb">
          
        </div>
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Editar Categoria Secundaria</h4>
                
              </div>
              <div class="card-body">
                
                <form action="{{route('admin.child-category.update', $childCategory->id)}}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="form-group">
                    <label for="inputState">Categoria</label>
                    <select id="inputState" class="form-control main-category" name="category">
                      
                      @foreach ($categories as $category )
                      <option {{$category->id == $childCategory->category_id ? ' selected ': ''}} value="{{$category->id}}">{{$category->name}}</option>
                      @endforeach
                      
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="inputState">Sub Categoria</label>
                    <select id="inputState" class="form-control sub-category" name="sub_category">
                      
                      @foreach ($subCategories as $subCategory )
                      <option {{$subCategory->id == $childCategory->sub_category_id ? ' selected ': ''}} value="{{$subCategory->id}}" disable>{{$subCategory->name}}</option>
                      @endforeach
                      
                      
                    </select>
                  </div>

                    <div class="form-group">
                        <label>Nombre De Categoria Secundaria</label>
                        <input type="text" class="form-control" name="name" value="{{$childCategory->name}}">
                    </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                        <select id="inputState" class="form-control" name="status">
                          <option selected="">Seleccionar...</option>
                          <option value="1" {{$childCategory->status == 1 ? 'selected':''}} >Activo</option>
                          <option value="0" {{$childCategory->status == 0 ? 'selected':''}} >Inactivo</option>
                        </select>
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
      let id = $(this).val();
      $.ajax({
        method: 'GET',
        url:"{{route('admin.get-subcategories')}}",
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

  })
</script>
    
@endpush