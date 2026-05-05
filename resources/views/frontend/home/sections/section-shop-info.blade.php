<style>
    .shop-info-row {
        display: flex;
        align-items: stretch;
    }
    .shop-info-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        padding: 24px 16px;
        gap: 10px;
    }
    .shop-info-item img {
        width: 64px;
        height: 64px;
        object-fit: contain;
        flex-shrink: 0;
    }
    .shop-info-item .shop-info-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .shop-info-item .shop-info-icon i {
        font-size: 42px;
        color: var(--azul-principal, #003E7E);
    }
    .shop-info-item h4 {
        font-size: 15px;
        font-weight: 700;
        color: var(--negro-texto, #1A202C);
        margin: 0;
        line-height: 1.3;
    }
    .shop-info-item p {
        font-size: 13px;
        color: var(--gris-texto, #4A5568);
        line-height: 1.55;
        margin: 0;
    }
</style>

<div class="container">
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="shopping-info-home">
                <div class="row shop-info-row">

                    <div class="col-3 shop-info-item">
                        <img src="{{ asset('animations-icons/payment-protected-home.gif') }}" alt="Compra Protegida">
                        <h4>Compra protegida</h4>
                        <p>Contamos con seguridad SSL para una transacción 100% segura.</p>
                    </div>

                    <div class="col-3 shop-info-item">
                        <img src="{{ asset('frontend/images/iconos/box-free-home.webp') }}" alt="Envío gratis">
                        <h4>Envío gratis</h4>
                        <p>Envío sin costo en pedidos mayores a $3,000 MXN.</p>
                    </div>

                    <div class="col-3 shop-info-item">
                        <img src="{{ asset('frontend/images/iconos/how-to-pay-home.webp') }}" alt="Formas de Pago">
                        <h4>Elige cómo pagar</h4>
                        <p>Tarjeta de débito, crédito, PayPal, transferencia bancaria y más.</p>
                    </div>

                    <div class="col-3 shop-info-item">
                        <div class="shop-info-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h4>Factura con tu RFC</h4>
                        <p>Emitimos tu CFDI de forma inmediata con los datos fiscales que nos indiques.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
