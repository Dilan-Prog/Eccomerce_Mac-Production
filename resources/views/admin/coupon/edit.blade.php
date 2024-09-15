@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Actualizar Cupon</h1>
        <div class="section-header-breadcrumb">

        </div>
      </div>


      <div class="section-body">


        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h4>Actualizar Nuevo Cupon</h4>
              </div>
              <div class="card-body">

                <form action="{{route('admin.coupons.update', $coupon->id)}}" method="POST">
                  @csrf
                  @method('PUT')
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" name="name" value="{{$coupon->name}}">
                    </div>
                    <div class="form-group">
                        <label>Codigo</label>
                        <input type="text" class="form-control" name="cod" value="{{$coupon->cod}}">
                    </div>
                    <div class="form-group">
                        <label>Cantidad</label>
                        <input type="text" class="form-control" name="quantity" value="{{$coupon->quantity}}">
                    </div>
                    <div class="form-group">
                        <label>Max Usos por Persona(un mismo usuario)</label>
                        <input type="text" class="form-control" name="max_use" value="{{$coupon->max_use}}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de inicio</label>
                                <input type="text" class="form-control datepicker" name="start_date" value="{{$coupon->start_date}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha de Fin</label>
                                <input type="text" class="form-control datepicker" name="end_date" value="{{$coupon->end_date}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputState">Tipo de descuento</label>
                                <select id="inputState" class="form-control sub-category" name="discount_type">
                                    <option {{$coupon->discount_type =='percent' ? 'selected' : ''}} value="percent">Pocentaje (%)</option>
                                    <option {{$coupon->discount_type =='amount' ? 'selected' : ''}} value="amount">Monto o Precio ({{$settings->currency_icon}})</option>
                                </select>
                              </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Valor De Descuento</label>
                                <input type="text" class="form-control" name="discount" value="{{$coupon->discount}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputState">Estado</label>
                        <select id="inputState" class="form-control" name="status">
                          <option selected="">Selecionar</option>
                          <option {{$coupon->status == '1' ? 'selected' : ''}}  value="1">Activo</option>
                          <option {{$coupon->status == '0' ? 'selected' : ''}}  value="0">Inactivo</option>
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

