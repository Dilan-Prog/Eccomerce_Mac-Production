<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PaypalSetting;
use App\Models\ShippingRule;
use App\Models\StripeSetting;
use App\Models\Transfer;
use App\Models\UserAddress;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckOutController extends Controller
{
    public function index(){

        // Con el carrito vacío (ej. el usuario navegó de vuelta con el
        // botón "Atrás" del navegador después de pagar) no hay nada que
        // cobrar — regresar al carrito en vez de mostrar el checkout vacío.
        if (Cart::content()->isEmpty()) {
            toastr('Tu carrito está vacío. Agrega productos antes de continuar.', 'error', 'Carrito vacío');
            return redirect()->route('cart-details');
        }

        $addresses      = UserAddress::where('user_id', Auth::user()->id)->get();
        $shippingMethod = ShippingRule::where('status', 1)->get();
        $transferInfo   = Transfer::first();

        // PaypalSetting/StripeSetting son configuraciones tipo "singleton"
        // (el formulario de /admin/payment-settings siempre guarda/lee la
        // misma fila vía updateOrCreate(['id' => 1], ...) — ver
        // PaypalSettingController/StripeSettingController). Antes esta
        // pantalla filtraba por status=1 en el WHERE: si por cualquier
        // motivo existiera más de una fila en la tabla (ej. una fila
        // huérfana de una migración/seed vieja) con status=1, el checkout
        // seguía mostrando la pasarela aunque el admin apagara el toggle,
        // porque el WHERE podía encontrar esa OTRA fila. Ahora se lee la
        // misma fila que usa el resto del flujo de pago
        // (PaymentController::payWithStripe()/paywithPaypal() también usan
        // ::first() sin WHERE) y el status se revisa aparte, así el
        // checkout siempre refleja exactamente lo que se va a cobrar.
        $paypalInfo    = PaypalSetting::first();
        if ($paypalInfo && !$paypalInfo->status) {
            $paypalInfo = null;
        }
        $stripeSetting = StripeSetting::first();
        if ($stripeSetting && !$stripeSetting->status) {
            $stripeSetting = null;
        }

        return view('frontend.pages.checkout', compact(
            'addresses', 'shippingMethod', 'transferInfo', 'paypalInfo', 'stripeSetting'
        ));
    }

    public function createAddress(Request $request){

        $request->validate(UserAddress::validationRules());

        $address = new UserAddress();
        $address->user_id = Auth::user()->id;
        $address->name = $request->name;
        $address->email = $request->email;
        $address->phone = $request->phone;
        $address->zip = $request->zip;
        $address->state = $request->state;
        $address->city = $request->city;
        $address->col = $request->col;
        $address->street = $request->street;
        $address->street_number = $request->street_number;
        $address->street_1 = $request->street_1;
        $address->street_2 = $request->street_2;
        $address->address = $request->address;
        $address->save();

        toastr('Creado Con Exito', 'success', 'Success');
        return redirect()->back();

    }

    public function checkOutFormSumit(Request $request){

        if (Cart::content()->isEmpty()) {
            return response(['status' => 'error', 'message' => 'Tu carrito está vacío.'], 422);
        }

        $request->validate([
            'shipping_method_id' => ['required', 'integer'],
            'shipping_address_id' => ['required', 'integer'],
        ]);

        $shippingMethod = ShippingRule::findOrFail($request->shipping_method_id);
        if($shippingMethod){
            Session::put('shipping_method', [
                'id' => $shippingMethod->id,
                'name' => $shippingMethod->name,
                'type' => $shippingMethod->type,
                'cost' => $shippingMethod->cost
            ]);
        }

        $address = UserAddress::where('user_id', Auth::user()->id)->findOrFail($request->shipping_address_id)->toArray();
        if($address){
            Session::put('address', $address);
        }

        Session::put('payment_method', $request->input('payment_method', 'pending'));
        Session::put('order_notes',    $request->input('order_notes', ''));

        return response(['status' => 'session_saved']);

    }

}
