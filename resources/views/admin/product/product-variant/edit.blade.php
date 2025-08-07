@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Actualizacion de Variante</h1>
        <div class="section-header-breadcrumb">

        </div>
      </div>


      <div class="section-body">


        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Actualizar Variante</h4>
              </div>
              <div class="card-body">

                <form action="{{route('admin.products-variant.update', $variant->id)}}" method="POST">
                  @csrf
                  @method('PUT')
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name" value="{{$variant->name}}">
                    </div>

                    <div class="form-group">
                        <label for="inputState">Estado</label>
                        <select id="inputState" class="form-control" name="status">
                          <option selected="" value="">Seleccionar...</option>
                          <option {{$variant->status == 0 ? 'selected' : ''}} value="0">Inactivo</option>
                          <option {{$variant->status == 1 ? 'selected' : ''}} value="1">Activo</option>
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
