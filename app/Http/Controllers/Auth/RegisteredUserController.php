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
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'account_type'      => ['nullable', 'in:personal,b2b'],
            'rfc'               => ['required', 'string', 'min:12', 'max:13', 'unique:users,rfc'],
            'tipo_cliente'      => ['required', 'in:revendedor,tecnico,empresa,contratista'],
            'constancia_fiscal' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'giro_industrial'   => ['nullable', 'string', 'max:100'],
            'volumen_mensual'   => ['nullable', 'string', 'max:50'],
            'ciudad'            => ['required', 'string', 'max:100'],
        ];

        if ($isB2b) {
            $rules['empresa'] = ['required', 'string', 'max:255'];
        }

        $messages = [
            'name.required'              => 'El nombre es obligatorio.',
            'email.required'             => 'El correo electrónico es obligatorio.',
            'email.email'                => 'Ingresa un correo electrónico válido.',
            'email.unique'                => 'Ya existe una cuenta registrada con este correo.',
            'password.required'          => 'La contraseña es obligatoria.',
            'password.confirmed'         => 'La confirmación de contraseña no coincide.',
            'rfc.required'               => 'El RFC es obligatorio.',
            'rfc.min'                    => 'El RFC debe tener 12 o 13 caracteres.',
            'rfc.max'                    => 'El RFC debe tener 12 o 13 caracteres.',
            'rfc.unique'                  => 'Este RFC ya está registrado en nuestro sistema.',
            'tipo_cliente.required'      => 'Selecciona un tipo de cliente.',
            'tipo_cliente.in'            => 'El tipo de cliente seleccionado no es válido.',
            'constancia_fiscal.required' => 'Debes subir tu Constancia de Situación Fiscal.',
            'constancia_fiscal.mimes'    => 'La Constancia de Situación Fiscal debe ser un archivo PDF.',
            'constancia_fiscal.max'      => 'La Constancia de Situación Fiscal no debe pesar más de 5 MB.',
            'ciudad.required'            => 'La ciudad es obligatoria.',
            'empresa.required'           => 'El nombre de la empresa o razón social es obligatorio.',
        ];

        $request->validate($rules, $messages);

        $csfPath = null;
        if ($request->hasFile('constancia_fiscal')) {
            $csfPath = $request->file('constancia_fiscal')->store('csf');
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
