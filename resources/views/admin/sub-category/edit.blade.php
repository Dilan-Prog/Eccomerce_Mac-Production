@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Actualizar Categoria</h1>
        <div class="section-header-breadcrumb">
          
        </div>
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Actualizar Categoria</h4>
                
              </div>
              <div class="card-body">
                
                <form action="{{route('admin.sub-category.update', $subCategory->id)}}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="form-group">
                    <label for="inputState">Categoria</label>
                    <select id="inputState" class="form-control" name="category">
                      <option selected="">Seleccionar...</option>
                      @foreach ($categories as $category )
                      <option {{$category->id == $subCategory->category_id ? 'selected' : ''}} value="{{$category->id}}">{{$category->name}}</option>
                      @endforeach
                      
                    </select>
                  </div>
                    
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name" value="{{$subCategory->name}}">
                    </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                        <select id="inputState" class="form-control" name="status">
                          
                          <option value="1" {{$subCategory->status == 1 ? 'selected':''}}>Activo</option>
                          <option value="0" {{$subCategory->status == 0 ? 'selected':''}}>Inactivo</option>
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