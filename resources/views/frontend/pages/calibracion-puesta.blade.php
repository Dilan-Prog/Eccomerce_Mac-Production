@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Calibracion y Puesta en Marcha
@endsection

@section('content')
    <section class="quotes-personal">
            <div class="container-fluid p-0 m-0" style=" max-width: 100%;">
        
                <img src="img/Header/Header-Calibracion-Marcha.png" class="img-fluid w-100 p-0 m-0" alt="">
                
            </div>
        
        
        
        
        
        <div class="container mt-5 mb-4" style="background: white">
            <div class="container">

                <div class="row">
                    <!-- Primera Columna -->
                    <div class="col-md-6 d-flex justify-content-center align-items-center">
                    <img src="{{asset('uploads/Marcha-img.png')}}" class="img-fluid p-4" alt="Imagen" style="margin-right: 50px;">
                    </div>
                <!-- Segunda Columna -->
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    
                    <div class="text-right" style="font-family: 'Montserrat', sans-serif;">
                    <h1 style="font-weight: 800;">Servicio De Calibraci&oacute;n Y Puesta En Marcha</h1>
                    <p style="font-weight: 600;">Ofrecemos un servicio completo de calibraci&oacute;n y puesta en marcha dise&ntilde;ado para garantizar que tus equipos operen a su m&aacute;ximo rendimiento. Este proceso meticuloso abarca una variedad de aspectos clave para asegurar la precisi&oacute;n, confiabilidad y eficiencia de tus Instrumentos y maquinaria.</p>
                    </div>
                </div>
                </div>
            </div>

        </div>
        
        
        
        <!-- Contenedor de Beneficios y Formulario -->
        <div class="container" style="background-color: white ;">
        <div class="container " style="background-color: white ;">
            
            <div class="row">
            <!-- Beneficios -->
            <div class="col-md-6 d-flex align-items-center justify-content-center text-right m-0 p-0" style="font-family: 'Montserrat', sans-serif;">
                <div>
                <h2 style="font-weight: 800;">Beneficios:</h2>
                <ul style="font-weight: 600;">
                    <ul style="font-weight: 600;">
                        <li>Calibraci&oacute;n Precisa: Ajuste detallado de Instrumentos para mediciones exactas y confiables.</li>
                        <li>Verificaci&oacute;n de Par&aacute;metros: Comprobaci&oacute;n minuciosa para garantizar la coherencia y precisi&oacute;n.</li>
                        <li>Optimizaci&oacute;n del Rendimiento: Configuraci&oacute;n de equipos para maximizar su eficiencia.</li>
                        <li>Pruebas de Funcionamiento: Realizaci&oacute;n de pruebas exhaustivas para verificar el correcto funcionamiento.</li>
                        <li>Identificaci&oacute;n y Correcci&oacute;n de Desviaciones: Detecci&oacute;n y correcci&oacute;n de desviaciones para asegurar resultados precisos.</li>
                        <li>Documentaci&oacute;n Detallada: Informes detallados documentando procedimientos y resultados.</li>
                        <li>Puesta en Marcha Efectiva: Inicio y verificaci&oacute;n del funcionamiento normal despu&eacute;s de la calibraci&oacute;n.</li>
                        <li>Capacitaci&oacute;n (si es necesario): Proporcionamos capacitaci&oacute;n para operar y mantener adecuadamente los equipos.</li>
                    </ul>
                    <p style="font-weight: 400;">Mant&eacute;n tu hogar o negocio c&aacute;lido y eficiente. &iexcl;Cont&aacute;ctanos hoy para programar tu servicio de reparaci&oacute;n de calderas y disfruta de un sistema de calefacci&oacute;n en perfecto estado!</p>
                </div>
            </div>
        
            <!-- Separador MT-5 -->
            <div class="mt-5 d-md-none"></div>
        
            <!-- Formulario -->
            <div class="col-md-6 d-flex align-items-center justify-content-center m-0 p-0">
                <form class="row g-3 m-5" id="web-lead-form" action="{{ route('web-leads.store') }}" method="POST" style="font-family: 'Montserrat', sans-serif;">
@include('frontend.partials.web-lead-alert', ['formType' => 'calibracion_puesta'])
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
                            <option selected>Servicio De Calibraci&oacute;n Y Puesta En Marcha</option>
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
@include('frontend.partials.web-lead-fields', ['formType' => 'calibracion_puesta'])
                </form>
            </div>
            </div>
        </div>
        </div>
    </section>




@endsection
