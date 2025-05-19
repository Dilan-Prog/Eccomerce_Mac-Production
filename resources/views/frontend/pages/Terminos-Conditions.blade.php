@extends('frontend.layouts.master')

@section('title')
  Terminos y Condiciones
@endsection
@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <div class="bg-white p-4 rounded shadow">
                <h1 class="mb-3 text-center" style="color:#00468c; font-weight:800;">TÉRMINOS Y CONDICIONES DE COMPRA Y USO</h1>
                <p class="text-center mb-1"><strong>MAC DEL NORTE – MONITOREO, AUTOMATIZACIÓN Y CONTROLES DEL NORTE, S.A.P.I. de C.V.</strong></p>
                <p class="text-center mb-1">RFC: NMA180313M46</p>
                <p class="text-center mb-1">Domicilio Fiscal: Apodaca, Nuevo León, México.</p>
                <p class="text-center mb-3"><em>Última actualización: 9 de mayo de 2025</em></p>
                <hr>
                <ol class="mb-4" style="font-size:16px;">
                    <li class="mb-3">
                        <strong>ACEPTACIÓN DE TÉRMINOS</strong><br>
                        Al acceder o usar este sitio (“Sitio”), realizar una compra o solicitar un servicio, usted acepta estos Términos y Condiciones y nuestra Política de Privacidad (documento independiente).
                    </li>
                    <li class="mb-3">
                        <strong>OBJETO</strong><br>
                        Este Sitio permite la adquisición de productos industriales (controladores Honeywell DC1010, DC2800; videograbadores; medidores de flujo; equipos de combustión y control de flama; amplificadores de flama) y la contratación de servicios técnicos (instalación, reparación, configuración, calibración con trazabilidad EMA).
                    </li>
                    <li class="mb-3">
                        <strong>REGISTRO Y USUARIOS</strong>
                        <ul>
                            <li>Persona física o moral del sector industrial (petrolero, químico, minero, metalmecánico).</li>
                            <li>Para comprar o cotizar, debe registrarse proporcionando datos básicos: nombre, empresa, teléfono, correo, dirección de envío.</li>
                        </ul>
                    </li>
                    <li class="mb-3">
                        <strong>PRECIO Y DISPONIBILIDAD</strong>
                        <ul>
                            <li>Los precios incluyen IVA y se expresan en pesos mexicanos (MN).</li>
                            <li>Sujetos a disponibilidad y hasta agotar existencias.</li>
                            <li>Las imágenes y especificaciones son ilustrativas y pueden cambiar sin previo aviso.</li>
                        </ul>
                    </li>
                    <li class="mb-3">
                        <strong>FORMAS DE PAGO</strong>
                        <ul>
                            <li>Tarjetas: PayPal, Stripe (incluye meses sin intereses sujetas a aprobación).</li>
                            <li>Transferencia electrónica.</li>
                            <li>Pago vía WhatsApp con cotización formal.</li>
                            <li>Depósito bancario (se enviarán datos al correo tras confirmar pedido).</li>
                        </ul>
                    </li>
                    <li class="mb-3">
                        <strong>ENVÍO</strong>
                        <ul>
                            <li>Envío gratis en compras mayores a $3,000 MN.</li>
                            <li>Paqueterías: Estafeta, FedEx, DHL y locales.</li>
                            <li>Tiempos y costos se muestran al generar el pedido.</li>
                            <li>Entrega en tienda disponible; consulte requisitos de identificación.</li>
                        </ul>
                    </li>
                    {{-- <li class="mb-3">
                        <strong>SEGURO DE ENVÍO</strong><br>
                        Opcional al 2% del valor de compra. Cubre robo y averío. Reclamo en 48 h al correo <a href="mailto:logistica@macdelnorte.mx">logistica@macdelnorte.mx</a> con fotos y reporte de paquetería.
                    </li> --}}
                    <li class="mb-3">
                        <strong>CANCELACIONES Y MODIFICACIONES</strong>
                        <ul>
                            <li>Cancelación gratuita antes de envío.</li>
                            <li>Máximo 3 modificaciones en 12 h tras pago; posteriormente cancelar y generar nuevo pedido.</li>
                        </ul>
                    </li>
                    <li class="mb-3">
                        <strong>DEVOLUCIONES Y GARANTÍA</strong>
                        <ul>
                            <li>Devolución en 24 h de recibido, sin abrir y en condiciones originales.</li>
                            <li>Productos a pedido, consumibles y software no retornables.</li>
                            <li>Garantía de fabricación según proveedor; proceso sujeto a envío de RMA y empaque conforme instrucciones.</li>
                        </ul>
                    </li>
                    <li class="mb-3">
                        <strong>RESPONSABILIDAD</strong>
                        <ul>
                            <li>No somos responsables por uso incorrecto o condiciones distintas a las recomendadas.</li>
                            <li>No cubrimos fallas por daños físicos o instalación ajena.</li>
                            <li>No nos responsabilizamos por retrasos o daños por paquetería.</li>
                        </ul>
                    </li>
                    <li class="mb-3">
                        <strong>PROPIEDAD INTELECTUAL</strong><br>
                        Contenido, marcas y software del Sitio son de MAC DEL NORTE y Honeywell (distribuidor oficial Silver). Queda prohibido su uso no autorizado.
                    </li>
                    <li class="mb-3">
                        <strong>SEGURIDAD Y USO ACEPTABLE</strong><br>
                        Prohibido interferir con el Sitio o bases de datos. Violaciones de seguridad pueden generar acciones legales e indemnizaciones.
                    </li>
                    <li class="mb-3">
                        <strong>SITIOS Y BIENES DE TERCEROS</strong><br>
                        Los enlaces a terceros son responsabilidad de su proveedor. MAC DEL NORTE no avala ni garantiza productos o servicios externos.
                    </li>
                    <li class="mb-3">
                        <strong>JURISDICCIÓN</strong><br>
                        Estos términos se rigen por las leyes mexicanas. Para controversias, se someten a tribunales de Nuevo León, renunciando a otro fuero.
                    </li>
                </ol>
                <hr>
                <div class="text-center">
                    <strong>Contacto:</strong> <a href="mailto:contacto@macdelnorte.com">contacto@macdelnorte.com</a> | 81-2473-8768
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
