<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $user = auth()->user();
        $tipo = $this->input('tipo_persona');

        // RFC: empresa = 3 letras + 6 dígitos + 3 alfanuméricos = 12 chars
        //       física  = 4 letras + 6 dígitos + 3 alfanuméricos = 13 chars
        $rfcSize  = $tipo === 'fisica' ? 13 : a12;
        $rfcRegex = $tipo === 'fisica'
            ? 'regex:/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/i'
            : 'regex:/^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/i';

        $rules = [
            'tipo_persona'     => ['required', 'in:empresa,fisica'],
            'direccion_fiscal' => ['required', 'string'],
        ];

        // Teléfono: ya viene del registro si el usuario lo capturó.
        $rules['telefono'] = $user->phone
            ? ['nullable', 'string', 'max:20']
            : ['required', 'string', 'max:20'];

        // RFC: ya viene del registro; si no, se captura aquí.
        $rules['rfc'] = $user->rfc
            ? ['nullable', 'string']
            : ['required', 'string', 'size:' . $rfcSize, $rfcRegex];

        // Razón social: solo se pide si es empresa y no tiene "company" en su perfil.
        $rules['razon_social'] = ($tipo === 'empresa' && ! $user->company)
            ? ['required', 'string', 'max:250']
            : ['nullable', 'string', 'max:250'];

        // CIF: ya subido en el registro (csf_path); si no, se exige aquí.
        $rules['cif'] = $user->csf_path
            ? ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096']
            : ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'telefono.required'         => 'El teléfono es obligatorio.',
            'tipo_persona.required'     => 'Selecciona el tipo de persona.',
            'tipo_persona.in'           => 'El tipo de persona debe ser empresa o física.',
            'razon_social.required_if'  => 'La razón social es obligatoria para personas morales.',
            'rfc.required'              => 'El RFC es obligatorio.',
            'rfc.regex'                 => 'El RFC no tiene el formato válido (12 dígitos para empresa, 13 para persona física).',
            'direccion_fiscal.required' => 'La dirección fiscal es obligatoria.',
            'cif.required'              => 'El CIF (Constancia de Situación Fiscal) es obligatorio.',
            'cif.mimes'                 => 'El CIF debe ser PDF, JPG o PNG.',
            'cif.max'                   => 'El CIF no debe superar los 4 MB.',
        ];
    }
}
