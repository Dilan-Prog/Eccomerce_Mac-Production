@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Editar Comercio</h1>
        <div class="section-header-breadcrumb">
          
        </div>
      </div>


      <div class="section-body">
        

        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <a href="javascript:history.back()"" class="btn btn-primary">Regresar</a>
            </div>
            <div class="card">
              <div class="card-header">
                <h4>Actualizar Opcion De Comercio</h4>
                
              </div>
              <div class="card-body">
                
                <form action="{{route('admin.products-more-eccomerce.update', $productMoreEccomerce->id)}}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="form-group">
                    <label>Nombre Del Comercio</label>
                    <input type="text" class="form-control" name="nameEccomerce" value="{{$productMoreEccomerce->nameEccomerce}}">
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" name="product" value="{{request()->product}}">
                </div>
                <div class="form-group">
                  <label>Link del Producto</label>
                  <input type="text" class="form-control" name="linkProduct" value="{{$productMoreEccomerce->linkProduct}}">
              </div>
                <div class="form-group">
                    <label for="inputState">Estado</label>
                    <select id="inputState" class="form-control" name="status">
                      <option selected="" value="">Seleccionar...</option>
                      <option {{$productMoreEccomerce->status == 0 ? 'selected' : ''}} value="0">Inactive</option>
                          <option {{$productMoreEccomerce->status == 1 ? 'selected' : ''}} value="1">Active</option>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
              </div>
              
            </div>
          </div>
          
        </div>
        
      </div>
    </section>




@endsection