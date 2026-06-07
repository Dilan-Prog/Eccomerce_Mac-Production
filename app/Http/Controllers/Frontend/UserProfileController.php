<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function index(){
        $addresses = UserAddress::where('user_id', Auth::user()->id)->get();
        return view('frontend.dashboard.profile', compact('addresses'));
    }

    public function updateProfile(Request $request){

        $request -> validate([
            'name' =>['required','max:100'],
            'email'=> ['required', 'email','unique:users,email,'.Auth::user()->id],
            'image'=>['image','max:2048']
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->name  = $request->name;
        $user->email = $request->email;
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
