@extends('frontend.layouts.master')

@section('title')
    Instalacion de Controles de Temperatura
@endsection
@section('content')
    <main>
        <section class="services_content_start">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_start_img">
                            <img src="{{ asset('uploads/servicios/calibracion-ema-1.png') }}"
                                alt="image">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_start_text">
                            <article>
                                <span class="services_content_subtitle">Servicio Profesional</span>
                                <h1 class="services_content_title_start">Calibraciones EMA de Videoregistradores y Medidores
                                    de Flujo</h1>
                                <p class="services_content_description">
                                    Contamos con acreditación EMA para calibrar y certificar videoregistradores de variables
                                    analógicas y medidores de flujo industriales. Asegura la trazabilidad, confiabilidad y
                                    cumplimiento normativo de tus instrumentos de medición.
                                </p>
                                <button class="services_start_button"><i class="fa fa-phone-alt"></i> Atencion
                                    Inmediata</button>
                                <button class="services_start_button"><i class="fa fa-whatsapp"></i> Escribenos</button>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="services_content_info">
            <article>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                            <div class="wsus_content_info">
                                <span class="wsus_content_info_subtitle">Calibración con trazabilidad</span>
                                <h4 class="wsus_content_info_title">Calibraciones EMA de videoregistradores</h4>
                                <p class="wsus_content_info_description">
                                    Realizamos calibraciones acreditadas ante la EMA (Entidad Mexicana de Acreditación) para
                                    videoregistradores de variables analógicas como temperatura, presión, flujo, nivel o
                                    señales eléctricas. Emitimos certificados válidos ante auditorías ISO 9001, IATF, NOM y
                                    clientes con requerimientos internacionales.
                                </p>
                                <button class="services_content_info_button"><i class="fa fa-phone-alt"></i>
                                    Contáctanos</button>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                            <div class="wsus_content_info">
                                <span class="wsus_content_info_subtitle">Trazabilidad nacional e internacional</span>
                                <h4 class="wsus_content_info_title">Medidores de flujo confiables y certificados</h4>
                                <p class="wsus_content_info_description">
                                    Calibramos medidores de flujo tipo electromagnético, ultrasónico, turbina o Coriolis
                                    bajo normas nacionales e internacionales, con trazabilidad al CENAM. Garantizamos
                                    exactitud en tus mediciones para procesos industriales, comerciales o de calidad,
                                    asegurando cumplimiento ante PROFECO, ISO e industria farmacéutica o alimentaria.
                                </p>
                                <button class="services_content_info_button"><i class="fa fa-phone-alt"></i>
                                    Contáctanos</button>
                            </div>


                        </div>
                        <div class="col-12 col-md-4 col-lg-4 col-xl-4 content_img_service_personalizate">
                            <div class="wsus_content_info_img">
                                <img src="{{ asset('uploads/servicios/calibracion-ema-2.png') }}"
                                    alt="image">
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </section>
        <section class="services_content_ours_process">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_ours_process_img-video">
                            <img src="{{ asset('uploads/servicios/calibracion-ema-3.png') }}"
                                alt="Calibración EMA">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_ours_process_text">
                            <article>
                                <span class="services_content_ours_process_text_subtitle_one">Nuestro Proceso para</span>
                                <h2 class="services_content_ours_process_text_title">Calibraciones EMA de Videoregistradores
                                    y Medidores de Flujo</h2>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">01</span> Recepción y verificación de equipo
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Registramos y verificamos trazabilidad de cada instrumento (videoregistrador o
                                        medidor de flujo), su estado, rango de operación y última calibración EMA.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">02</span> Ajuste y acondicionamiento
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Preparamos el equipo: limpieza, revisión de conexiones, acondicionamiento de
                                        sensores y verificación de fuente de alimentación, para asegurar condiciones óptimas
                                        de calibración.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">03</span> Calibración con patrones certificados
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Realizamos la calibración con patrones rastreables a CENAM/EMA: señales eléctricas
                                        (4–20 mA, mV, V), caudales de prueba y variables analógicas (T/C, RTD), siguiendo
                                        procedimientos EMA.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">04</span> Registro y análisis de datos
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Capturamos las lecturas previas y posteriores a la calibración, analizamos
                                        desviaciones e incertidumbre, y generamos reporte técnico conforme a EMA.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">05</span> Emisión de certificado EMA
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Entregamos certificado oficial EMA con trazabilidad completa, valores de corrección,
                                        curva de calibración y niveles de confianza para auditorías.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">06</span> Devolución y soporte post-calibración
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Devolvemos el equipo con etiqueta de calibración, informe técnico y ofrecemos
                                        soporte para interpretación de resultados y programación de la próxima calibración.
                                    </p>
                                </div>

                                <button class="services_content_info_button"><i class="fa fa-phone-alt"></i> Quiero
                                    Contactarlos</button>
                                <button class="services_content_info_button"><i class="fa fa-whatsapp"
                                        aria-hidden="true"></i> Escríbenos</button>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>




        <section class="services_content_benefits">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-7 col-lg-7 col-xl-7  benefits_text">
                        <div class="content_benefits_text">
                            <h3 class="benefits_text_title">Beneficios de Nuestros Servicios</h3>
                            <p class="benefits_text_title_description">Beneficios de Nuestros Servicios</p>
                            <div class="benefist_skills">
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Certificados de
                                    calibración con validez oficial ante auditorías ISO, NOM, IATF y clientes
                                    internacionales.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Calibración de
                                    videoregistradores en variables: temperatura, presión, corriente, voltaje y señales 4-20
                                    mA.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Calibración de
                                    medidores de flujo con trazabilidad al CENAM y apego a normas internacionales.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Equipos patrón
                                    certificados y procedimientos avalados por la EMA.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Reporte de
                                    desviaciones, errores e incertidumbre expandida.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Calibraciones en
                                    sitio o en laboratorio, según tu necesidad.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Aseguramiento de
                                    calidad en tus procesos productivos, evitando sanciones o rechazos.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Programas de
                                    calibración periódica y etiquetado de equipos conforme a normativas.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5 col-lg-5 col-xl-5">
                        <div class="form-service-content">
                            <form class="row form-service" action="https://formsubmit.co/dilanp270105@gmail.com"
                                method="POST" style="font-family: 'Montserrat', sans-serif;">
                                <h2 class="form-service_title">Formulario de Contacto</h2>
                                <div class="row ">
                                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                                        <label for="inputNombre" class="form-label-service">Nombre</label>
                                        <input type="text" class="form-control-service" id="inputNombre"
                                            placeholder="Nombre" aria-label="First name" name="Nombre" required
                                            pattern="[A-Za-z]+" maxlength="30">
                                        <div class="invalid-feedback">
                                            El nombre no puede estar en blanco.
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                                        <label for="inputAddress2" class="form-label-service">Apellidos</label>
                                        <input type="text" class="form-control-service" placeholder="Apellidos"
                                            aria-label="Last name" name="Apellido" pattern="[A-Za-z]+" required>
                                    </div>

                                </div>
                                <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                                    <label for="inputEmail4" class="form-label-service">Email</label>
                                    <input type="email" class="form-control-service" id="inputEmail4"
                                        placeholder="example@gmail.com" name="email" required>
                                </div>
                                <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                                    <label for="telefono" class="form-label-service">N&uacute;mero de telefono</label>
                                    <input type="tel" class="form-control-service" id="inputAddress"
                                        placeholder="Telefono" name="Telefono" required pattern="[0-9]{10}"
                                        title="Ingrese un número de teléfono válido">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputCity" class="form-label-service">Empresa</label>
                                    <input type="text" class="form-control-service" id="inputCity"
                                        placeholder="Nombre Empresa" required name="Ciudad" required>
                                </div>

                                <div class="col-md-4">
                                    <label for="inputCity" class="form-label-service">Ciudad</label>
                                    <input type="text" class="form-control-service" id="inputCity"
                                        placeholder="Ciudad Juarez" name="Ciudad" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputState" class="form-label-service">Estado</label>
                                    <select id="inputState" class="form-select-service" name="Estado" required>
                                        <option selected disabled value="">Seleccionar..</option>
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
                                    <label for="inputOperation" class="form-label-service">Operaci&oacute;n</label>
                                    <select id="inputOperation" class="form-select" name="Operacion" required>
                                        <option selected>Servicio De Calibraci&oacute;n Y Puesta En Marcha</option>
                                    </select>
                                </div>

                                <div class="col-12 form-floating">
                                    <label for="floatingTextarea2" style="color: #ffffff">Mensaje</label>
                                    <textarea class="form-control-service" id="floatingTextarea2" style="height: 100px" name="Mensaje"></textarea>
                                </div>
                                <div class="col-12 form-button-submit">
                                    <button type="submit" class="btn btn-form-service">Enviar solicitud</button>
                                    <input type="hidden" name="_next"
                                        value="http://127.0.0.1:8000/cotizar"><!--Cambiar url-->
                                    <input type="hidden" name="_captcha" value="false">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        {{-- FUTURO --}}
        {{-- <section class="services_content_slider_services">
    <div class="container-fluid">
      <h2 class="text-center">Mas Servicios</h2>
      <div class="row">
      <div class="col-4">
        <a href="">
        <div class="services_content_slider_services_img">
          <img src="{{asset('frontend/images/imagen ejemplo.png')}}" alt="image">
          <h3 class="services_content_slider_services_title">Calibracion de Controladores</h3>
          <p class="services_content_slider_services_description">Lorem ipsum dolor sit amet consectetur adipisicing
          elit. Quisquam, voluptatibus.</p>
          <button class="services_content_slider_services_button">Ver Servicio</button>
        </div>
        </a>
      </div>
      <div class="col-4">
        <a href="">
        <div class="services_content_slider_services_img">
          <img src="{{asset('frontend/images/imagen ejemplo.png')}}" alt="image">
          <h3 class="services_content_slider_services_title">Calibracion de Controladores</h3>
          <p class="services_content_slider_services_description">Lorem ipsum dolor sit amet consectetur adipisicing
          elit. Quisquam, voluptatibus.</p>
          <button class="services_content_slider_services_button">Ver Servicio</button>
        </div>
        </a>
      </div>
      <div class="col-4">
        <a href="">
        <div class="services_content_slider_services_img">
          <img src="{{asset('frontend/images/imagen ejemplo.png')}}" alt="image">
          <h3 class="services_content_slider_services_title">Calibracion de Controladores</h3>
          <p class="services_content_slider_services_description">Lorem ipsum dolor sit amet consectetur adipisicing
          elit. Quisquam, voluptatibus.</p>
          <button class="services_content_slider_services_button">Ver Servicio</button>
        </div>
        </a>
      </div>
      </div>
    </div>
    </section> --}}
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
    <script>
        $(document).ready(function() {
            $('#inputState').select2({
                width: '100%',
                placeholder: 'Seleccionar..',
                allowClear: true
            });
            $('#inputOperation').select2({
                width: '100%',
                allowClear: true
            });
        });
    </script>
@endpush
