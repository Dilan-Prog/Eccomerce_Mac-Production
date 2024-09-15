<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorProfileController extends Controller
{
    //
    public function index(){
        return view('vendor.dashboard.profile');
    }

    public function updateProfile(Request $request){
        
        $request -> validate([
            'name' =>['required','max:100'],
            'email'=> ['required', 'email','unique:users,email,'.Auth::user()->id],
            'image'=>['image','max:2048']
        ]);
        
        $user = Auth::user();

        

        
        $user->name = $request->name;
        $user->email= $request->email;
        $user->save();


        toastr()->success('Perfil Actualizado Correctamente');
        return redirect()->back();
        

    }

    public function updatePassword(Request $request){
        
        $request->validate([
            'current_password' =>['required','current_password'],
            'password' =>  ['required', 'confirmed', 'min:8']
           
        ]);

            $request->user()->update([
                'password'=> bcrypt($request->password)
            ]);

            toastr()->success('Contraseña Actualizada Correctamente');
            return redirect()->back();
    }

}
