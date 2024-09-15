@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nueva Sub Categoria</h1>
        <div class="section-header-breadcrumb">
          
        </div>
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Crear Sub Categoria</h4>
                
              </div>
              <div class="card-body">
                
                <form action="{{route('admin.sub-category.store')}}" method="POST">
                  @csrf
                  <div class="form-group">
                    <label for="inputState">Categoria</label>
                    <select id="inputState" class="form-control" name="category">
                      <option selected="">Seleccionar...</option>
                      @foreach ($categories as $category )
                      <option value="{{$category->id}}">{{$category->name}}</option>
                      @endforeach
                      
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
                      <button type="submit" class="btn btn-primary">Crear</button>
                </form>
              </div>
              
            </div>
          </div>
          
        </div>
        
      </div>
    </section>




@endsection