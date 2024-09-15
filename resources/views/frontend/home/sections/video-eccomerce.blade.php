<section id="video-eccomerce">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-md-12 ">
                <div class="content-eccomerce-information">
                    <h3>¿Comó comprar en macdelnorte.com?</h3>
                    <p>Aqui te dejamos un peque&ntilde;o video guiandote paso a paso de como comprar a traves de nuestro
                        sitio Web.
                    </p>
                    <p><b>¿Preguntas y Dudas?</b></p>
                    <p>Contactanos via whatsapp o por correo electronico te atenderemos a la brevedad para ayudarte a
                        concretar tu compra.</p>
                    <div class="d-flex mt-1 justify-content-start align-items-center">
                        {{-- subir a servidor el <div class="d-flex mt-1 justify-content-center align-items-center"> --}}
                        <button class="common_btn mr-2"><a href="{{ route('contact') }}" target="_black"
                                style="text-decoration: none; color:white"><i class="fa fa-envelope"></i> Correo Electronico</a></button>
                        <p style="margin-left: 10px; margin-right: 10px;"> o </p>
                        <button class="common_btn"><a href="https://wa.link/f28njw" target="_black"
                                style="text-decoration: none; color:white"><i class="fa fa-whatsapp"></i>
                                81-35825559 </a></button>
                    </div>


                </div>

            </div>
            <div class="col-xl-6 col-md-12">
                <div class="iframe-video-eccomerce" >
                    <!-- Marcador de Posición -->
                    <div class="video-placeholder" onclick="loadVideo()">
                        <div class="play-button">
                            <i class="fa fa-play-circle-o"></i>
                        </div>
                        <img src="{{asset('frontend/images/logo/img-comocomprar.webp')}}" alt="Video Thumbnail" />
                    </div>
                    <!-- Contenedor para el Video (inicialmente oculto) -->
                    <div id="video-container" style="display: none">
                        <iframe 
                            id="video-frame"
                            src="" 
                            title="¿Cómo comprar en Mac del Norte?" 
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="no-referrer-when-downgrade" 
                            allowfullscreen 
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts allow-forms allow-presentation"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
</section>
