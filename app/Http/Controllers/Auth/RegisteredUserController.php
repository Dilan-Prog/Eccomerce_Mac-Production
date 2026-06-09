<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // lowercase es para minusculas

        $isB2b = $request->account_type === 'b2b';

        $rules = [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
            'account_type' => ['nullable', 'in:personal,b2b'],
        ];

        if ($isB2b) {
            $rules['empresa']          = ['required', 'string', 'max:255'];
            $rules['rfc']              = ['required', 'string', 'size:13'];
            $rules['tipo_cliente']     = ['required', 'in:revendedor,tecnico,empresa,contratista'];
            $rules['constancia_fiscal']= ['nullable', 'file', 'mimes:pdf', 'max:5120'];
            $rules['giro_industrial']  = ['nullable', 'string', 'max:100'];
            $rules['volumen_mensual']  = ['nullable', 'string', 'max:50'];
            $rules['ciudad']           = ['required', 'string', 'max:100'];
        }

        $request->validate($rules);

        $csfPath = null;
        if ($isB2b && $request->hasFile('constancia_fiscal')) {
            $csfPath = $request->file('constancia_fiscal')->store('csf', 'public');
        }

        $user = User::create([
            'name'            => $request->name,
            'last_name'       => '',
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'phone'           => $request->phone ?? '',
            'username'        => '',
            'company'         => $request->empresa ?? '',
            'rfc'             => strtoupper($request->rfc ?? ''),
            'account_type'    => $request->account_type ?? 'personal',
            'tipo_cliente'    => $isB2b ? $request->tipo_cliente : null,
            'giro_industrial' => $isB2b ? $request->giro_industrial : null,
            'volumen_mensual' => $isB2b ? $request->volumen_mensual : null,
            'ciudad'          => $isB2b ? $request->ciudad : null,
            'csf_path'        => $csfPath,
            'b2b_status'      => $isB2b ? 'pending' : 'personal',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
