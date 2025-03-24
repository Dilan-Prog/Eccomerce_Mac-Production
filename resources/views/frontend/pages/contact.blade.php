@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Contacto
@endsection

@section('content')

<section id="contact-section">
    <div class="container mt-5">
        <div class="text-center m-4">
            <h2 class="card-title" style="font-weight: bold">Contacto Oficina Matriz</h2>
            <p class="card-text">Contáctanos estamos para servirle.</p>
        </div>
    
        <div class="row justify-content-center">
            <!-- Bloque 1 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card" id="hover-contact">
                    <a href="mailto:contacto@macdelnorte.com" onclick="dataLayer.push({'event':'correo_contact_action','action':'click', ' label':'correo_contacto'})" target="_blank">
                        <div class="card-body d-flex align-items-center">
                         <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00468c" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                            <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/>
                         </svg>
                         <div class="d-flex flex-column ms-3">
                            <h3 class="mb-0" style="font-family: 'Montserrat', sans-serif;
                            font-weight: 600; color: black">Correo electronico </h3>
                            <div class="ms-4" >
                                <p class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                font-weight: 400; color: #313131">contacto@macdelnorte.com</p>
    
                            </div>
                        </div>
                    </div>
                    </a>
                </div>
            </div>
    
            <!-- Bloque 2 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card" id="hover-contact">
                    <a href="https://wa.link/f28njw" 
                    target="_blank" 
                    id="whatsapp-link"
                    onclick="dataLayer.push({'event': 'whatsapp_conversion', 'action': 'click', 'label': 'whatsapp-icon'});" >
                        <div class="card-body d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00468c" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                            </svg>
                            <div class="d-flex flex-column ms-3" >
                                <a href=""></a><h3 class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                font-weight: 600; color: black">WhatsApp</h3>
                                <div class="ms-4">
                                    <p class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                    font-weight: 400; color: #313131">+81-35825559</p>
        
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
    
        </div>
    
        <div class="row justify-content-center">
    
            <!-- Bloque 3 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card" id="hover-contact">
                    <a href="">
                        <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00468c" class="bi bi-telephone-inbound-fill" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877zM15.854.146a.5.5 0 0 1 0 .708L11.707 5H14.5a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 1 0v2.793L15.146.146a.5.5 0 0 1 .708 0"/>
                        </svg>
                        <div class="d-flex flex-column ms-3">
                            <h3 class="mb-0" style="font-family: 'Montserrat', sans-serif;
                            font-weight: 600; color: black">Telefono</h3>
                            <div class="ms-4">
                                <p class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                font-weight: 600; color: black">81-2473-8744 / 81-2473-8768</p>
    
    
                            </div>
                        </div>
    
    
                    </div>
                    </a>
                </div>
            </div>
    
            <!-- Bloque 4 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card" id="hover-contact">
                    <a href="">
                        <div class="card-body d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00468c" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                            <div class="d-flex flex-column ms-3">
                                <h3 class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                font-weight: 600; color: black">Ubicaci&oacute;n</h3>
                                <div class="ms-4">
                                    <p class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                    font-weight: 400; color: #313131">Apodaca N.L. CP.66612</p>
    
                                </div>
                            </div>
                        </div>
    
    
                    </a>
                </div>
            </div>
    
        </div>
    </div>
    
    <div class="container-flex ">
    
        <div class="row" id="google-map-div">
          <div class="col-lg-6  d-flex justify-content-center align-items-center" style="max-width: 100%;" >
            <div>
                <h2 id="google-maps-title">Oficina Matriz</h2>
                <p class="mb-0" id="google-maps-text">C. Castaño No.718</p>
                <p class="mb-0" id="google-maps-text">Col. Ebanos Norte 2do Sector</p>
                <p class="" id="google-maps-text">Apodaca N.L. CP.66612</p>
            </div>
          </div>
          <div class="col-lg-6 " >
            <div class="google-map" id="google-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14370.842519182363!2d-100.19980187739522!3d25.780119860140644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8662ec0aba444135%3A0x6047a3f76e4cbabd!2sCabecera%20Municipal%20(Apodaca)%2C%20Apodaca%20Centro%2C%20Cd%20Apodaca%2C%20N.L.!5e0!3m2!1ses!2smx!4v1630797429181!5m2!1ses!2smx" allowfullscreen="" loading="lazy"></iframe>
            </div>
          </div>
        </div>
    </div>
    
    <div class="container mt-5">
        <div class="text-center m-4">
            <h2 class="card-title" style="font-weight: bold">Contacto Sucursal</h2>
            <p class="card-text">Contáctanos estamos para servirle.</p>
        </div>
    
        <div class="row justify-content-center">
            <!-- Bloque 1 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card" id="hover-contact">
                    <a href="">
                        <div class="card-body d-flex align-items-center">
                         <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00468c" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                            <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/>
                         </svg>
                         <div class="d-flex flex-column ms-3">
                            <h3 class="mb-0" style="font-family: 'Montserrat', sans-serif;
                            font-weight: 600; color: black">Correo electronico </h3>
                            <div class="ms-4" >
                                <p class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                font-weight: 400; color: #313131">ventasmty@macdelnorte.com</p>
    
                            </div>
                        </div>
                    </div>
                    </a>
                </div>
            </div>
    
            <!-- Bloque 2 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card" id="hover-contact">
                    <a href="https://wa.link/f28njw">
                        <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00468c" class="bi bi-whatsapp" viewBox="0 0 16 16">
                            <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                        </svg>
                        <div class="d-flex flex-column ms-3" >
                            <h3 class="mb-0" style="font-family: 'Montserrat', sans-serif;
                            font-weight: 600; color: black">WhatsApp</h3>
                            <div class="ms-4">
                                <p class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                font-weight: 400; color: #313131">+81-10946873</p>
    
                            </div>
                        </div>
                    </div>
                    </a>
                </div>
            </div>
    
        </div>
    
        <div class="row justify-content-center">
    
            <!-- Bloque 3 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card" id="hover-contact">
                    <a href="">
                        <div class="card-body d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00468c" class="bi bi-telephone-inbound-fill" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877zM15.854.146a.5.5 0 0 1 0 .708L11.707 5H14.5a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 1 0v2.793L15.146.146a.5.5 0 0 1 .708 0"/>
                        </svg>
                        <div class="d-flex flex-column ms-3">
                            <h3 class="mb-0" style="font-family: 'Montserrat', sans-serif;
                            font-weight: 600; color: black">Telefono</h3>
                            <div class="ms-4">
                                <p class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                font-weight: 600; color: black">81-1094-6873 / 81-1094-6852</p>
    
    
                            </div>
                        </div>
    
    
                    </div>
                    </a>
                </div>
            </div>
    
            <!-- Bloque 4 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card" id="hover-contact">
                    <a href="">
                        <div class="card-body d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00468c" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                            <div class="d-flex flex-column ms-3">
                                <h3 class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                font-weight: 600; color: black">Ubicaci&oacute;n</h3>
                                <div class="ms-4">
                                    <p class="mb-0" style="font-family: 'Montserrat', sans-serif;
                                    font-weight: 400; color: #313131">398 Plaza Ebano en calle Oaxaca</p>
    
                                </div>
                            </div>
                        </div>
    
    
                    </a>
                </div>
            </div>
    
        </div>
    </div>
    
    <div class="container-flex ">
        <div class="row" id="google-map-div">
            <div class="col-lg-6  d-flex justify-content-center align-items-center" style="max-width: 100%;" >
              <div>
                  <h2 id="google-maps-title">Sucursal Venta al Publico</h2>
                  <p class="mb-0" id="google-maps-text">398 Plaza Ebano en calle Oaxaca</p>
                  <p class="mb-0" id="google-maps-text">Esquina con padre mier en el</p>
                  <p class="" id="google-maps-text">Centro De Apodaca N.L.</p>
              </div>
            </div>
            <div class="col-lg-6 " >
              <div class="google-map" id="google-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3592.5370002547666!2d-100.1914711238637!3d25.78585230760537!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8662ec0c4e88f461%3A0x539ceac14f3faed3!2sOaxaca%20398%2C%20Cabecera%20Municipal%20(Apodaca)%2C%20Nuevo%20Apodaca%2C%2066600%20Cdad.%20Apodaca%2C%20N.L.!5e0!3m2!1ses-419!2smx!4v1717806549974!5m2!1ses-419!2smx" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
            </div>
        </div>
    </div>

</section>





@endsection
