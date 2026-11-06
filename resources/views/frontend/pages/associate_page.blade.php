@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} || Asociados y Revendedores
@endsection

@section('content')
    <section id="associate_page">
        <div class="associate-site-content">
            <div class="associate-start-text">
                <img src="{{ asset('frontend/images/iconos/associated-start.webp') }}" alt="">
                <div class="container">

                    <p class="associate-text-eslogan">NO SOMOS UNA OPCION, SOMOS LA ELECCION CORRECTA</p>
                    <h1>ASOCIADOS Y <br>REVENDEDORES</h1>
                    {{-- PONER UN AFTER --}}
                    <p>Unete a nuestro equipo de Asociados/Revendedores y aprovecha los grandes beneficios <br> que puedes
                        obtener
                        para tu industria.</p>
                    <button class="common_btn"><a href="#associate-form">QUIERO UNIRME</a></button>
                </div>

            </div>
            <div class="associate-content-card">
                <div class="container">
                    <div class="row mt-4">
                        <div class="col-sm-12 col-md-3">
                            <div class="card card-start-end">
                                <div class="card-body">
                                    <img src="{{asset('frontend/images/iconos/tecnologia.webp')}}" alt="">
                                    <h5>Nuevas Tecnologias</h5>
                                    <p class="associate-desription">Acceso a tecnología de primera mano con demos y capacitaciones por parte del fabricante.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <img src="{{asset('frontend/images/iconos/price-unique.webp')}}" alt="">
                                    <h5>Precios Exclusivos</h5>
                                    <p class="associate-desription">Para todos los Revendedores y Asociados para tener una mayor competitividad en el mercado y Stock Inmediato.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <img src="{{asset('frontend/images/iconos/tarjeta-de-credito.webp')}}" alt="">
                                    <h5>Credito</h5>
                                    <p class="associate-desription">Credito para mayor flexibilidad y adquisicion de producto para crecimiento continuo de nuestros asociados y revendedores.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="card card-start-end">
                                <div class="card-body">
                                    <img src="{{asset('frontend/images/iconos/capacitacion.webp')}}" alt="">
                                    <h5>Capacitaciones</h5>
                                    <p class="associate-desription">Especializamos y preparamos a nuestros revendedores y asociadoas para tener un mayor conocimiento sobre los productos y puedan enfrentarse a los desafios del mercado. </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="container" style="background-color: white ;">
            <div class="container " style="background-color: white ;">
              <div class="row ">
                <!-- Formulario -->
                <div class="col-md-12 d-flex align-items-center justify-content-center m-0 p-0" id="associate-form">
                  <form class="row g-3 m-5" id="web-lead-form" action="{{ route('web-leads.store') }}" method="POST" style="font-family: 'Montserrat', sans-serif;">
@include('frontend.partials.web-lead-alert', ['formType' => 'asociado'])
                      <h2 style="font-weight: 800;">Llenar Formulario</h2>
                      <div class="row">
                          <div class="col-md-6">
                              <label for="inputNombre" class="form-label">Nombre</label>
                              <input type="text" class="form-control" id="inputNombre" placeholder="Nombre" aria-label="First name" name="first_name" value="{{ old('first_name') }}" required pattern="[\p{L}' .\-]+" maxlength="30" >
                              @error('first_name')<div class="text-danger small">{{ $message }}</div>@enderror
                              <div class="invalid-feedback">
                                  El nombre no puede estar en blanco.
                              </div>
                          </div>
                          <div class="col-md-6">
                              <label for="inputAddress2" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" placeholder="Apellidos" aria-label="Last name" name="last_name" value="{{ old('last_name') }}" pattern="[\p{L}' .\-]+" required >
                            @error('last_name')<div class="text-danger small">{{ $message }}</div>@enderror
                          </div>

                      </div>
                      <div class="col-md-6">
                        <label for="inputEmail4" class="form-label">Email</label>
                        <input type="email" class="form-control" id="inputEmail4" placeholder="example@gmail.com" name="email" value="{{ old('email') }}" required>
                        @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                      <div class="col-md-6">
                        <label for="telefono" class="form-label">N&uacute;mero de telefono</label>
                        <input type="tel" class="form-control" id="inputAddress" placeholder="Telefono" name="phone" value="{{ old('phone') }}" required  pattern="[0-9+\s\-\(\)]{10,20}" title="Ingrese un número de teléfono válido (10 dígitos)">
                        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                      <div class="col-md-4">
                          <label for="inputCity" class="form-label">Empresa</label>
                          <input type="text" class="form-control" id="inputCity" placeholder="Nombre Empresa" required name="company" value="{{ old('company') }}">
                          @error('company')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                      <div class="col-md-4">
                        <label for="inputCity" class="form-label">Ciudad</label>
                        <input type="text" class="form-control" id="inputCity" placeholder="Ciudad Juarez" name="city" value="{{ old('city') }}" required>
                        @error('city')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>
                      <div class="col-md-4">
                          <label for="inputState" class="form-label">Estado</label>
                          <select id="inputState" class="form-select" name="state" required>
                            <option @selected(! old('state')) disabled value="" >Seleccionar..</option>
                              <option @selected(old('state') === 'Aguascalientes')>Aguascalientes</option>
                              <option @selected(old('state') === 'Baja California')>Baja California</option>
                              <option @selected(old('state') === 'Baja California Sur')>Baja California Sur</option>
                              <option @selected(old('state') === 'Campeche')>Campeche</option>
                              <option @selected(old('state') === 'Chiapas')>Chiapas</option>
                              <option @selected(old('state') === 'Chihuahua')>Chihuahua</option>
                              <option @selected(old('state') === 'Coahuila')>Coahuila</option>
                              <option @selected(old('state') === 'Colima')>Colima</option>
                              <option @selected(old('state') === 'Durango')>Durango</option>
                              <option @selected(old('state') === 'Estado de México')>Estado de M&eacute;xico</option>
                              <option @selected(old('state') === 'Guanajuato')>Guanajuato</option>
                              <option @selected(old('state') === 'Guerrero')>Guerrero</option>
                              <option @selected(old('state') === 'Hidalgo')>Hidalgo</option>
                              <option @selected(old('state') === 'Jalisco')>Jalisco</option>
                              <option @selected(old('state') === 'Michoacán')>Michoac&aacute;n</option>
                              <option @selected(old('state') === 'Morelos')>Morelos</option>
                              <option @selected(old('state') === 'Nayarit')>Nayarit</option>
                              <option @selected(old('state') === 'Nuevo León')>Nuevo Le&oacute;n</option>
                              <option @selected(old('state') === 'Oaxaca')>Oaxaca</option>
                              <option @selected(old('state') === 'Puebla')>Puebla</option>
                              <option @selected(old('state') === 'Querétaro')>Quer&eacute;taro</option>
                              <option @selected(old('state') === 'Quintana Roo')>Quintana Roo</option>
                              <option @selected(old('state') === 'San Luis Potosí')>San Luis Potos&iacute;</option>
                              <option @selected(old('state') === 'Sinaloa')>Sinaloa</option>
                              <option @selected(old('state') === 'Sonora')>Sonora</option>
                              <option @selected(old('state') === 'Tabasco')>Tabasco</option>
                              <option @selected(old('state') === 'Tamaulipas')>Tamaulipas</option>
                              <option @selected(old('state') === 'Tlaxcala')>Tlaxcala</option>
                              <option @selected(old('state') === 'Veracruz')>Veracruz</option>
                              <option @selected(old('state') === 'Yucatán')>Yucat&aacute;n</option>
                              <option @selected(old('state') === 'Zacatecas')>Zacatecas</option>
                          </select>
                          @error('state')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>

                      <div class="col-md-12">
                          <label for="inputOperation" class="form-label">Operaci&oacute;n</label>
                          <select id="inputOperation" class="form-select" name="operation" required>
                              <option selected>Asociado/Renvendedor</option>
                          </select>
                          @error('operation')<div class="text-danger small">{{ $message }}</div>@enderror
                      </div>

                      <div class="col-12 form-floating">
                          <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px" name="message">{{ old('message') }}</textarea>
                          <label for="floatingTextarea2">Mensaje</label>
                          @error('message')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>



                      <div class="col-12">
                        <button type="submit" class="btn btn-dark w-100" >Enviar solicitud</button>
                      </div>
@include('frontend.partials.web-lead-fields', ['formType' => 'asociado'])
                    </form>
                </div>
              </div>
            </div>
          </div>

    </section>
@endsection
