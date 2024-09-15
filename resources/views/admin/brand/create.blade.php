@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nueva Marcas</h1>
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Crear Marca</h4>
                
              </div>
              <div class="card-body">
                <form action="{{route('admin.brand.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Logo Marca</label>
                        <input type="file" class="form-control" name="logo">
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name">
                    </div>
                  
                    <div class="form-group">
                        <label for="inputState">Favoritos</label>
                            <select id="inputState" class="form-control" name="is_featured">
                                <option selected="">Seleccionar...</option>
                                <option value="1">Si</option>
                                <option value="0">No</option>
                            </select>
                      </div>

                      <div class="form-group">
                        <label for="inputState">Status</label>
                            <select id="inputState" class="form-control" name="status">
                                <option selected="">Seleccionar...</option>
                                <option value="1">Activo</option>
                                <option value="0">Inativo</option>
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