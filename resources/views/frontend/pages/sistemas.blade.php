@extends('frontend.layouts.master')

@section('title')
    Instalacion de Controles de Temperatura
@endsection
@section('content')
<main>
  <section class="services_content_start">
    <div class="row">
      <div class="col-6">
        <div class="services_content_start_img">
          <img src="{{asset('frontend/images/imagen ejemplo.png')}}" alt="image">
        </div>
      </div>
      <div class="col-6">
        <div class="services_content_start_text">
          <article>
            <span class="services_content_subtitle">Servicio Profesional</span>
            <h1 class="services_content_title_start">Instalacion de Controladores de Temperatura</h1>
            <p class="services_content_description">Nuestro servicio incluye la instalación de controladores de temperatura precisos y eficientes para distintos entornos: industriales, comerciales y residenciales. 
              Garantizamos un control térmico óptimo y confiable en cada proyecto.</p>
              <button class="services_start_button">Atencion Inmediata</button>
              <button class="services_start_button"><i class="fa fa-whatsapp"></i> Escribenos</button>
          </article>
        </div>
      </div>
    </div>
  </section>
  <section class="services_content_info">
    <article>
      <div class="row">
        <div class="col-4">
          <div class="wsus_content_info">
            <span class="wsus_content_info_subtitle">Cumplimiento de Normativa</span>  
            <h4 class="wsus_content_info_title">Cumplimiento de Normativa Sin Complicaciones</h4>
            <p class="wsus_content_info_description">Cumplir con las normativas industriales puede parecer un laberinto: NOM, ISO, seguridad eléctrica… Pero no te preocupes, nosotros nos encargamos. Nos especializamos en la instalación correcta de controladores de temperatura bajo estándares nacionales e internacionales, listos para auditorías y sin riesgos.
  
              Sabemos lo que buscan los inspectores… y lo que necesitas tú para seguir operando sin contratiempos.</p>
            <button class="services_content_info_button">Contactanos</button>
          </div>
        </div>
        <div class="col-4">
          <div class="wsus_content_info">
            <span class="wsus_content_info_subtitle">Cumplimiento de Normativa</span>  
            <h4 class="wsus_content_info_title">Cumplimiento de Normativa Sin Complicaciones</h4>
            <p class="wsus_content_info_description">Cumplir con las normativas industriales puede parecer un laberinto: NOM, ISO, seguridad eléctrica… Pero no te preocupes, nosotros nos encargamos. Nos especializamos en la instalación correcta de controladores de temperatura bajo estándares nacionales e internacionales, listos para auditorías y sin riesgos.
  
              Sabemos lo que buscan los inspectores… y lo que necesitas tú para seguir operando sin contratiempos.</p>
            <button class="services_content_info_button">Contactanos</button>
          </div>
        </div>
        <div class="col-4">
          <div class="wsus_content_info_img">
            <img src="{{asset('uploads/prueba_servicios.png')}}" alt="image">  
          </div>

        </div>
      </div>
    </article>
  </section>
  <section class="services_content_ours_process">
    <div class="row">
      <div class="col-6">
        <div class="services_content_ours_process_img-video" >
          <lite-youtube videoid="3w3xq8VJQSc"></lite-youtube>
        </div>
      </div>
      <div class="col-6">
        <div class="services_content_ours_process_text">
          <article>
            <span class="services_content_ours_process_text_subtitle_one">Nuestro Proceso para</span>
            <h2 class="services_content_ours_process_text_title">Instalaciones de Controladores de Temperatura</h2>
        
            <div class="accordion_item">
              <div class="accordion_header" onclick="toggleAccordion(this)">
                <h3 class="services_content_ours_process_text_subtitle">
                  <span class="number_our_process">01</span> Diagnóstico de Normativas Aplicables
                  <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                </h3>
              </div>
              <p class="our_process_text_description" style="display: none;">
                Antes de comenzar, analizamos tu industria, entorno operativo y equipos involucrados. Así identificamos las normativas que deben cumplirse (NOM, ISO, IEC, UL, etc.) en la instalación del controlador.
              </p>
            </div>
            
            <div class="accordion_item">
              <div class="accordion_header" onclick="toggleAccordion(this)">
                <h3 class="services_content_ours_process_text_subtitle">
                  <span class="number_our_process">02</span> Diagnóstico de Normativas Aplicables
                  <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                </h3>
              </div>
              <p class="our_process_text_description" style="display: none;">
                Antes de comenzar, analizamos tu industria, entorno operativo y equipos involucrados. Así identificamos las normativas que deben cumplirse (NOM, ISO, IEC, UL, etc.) en la instalación del controlador.
              </p>
            </div>
            <div class="accordion_item">
              <div class="accordion_header" onclick="toggleAccordion(this)">
                <h3 class="services_content_ours_process_text_subtitle">
                  <span class="number_our_process">03</span> Diagnóstico de Normativas Aplicables
                  <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                </h3>
              </div>
              <p class="our_process_text_description" style="display: none;">
                Antes de comenzar, analizamos tu industria, entorno operativo y equipos involucrados. Así identificamos las normativas que deben cumplirse (NOM, ISO, IEC, UL, etc.) en la instalación del controlador.
              </p>
            </div>
            <div class="accordion_item">
              <div class="accordion_header" onclick="toggleAccordion(this)">
                <h3 class="services_content_ours_process_text_subtitle">
                  <span class="number_our_process">04</span> Diagnóstico de Normativas Aplicables
                  <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                </h3>
              </div>
              <p class="our_process_text_description" style="display: none;">
                Antes de comenzar, analizamos tu industria, entorno operativo y equipos involucrados. Así identificamos las normativas que deben cumplirse (NOM, ISO, IEC, UL, etc.) en la instalación del controlador.
              </p>
            </div>
            <!-- Puedes copiar este bloque para agregar más pasos -->
            <button class="services_content_info_button">Quiero Contactarlos</button>
          </article>
      </div>
    </div>
  </section>
  <section class="services_content_benefits">
    <div class="row benefits_row">
      <div class="col-6 benefits_text">
        <div class="content_benefits_text">
          <h3 class="benefits_text_title">Beneficios de Nuestros Servicios</h3>
          <p class="benefits_text_title_description">Beneficios de Nuestros Servicios</p>
          <div class="benefist_skills">
            <p class="benefits_text_description"><i class="fas fa-check"></i>Garantia de 3 meses</p>
            <p class="benefits_text_description"><i class="fas fa-check"></i>Cumplimiento de Normativa</p>
            <p class="benefits_text_description"><i class="fas fa-check"></i>Garantia de 3 meses</p>
            <p class="benefits_text_description"><i class="fas fa-check"></i>Cumplimiento de Normativa</p>
            <p class="benefits_text_description"><i class="fas fa-check"></i>Garantia de 3 meses</p>
            <p class="benefits_text_description"><i class="fas fa-check"></i>Cumplimiento de Normativa</p>
          </div>
        </div>
      </div>
      <div class="col-6 d-flex align-items-center justify-content-center m-0 p-0">
          <form class="row g-3 m-5" action="https://formsubmit.co/dilanp270105@gmail.com" method="POST" style="font-family: 'Montserrat', sans-serif;">
              <h2 style="font-weight: 800;">Llenar Formulario</h2>
              <div class="row ">
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
  </section>
  <section class="services_content_slider_services">
    <div>
      <h2>Mas Servicios</h2>
    </div>
  </section>
</main>

@endsection

@push('scripts')
<script>
  function toggleAccordion(header) {
    const description = header.nextElementSibling;
    const icon = header.querySelector('.accordion_icon_ours_service');

    if (description.style.display === "none" || description.style.display === "") {
      description.style.display = "block";
      icon.innerHTML = '<i class="fas fa-caret-up"></i>';
    } else {
      description.style.display = "none";
      icon.innerHTML = '<i class="fas fa-caret-down"></i>';
    }
  }
</script>
  
@endpush
