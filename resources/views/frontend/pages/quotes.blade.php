@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Cotización
@endsection

@section('content')
    <section class="quotes-personal">
        <div class="container">
            <h1 class="text-center mt-5">Solicita una Cotizaci&oacute;n</h1>
            <form class="row g-3 m-5 p-0" action="https://formsubmit.co/dilanp270105@gmail.com" method="POST" style="font-family: 'Montserrat', sans-serif;">

                <div class="row m-0 p-0">
                    <div class="col-md-6">
                        <label for="inputNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="inputNombre" placeholder="Nombre" aria-label="First name" name="Nombre" required pattern="[A-Za-z]+" maxlength="30" >

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
                    <label for="inputEmpresa" class="form-label">Empresa</label>
                    <input type="text" class="form-control" id="inputEmpresa" placeholder="Nombre Empresa" required name="Empresa">
                  </div>

                <div class="col-md-4">
                  <label for="inputCity" class="form-label">Ciudad</label>
                  <input type="text" class="form-control" id="inputCity" placeholder="Ej.Ciudad Juarez" name="Ciudad" required>
                </div>
                <div class="col-md-4">
                    <label for="inputState" class="form-label" >Estado</label>
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
                    <label for="inputOperation" class="form-label" >Operaci&oacute;n</label>
                    <select id="inputOperation" class="form-select" name="Operacion" required>
                      <option selected>Cotizaci&oacute;n De 1 o M&aacute;s Productos</option>
                        <option >Servicio Sistemas De Control</option>
                        <option >Servicio Medici&oacute;n De Gas</option>
                        <option >Servicio Calibraci&oacute;n Y Puesta En Marcha</option>
                        
                    </select>
                </div>

                <div class="col-12 form-floating">
                    <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px" name="Mensaje"></textarea>
                    <label for="floatingTextarea2">Mensaje</label>
                  </div>



                <div class="col-12">
                  <button type="submit" class="btn btn-dark w-100">Enviar solicitud</button>
                  <input type="hidden" name="_next" value="http://127.0.0.1:8000/cotizar"><!--Cambiar url-->
                  <input type="hidden" name="_captcha" value="false">
                 </div>
              </form>
        </div>
    </section>




@endsection
