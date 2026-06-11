<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    public function viewCsf()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user->csf_path && Storage::exists($user->csf_path), 404);

        return Storage::response($user->csf_path, null, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function updateB2bInfo(Request $request){

        $request->validate([
            'company'          => ['required', 'string', 'max:255'],
            'rfc'              => ['required', 'string', 'size:13'],
            'tipo_cliente'     => ['required', 'in:revendedor,tecnico,empresa,contratista'],
            'giro_industrial'  => ['nullable', 'string', 'max:100'],
            'volumen_mensual'  => ['nullable', 'string', 'max:50'],
            'ciudad'           => ['required', 'string', 'max:100'],
            'constancia_fiscal'=> [Auth::user()->csf_path ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->company         = $request->company;
        $user->rfc             = strtoupper($request->rfc);
        $user->tipo_cliente    = $request->tipo_cliente;
        $user->giro_industrial = $request->giro_industrial;
        $user->volumen_mensual = $request->volumen_mensual;
        $user->ciudad          = $request->ciudad;
        $user->account_type    = 'b2b';

        if ($request->hasFile('constancia_fiscal')) {
            if ($user->csf_path) {
                Storage::delete($user->csf_path);
            }
            $user->csf_path   = $request->file('constancia_fiscal')->store('csf');
            $user->b2b_status = 'pending';
        }

        $user->save();

        toastr()->success('Información B2B actualizada correctamente');
        return redirect()->route('user.profile', ['tab' => 'b2b']);
    }

}
