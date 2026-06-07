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
        $tipo = $this->input('tipo_persona');

        // RFC: empresa = 3 letras + 6 dígitos + 3 alfanuméricos = 12 chars
        //       física  = 4 letras + 6 dígitos + 3 alfanuméricos = 13 chars
        $rfcRegex = $tipo === 'empresa'
            ? 'regex:/^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/i'
            : 'regex:/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/i';

        return [
            'telefono'        => ['required', 'string', 'max:20'],
            'tipo_persona'    => ['required', 'in:empresa,fisica'],
            'razon_social'    => ['required_if:tipo_persona,empresa', 'nullable', 'string', 'max:250'],
            'curp'            => [
                'required_if:tipo_persona,fisica',
                'nullable',
                'string',
                // CURP estándar mexicano: 18 caracteres
                'regex:/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/i',
            ],
            'rfc'             => ['required', 'string', $rfcRegex],
            'direccion_fiscal' => ['required', 'string'],
            'cif'             => [
                $this->hasFile('cif') ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.required'         => 'El teléfono es obligatorio.',
            'tipo_persona.required'     => 'Selecciona el tipo de persona.',
            'tipo_persona.in'           => 'El tipo de persona debe ser empresa o física.',
            'razon_social.required_if'  => 'La razón social es obligatoria para personas morales.',
            'curp.required_if'          => 'El CURP es obligatorio para personas físicas.',
            'curp.regex'                => 'El CURP no tiene el formato válido (18 caracteres).',
            'rfc.required'              => 'El RFC es obligatorio.',
            'rfc.regex'                 => 'El RFC no tiene el formato válido (12 dígitos para empresa, 13 para persona física).',
            'direccion_fiscal.required' => 'La dirección fiscal es obligatoria.',
            'cif.required'              => 'El CIF (Constancia de Situación Fiscal) es obligatorio.',
            'cif.mimes'                 => 'El CIF debe ser PDF, JPG o PNG.',
            'cif.max'                   => 'El CIF no debe superar los 4 MB.',
        ];
    }
}
