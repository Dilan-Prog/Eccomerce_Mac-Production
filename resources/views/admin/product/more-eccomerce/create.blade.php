@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nuevo Comercio</h1>
        
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <a href="javascript:history.back()"" class="btn btn-primary">Regresar</a>
            </div>
            <div class="card">
              <div class="card-header">
                <h4>Crear Nueva Opcion De Comercio</h4>
                
              </div>
              <div class="card-body">
                
                <form action="{{route('admin.products-more-eccomerce.store')}}" method="POST">
                  @csrf
                    <div class="form-group">
                        <label>Nombre Del Comercio</label>
                        <input type="text" class="form-control" name="nameEccomerce" value="">
                    </div>
                    <div class="form-group">
                        <input type="hidden" class="form-control" name="product" value="{{request()->product}}">
                    </div>
                    <div class="form-group">
                      <label>Link del Producto</label>
                      <input type="text" class="form-control" name="linkProduct" value="">
                  </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                        <select id="inputState" class="form-control" name="status">
                          <option selected="" value="">Seleccionar...</option>
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