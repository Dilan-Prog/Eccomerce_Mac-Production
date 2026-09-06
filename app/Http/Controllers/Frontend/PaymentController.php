<?php

namespace App\Http\Controllers\Frontend;

use GuzzleHttp\Client;
use App\Events\PaymentProcessed;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\PaypalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\StripeSetting;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Notifications\BuytoPay;
use App\Notifications\buytopayAdmin;
use App\Support\CartPricing;
use Illuminate\Support\Facades\Notification;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PaymentController extends Controller
{
    /**
     * El pago es el ÚLTIMO paso del checkout: exige que los anteriores estén
     * realmente completos antes de dejar cobrar.
     *
     * El orden de pasos que se ve en pantalla (dirección -> método de envío ->
     * método de pago) lo controlaba SOLO el navegador: las secciones se
     * bloquean con CSS (pointer-events + max-height, ver checkout.blade.php) y
     * el botón valida con jQuery. Eso ayuda al cliente honesto, pero no impide
     * llegar directo a /payment, /paypal/payment o POST /payment/transfer y
     * generar una orden sin dirección o sin método de envío — se guardaba con
     * order_address / shipping_method en null. Aquí es donde de verdad se
     * cierra el paso.
     *
     * También cubre el caso normal de sesión expirada o de la pestaña vieja
     * que quedó abierta después de completar otra compra (clearSession() borra
     * address y shipping_method al cerrar la orden).
     *
     * Devuelve null si todo está en orden, o el redirect al paso que falta.
     */
    private function ensureCheckoutCompleted()
    {
        if (\Cart::content()->isEmpty()) {
            toastr('Tu carrito está vacío. Agrega productos antes de continuar.', 'error', 'Carrito vacío');
            return redirect()->route('cart-details');
        }

        if (!Session::has('address')) {
            toastr('Elige una dirección de envío antes de pagar.', 'error', 'Falta la dirección');
            return redirect()->route('user.checkout');
        }

        if (!Session::has('shipping_method')) {
            toastr('Elige un método de envío antes de pagar.', 'error', 'Falta el método de envío');
            return redirect()->route('user.checkout');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->ensureCheckoutCompleted()) {
            return $redirect;
        }

        $paypalInfo = PaypalSetting::first();
        $userInfo = User::first();
        $transferInfo = Transfer::first();

        $paypalClientId = $paypalInfo->activeClientId();
        return view('frontend.pages.payment', compact('paypalInfo', 'userInfo', 'transferInfo', 'paypalClientId'));
    }

    public function paymentTransferSuccess(Request $request)
    {

        if (!$request->hasValidSignature()) {
            // Si la firma no es válida, redirigir al index con un mensaje de error
            return redirect()->route('index')->with('error', 'La URL de pago ha caducado.');
        }

        $order = Order::where('id', $request->query('order'))
            ->where('user_id', Auth::id())
            ->where('payment_method', 'transfer')
            ->firstOrFail();

        return view('frontend.pages.payment-transfer-success', compact('order'));
    }


    public function paymentSuccess(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return Redirect::route('index')->with('error', 'La URL de pago Exitoso ah caducado.');
        }

        $order = Order::where('id', $request->query('order'))
            ->where('user_id', Auth::id())
            ->firstOrFail();

        abort_unless(in_array($order->payment_method, ['paypal', 'stripe']), 404);

        return view('frontend.pages.payment-success', compact('order'));
    }


    public function storeOrder($refBank, $paymentMethod, $paymentStatus, $transactionId, $paidAmount, $paidCurrencyName)
    {
        return DB::transaction(function () use ($refBank, $paymentMethod, $paymentStatus, $transactionId, $paidAmount, $paidCurrencyName) {
            $setting = GeneralSetting::first();
            $order = new Order();


            if ($paymentMethod === 'transfer') {
                $order->invocie_id = $refBank;
                // Si es transferencia, usar el valor de refBank como invocie_id
                $order->amount = getFinalPayableAmount();
            } else {
                // random_int + verificación de unicidad: rand() por sí solo puede
                // repetir el mismo invocie_id entre dos órdenes distintas.
                do {
                    $invoiceId = random_int(100000, 999999);
                } while (Order::where('invocie_id', $invoiceId)->exists());
                $order->invocie_id = $invoiceId;
                $order->amount = getFinalPayableAmount();
            }


            $order->user_id = Auth::user()->id;
            $order->sub_total = getCartTotal();

            $order->currency_name = $setting->currency_name;
            $order->currency_icon = $setting->currency_icon;
            $order->product_qty = \Cart::content()->count();
            $order->payment_method = $paymentMethod;
            $order->payment_status =  $paymentStatus;
            $order->order_address = json_encode(Session::get('address'));
            $order->shipping_method = json_encode(Session::get('shipping_method'));
            $order->coupon = json_encode(Session::get('coupon'));
            $order->coupon_code = Session::get('coupon.coupon_code');
            $order->order_status = 'pending';
            $order->save();

            // store order products — precio y stock se resuelven EN VIVO vía
            // CartPricing (antes: Product::where('id', $item->id) truena con
            // un id tipo "comb_45" de un producto con variante, y el precio
            // usado era el valor cacheado en el carrito al momento de
            // agregarlo, no el actual).
            //
            // Si el stock no alcanza para surtir algún renglón completo, el
            // pedido NO se rechaza — el pago ya se cobró en los 4 métodos de
            // pago (paypalSuccess/captureOrder/payWithStripe/payWithTransfer
            // llaman a storeOrder() después de capturar el cobro) — en vez de
            // eso se registra igual y se marca "pendiente_de_surtir" al final.
            $pedidoIncompleto = false;

            foreach (\Cart::content() as $item) {
                $resolved = CartPricing::resolve($item, lockForUpdate: true);
                $product = $resolved['product'];
                $combination = $resolved['combination'];

                if (!$product) {
                    // Producto/combinación borrado entre que se agregó al
                    // carrito y el checkout — no hay nada que descontar ni
                    // registrar correctamente para este renglón.
                    $pedidoIncompleto = true;
                    continue;
                }

                $unitPrice = $resolved['price'];
                $availableQty = $resolved['stock'];

                $orderProduct = new OrderProduct();
                $orderProduct->order_id = $order->id;
                $orderProduct->product_id = $product->id;
                $orderProduct->product_name = $product->name;
                $orderProduct->sku = $combination->sku ?? $product->sku;
                $orderProduct->productModel = $product->productModel;
                $orderProduct->unit_price = $unitPrice;
                $orderProduct->qty = $item->qty;
                $orderProduct->save();

                if ($availableQty < $item->qty) {
                    $pedidoIncompleto = true;
                }

                // Descuenta el campo correcto sin bajar de 0 — el faltante
                // (si lo hay) queda reflejado solo en order_status, no en una
                // columna de stock negativa.
                $newQty = max(0, $availableQty - $item->qty);

                if ($combination) {
                    $combination->qty = $newQty;
                    $combination->save();
                } elseif ($product->qty_personalizated == 0) {
                    $product->qty_aspel = $newQty;
                    $product->save();
                } else {
                    $product->qty = $newQty;
                    $product->save();
                }
            }

            if ($pedidoIncompleto) {
                $order->order_status = 'pendiente_de_surtir';
                $order->save();
            }

            // store transaction details
            $transaction = new Transaction();
            $transaction->order_id = $order->id;
            $transaction->transaction_id = $transactionId;
            $transaction->payment_method = $paymentMethod;
            $transaction->amount = getFinalPayableAmount();
            $transaction->amount_real_currency = $paidAmount;
            $transaction->amount_real_name = $paidCurrencyName;
            $transaction->save();

            return $order;
        });
    }

    public function clearSession()
    {
        \Cart::destroy();
        Session::forget('address');
        Session::forget('shipping_method');
        Session::forget('coupon');
        // Sin esto, el importe de la orden ya cobrada quedaria en sesion y la
        // comprobacion de captureOrder() lo compararia contra la compra
        // siguiente.
        Session::forget('paypal_order');
    }

    public function paypalConfig()
    {

        $paypalSetting = PaypalSetting::first();

        $config = [
            'mode'    => $paypalSetting->mode == 1 ? 'live' : 'sandbox', // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
            'sandbox' => [
                'client_id'         => $paypalSetting->activeClientId(),
                'client_secret'     => $paypalSetting->activeSecretKey(),
                'app_id'            => 'APP-80W284485P519543T',
            ],
            'live' => [
                'client_id'         => $paypalSetting->activeClientId(),
                'client_secret'     => $paypalSetting->activeSecretKey(),
                'app_id'            => '',
            ],

            'payment_action' => 'Sale',
            'currency'       => $paypalSetting->currency_name,
            'notify_url'     => '',
            'locale'         => 'en_US',
            'validate_ssl'   => true,
        ];

        return $config;
    }

    /**Paypal redirect */

    public function paywithPaypal()
    {
        if ($redirect = $this->ensureCheckoutCompleted()) {
            return $redirect;
        }

        $config = $this->paypalConfig();

        $provider = new PayPalClient($config);
        $provider->getAccessToken();
        // $provider->setApiCredentials($config);
        $payableAmount = getFinalPayableAmount();
        /**Get Final Ammount */

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('user.paypal.success'),
                "cancel_url" => route('user.paypal.cancel'),
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $config['currency'],
                        "value" => $payableAmount
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {

            foreach ($response['links'] as $link) {

                if ($link['rel'] === 'approve') {
                    return redirect()->away($link['href']);
                }
            }
        } else {
            return redirect()->route('user.paypal.cancel');
        }
    }

    public function paypalSuccess(Request $request)
    {
        // El candado va antes de capturePaymentOrder(): si aqui falta la
        // direccion o el metodo de envio, es mejor no cobrar que cobrar y
        // guardar una orden que no se puede surtir.
        if ($redirect = $this->ensureCheckoutCompleted()) {
            return $redirect;
        }

        $config = $this->paypalConfig();
        $provider = new PayPalClient($config);
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request->token);
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $paypalSetting = PaypalSetting::first();
            $paidAmount = getFinalPayableAmount();
            /**Get Final Ammount */
            $order = $this->storeOrder(null, 'paypal', 1, $response['id'], $paidAmount, $paypalSetting->currency_name);

            // clear session
            $this->clearSession();

            $signedUrl = URL::temporarySignedRoute(
                'user.payment.success',
                now()->addSeconds(30),
                ['order' => $order->id]
            );
            try {
                $this->notifyPaymentProcessed($order);
            } catch (\Exception $e) {
                \Log::error('Error al enviar la notificación al pagar por paypal: ' . $e->getMessage());
            }

            return redirect()->to($signedUrl);
        }

        return redirect()->route('user.paypal.cancel');
    }

    public function paypalCancel()
    {
        toastr('Algo Salio Mal en el Pago, Prueba con otro metodo o intentalo mas tarde', 'error', 'Error');
        return redirect()->route('user.payment');
    }

    //Nuevos End points para Paypal (Agregacion de botones de pago)
    public function createOrder(Request $request)
    {
        if ($this->ensureCheckoutCompleted()) {
            return response()->json([
                'error' => 'Tu sesión de compra expiró o falta un paso. Vuelve al checkout.',
                'redirect_url' => route('user.checkout'),
            ], 422);
        }

        Log::info('Creando orden PayPal con total: ' . getFinalPayableAmount());
        Log::info('Datos de sesión (createOrder): ', session()->all());

        $config = $this->paypalConfig();
        $provider = new PayPalClient($config);
        $provider->getAccessToken();
        $payableAmount = number_format(getFinalPayableAmount(), 2, '.', '');

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $config['currency'],
                        "value" => $payableAmount
                    ]
                ]
            ],
            "application_context" => [
                "shipping_preference" => "NO_SHIPPING",
                "locale" => "es-MX",
                "payment_method" => [
                    "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED"
                ]
            ]
        ]);
        /** PAYPAL MESES SIN INTERESES DESDE CUENTA DEL USUARIO HACER LA LOGICA PARA AQUELLOS PRODUCTOS QUE
         *  NO CUMPLAN CON MESES SIN INTERESES QUE VALGA 3 MESES Y SI SE VA A DAR MAS MESES SIN INTERESES TENEMOS QUE PLANEARLO */


        if (isset($response['id'])) {
            // Se guarda el importe con el que se creo la orden. El cliente
            // puede volver atras y cambiar el metodo de envio despues de que
            // el formulario de pago ya esta abierto: la orden de PayPal
            // seguiria siendo la vieja y cobraria el total anterior.
            // captureOrder() lo compara antes de cobrar.
            Session::put('paypal_order', [
                'id' => $response['id'],
                'amount' => $payableAmount,
            ]);

            return response()->json(['id' => $response['id']]);
        }

        return response()->json(['error' => 'No se pudo crear la orden'], 500);
    }


    public function captureOrder(Request $request)
    {
        // Antes de capturar, no despues: el JS de PayPal navega a redirect_url,
        // asi que el cliente termina de vuelta en el checkout sin haber pagado.
        if ($this->ensureCheckoutCompleted()) {
            return response()->json(['redirect_url' => route('user.checkout')], 422);
        }

        Log::info('Capturando orden PayPal con ID: ' . $request->orderId);
        Log::info('Datos de sesión (captureOrder): ', session()->all());

        // El total pudo cambiar entre que se creo la orden y este momento (el
        // cliente volvio atras y eligio otro envio). Cobrar la orden vieja
        // significaria cobrarle de menos y registrar el pedido por el importe
        // nuevo, perdiendo la diferencia en silencio.
        //
        // Se comprueba ANTES de capturar: una vez capturado, el dinero ya se
        // movio y arreglarlo exigiria un reembolso.
        $ordenGuardada = Session::get('paypal_order');
        $totalActual = number_format(getFinalPayableAmount(), 2, '.', '');

        if (is_array($ordenGuardada)
            && ($ordenGuardada['id'] ?? null) === $request->orderId
            && ($ordenGuardada['amount'] ?? null) !== $totalActual) {

            Log::critical('PayPal: la orden se creó con un total distinto al actual.', [
                'orderId' => $request->orderId,
                'total_al_crear' => $ordenGuardada['amount'] ?? null,
                'total_actual' => $totalActual,
            ]);

            return response()->json(['rechazo' => [
                'titulo' => 'El total de tu pedido cambió',
                'mensaje' => 'Cambiaste el método de envío después de abrir el formulario de pago, '
                    . 'así que el importe ya no coincide. No se realizó ningún cobro.',
                'sugerencias' => [
                    'Vuelve a abrir el formulario de tarjeta para pagar el total correcto.',
                ],
                'codigo' => 'TOTAL_DESACTUALIZADO',
            ]], 409);
        }
        $config = $this->paypalConfig();
        $provider = new PayPalClient($config);
        $provider->getAccessToken();

        try {
            $response = $provider->capturePaymentOrder($request->orderId);
        } catch (\Throwable $e) {
            \Log::critical('PayPal: excepcion al capturar la orden.', [
                'orderId' => $request->orderId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['rechazo' => $this->motivoRechazo('PAYPAL_ERROR', 'PAYPAL_ERROR')], 502);
        }

        // El estado de la ORDEN no basta. En los pagos con tarjeta avanzada
        // (Expanded Checkout) PayPal responde con la orden en COMPLETED aunque
        // el banco haya rechazado el cobro: el rechazo viaja un nivel mas
        // abajo, en purchase_units[].payments.captures[].status. Mirar solo
        // $response['status'] hacia que TODO rechazo del emisor se guardara
        // como una venta pagada.
        $captura = $response['purchase_units'][0]['payments']['captures'][0] ?? null;
        $estadoOrden = $response['status'] ?? null;
        $estadoCaptura = $captura['status'] ?? null;

        // Se registra siempre, no solo al fallar: sin esta linea un rechazo
        // aceptado por error no dejaba ningun rastro que permitiera notarlo.
        \Log::info('PayPal: resultado de la captura.', [
            'orderId' => $request->orderId,
            'order_status' => $estadoOrden,
            'capture_status' => $estadoCaptura,
            'processor_response' => $captura['processor_response'] ?? null,
            'status_details' => $captura['status_details'] ?? null,
        ]);

        if ($estadoOrden === 'COMPLETED' && $estadoCaptura === 'COMPLETED') {
            $paypalSetting = PaypalSetting::first();
            $paidAmount = getFinalPayableAmount();
            $order = $this->storeOrder(null, 'paypal', 1, $response['id'], $paidAmount, $paypalSetting->currency_name);
            $this->clearSession();

            $signedUrl = URL::temporarySignedRoute(
                'user.payment.success',
                now()->addSeconds(30),
                ['order' => $order->id]
            );
            /**AL MOMENTO DE ENVIAR EL CORREO POR LA LAPTOP NO SE ENVIA LA NOTIFICACION YA QUE A AHI UN PROBLEMA CON EL ARCHIVO .env
             * PERO AL MOMENTO DE ENVIAR LA NOTIFIACION POR LA COMPUTADORA DE ESCRITORIO ( CON UN DIFERENTE ARCHIVO .env) SI SE ENVIA
             * POR LO QUE SE TIENE QUE HACER UNA VERIFICACION DE QUE EL ARCHIVO .env SEA EL CORRECTO
             */
            try {
                $this->notifyPaymentProcessed($order);
            } catch (\Exception $e) {
                \Log::error('Error al enviar la notificación al pagar por paypal: ' . $e->getMessage());
            }

            return response()->json(['redirect_url' => $signedUrl]);
        }
        // Cobro no realizado: NO se crea pedido. Se devuelve el motivo para
        // que el checkout lo explique en pantalla, en vez de mandar al cliente
        // a una pagina de cancelacion que no dice nada.
        \Log::critical('PayPal: la captura no se completo.', [
            'orderId' => $request->orderId,
            'order_status' => $estadoOrden,
            'capture_status' => $estadoCaptura,
            'respuesta' => $response,
        ]);

        $codigo = $captura['processor_response']['response_code']
            ?? $captura['status_details']['reason']
            ?? $estadoCaptura;

        return response()->json(['rechazo' => $this->motivoRechazo($estadoCaptura, $codigo)], 402);
    }

    /**
     * Traduce el rechazo de PayPal/el banco a algo que el cliente pueda
     * entender y accionar. PayPal devuelve codigos crudos (5100, 5400,
     * INSTRUMENT_DECLINED...) que no le dicen nada a quien esta pagando.
     */
    private function motivoRechazo(?string $estadoCaptura, ?string $codigo): array
    {
        $mapa = [
            // Codigos del procesador (processor_response.response_code).
            '0500' => ['Tu banco no autorizó el cargo', 'El emisor rechazó la operación sin dar un motivo específico. No se te cobró nada.', [
                'Llama al número que aparece al reverso de tu tarjeta y pide que autoricen el cargo.',
                'Intenta con otra tarjeta.',
            ]],
            '5100' => ['Tu banco rechazó la tarjeta', 'El banco emisor no autorizó el cargo y no se realizó ningún cobro. Suele pasar cuando el banco tiene bloqueadas las compras por internet.', [
                'Llama a tu banco y pide que autoricen compras en línea con esta tarjeta.',
                'Intenta con otra tarjeta, de preferencia de otro banco.',
            ]],
            '5110' => ['El código de seguridad no coincide', 'El CVV que capturaste no corresponde a la tarjeta. No se realizó ningún cobro.', [
                'Vuelve a capturar los 3 dígitos del reverso de tu tarjeta (4 al frente en American Express).',
            ]],
            '5120' => ['Fondos insuficientes', 'La tarjeta no tiene saldo o línea de crédito disponible para este monto.', [
                'Verifica tu saldo o línea disponible.',
                'Intenta con otra tarjeta.',
            ]],
            '5140' => ['La tarjeta está cancelada', 'El banco reporta esta tarjeta como cerrada.', [
                'Usa una tarjeta vigente.',
            ]],
            '5180' => ['Tarjeta restringida', 'El banco tiene una restricción activa sobre esta tarjeta.', [
                'Comunícate con tu banco para levantar la restricción.',
                'Intenta con otra tarjeta.',
            ]],
            '5400' => ['La tarjeta está vencida', 'La fecha de vencimiento ya pasó o quedó mal capturada.', [
                'Revisa la fecha de vencimiento.',
                'Usa una tarjeta vigente.',
            ]],
            '5650' => ['Tu banco pide verificación adicional', 'La operación requiere que confirmes tu identidad con el banco.', [
                'Vuelve a intentarlo y completa la verificación que te pida tu banco.',
            ]],
            '5700' => ['Operación no permitida', 'El banco no permite este tipo de compra con esta tarjeta.', [
                'Usa una tarjeta habilitada para compras en línea.',
            ]],
            '5930' => ['La tarjeta no está activada', 'El banco reporta que la tarjeta aún no ha sido activada.', [
                'Actívala con tu banco y vuelve a intentarlo.',
            ]],
            '9500' => ['Operación rechazada por seguridad', 'El banco marcó la operación como sospechosa. No se realizó ningún cobro.', [
                'Comunícate con tu banco para autorizarla.',
            ]],
            '9520' => ['Tarjeta reportada', 'El banco reporta esta tarjeta como extraviada o robada.', [
                'Comunícate con tu banco.',
                'Usa otra tarjeta.',
            ]],
            '1330' => ['Los datos de la tarjeta no son válidos', 'El número de tarjeta no corresponde a una cuenta activa.', [
                'Revisa que el número esté completo y sin errores.',
            ]],

            // Motivos que devuelve PayPal en status_details.reason.
            'INSTRUMENT_DECLINED' => ['Tu banco rechazó la tarjeta', 'El emisor no autorizó el cargo. No se realizó ningún cobro.', [
                'Intenta con otra tarjeta.',
                'Comunícate con tu banco.',
            ]],
            'PAYER_ACTION_REQUIRED' => ['Falta confirmar el pago', 'Tu banco pide un paso de verificación que no se completó.', [
                'Vuelve a intentarlo y completa la verificación.',
            ]],

            // Fallo de nuestro lado al hablar con PayPal.
            'PAYPAL_ERROR' => ['No pudimos comunicarnos con PayPal', 'Ocurrió un problema al procesar el pago. No se realizó ningún cobro a tu tarjeta.', [
                'Espera un momento y vuelve a intentarlo.',
                'Si el problema sigue, comunícate con nosotros.',
            ]],
        ];

        if ($codigo !== null && isset($mapa[$codigo])) {
            [$titulo, $mensaje, $sugerencias] = $mapa[$codigo];

            return [
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'sugerencias' => $sugerencias,
                'codigo' => $codigo,
            ];
        }

        return [
            'titulo' => 'No pudimos procesar tu pago',
            'mensaje' => 'El banco no autorizó el cargo. No se realizó ningún cobro a tu tarjeta.',
            'sugerencias' => [
                'Revisa que el número, la fecha de vencimiento y el CVV estén correctos.',
                'Intenta con otra tarjeta.',
                'Comunícate con tu banco para saber por qué se rechazó.',
            ],
            'codigo' => $codigo,
        ];
    }


    /**Payment Stripe */
    public function payWithStripe(Request $request)
    {
        if ($redirect = $this->ensureCheckoutCompleted()) {
            return $redirect;
        }


        // calculate payable amount depending on currency rate
        $stripeSetting = StripeSetting::first();
        if (!$stripeSetting || !$request->filled('stripe_token')) {
            Log::error('Stripe: configuración o token de tarjeta faltante.', [
                'has_stripe_setting' => (bool) $stripeSetting,
                'has_stripe_token' => $request->filled('stripe_token'),
            ]);
            toastr('No se pudo procesar el pago. Intenta de nuevo o usa otro método.', 'error', 'Error');
            return redirect()->route('user.payment');
        }

        // $total = getFinalPayableAmount();
        // $payableAmount = round($total * $stripeSetting->currency_rate, 2);
        $payableAmount = getFinalPayableAmount();
        /**Get Final Ammount */

        try {
            Stripe::setApiKey($stripeSetting->activeSecretKey());
            $response = Charge::create([
                // round() evita errores de punto flotante (ej. 1999.9999999999998) que Stripe rechaza por no ser entero
                "amount" => (int) round($payableAmount * 100),
                "currency" => trim($stripeSetting->currency_name),
                "source" => $request->stripe_token,
                "description" => "Venta Por Web Macdelnorte"
            ]);
        } catch (CardException $e) {
            // Errores de tarjeta (rechazada, fondos insuficientes, etc.) son seguros de mostrar al cliente
            Log::warning('Stripe: tarjeta rechazada - ' . $e->getMessage(), ['payable_amount' => $payableAmount]);
            toastr('No se pudo procesar el pago: ' . $e->getMessage(), 'error', 'Error');
            return redirect()->route('user.payment');
        } catch (ApiErrorException $e) {
            // Errores de configuración/API (llaves, moneda, conexión) no deben exponerse al cliente
            Log::error('Error de Stripe al cobrar: ' . $e->getMessage(), [
                'stripe_error_type' => get_class($e),
                'payable_amount' => $payableAmount,
            ]);
            toastr('No se pudo procesar el pago con tarjeta. Intenta de nuevo o usa otro método.', 'error', 'Error');
            return redirect()->route('user.payment');
        }

        if ($response->status === 'succeeded') {
            $order = $this->storeOrder(null, 'stripe', 1, $response->id, $payableAmount, $stripeSetting->currency_name);
            // clear session
            $this->clearSession();

            $signedUrl = URL::temporarySignedRoute(
                'user.payment.success',
                now()->addSeconds(30),
                ['order' => $order->id]
            );
            try {
                $this->notifyPaymentProcessed($order);
            } catch (\Exception $e) {
                \Log::error('Error al enviar la notificación al pagar por Stripe: ' . $e->getMessage());
            }

            return redirect()->to($signedUrl);
        } else {
            toastr('Someting went wrong try agin later!', 'error', 'Error');
            return redirect()->route('user.payment');
        }
    }


    public function payWithTransfer(Request $request)
    {
        if ($redirect = $this->ensureCheckoutCompleted()) {
            return $redirect;
        }

        $refBank = $request->input('refBank');

        $setting = GeneralSetting::first();

        // Lógica para guardar la orden y otros detalles aquí
        $paymentMethod = 'transfer'; // Método de pago (puedes usar 'transfer' para transferencia bancaria)
        $paymentStatus = 0; // 1 para pago completado
        $transactionId = 'TXN-' . uniqid(); // Generar un ID de transacción único (puedes usar algo como TXN-1234)
        $paidAmount = getFinalPayableAmount(); // Obtener el monto final a pagar desde tu lógica
        $paidCurrencyName = $setting->currency_name; // Nombre de la moneda

        // Guardar la orden
        $order = $this->storeOrder($refBank, $paymentMethod, $paymentStatus, $transactionId, $paidAmount, $paidCurrencyName);

        // Limpiar sesión después de completar la orden
        $this->clearSession();

        $signedUrl = URL::temporarySignedRoute(
            'user.payment-transfer.success',
            now()->addMinutes(1),
            ['order' => $order->id]
        );
        // Disparar la notificación
        try {
            $this->notifyPaymentProcessed($order);
        } catch (\Exception $e) {
            \Log::error('Error al enviar la notificación al pagar por transferencia: ' . $e->getMessage());
        }


        // Redirigir a la página de éxito de pago o a donde sea necesario
        return redirect()->to($signedUrl);
    }

    // Método para enviar la notificación de pago procesado
    protected function notifyPaymentProcessed($order)
    {
        // Obtener el usuario actual o el notificable apropiado
        $user = auth()->user(); // Ajusta esto según cómo obtienes al usuario

        // Enviar la notificación
        Notification::send($user, new BuytoPay($order));
        Notification::route('mail', 'dilanp270105@gmail.com') //cambiar a ventas1@macdelnorte.com
            ->notify(new buytopayAdmin($order));
    }
}
