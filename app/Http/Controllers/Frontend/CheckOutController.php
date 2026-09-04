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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckOutController extends Controller
{
    /**
     * Formas de pago que el checkout acepta. Son las mismas tres opciones que
     * pinta resources/views/frontend/pages/checkout.blade.php
     * (name="payment_radio") y las únicas que PaymentController sabe cobrar.
     */
    public const PAYMENT_METHODS = ['stripe', 'paypal', 'spei'];


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
            // La dirección se acota al propio usuario dentro de la regla: sin
            // ese where, mandar el id de la dirección de otro cliente pasaba
            // la validación (el findOrFail de abajo sí filtraba, pero
            // respondía un 404 seco en vez de un error de formulario).
            'shipping_address_id' => [
                'required',
                'integer',
                Rule::exists('user_addresses', 'id')->where('user_id', Auth::id()),
            ],
            // status = 1 dentro de la regla: una regla de envío que el admin
            // desactivó no debe poder usarse aunque su id siga siendo válido.
            'shipping_method_id' => [
                'required',
                'integer',
                Rule::exists('shipping_rules', 'id')->where('status', 1),
            ],
            // Antes esto no se validaba y caía a 'pending' si no llegaba, así
            // que se podía cerrar el paso de checkout sin elegir cómo pagar.
            'payment_method' => ['required', Rule::in(self::PAYMENT_METHODS)],
        ], [
            'shipping_address_id.required' => 'Selecciona una dirección de envío antes de continuar.',
            'shipping_address_id.exists' => 'La dirección de envío seleccionada no es válida.',
            'shipping_method_id.required' => 'Selecciona un método de envío antes de continuar.',
            'shipping_method_id.exists' => 'El método de envío seleccionado ya no está disponible.',
            'payment_method.required' => 'Selecciona un método de pago antes de continuar.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
        ]);

        $shippingMethod = ShippingRule::findOrFail($request->shipping_method_id);

        // Una regla de tipo "min_cost" (ej. envío gratis a partir de $2,299)
        // solo aplica si el pedido alcanza ese monto. La pantalla ya solo
        // muestra las que califican, pero eso es cosmético: sin esta
        // comprobación bastaba con mandar el id de la regla para llevarse el
        // envío gratis con un carrito de cualquier importe.
        if ($shippingMethod->type === 'min_cost' && getCartTotal() < (float) $shippingMethod->min_cost) {
            throw ValidationException::withMessages([
                'shipping_method_id' => ['Ese método de envío requiere un pedido mínimo de $' . number_format((float) $shippingMethod->min_cost, 2) . '.'],
            ]);
        }

        Session::put('shipping_method', [
            'id' => $shippingMethod->id,
            'name' => $shippingMethod->name,
            'type' => $shippingMethod->type,
            'cost' => $shippingMethod->cost,
        ]);

        $address = UserAddress::where('user_id', Auth::user()->id)->findOrFail($request->shipping_address_id)->toArray();
        Session::put('address', $address);

        Session::put('payment_method', $request->payment_method);
        Session::put('order_notes',    $request->input('order_notes', ''));

        return response(['status' => 'session_saved']);

    }

}
