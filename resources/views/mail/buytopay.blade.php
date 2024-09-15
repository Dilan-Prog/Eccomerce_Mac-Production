<body style="font-family: Arial, sans-serif; margin: 0; padding: 0;">

    <header style="background-color: #00468c; padding: 20px; text-align: center; max-width: 600px; margin: 0 auto;">
        <a href="https://www.macdelnorte.com/" style="font-size: 20px; color: white; text-decoration: none; display: block; margin-bottom: 10px;">Mac Del Norte</a>
        <div class="row" style="text-align: center;">
            <div style="display: inline-block; margin: 0 5px;">
                <a href="https://www.macdelnorte.com/" style="text-decoration: none; color: white;">Inicio</a>
            </div>
            <div style="display: inline-block; margin: 0 5px;">
                <a href="https://www.macdelnorte.com/products" style="text-decoration: none; color: white;">Productos</a>
            </div>
            <div style="display: inline-block; margin: 0 5px;">
                <a href="https://www.macdelnorte.com/contact" style="text-decoration: none; color: white;">Contacto</a>
            </div>
            <div style="display: inline-block; margin: 0 5px;">
                <a href="https://www.macdelnorte.com/about" style="text-decoration: none; color: white;">Nosotros</a>
            </div>
        </div>
        
        
    </header>
    

    @php
        $transfer = \App\Models\Transfer::first();
    @endphp

    @switch($order->payment_method)
        @case('transfer')
            <section style="border: 1px solid #ccc; padding: 20px; text-align: center; margin: 20px auto; max-width: 600px;">
                <div class="order-info" style="margin-bottom: 20px;">
                    <h1>Pedido Pendiente De Validación De Pago</h1>
                    <p>Número de Orden (Referencia Bancaria): <br><span id="order-id">{{ $order->invocie_id }}</span></p>
                    <small>(Si tu pago es por transferencia interbancaria, poner en Referencia tu número de orden.)</small>
                </div>

                <div class="order-secondary" style="background-color: #00468c; color: white; padding: 25px; margin-bottom: 20px;">
                    <h1>Total De Compra</h1>
                    <h1>${{ formatCurrency($order->amount) }}MXN</h1>
                    <h1 style="font-size: 20px; color: white; ">Pago Por Transferencia Bancaria</h1>
                    <h1 style="font-size: 20px;  color: white;">(Validando Su Pago Una Vez Confirmado Nos Comunicaremos Con Usted Para Enviarle Su Número De Guía)</h1>
                    <p style="color: white;" >Gracias por comprar en Mac del Norte. Nuestros Clientes Son La Parte Más Valiosa De Nuestra Empresa. Agradecemos Tu Compra.</p>
                </div>

                <div class="bank-info" style="text-align: center; margin-bottom: 20px;">
                    <h2>Cuenta a Pagar</h2>
                    <h2>Entidad Bancaria</h2>
                    <p>{{ $transfer->nameBank }}</p>

                    <h2>Titular</h2>
                    {{-- Poner el nombre de la razón social de Mac --}}
                    <p>{{ $transfer->nameTitular }}</p>

                    <h2>Número de Cuenta</h2>
                    <p>{{ $transfer->accountNumber }}</p>

                    <h2>Clabe Interbancaria</h2>
                    <p>{{ $transfer->accountClabe }}</p>
                </div>
            </section>
            @break

        @case('stripe')
            <section style="border: 1px solid #ccc; padding: 20px; text-align: center; margin: 20px auto; max-width: 600px;">
                <div class="order-info" style="margin-bottom: 20px;">
                    <h1>Pedido Pendiente De Validación De Pago</h1>
                    <p>Número de Orden: <br><span id="order-id">{{ $order->invocie_id }}</span></p>
                </div>

                <div class="order-secondary" style="background-color: #00468c; color: white; padding: 25px; margin-bottom: 20px;">
                    <h1>Total De Compra</h1>
                    <h1>${{ formatCurrency($order->amount) }}MXN</h1>
                    <h1 style="font-size: 20px;">Pago Por Tarjeta de Débito/Crédito</h1>
                    <h1 style="font-size: 20px;">Estado: Pendiente por Confirmar</h1>
                    <p>Gracias por comprar en Mac del Norte. Nuestros Clientes Son La Parte Más Valiosa De Nuestra Empresa. Agradecemos Tu Compra.</p>
                </div>
            </section>
            @break

        @case('paypal')
            <section style="border: 1px solid #ccc; padding: 20px; text-align: center; margin: 20px auto; max-width: 600px;">
                <div class="order-info" style="margin-bottom: 20px;">
                    <h1>Pedido Pendiente De Validación De Pago</h1>
                    <p>Número de Orden: <br><span id="order-id">{{ $order->invocie_id }}</span></p>
                </div>

                <div class="order-secondary" style="background-color: #00468c; color: white; padding: 25px; margin-bottom: 20px;">
                    <h1>Total De Compra</h1>
                    <h1>${{ formatCurrency($order->amount) }}MXN</h1>
                    <h1 style="font-size: 20px;">Pago Por Paypal</h1>
                    <h1 style="font-size: 20px;">Estado: Pendiente por Confirmar</h1>
                    <p>Gracias por comprar en Mac del Norte. Nuestros Clientes Son La Parte Más Valiosa De Nuestra Empresa. Agradecemos Tu Compra.</p>
                </div>
            </section>
            @break

        @default
    @endswitch
    <footer style="background-color: #00468c; color: white; text-align: center; padding: 10px; margin: 20px auto; max-width: 600px;">
        <p>Todos los derechos reservados &copy; 2022 - 2024</p>
    </footer>

</body>
