@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Servicio Medicion de gas
@endsection

@section('content')

    <section class="quotes-personal">
        <div class="container">
            <div class="container-fluid p-0 m-0" style=" max-width: 100%;">
    
                <img src="img/Header/Header-Gas.png" class="img-fluid w-100 p-0 m-0" alt="">
                
        </div>
        
        
        
        
        
        <div class="container mt-5 mb-4" style="background: white">
            <div class="container">

                <div class="row">
                    <!-- Primera Columna -->
                    <div class="col-md-6 d-flex justify-content-center align-items-center">
                      <img src="{{asset('uploads/Gas-img.png')}}" class="img-fluid p-4" alt="Imagen" style="margin-right: 50px;">
                    </div>
                  <!-- Segunda Columna -->
                  <div class="col-md-6 d-flex align-items-center justify-content-center">
                    
                    <div class="text-right" style="font-family: 'Montserrat', sans-serif;">
                      <h1 style="font-weight: 700;">Servicio De Medicion De Gas</h1>
                      <p style="font-weight: 500;">Nuestro servicio de medición de gas implica la evaluación y cuantificación precisa de los niveles de gas en entornos específicos. Este servicio no solo garantiza la seguridad al prevenir riesgos de explosiones o intoxicación, sino que también asegura el cumplimiento normativo ambiental. Además, optimiza la eficiencia energética al medir y ajustar el consumo de gas en procesos industriales.</p>
                    </div>
                  </div>
                </div>
            </div>
        </div>
        
         
        
        <!-- Contenedor de Beneficios y Formulario -->
        <div class="container" style="background-color: white ; ">
          <div class="container ">
            
            <div class="row">
              <!-- Beneficios -->
              <div class="col-md-6 d-flex align-items-center justify-content-center text-right m-0 p-0" style="font-family: 'Montserrat', sans-serif;">
                <div>
                  <h2 style="font-weight: 800;">Beneficios:</h2>
                  <ul style="font-weight: 600;">
                    <li >Seguridad Asegurada: Verificación de niveles seguros para prevenir riesgos.</li>
                    <li>Cumplimiento Normativo: Garant&iacute;a de conformidad con est&aacute;ndares regulatorios.</li>
                    <li>Eficiencia Energ&eacute;tica: Optimizaci&oacute;n del consumo para mayor eficiencia.</li>
                    <li>Calibraci&oacute;n de Equipos: Ajuste preciso para mediciones confiables.</li>
                    <li>Prevenci&oacute;n de Problemas: Identificaci&oacute;n temprana de posibles irregularidades.</li>
                    <li>Informes Detallados: Datos &uacute;tiles para la toma de decisiones y mantenimiento.</li>
                  </ul>
                  <p style="font-weight: 600;">Conf&iacute;a en nosotros para mantener tus operaciones seguras y eficientes. ¡Cont&aacute;ctanos para obtener m&aacute;s informaci&oacute;n sobre nuestro servicio de medici&oacute;n de gas!</p>
                </div>
              </div>
          
              <!-- Separador MT-5 -->
              <div class="mt-5 d-md-none"></div>
          
              <!-- Formulario -->
              <div class="col-md-6 d-flex align-items-center justify-content-center m-0 p-0">
                
                <form class="row g-3 m-5" id="web-lead-form" action="{{ route('web-leads.store') }}" method="POST" style="font-family: 'Montserrat', sans-serif;">
@include('frontend.partials.web-lead-alert', ['formType' => 'medicion'])
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
                            <option selected>Servicio Medición De Gas</option>
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
@include('frontend.partials.web-lead-fields', ['formType' => 'medicion'])
                  </form>
              </div>
            </div>
          </div>
        </div>
        </div>
    </section>




@endsection
