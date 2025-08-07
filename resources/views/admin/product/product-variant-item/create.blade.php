@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nuevo Item</h1>
        <div class="section-header-breadcrumb">

        </div>
      </div>


      <div class="section-body">


        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Crear Nuevo Item de Variante</h4>

              </div>
              <div class="card-body">

                <form action="{{route('admin.products-variant-item.store')}}" method="POST">
                  @csrf
                    <div class="form-group">
                        <label>Nombre de la variante</label>
                        <input type="text" class="form-control" name="variant_name" value="{{$variant->name}}" readonly>
                    </div>
                    
                    <div class="form-group">
                        <input type="hidden" class="form-control" name="variant_id" value="{{$variant->id}}" readonly>
                    </div>
                    <div class="form-group">
                        <input type="hidden" class="form-control" name="product_id" value="{{$product->id}}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Item</label>
                        <input type="text" class="form-control" name="name" value="">
                    </div>
                    {{-- <div class="form-group">
                        <label>Precio <code>(Establecer 0 para hacerlo gratis)</code></label>
                        <input type="text" class="form-control" name="price" value="">
                    </div>
                    <div class="form-group">
                        <label> Cantidad <code> (Stock de la Variante) </code></label>
                        <input type="number" class="form-control" name="qty">
                    </div>
                    <div class="form-group">
                        <label for="inputState">Estado por Defecto</label>
                        <select id="inputState" class="form-control" name="is_default">
                            <option selected="">Seleccionar</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div> --}}
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
