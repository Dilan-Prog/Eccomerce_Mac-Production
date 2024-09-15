@extends('frontend.layouts.master')
@section('title')
{{$settings->site_name}} || Recuperar Contrase&ntilde;a
@endsection

@section('content')

    <!--============================
        FORGET PASSWORD START
    ==============================-->
    <section id="wsus__login_register">
        <div class="container">
            <div class="row">
                <div class="col-xl-5 m-auto">
                    <div class="wsus__forget_area">
                        <span class="qiestion_icon"><i class="fal fa-question-circle"></i></span>
                        <h4>Recuperar Contraseña
                        </h4>
                        <p>Ingrese la Direccion de Correo electronico registrada en<span>Mac del norte</span></p>
                        <div class="wsus__login">
                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf
                                <div class="wsus__login_input">
                                    <i class="fal fa-envelope"></i>
                                    <input id="email" type="email" name="email" value="{{old('email')}}" placeholder="Correo Electronico">
                                </div>
                                <button class="common_btn" type="submit">Enviar</button>
                            </form>
                        </div>
                        <a class="see_btn mt-4" href="{{route('login')}}">Ir a Iniciar Sesion</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--============================
        FORGET PASSWORD END
    ==============================-->




@endsection
