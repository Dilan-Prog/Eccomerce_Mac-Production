{{--
    Plantilla de oferta personalizada por email (Parte 3b del plan de
    automatización n8n/email marketing). Renderizada por
    MarketingOfferBuilder + MarketingDataController::email() y devuelta como
    HTML puro para que n8n la mande vía la API transaccional de Brevo — por
    eso es 100% <table> con estilos en línea (sin CSS externo ni flex/grid,
    los clientes de correo no lo soportan), igual que resources/views/mail/buytopay.blade.php.
    Reutiliza el azul de marca (#00468c) para verse consistente con los
    demás correos del sitio.

    Variables esperadas: $user (App\Models\User), $category (?App\Models\Category),
    $products (Collection<App\Models\Product>), $coupon (?App\Models\Coupon).
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Oferta especial — Mac Del Norte</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;">
        <tr>
            <td align="center" style="padding:20px 0;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; max-width:600px; width:100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#00468c; padding:20px; text-align:center;">
                            <a href="https://www.macdelnorte.com/" style="font-size:20px; color:#ffffff; text-decoration:none;">Mac Del Norte</a>
                        </td>
                    </tr>

                    {{-- Saludo --}}
                    <tr>
                        <td style="padding:24px 20px 8px;">
                            <h1 style="margin:0 0 8px; font-size:20px; color:#222222;">
                                Hola{{ $user->name ? ' ' . $user->name : '' }},
                            </h1>
                            @if ($category)
                                <p style="margin:0; font-size:14px; color:#555555; line-height:1.5;">
                                    Preparamos algunas recomendaciones en <strong>{{ $category->name }}</strong> pensando en tus compras anteriores.
                                </p>
                            @else
                                <p style="margin:0; font-size:14px; color:#555555; line-height:1.5;">
                                    Estos son algunos de los productos favoritos de nuestros clientes — pensamos que te podrían interesar.
                                </p>
                            @endif
                        </td>
                    </tr>

                    {{-- Cupón --}}
                    @if ($coupon)
                        <tr>
                            <td style="padding:16px 20px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#00468c; border-radius:6px;">
                                    <tr>
                                        <td style="padding:16px; text-align:center; color:#ffffff;">
                                            <p style="margin:0 0 6px; font-size:14px;">Usa el código</p>
                                            <p style="margin:0 0 6px; font-size:24px; font-weight:bold; letter-spacing:1px;">{{ $coupon->cod }}</p>
                                            <p style="margin:0; font-size:14px;">
                                                @if ($coupon->discount_type === 'percent')
                                                    {{ (int) $coupon->discount }}% de descuento en esta categoría
                                                @else
                                                    ${{ formatCurrency($coupon->discount) }} MXN de descuento en esta categoría
                                                @endif
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    {{-- Productos --}}
                    @foreach ($products as $product)
                        <tr>
                            <td style="padding:0 20px 16px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:6px;">
                                    <tr>
                                        <td width="110" style="padding:12px; vertical-align:top;">
                                            @if ($product->thumb_image)
                                                <img src="{{ asset($product->thumb_image) }}" alt="{{ $product->name }}" width="90" style="display:block; width:90px; height:auto; border:0;">
                                            @endif
                                        </td>
                                        <td style="padding:12px 12px 12px 0; vertical-align:top;">
                                            <p style="margin:0 0 6px; font-size:15px; color:#222222; font-weight:bold;">{{ $product->name }}</p>
                                            <p style="margin:0 0 10px; font-size:16px; color:#00468c; font-weight:bold;">${{ formatCurrency($product->effectivePrice()) }} MXN</p>
                                            <a href="{{ route('product-detail', $product->slug) }}" style="display:inline-block; padding:8px 14px; background-color:#00468c; color:#ffffff; text-decoration:none; font-size:13px; border-radius:4px;">Ver producto</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endforeach

                    @if ($products->isEmpty())
                        <tr>
                            <td style="padding:0 20px 16px;">
                                <p style="margin:0; font-size:14px; color:#777777;">Pronto tendremos nuevas recomendaciones para ti.</p>
                            </td>
                        </tr>
                    @endif

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#00468c; color:#ffffff; text-align:center; padding:16px;">
                            <p style="margin:0; font-size:12px;">Todos los derechos reservados &copy; {{ date('Y') }} Mac Del Norte</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
