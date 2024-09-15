@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Servicio Sistemas de control
@endsection

@section('content')
    <section class="quotes-personal">
        <div class="container-fluid p-0 m-0" style=" max-width: 100%;">
    
            <img src="img/Header/Header-Sistemas-Control.png" class="img-fluid w-100" alt="">
            
    </div>
    
    
    
    
    
    <div class="container mt-5 mb-4" style="background: white">
        <div class="container">
            <div class="row">
                <!-- Primera Columna -->
                <div class="col-md-6 d-flex justify-content-center align-items-center">
                  <img src="{{asset('uploads/Sistemas-img.png')}}" class="img-fluid" alt="Imagen" style="margin-right: 50px; height: 80%;">
                </div>
              <!-- Segunda Columna -->
              <div class="col-md-6 d-flex align-items-center justify-content-center">
                
                <div class="text-right" style="font-family: 'Montserrat', sans-serif;">
                  <h1 style="font-weight: 800;">Sistemas De Control</h1>
                  <p style="font-weight: 600;">En el n&uacute;cleo de nuestra misi&oacute;n se encuentra el compromiso de elevar el rendimiento de tus procesos industriales mediante soluciones de sistemas de control adaptadas a medida. Nuestro servicio integral abarca una gama completa de acciones diseñadas para maximizar la eficiencia, la confiabilidad y la flexibilidad de tus operaciones.</p>
                </div>
              </div>
            </div>

        </div>
    </div>
    
     
    
    <!-- Contenedor de Beneficios y Formulario -->
    <div class="container" style="background-color: white ;">
      <div class="container " style="background-color: white ;">
        
        <div class="row ">
          <!-- Beneficios -->
          <div class="col-md-6 d-flex align-items-center justify-content-center text-right m-0 p-0" style="font-family: 'Montserrat', sans-serif;">
            <div>
              <h2 style="font-weight: 700;">Beneficios:</h2>
              <ul style="font-weight: 500;">
                <li>An&aacute;lisis Profundo de Requerimientos: Evaluamos detalladamente los requisitos espec&iacute;ficos de tu sistema para dise&ntilde;ar soluciones personalizadas.</li>
                <li>Dise&ntilde;o Adaptable: Desarrollamos sistemas de control a medida que se adaptan perfectamente a las particularidades de tu entorno operativo.</li>
                <li>Programaci&oacute;n y Configuraci&oacute;n Eficiente: Realizamos la programaci&oacute;n y configuraci&oacute;n precisa de controladores y PLCs para asegurar un control efectivo y eficiente.</li>
                <li>Integraci&oacute;n de Sensores y Actuadores: Implementamos la integraci&oacute;n inteligente de sensores y actuadores para mejorar la adquisici&oacute;n de datos y la ejecuci&oacute;n de acciones controladas.</li>
                <li>Desarrollo de L&oacute;gica de Control Avanzada: Creamos algoritmos y l&oacute;gica de control avanzada para garantizar un rendimiento coherente y optimizado.</li>
                <li>Pruebas Rigurosas y Validaci&oacute;n: Sometemos el sistema a pruebas exhaustivas para validar su funcionamiento en diversas condiciones operativas.</li>
              
               
                  </ul>
                  <p style="font-weight: 600;">Conf&iacute;a en nosotros para optimizar la eficiencia y la confiabilidad de tus operaciones industriales a trav&eacute;s de soluciones de control adaptadas a tus necesidades espec&iacute;ficas. &iexcl;Cont&aacute;ctanos hoy y lleva tu control industrial al siguiente nivel!</p>
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
                        <input type="text" class="form-control" id="inputNombre" placeholder="Nombre" aria-label="First name" name="name" required pattern="[A-Za-z]+" maxlength="30" >
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
                        <option selected>Sistemas De Control</option>
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
