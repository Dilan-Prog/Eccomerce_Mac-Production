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
                            <option selected>Servicio Medición De Gas</option>
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
        </div>
    </section>




@endsection
