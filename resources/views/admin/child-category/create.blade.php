@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nueva Categoria Secundaria</h1>
        <div class="section-header-breadcrumb">
          
        </div>
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Crear Categoria Secundaria</h4>
                
              </div>
              <div class="card-body">
                
                <form action="{{route('admin.child-category.store')}}" method="POST">
                  @csrf
                  <div class="form-group">
                    <label for="inputState">Categoria</label>
                    <select id="inputState" class="form-control main-category" name="category">
                      <option selected="">Seleccionar...</option>
                      @foreach ($categories as $category )
                      <option value="{{$category->id}}">{{$category->name}}</option>
                      @endforeach
                      
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="inputState">Sub Categoria</label>
                    <select id="inputState" class="form-control sub-category" name="sub_category">
                      <option selected="">Seleccionar...</option>
                      
                      
                    </select>
                  </div>

                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name" value="">
                    </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                        <select id="inputState" class="form-control" name="status">
                          <option selected="">Seleccionar...</option>
                          <option value="1">Activo</option>
                          <option value="0">Inactivo</option>
                        </select>
                      </div>
                      <button type="submit" class="btn btn-primary">Create</button>
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