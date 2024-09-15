@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Nuevo Cupon</h1>
        <div class="section-header-breadcrumb">

        </div>
      </div>


      <div class="section-body">


        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Crear Nuevo Cupon</h4>
              </div>
              <div class="card-body">

                <form action="{{route('admin.coupons.store')}}" method="POST">
                  @csrf

                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name" value="">
                    </div>
                    <div class="form-group">
                        <label>Codigo</label>
                        <input type="text" class="form-control" name="cod" value="">
                    </div>
                    <div class="form-group">
                        <label>Cantidad</label>
                        <input type="text" class="form-control" name="quantity" value="">
                    </div>
                    <div class="form-group">
                        <label>Max Usos por Persona(un mismo usuario)</label>
                        <input type="text" class="form-control" name="max_use" value="">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de inicio</label>
                                <input type="text" class="form-control datepicker" name="start_date" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Fin</label>
                                <input type="text" class="form-control datepicker" name="end_date" value="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputState">Tipo de descuento</label>
                                <select id="inputState" class="form-control sub-category" name="discount_type">
                                    <option value="percent">Pocentaje (%)</option>
                                    <option value="amount">Monto o Precio ({{$settings->currency_icon}})</option>
                                </select>
                              </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Valor De Descuento</label>
                                <input type="text" class="form-control" name="discount" value="">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputState">Status</label>
                        <select id="inputState" class="form-control" name="status">
                          <option selected="">Choose...</option>
                          <option value="1">Active</option>
                          <option value="0">Inactive</option>
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

