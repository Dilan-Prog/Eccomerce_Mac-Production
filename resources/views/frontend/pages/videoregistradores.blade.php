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
                            <img src="{{ asset('uploads/servicios/instalacion_videoregistradores-1.png') }}" alt="image">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_start_text">
                            <article>
                                <span class="services_content_subtitle">Servicio Profesional</span>
                                <h1 class="services_content_title_start">Instalacion de Videoregistradores</h1>
                                <p class="services_content_description">Especialistas en sistemas de grabadores de variables
                                    analógicas. Ofrecemos instalación profesional de grabadores de datos para garantizar la
                                    confiabilidad y precisión de tus procesos industriales.</p>
                                <button class="services_start_button"><i class="fa fa-phone-alt"></i> Atención
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
                                <span class="wsus_content_info_subtitle">Control de grabadores de variables confiable</span>
                                <h4 class="wsus_content_info_title">Soluciones para grabadores de variables analógicas
                                    industriales</h4>
                                <p class="wsus_content_info_description">En el sector industrial, un grabador de variables
                                    mal instalado puede comprometer la precisión y fiabilidad del monitoreo de datos
                                    críticos. Evaluamos cada aspecto de tu entorno (tipo de sensores, almacenamiento de
                                    datos, conectividad con sistemas de gestión y redundancia de datos) para asegurar un
                                    sistema de grabadores de variables analógicas estable, seguro y eficiente, con respaldo
                                    continuo.</p>
                                <button class="services_content_info_button"><i class="fa fa-phone-alt"></i>
                                    Contactanos</button>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                            <div class="wsus_content_info">
                                <span class="wsus_content_info_subtitle">Monitoreo eficiente</span>
                                <h4 class="wsus_content_info_title">Integración y adaptación a tu infraestructura</h4>
                                <p class="wsus_content_info_description">Instalamos grabadores de variables analógicas de
                                    última generación, desde modelos básicos hasta sistemas avanzados con capacidades de
                                    grabación continua, acceso remoto y configuración personalizada. Trabajamos con marcas
                                    líderes en el sector como Honeywell, Omron, Siemens y Yokogawa para garantizar que tu
                                    solución de grabadores de variables se adapte perfectamente a las necesidades de tu
                                    proceso industrial.</p>
                                <button class="services_content_info_button"> <i class="fa fa-phone-alt"></i>
                                    Contactanos</button>
                            </div>

                        </div>
                        <div class="col-12 col-md-4 col-lg-4 col-xl-4 content_img_service_personalizate">
                            <div class="wsus_content_info_img">
                                <img src="{{ asset('uploads/servicios/instalacion_videoregistradores-2.png') }}" alt="image">
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
                            {{-- <lite-youtube videoid="3w3xq8VJQSc"></lite-youtube> --}}
                            <img src="{{ asset('uploads/servicios/instalacion_videoregistradores-3.png') }}" alt="image">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_ours_process_text">
                            <article>
                                <span class="services_content_ours_process_text_subtitle_one">Nuestro Proceso para</span>
                                <h2 class="services_content_ours_process_text_title">Instalaciones de Controladores de
                                    Temperatura</h2>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">01</span> Evaluación técnica del entorno
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Analizamos la infraestructura de sensores, el tipo de variables a medir (como
                                        temperatura, presión, etc.) y las necesidades específicas del entorno para
                                        determinar la mejor configuración del grabador.
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">02</span> Selección del grabador adecuado
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Recomendamos el modelo de grabador adecuado según el número de entradas analógicas,
                                        la compatibilidad con los sensores (T/C, RTD, lineales) y la capacidad de
                                        almacenamiento necesaria para tu proceso.
                                    </p>
                                </div>
                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">03</span> Instalación eléctrica y física
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Conectamos los sensores y transmisores a los grabadores de forma segura, asegurando
                                        las conexiones y la integración con otros sistemas de monitoreo si es necesario.
                                    </p>
                                </div>
                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">04</span> Configuración y parametrización
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Ajustamos los parámetros de grabación, rangos de medición, y configuramos alarmas o
                                        notificaciones, según las necesidades específicas del cliente.
                                    </p>
                                </div>
                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">05</span> Integración con sistemas existentes
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Integramos el grabador con sistemas de gestión de datos o almacenamiento en la nube
                                        para que los datos estén siempre disponibles y sean fáciles de analizar.
                                    </p>
                                </div>
                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">06</span> Pruebas y puesta en marcha
                                            <span class="accordion_icon_ours_service"><i
                                                    class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Verificamos la precisión de las mediciones, el funcionamiento de las alarmas y la
                                        capacidad de almacenamiento antes de entregar el sistema, asegurando que cumpla con
                                        los requisitos operativos.
                                    </p>
                                </div>
                                <!-- Puedes copiar este bloque para agregar más pasos -->
                                <button class="services_content_info_button"><i class="fa fa-phone-alt"></i> Quiero
                                    Contactarlos</button>
                                <button class="services_content_info_button"><i class="fa fa-whatsapp"
                                        aria-hidden="true"></i> Escribenos</button>
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
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Instalación
                                    garantizada bajo estándares NOM e ISO.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Reducción de
                                    fallos en la captura y almacenamiento de datos.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Aumento de la
                                    precisión y fiabilidad de los registros de variables analógicas.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Asesoría técnica
                                    desde la evaluación del entorno hasta la calibración y configuración del sistema.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Adaptación total
                                    a entornos industriales exigentes y con espacio limitado.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Soporte
                                    post-instalación ante dudas o ajustes de configuración.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Tiempos de
                                    instalación rápidos sin interrumpir las operaciones industriales.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Equipos
                                    compatibles con sistemas de automatización industrial y SCADA.</p>
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
