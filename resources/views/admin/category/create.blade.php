@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nueva Category</h1>
        <div class="section-header-breadcrumb">
          
        </div>
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Crear Nueva Categoria</h4>
                
              </div>
              <div class="card-body">
                
                <form action="{{route('admin.category.store')}}" method="POST">
                  @csrf
                    <div class="form-group">
                        <label>Icon</label>
                            <div>
                                <button class="btn btn-primary" data-selected-class="btn-danger"
                                data-unselected-class="btn-info" role="iconpicker" name="icon"></button>
                            </div>
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name" value="">
                    </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                        <select id="inputState" class="form-control" name="status">
                          <option selected="">Selecionar</option>
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