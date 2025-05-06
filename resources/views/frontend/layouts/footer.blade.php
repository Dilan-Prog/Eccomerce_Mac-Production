  <!--============================
        FOOTER PART START
    ==============================-->
    <footer class="footer_2" >
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-xl-3 col-sm-3 col-md-3 col-lg-3">
                    <div class="wsus__footer_content">
                        <a class="wsus__footer_2_logo" href="{{route('index')}}">
                            <img src="{{asset('frontend/images/logo/logo-negro-azul.png')}}" alt="logo" id="logo-horizontal">
                            <img  src="{{asset('frontend/images/logo/silver-parther.webp')}}" alt="Honeywell Parther" id="parther">
                        </a>
                        <ul class="wsus__footer_social">
                            <li><a class="whatsapp" href="https://wa.link/f28njw" target="_blank"><i class="fab fa-whatsapp" ></i></a></li>
                            <li><a class="facebook" href="https://www.facebook.com/macdelnorteofficial" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                            <li> <a href="https://twitter.com/MacDelNorte" class="twitter" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"  class="bi bi-twitter-x" viewBox="0 0 16 16">
                                    <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
                                </svg></a>
                            </li>
                            <li><a class="linkedin" href="https://www.linkedin.com/in/mac-del-norte-480944235/" target="_blank"><i class="fab fa-linkedin"></i></a></li>
                        </ul>
                        <ul class="wsus__footer_social">
                            <li><a class="youtube" href="https://www.youtube.com/@macdelnorte7889" target="_blank"><i class="fab fa-youtube"></i></a></li>
                            <li><a class="instagram" href="https://www.youtube.com/@macdelnorte7889" target="_blank"><i class="fab fa-instagram"></i></a></li>
                            <li><a class="instagram" href="https://www.tiktok.com/@macdelnorteoficial" target="_blank"><i class="fab fa-tiktok"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-2 col-sm-2 col-md-2 col-lg-2">
                    <div class="wsus__footer_content">
                        <h5>Paginas</h5>
                        <ul class="wsus__footer_menu">
                            <li><a href="{{ route('index') }}"><i class="fas fa-caret"></i> Inicio</a></li>
                            <li><a href="{{ route('price') }}"><i class="fas fa-caret"></i> Cotizacion</a></li>
                            <li><a href="{{ route('products.index') }}"><i class="fas fa-caret"></i> Productos</a></li>
                            <li><a href="{{ route('about') }}"><i class="fas fa-caret"></i> Nosotros</a></li>
                            <li><a href="#"><i class="fas fa-caret"></i> Servicios</a></li>
                            <li><a href="{{ route('contact') }}"><i class="fas fa-caret"></i> Contacto</a></li>
                            <li><a href="{{ route('contact') }}"><i class="fas fa-caret"></i> Terminos y Condiciones</a></li>

                        </ul>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-3 col-md-3 col-lg-3">
                    <div class="wsus__footer_content">
                        <h5>Oficina Matriz</h5>
                        <ul class="wsus__footer_content wsus__footer_content_2">
                            <li><a href="https://wa.link/f28njw"><i class="fab fa-whatsapp"></i> 81-3582-5559</a></li>
                            <li><a href="#"><i class="fas fa-phone-square"></i> 81-2473-8768</a></li>
                            <li><a href="#"><i class="fas fa-envelope"></i> contacto@macdelnorte.com</a></li>
                            <li><a href="#"><i class="fas fa-envelope"></i> ventas1@macdelnorte.com</a></li>
                        </ul>
                        <br>
                        <h5>Oficina Surcusal</h5>
                        <ul class="wsus__footer_content wsus__footer_content_2">
                            <li><a href="https://wa.link/f28njw"><i class="fab fa-whatsapp"></i> 81-3255-6162</a></li>
                            <li><a href="#"><i class="fas fa-phone-square"></i> 81-1094-6852</a></li>
                            <li><a href="#"><i class="fas fa-envelope"></i> ventasmty@macdelnorte.com</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-4 col-md-4 col-lg-4">
                    <div class="container">
                        <div class="wsus__footer_content wsus__footer_content_2 d-flex flex-column justify-content-start align-items-center">
                            <img src="{{ asset('frontend/images/iconos/Asesorias-footer.webp') }}" alt="Asesorias" width="100px" height="107px">
                            <h5>Asesorias Tecnicas/ Soporte</h5>
                            <ul class="wsus__footer_content wsus__footer_content_2" >
                                <li><a href="#"><i class="fas fa-phone-square"></i> 81-2473-8744</a></li>
                                <li> <a href="mailto:product.manager@macdelnorte.com"><i class="fas fa-envelope"></i> product.manager@macdelnorte.com</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="wsus_logos-parther_footer">
            <img src="{{asset('frontend/images/iconos-empresas/mastercard-logo_with_bgc.webp')}}" alt="">
            <img src="{{asset('frontend/images/iconos-empresas/Visa_logo_with_bgc.webp')}}" alt="">
            <img src="{{asset('frontend/images/iconos-empresas/bank_BBVA-logo_with_bgc.webp')}}" alt="">
            <img src="{{asset('frontend/images/iconos-empresas/Paypal-logo_with_bgc.webp')}}" alt="">
            <img src="{{asset('frontend/images/iconos-empresas/delivery_DHL-logo_with_bgc.webp')}}" alt="">
            <img src="{{asset('frontend/images/iconos-empresas/delivery_Estafeta-logo_with_bgc.webp')}}" alt="">
            <img src="{{asset('frontend/images/iconos-empresas/delivery_paquete_express-logo.webp')}}" alt="">
        </div>


        <div class="wsus__footer_bottom">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="wsus__copyright d-flex justify-content-center">
                            <p style="color: white">Copyright ©2024 Mac Del Norte. Todos Los Derechos Reservados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--============================
        FOOTER PART END
    ==============================-->
