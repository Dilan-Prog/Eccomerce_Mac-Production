@extends('frontend.layouts.master')
@section('title')
{{$settings->site_name}} || Restablecer Contrase&ntilde;a
@endsection
@section('content')

    <!--============================
        CHANGE PASSWORD START
    ==============================-->
    <section id="wsus__login_register">
        <div class="container">
            <div class="row">
                <div class="col-xl-5 col-md-10 col-lg-7 m-auto">
                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf
                        <div class="wsus__change_password">
                            
                            
                            <h4>Restablecer Contraseña</h4>
                                <!-- Password Reset Token -->
                                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                            <div class="wsus__single_pass">
                                <label>Correo electronico</label>
                                <input name="email" id="email" value="{{old('email',$request->email)}}" type="emai" placeholder="Correo electronico">
                            </div>
                            <div class="wsus__single_pass">
                                <label>Nueva Contraseña</label>
                                <input id="password" name="password" type="password" placeholder="Nueva Contraseña">
                            </div>
                            <div class="wsus__single_pass">
                                <label>Confirmar Contraseña</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirmar Contraseña">
                            </div>
                            <button class="common_btn" type="submit">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!--============================
        CHANGE PASSWORD END
    ==============================-->
    @endsection