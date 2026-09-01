<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(UserAddress::validationRules());

        $address = new UserAddress();
        $address->user_id      = Auth::user()->id;
        $address->name         = $request->name;
        $address->email        = $request->email;
        $address->phone        = $request->phone;
        $address->zip          = $request->zip;
        $address->state        = $request->state;
        $address->city         = $request->city;
        $address->col          = $request->col;
        $address->street       = $request->street;
        $address->street_number = $request->street_number;
        $address->street_1     = $request->street_1;
        $address->street_2     = $request->street_2;
        $address->address      = $request->address;
        $address->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Dirección creada correctamente', 'id' => $address->id]);
        }

        toastr('Creado Con Exito', 'success', 'Success');
        return redirect()->route('user.profile', ['tab' => 'addresses']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate(UserAddress::validationRules());

        $address = UserAddress::where('user_id', Auth::user()->id)->findOrFail($id);
        $address->user_id       = Auth::user()->id;
        $address->name          = $request->name;
        $address->email         = $request->email;
        $address->phone         = $request->phone;
        $address->zip           = $request->zip;
        $address->state         = $request->state;
        $address->city          = $request->city;
        $address->col           = $request->col;
        $address->street        = $request->street;
        $address->street_number = $request->street_number;
        $address->street_1      = $request->street_1;
        // El modal de "Mis direcciones" (resources/views/frontend/dashboard/profile.blade.php)
        // no tiene campos para street_2/address — sin este guard, cada edición
        // desde ese modal borraba en silencio la calle 2 / indicaciones
        // guardadas antes (ej. desde el viejo formulario de página completa).
        if ($request->has('street_2')) {
            $address->street_2 = $request->street_2;
        }
        if ($request->has('address')) {
            $address->address = $request->address;
        }
        $address->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Dirección actualizada correctamente']);
        }

        toastr('Actualizacion Exitosa', 'success', 'Success');
        return redirect()->route('user.profile', ['tab' => 'addresses']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $address = UserAddress::where('user_id', Auth::user()->id)->findOrFail($id);
        $address->delete();

        return response(['status' => 'success', 'message' => 'Borrado Exitosamente']);
    }
}
