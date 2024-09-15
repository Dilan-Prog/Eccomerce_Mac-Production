@extends('frontend.dashboard.layouts.master')

@section('title')
{{$settings->site_name}} || Direcci&oacute;n
@endsection

@section('content')
  <section id="wsus__dashboard">
    <div class="container-fluid">

        @include('frontend.dashboard.layouts.sidebar')

        <div class="row">
            <div class="col-xl-9 col-xxl-10 col-lg-9 ms-auto">
              <div class="dashboard_content">
                <h3><i class="fal fa-gift-card"></i> Direcciones</h3>
                <div class="wsus__dashboard_add">
                  <div class="row">
                    @foreach ($addresses as $address )
                    <div class="col-xl-6">

                      <div class="wsus__dash_add_single">
                        <h4>Dirección de Envío</h4>
                        <ul>
                          <li><span>Nombre :</span>{{$address->name}}</li>
                          <li><span>Numero de Telefono :</span>{{$address->phone}}</li>
                          <li><span>Correo Electronico :</span>{{$address->email}}</li>
                          <li><span>Codigo Postal :</span>{{$address->zip}}</li>
                          <li><span>Estado :</span>{{$address->state}}</li>
                          <li><span>Ciudad :</span>{{$address->city}}</li>
                          <li><span>Colonia :</span>{{$address->col}}</li>
                          <li><span>Calle :</span>{{$address->street}}</li>
                          <li><span>N&uacute;mero Interior :</span>{{$address->street_number}}</li>

                        </ul>
                        <div class="wsus__address_btn">
                          <a href="{{route('user.address.edit', $address->id)}}" class="edit"><i class="fal fa-edit"></i> Editar</a>
                          <a href="{{route('user.address.destroy', $address->id)}}" class="del delete-item"><i class="fal fa-trash-alt"></i> Borrar</a>
                        </div>
                      </div>
                    </div>
                      
                    @endforeach
                    <div class="col-12">
                      <a href="{{route('user.address.create')}}" class="add_address_btn common_btn"><i class="far fa-plus"></i>
                        Agregar Nueva Direcci&oacute;n</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
    </div>
  </section>
  @endsection