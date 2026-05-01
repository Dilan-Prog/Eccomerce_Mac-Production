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
                            <img src="{{ asset('uploads/servicios/instalacion_plc-1.png') }}" alt="image">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_start_text">
                            <article>
                                <span class="services_content_subtitle">Servicio Profesional</span>
                                <h1 class="services_content_title_start">Instalacion, Configuración y proyecto llave en mano
                                    de PLC</h1>
                                <p class="services_content_description">
                                    Especialistas en sistemas de automatización con PLC. Ofrecemos servicio integral —desde
                                    el diseño del proyecto hasta la puesta en marcha— para garantizar un control fiable y
                                    eficiente de tus procesos industriales.
                                </p>
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
                                <span class="wsus_content_info_subtitle">Control de procesos automatizado</span>
                                <h4 class="wsus_content_info_title">Soluciones de PLC llave en mano</h4>
                                <p class="wsus_content_info_description">
                                    En entornos industriales, un PLC mal dimensionado o configurado puede causar paros,
                                    defectos o ineficiencias. Diseñamos y entregamos proyectos llave en mano de PLC,
                                    evaluando protocolos, E/S digitales y analógicas, comunicaciones (Modbus, Profinet,
                                    EtherNet/IP) y redundancia para asegurar un sistema robusto y escalable.
                                </p>
                                <button class="services_content_info_button"><i class="fa fa-phone-alt"></i>
                                    Contáctanos</button>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-4 col-xl-4">
                            <div class="wsus_content_info">
                                <span class="wsus_content_info_subtitle">Implementación y puesta en marcha</span>
                                <h4 class="wsus_content_info_title">Adaptación a tu infraestructura de control</h4>
                                <p class="wsus_content_info_description">
                                    Instalamos y configuramos PLC de las principales marcas (Honeywell,Siemens, Allen‑Bradley, Schneider, Mitsubishi), creando lógicas de control, pantallas HMI y comunicaciones con SCADA. Nos aseguramos de que tu sistema funcione sin conflictos y cumpla con normas IEC, UL y NOM.
                                </p>
                                <button class="services_content_info_button"><i class="fa fa-phone-alt"></i> Contáctanos</button>
                            </div>


                        </div>
                        <div class="col-12 col-md-4 col-lg-4 col-xl-4 content_img_service_personalizate">
                            <div class="wsus_content_info_img">
                                <img src="{{ asset('uploads/servicios/instalacion_plc-2.png') }}" alt="image">
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </section>
        <section class="services_content_ours_process">
            <div class="container">
                <div class="row">
                    <!-- Imagen o video ilustrativo -->
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_ours_process_img-video">
                            <img src="{{ asset('uploads/servicios/instalacion_plc-3.png') }}" alt="Instalación de PLC">
                        </div>
                    </div>
                    <!-- Texto del proceso -->
                    <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="services_content_ours_process_text">
                            <article>
                                <span class="services_content_ours_process_text_subtitle_one">Nuestro Proceso para</span>
                                <h2 class="services_content_ours_process_text_title">Proyecto Llave en Mano de PLC</h2>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">01</span> Ingeniería y diseño del sistema
                                            <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Definimos especificaciones del PLC, E/S, HMI y comunicaciones según requisitos de proceso, normativas y entorno operativo.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">02</span> Suministro de equipos y materiales
                                            <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Proveemos PLC, módulos de E/S, fuentes, cables, gabinetes y HMI de acuerdo al diseño aprobado.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">03</span> Instalación eléctrica y montaje
                                            <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Montamos gabinetes, tendido de cables y conexionado de PLC, E/S y HMI, cumpliendo normas de seguridad eléctrica.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">04</span> Programación y configuración
                                            <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Desarrollamos la lógica ladder o estructurada, configuramos HMI y establecemos comunicaciones con SCADA o ERP.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">05</span> Integración y pruebas en planta
                                            <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Ejecutamos pruebas de I/O, simulaciones de proceso y validación de secuencias antes de la puesta en marcha.
                                    </p>
                                </div>

                                <div class="accordion_item">
                                    <div class="accordion_header" onclick="toggleAccordion(this)">
                                        <h3 class="services_content_ours_process_text_subtitle">
                                            <span class="number_our_process">06</span> Puesta en marcha y capacitación
                                            <span class="accordion_icon_ours_service"><i class="fas fa-caret-down"></i></span>
                                        </h3>
                                    </div>
                                    <p class="our_process_text_description" style="display: none;">
                                        Activamos el sistema en condiciones reales, entrenamos a tu equipo de operación y entregamos documentación técnica completa.
                                    </p>
                                </div>

                                <button class="services_content_info_button"><i class="fa fa-phone-alt"></i> Quiero Contactarlos</button>
                                <button class="services_content_info_button"><i class="fa fa-whatsapp" aria-hidden="true"></i> Escríbenos</button>
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
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Proyecto llave en mano: diseño, suministro, instalación y puesta en marcha.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Cumplimiento de estándares internacionales IEC, UL, NOM e ISA.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Reducción de tiempos de paro y optimización de ciclos de producción.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Desarrollo de lógicas de control a medida y pantallas HMI intuitivas.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Integración con sistemas SCADA, ERP y redes industriales.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Soporte técnico y capacitación para tu equipo de mantenimiento.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Documentación completa: manuales eléctricos, diagramas ladder y esquemas de cableado.</p>
                                <p class="benefits_text_description"><i class="fas fa-check-circle"></i> Escalabilidad para futuras ampliaciones o migraciones de sistema.</p>
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
