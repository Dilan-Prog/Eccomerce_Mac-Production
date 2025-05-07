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
use Illuminate\Support\Facades\Notification;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Charge;
use Stripe\Stripe;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;


class PaymentController extends Controller
{
    public function index(){
        $paypalInfo = PaypalSetting::first();
        $userInfo = User::first();
        $transferInfo = Transfer::first();

        if(!Session::has('address')){
            return redirect()->route('user.checkout');
        }

        $paypalClientId = $paypalInfo->mode == 1 ? $paypalInfo->client_id : $paypalInfo->client_id;
        return view('frontend.pages.payment', compact('paypalInfo', 'userInfo', 'transferInfo', 'paypalClientId'));
    }

    public function paymentTransferSuccess(Request $request){

        if (!$request->hasValidSignature()) {
            // Si la firma no es válida, redirigir al index con un mensaje de error
            return redirect()->route('index')->with('error', 'La URL de pago ha caducado.');
        }

        $order = Order::where('payment_method', 'transfer')->latest()->first();

        return view('frontend.pages.payment-transfer-success', compact('order'));
    }


    public function paymentSuccess(Request $request){
        if (!$request->hasValidSignature()) {
            return Redirect::route('index')->with('error', 'La URL de pago Exitoso ah caducado.');
        }

        $order = Order::whereIn('payment_method', ['paypal', 'stripe'])
                   ->latest()
                   ->first();

        $view = '';
        switch ($order->payment_method) {
        case 'paypal':
            $view = 'frontend.pages.payment-success';
            break;
        case 'stripe':
            $view = 'frontend.pages.payment-success';
            break;
        default:
            abort(404); // Manejar cualquier otro método de pago no esperado
    }
        return view($view, compact('order'));

    }


    public function storeOrder($refBank,$paymentMethod, $paymentStatus, $transactionId, $paidAmount, $paidCurrencyName)
    {
        $setting = GeneralSetting::first();
        $order = new Order();


        if ($paymentMethod === 'transfer') {
            $order->invocie_id = $refBank; // Si es transferencia, usar el valor de refBank como invocie_id

            // Aplicar el descuento si es pago por transferencia
            $discount = 0.02; // por ejemplo, 10% de descuento
            $finalPayableAmount = getFinalPayableAmount() * (1 - $discount);
            $order->amount = $finalPayableAmount;
        } else {
            $order->invocie_id = rand(1, 999999); // Generar aleatoriamente si no es transferencia
            $order->amount = getFinalPayableAmount();
        }


        $order->user_id = Auth::user()->id;
        $order->sub_total = getCartTotal();

        $order->currency_name = $setting->currency_name;
        $order->currency_icon = $setting->currency_icon;
        $order->product_qty =\Cart::content()->count();
        $order->payment_method = $paymentMethod;
        $order->payment_status =  $paymentStatus;
        $order->order_address = json_encode(Session::get('address'));
        $order->shipping_method = json_encode(Session::get('shipping_method'));
        $order->coupon = json_encode(Session::get('coupon'));
        $order->order_status = 'pending';
        $order->save();

        $order = Order::find($order->id);
         // store order products
         foreach(\Cart::content() as $item){
            $product = Product::find($item->id);
            $orderProduct = new OrderProduct();
            $orderProduct->order_id = $order->id;
            $orderProduct->product_id = $product->id;
            // $orderProduct->vendor_id = $product->vendor_id;
            $orderProduct->product_name = $product->name;
            $orderProduct->sku = $product->sku;
            $orderProduct->productModel = $product->productModel;
            $orderProduct->unit_price = $item->price;
            $orderProduct->qty = $item->qty;
            $orderProduct->save();

            // update product quantity
            $updatedQty = ($product->qty - $item->qty);
            $product->qty = $updatedQty;
            $product->save();
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



    }

    public function clearSession()
    {
        \Cart::destroy();
        Session::forget('address');
        Session::forget('shipping_method');
        Session::forget('coupon');
    }

    public function paypalConfig(){

        $paypalSetting = PaypalSetting::first();

        $config = [
            'mode'    => $paypalSetting->mode == 1 ? 'live' : 'sandbox', // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
            'sandbox' => [
                'client_id'         => $paypalSetting->client_id,
                'client_secret'     => $paypalSetting->secret_key,
                'app_id'            => 'APP-80W284485P519543T',
            ],
            'live' => [
                'client_id'         => $paypalSetting->client_id,
                'client_secret'     => $paypalSetting->secret_key,
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

    public function paywithPaypal(){
        $config = $this->paypalConfig();

        $provider = new PayPalClient($config);
        $provider->getAccessToken();
        // $provider->setApiCredentials($config);
        $payableAmount = getFinalPayableAmount();/**Get Final Ammount */

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

        if(isset($response['id']) && $response['id'] != null){

            foreach($response['links'] as $link){

                if($link['rel'] === 'approve'){
                    return redirect()->away($link['href']);
                }
            }
        } else {
            return redirect()->route('user.paypal.cancel');
        }



    }

    public function paypalSuccess(Request $request)
    {
        $config = $this->paypalConfig();
        $provider = new PayPalClient($config);
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request->token);
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $paypalSetting = PaypalSetting::first();
            $paidAmount = getFinalPayableAmount();/**Get Final Ammount */
            $this->storeOrder(null,'paypal', 1, $response['id'], $paidAmount, $paypalSetting->currency_name);

            // clear session
            $this->clearSession();

            $order = Order::latest()->first();
            $signedUrl = URL::temporarySignedRoute(
                'user.payment.success', now()->addSeconds(30)
            );
            $this->notifyPaymentProcessed($order);

            return redirect()->to($signedUrl);
        }

        return redirect()->route('user.paypal.cancel');

    }

    public function paypalCancel()
    {
        toastr('Algo Salio Mal en el Pago, Prueba con otro metodo o intentalo mas tarde' , 'error' , 'Error');
        return redirect()->route('user.payment');
    }

    //Nuevos End points para Paypal (Agregacion de botones de pago)
    public function createOrder(Request $request){
        $config = $this->paypalConfig();
        $provider = new PayPalClient($config);
        $provider->getAccessToken();

        $response = $provider->createOrder([
        "intent" => "CAPTURE",
        "purchase_units" => [
            [
                "amount" => [
                    "currency_code" => $config['currency'],
                    "value" => getFinalPayableAmount()
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
/** PAYPAL MESES SIN INTERESES DESDE CUENTA DEL USUARIO HACER LA LOGICA PARA AQUELLOS PRODUCTOS QUE NO CUMPLAN CON MESES SIN INTERESES QUE VALGA 3 MESES Y SI SE VA A DAR MAS MESES SIN INTERESES TENEMOS QUE PLANEARLO */

    
        if (isset($response['id'])) {
            return response()->json(['id' => $response['id']]);
        }

        return response()->json(['error' => 'No se pudo crear la orden'], 500);
    }

    public function captureOrder(Request $request) {
        $config = $this->paypalConfig();
        $provider = new PayPalClient($config);
        $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($request->orderId);
        if(isset($response['status']) && $response['status'] === 'COMPLETED'){
            $paypalSetting = PaypalSetting::first();
            $paidAmount = getFinalPayableAmount();
            $this->storeOrder(null, 'paypal', 1 , $response['id'], $paidAmount, $paypalSetting->currency_name);
            $this->clearSession();

            $order = Order::latest()->first();
            $signedUrl = URL::temporarySignedRoute('user.payment.success', now()->addSeconds(30));
            $this->notifyPaymentProcessed($order);

            return response()->json(['redirect_url' => $signedUrl]);
        }
        $cancelUrl = route('user.paypal.cancel');
        return response()->json(['redirect_url' => $cancelUrl]);
    }


    /**Payment Stripe */
    public function payWithStripe(Request $request)
    {

        // calculate payable amount depending on currency rate
        $stripeSetting = StripeSetting::first();
        // $total = getFinalPayableAmount();
        // $payableAmount = round($total * $stripeSetting->currency_rate, 2);
        $payableAmount = getFinalPayableAmount();/**Get Final Ammount */

        Stripe::setApiKey($stripeSetting->secret_key);
       $response = Charge::create([
            "amount" => $payableAmount * 100,
            "currency" => $stripeSetting->currency_name,
            "source" => $request->stripe_token,
            "description" => "Venta Por Web Macdelnorte"
        ]);

        if($response->status === 'succeeded'){
            $this->storeOrder(null,'stripe', 1, $response->id, $payableAmount, $stripeSetting->currency_name);
            // clear session
            $this->clearSession();

            $order = Order::latest()->first();
            $signedUrl = URL::temporarySignedRoute(
                'user.payment.success', now()->addSeconds(30)
            );
            $this->notifyPaymentProcessed($order);

            return redirect()->to($signedUrl);
        }else {
            toastr('Someting went wrong try agin later!', 'error', 'Error');
            return redirect()->route('user.payment');
        }

    }


    public function payWithTransfer(Request $request)
    {
        $refBank = $request->input('refBank');

        $setting = GeneralSetting::first();

        // Lógica para guardar la orden y otros detalles aquí
        $paymentMethod = 'transfer'; // Método de pago (puedes usar 'transfer' para transferencia bancaria)
        $paymentStatus = 0; // 1 para pago completado
        $transactionId = 'TXN-' . uniqid(); // Generar un ID de transacción único (puedes usar algo como TXN-1234)
        $paidAmount = getFinalPayableAmount(); // Obtener el monto final a pagar desde tu lógica
        $paidCurrencyName = $setting->currency_name; // Nombre de la moneda

        // Guardar la orden
        $this->storeOrder($refBank,$paymentMethod, $paymentStatus, $transactionId, $paidAmount, $paidCurrencyName);

        // Limpiar sesión después de completar la orden
        $this->clearSession();

        // Obtener la orden recién guardada
        $order = Order::latest()->first();


        $signedUrl = URL::temporarySignedRoute(
            'user.payment-transfer.success', now()->addMinutes(1)
        );
        // Disparar la notificación
        $this->notifyPaymentProcessed($order);


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
        Notification::route('mail','undemy258@gmail.com')//cambiar a ventas1@macdelnorte.com
        ->notify(new buytopayAdmin($order));
    }




}
