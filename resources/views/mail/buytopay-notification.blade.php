<body style="font-family: Arial, sans-serif; margin: 0; padding: 0;">

    <header style="background-color: #00468c; padding: 20px; text-align: center; max-width: 600px; margin: 0 auto;">
        <a href="https://www.macdelnorte.com/" style="font-size: 20px; color: white; text-decoration: none; display: block; margin-bottom: 10px;">Mac Del Norte</a> 
    </header>
    @php
        $transfer = \App\Models\Transfer::first();
    @endphp
            <section style="border: 1px solid #ccc; padding: 20px; text-align: center; margin: 20px auto; max-width: 600px;">
                <div class="order-info" style="margin-bottom: 20px;">
                    <h1>Nuevo Pedido Recibido</h1>
                    <p>Número de Orden<br><span id="order-id">{{ $order->invocie_id }}</span></p>
                </div>

                <div class="order-secondary" style="background-color: #00468c; color: white; padding: 25px; margin-bottom: 20px;">
                    <h1>Total De Compra</h1>
                    <h1>${{ formatCurrency($order->amount) }}MXN</h1>
                    <h1 style="font-size: 20px; color: white; ">Metodo De Pago</h1>
                    
                   
                    @switch($order->payment_method)
                         @case('transfer')
                        <h1 style="font-size: 20px;  color: white;"> Transferencia</h1>
                    @break
                        @case('stripe')
                        <h1 style="font-size: 20px;  color: white;">Tarjeta De Debito/Credito</h1>
                    @break
                        @case('paypal')
                        <h1 style="font-size: 20px;  color: white;">Paypal</h1>
                    @break
                    @endswitch
                       
                        
                        {{-- <p style="color: white;">Nombre: {{ $order->user_id->name}}</p> --}}
                        {{-- <p style="color: white;">Correo: {{ $order_address->email }}</p> --}}
                    <p style="color: white;" >Monto:{{formatCurrency($order->amount)}}</p>
                    {{-- <p style="color: white;" >Metodo De Envio:{{$order->shipping_method->name}}</p> --}}
                    <p style="color: white;">Fecha: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</p>
                </div>
            </section>
    <footer style="background-color: #00468c; color: white; text-align: center; padding: 10px; margin: 20px auto; max-width: 600px;">
        <p>Todos los derechos reservados &copy; 2022 - 2024</p>
    </footer>

</body>
