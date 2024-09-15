<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ShippingRule;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckOutController extends Controller
{
    public function index(){

        $addresses = UserAddress::where('user_id', Auth::user()->id)->get();
        $shippingMethod = ShippingRule::where('status', 1)->get();
        return view('frontend.pages.checkout', compact('addresses', 'shippingMethod'));

    }

    public function createAddress(Request $request){

        $request->validate([
            'name' => ['required', 'max:200'],
            'email' => ['required', 'max:200', 'email'],
            'phone' => ['required', 'max:200'],
            'zip' => ['required', 'max:200'],
            'state' => ['required', 'max:200'],
            'city' => ['required', 'max:200'],
            'col' => ['required', 'max:200'],
            'street' => ['required', 'max:200'],
            'street_number' => ['max:200'],
            'street_1' => ['max:200'],
            'street_2' => ['max:200'],
            'address' => ['max:200'],
            
        ]);

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

        $address = UserAddress::findOrFail($request->shipping_address_id)->toArray();
        if($address){
            Session::put('address', $address);
        }

        return response(['status' => 'success', 'redirect_url' => route('user.payment')]);

    }

}
