<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = UserAddress::where('user_id', Auth::user()->id)->get();
        return view('frontend.dashboard.address.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('frontend.dashboard.address.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        return redirect()->route('user.address.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $address = UserAddress::find($id);
        return view('frontend.dashboard.address.edit', compact('address'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
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

        $address = UserAddress::findOrFail($id);
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

        toastr('Actualizacion Exitosa', 'success', 'Success');
        return redirect()->route('user.address.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $address = UserAddress::findOrFail($id);
        $address->delete();

        return response(['status' => 'success', 'message' => 'Borrado Exitosamente']);
    }
}
