@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Cotización
@endsection

@section('content')
    <section class="quotes-personal">
        <div class="container">
            <h1 class="text-center mt-5">Solicita una Cotización</h1>
            <form class="row g-3 m-5 p-0" id="web-lead-form" action="{{ route('web-leads.store') }}" method="POST" style="font-family: 'Montserrat', sans-serif;">
@include('frontend.partials.web-lead-alert', ['formType' => 'cotizacion'])

                <div class="row m-0 p-0">
                    <div class="col-md-6">
                        <label for="inputNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="inputNombre" placeholder="Nombre" aria-label="First name" name="first_name" value="{{ old('first_name') }}" required pattern="[\p{L}' .\-]+" maxlength="30" >
                        @error('first_name')<div class="text-danger small">{{ $message }}</div>@enderror

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
                    <label for="inputEmpresa" class="form-label">Empresa</label>
                    <input type="text" class="form-control" id="inputEmpresa" placeholder="Nombre Empresa" required name="company" value="{{ old('company') }}">
                    @error('company')<div class="text-danger small">{{ $message }}</div>@enderror
                  </div>

                <div class="col-md-4">
                  <label for="inputCity" class="form-label">Ciudad</label>
                  <input type="text" class="form-control" id="inputCity" placeholder="Ej.Ciudad Juarez" name="city" value="{{ old('city') }}" required>
                  @error('city')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="inputState" class="form-label" >Estado</label>
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
                    <label for="inputOperation" class="form-label" >Operaci&oacute;n</label>
                    <select id="inputOperation" class="form-select" name="operation" required>
                      <option @selected(old('operation', 'Cotización De 1 o Más Productos') === 'Cotización De 1 o Más Productos')>Cotizaci&oacute;n De 1 o M&aacute;s Productos</option>
                        <option @selected(old('operation') === 'Servicio Sistemas De Control')>Servicio Sistemas De Control</option>
                        <option @selected(old('operation') === 'Servicio Medición De Gas')>Servicio Medici&oacute;n De Gas</option>
                        <option @selected(old('operation') === 'Servicio Calibración Y Puesta En Marcha')>Servicio Calibraci&oacute;n Y Puesta En Marcha</option>

                    </select>
                    @error('operation')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 form-floating">
                    <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px" name="message">{{ old('message') }}</textarea>
                    <label for="floatingTextarea2">Mensaje</label>
                    @error('message')<div class="text-danger small">{{ $message }}</div>@enderror
                  </div>



                <div class="col-12">
                  <button type="submit" class="btn btn-dark w-100">Enviar solicitud</button>
                 </div>
@include('frontend.partials.web-lead-fields', ['formType' => 'cotizacion'])
              </form>
        </div>
    </section>




@endsection
