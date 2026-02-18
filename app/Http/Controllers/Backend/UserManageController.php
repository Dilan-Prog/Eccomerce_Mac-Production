<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\AccountCreatedMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


// PENDIENTE ARREGLAR EL CORREO Y EL TIPO DE PRECIO
class UserManageController extends Controller
{
    public function index()
    {
        return view('admin.manage-user.index');
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:200'],
            'last_name' => ['max:100'],
            'company' => ['max:200'],
            'rfc' => ['max:14', 'unique:users,rfc'],
            'phone' => ['max:15'],
            'role' => ['required'],
            // 'type_price' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed']
        ]);
        $user = new User();
        if($request->role === 'user'){
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->company = $request->company;
            $user->rfc = $request->rfc;
            $user->phone = $request->phone;
            $user->role = 'user';
            // $user->type_price = $request->type_price;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->status = 'active';
            $user->save();

            try {
                Mail::to($request->email)->send(new AccountCreatedMail($request->name, $request->email, $request->password));
                \Log::info("Correo enviado correctamente para el usuario : {$request->email}");
            } catch (\Throwable $e) {
                \Log::error('Mail send error (user create): '.$e->getMessage());
            }

            toastr('Nuevo Usuario Creado', 'success', 'success');
            return redirect()->back();
        }elseif($request->role === 'admin'){
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->company = $request->company;
            $user->rfc = $request->rfc;
            $user->phone = $request->phone;
            $user->role = 'admin';
            // $user->type_price = $request->type_price;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->status = 'active';
            $user->save();

            try {
                Mail::to($request->email)->send(new AccountCreatedMail($request->name, $request->email, $request->password));
                \Log::info("Correo enviado correctamente para el usuario administrador: {$request->email}");
            } catch (\Throwable $e) {
                \Log::error('Mail send error (admin create): '.$e->getMessage());
            }

            toastr('Nuevo Administrador Creado', 'success', 'success');
            return redirect()->back();
        }elseif($request->role === 'associate'){
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->company = $request->company;
            $user->rfc = $request->rfc;
            $user->phone = $request->phone;
            $user->role = 'associate';
            // $user->type_price = $request->type_price;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->status = 'active';
            $user->save();

            try {
                Mail::to($request->email)->send(new AccountCreatedMail($request->name, $request->email, $request->password));
                \Log::info("Correo enviado correctamente para el usuario asociado: {$request->email}");
            } catch (\Throwable $e) {
                \Log::error('Mail send error (associate create): '.$e->getMessage());
            }

            toastr('Nuevo Cliente Asociado Creado', 'success', 'success');
            return redirect()->back();
        }
    }
}
