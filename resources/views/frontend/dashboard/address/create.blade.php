@extends('frontend.dashboard.layouts.master')
@section('title')
{{$settings->site_name}} || Agregar Direcci&oacute;n
@endsection

@section('content')
  <section id="wsus__dashboard">
    <div class="container-fluid">

        @include('frontend.dashboard.layouts.sidebar')

        <div class="row">
            <div class="col-xl-9 col-xxl-10 col-lg-9 ms-auto">
              <div class="dashboard_content mt-2 mt-md-0">
                <h3><i class="fal fa-gift-card"></i>Crear Direccion</h3>
                <div class="wsus__dashboard_add wsus__add_address">
                  <form action="{{route('user.address.store')}}" method="POST">
                    @csrf
                    <div class="row">
                      <div class="col-xl-6 col-md-6">
                        <div class="wsus__add_address_single">
                          <label>Nombre <b>*</b></label>
                          <input type="text" placeholder="Nombre" name="name">
                        </div>
                      </div>
                      <div class="col-xl-6 col-md-6">
                        <div class="wsus__add_address_single">
                          <label>Correo Electronico</label>
                          <input type="email" placeholder="Email" name="email">
                        </div>
                      </div>
                      <div class="col-xl-6 col-md-6">
                        <div class="wsus__add_address_single">
                          <label>Telefono <b>*</b></label>
                          <input type="text" placeholder="Telefono" name="phone">
                        </div>
                      </div>
                      <div class="col-xl-6 col-md-6">
                        <div class="wsus__add_address_single">
                          <label>Codigo Postal<b>*</b></label>
                          <input type="text" placeholder="Codigo Postal" name="zip">
                        </div>
                      </div>
                      <div class="col-xl-6 col-md-6">
                        <div class="wsus__add_address_single">
                          <label>Estado <b>*</b></label>
                          <div class="wsus__topbar_select">
                            <select class="select_2" name="state">
                              <option value="">Seleccionar...</option>
                              @foreach (config('settings.state_list') as $state)
                              <option value="{{$state}}">{{$state}}</option>
                              
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="col-xl-6 col-md-6">
                        <div class="wsus__add_address_single">
                          <label>Ciudad <b>*</b></label>
                          <input type="text" placeholder="Ciudad" name="city">
                        </div>
                      </div>
                      <div class="col-xl-4 col-md-4">
                        <div class="wsus__add_address_single">
                          <label>Colonia <b>*</b></label>
                          <input type="text" placeholder="Colonia" name="col">
                        </div>
                      </div>
                      <div class="col-xl-5 col-md-5">
                        <div class="wsus__add_address_single">
                          <label>Calle<b>*</b></label>
                          <input type="text" placeholder="Calle" name="street">
                        </div>
                      </div>
                      <div class="col-xl-3 col-md-3">
                        <div class="wsus__add_address_single">
                          <label>Numero Interior<b>(opcional)</b></label>
                          <input type="text" placeholder="Numero Interior" name="street_number">
                        </div>
                      </div>
                      <div class="col-xl-12 col-md-12">
                        <div class="wsus__add_address_single">
                          <label>¿Entre Que Calles Esta?</label>
                          <div class="row">
                              <div class="col-md-6">
                                <label>Calle 1<b>*</b></label>
                                  <input type="text" placeholder="Calle" name="street_1">
                              </div>
                              <div class="col-md-6">
                                <label>Calle 2<b>*</b></label>
                                  <input type="text" placeholder="Calle" name="street_2">
                              </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-xl-12 col-md-12">
                        <div class="wsus__add_address_single">
                          <label>Indicaciones Adicionales De Esta Direccion<b>(opcional)</b></label>
                          <textarea type="text" placeholder="Descripción del exterior del edificio, lugares destacados para ubicarlo, medidas de seguridad recomendadas, entre otros detalles relevantes." name="address"></textarea>
                        </div>
                      </div>
                      <div class="col-xl-6">
                        <button type="submit" class="common_btn">Crear</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
    </div>
  </section>
  @endsection