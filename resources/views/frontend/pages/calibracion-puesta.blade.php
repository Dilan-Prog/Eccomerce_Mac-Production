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
                <form class="row g-3 m-5" action="https://formsubmit.co/dilanp270105@gmail.com" method="POST" style="font-family: 'Montserrat', sans-serif;">
                    <h2 style="font-weight: 800;">Llenar Formulario</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="inputNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="inputNombre" placeholder="Nombre" aria-label="First name" name="Nombre" required pattern="[A-Za-z]+" maxlength="30" >
                            <div class="invalid-feedback"> 
                                El nombre no puede estar en blanco.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inputAddress2" class="form-label">Apellidos</label>
                        <input type="text" class="form-control" placeholder="Apellidos" aria-label="Last name" name="Apellido" pattern="[A-Za-z]+" required >
                        </div> 
                        
                    </div>
                    
                    
                    <div class="col-md-6">
                    <label for="inputEmail4" class="form-label">Email</label>
                    <input type="email" class="form-control" id="inputEmail4" placeholder="example@gmail.com" name="email" required>
                    </div>
                    <div class="col-md-6">
                    <label for="telefono" class="form-label">N&uacute;mero de telefono</label>
                    <input type="tel" class="form-control" id="inputAddress" placeholder="Telefono" name="Telefono" required  pattern="[0-9]{10}" title="Ingrese un número de teléfono válido"> 
                    </div>
                    
                
                    
                    <div class="col-md-4">
                        <label for="inputCity" class="form-label">Empresa</label>
                        <input type="text" class="form-control" id="inputCity" placeholder="Nombre Empresa" required name="Ciudad" required>
                    </div>
                
                    <div class="col-md-4">
                    <label for="inputCity" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="inputCity" placeholder="Ciudad Juarez" name="Ciudad" required>
                    </div>
                    <div class="col-md-4">
                        <label for="inputState" class="form-label">Estado</label>
                        <select id="inputState" class="form-select" name="Estado" required>
                        <option selected disabled value="" >Seleccionar..</option>
                            <option>Aguascalientes</option>
                            <option>Baja California</option>
                            <option>Baja California Sur</option>
                            <option>Campeche</option>
                            <option>Chiapas</option>
                            <option>Chihuahua</option>
                            <option>Coahuila</option>
                            <option>Colima</option>
                            <option>Durango</option>
                            <option>Estado de M&eacute;xico</option>
                            <option>Guanajuato</option>
                            <option>Guerrero</option>
                            <option>Hidalgo</option>
                            <option>Jalisco</option>
                            <option>Michoac&aacute;n</option>
                            <option>Morelos</option>
                            <option>Nayarit</option>
                            <option>Nuevo Le&oacute;n</option>
                            <option>Oaxaca</option>
                            <option>Puebla</option>
                            <option>Quer&eacute;taro</option>
                            <option>Quintana Roo</option>
                            <option>San Luis Potos&iacute;</option>
                            <option>Sinaloa</option>
                            <option>Sonora</option>
                            <option>Tabasco</option>
                            <option>Tamaulipas</option>
                            <option>Tlaxcala</option>
                            <option>Veracruz</option>
                            <option>Yucat&aacute;n</option>
                            <option>Zacatecas</option>
                        </select>
                    </div>
                    
        
                    <div class="col-md-12">
                        <label for="inputOperation" class="form-label">Operaci&oacute;n</label>
                        <select id="inputOperation" class="form-select" name="Operacion" required>
                            <option selected>Servicio De Calibraci&oacute;n Y Puesta En Marcha</option>
                        </select>
                    </div>
                    
                    <div class="col-12 form-floating">
                        <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px" name="Mensaje"></textarea>
                        <label for="floatingTextarea2">Mensaje</label>
                    </div>
                
                    
                
                    <div class="col-12">
                    <button type="submit" class="btn btn-dark w-100" >Enviar solicitud</button>
                    <input type="hidden" name="_next" value="http://127.0.0.1:8000/cotizar"><!--Cambiar url-->
                    <input type="hidden" name="_captcha" value="false">
                    </div>
                </form>
            </div>
            </div>
        </div>
        </div>
    </section>




@endsection
