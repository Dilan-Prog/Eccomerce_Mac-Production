@extends('frontend.layouts.master')

@section('title')
    {{ $settings->site_name }} || Calibracion y Puesta en Marcha
@endsection

@section('content')
    <section id="associate_page">
        <div class="associate-site-content">
            <div class="associate-start-text">
                <img src="{{ asset('frontend/images/iconos/associated-start.webp') }}" alt="">
                <div class="container">

                    <p class="associate-text-eslogan">NO SOMOS UNA OPCION, SOMOS LA ELECCION CORRECTA</p>
                    <h1>ASOCIADOS Y <br>REVENDEDORES</h1>
                    {{-- PONER UN AFTER --}}
                    <p>Unete a nuestro equipo de Asociados/Revendedores y aprovecha los grandes beneficios <br> que puedes
                        obtener
                        para tu industria.</p>
                    <button class="common_btn"><a href="#associate-form">QUIERO UNIRME</a></button>
                </div>

            </div>
            <div class="associate-content-card">
                <div class="container">
                    <div class="row mt-4">
                        <div class="col-sm-12 col-md-3">
                            <div class="card card-start-end">
                                <div class="card-body">
                                    <img src="{{asset('frontend/images/iconos/tecnologia.webp')}}" alt="">
                                    <h5>Nuevas Tecnologias</h5>
                                    <p class="associate-desription">Acceso a tegnologia de primera mano con demos y capacitaciones por parte del fabricante.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <img src="{{asset('frontend/images/iconos/price-unique.webp')}}" alt="">
                                    <h5>Precios Exclusivos</h5>
                                    <p class="associate-desription">Para todos los Reveendedores y Asociados para tener una mayor competitividad en el mercado y Stock Inmediato.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="card">
                                <div class="card-body">
                                    <img src="{{asset('frontend/images/iconos/tarjeta-de-credito.webp')}}" alt="">
                                    <h5>Credito</h5>
                                    <p class="associate-desription">Credito para mayor flexibilidad y adquisicion de producto para crecimiento continuo de nuestros asociados y reveendedores.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="card card-start-end">
                                <div class="card-body">
                                    <img src="{{asset('frontend/images/iconos/capacitacion.webp')}}" alt="">
                                    <h5>Capacitaciones</h5>
                                    <p class="associate-desription">Especializamos y preparamos a nuestros revendedores y asociadoas para tener un mayor conocimiento sobre los productos y puedan enfrentarse a los desafios del mercado. </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        
        <div class="container" style="background-color: white ;">
            <div class="container " style="background-color: white ;">
              <div class="row ">
                <!-- Formulario -->
                <div class="col-md-12 d-flex align-items-center justify-content-center m-0 p-0" id="associate-form">
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
                              <option selected>Asociado/Renvendedor</option>
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
