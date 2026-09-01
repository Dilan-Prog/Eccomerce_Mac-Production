<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class UserAddress extends Model
{
    use HasFactory;

    /**
     * Reglas de validación compartidas por CheckOutController::createAddress()
     * y UserAddressController::store()/update() — antes cada uno tenía su
     * propia copia de las mismas reglas, y ninguna validaba el formato real
     * de los datos (solo "no vacío" y "máximo 200 caracteres"), así que un
     * teléfono como "7341036410asddadadads" pasaba sin problema.
     */
    public static function validationRules(): array
    {
        $letters = 'regex:/^[\pL\s\.\'-]+$/u';
        $alnum = 'regex:/^[\pL\pN\s\.,#\'-]+$/u';
        $alnumOptional = 'regex:/^[\pL\pN\s\.,#\'-]*$/u';

        return [
            'name'          => ['required', 'string', 'max:200', $letters],
            'email'         => ['required', 'max:200', 'email'],
            'phone'         => ['required', 'digits:10'],
            'zip'           => ['required', 'digits:5'],
            'state'         => ['required', Rule::in(config('settings.state_list', []))],
            'city'          => ['required', 'string', 'max:200', $letters],
            'col'           => ['required', 'string', 'max:200', $alnum],
            'street'        => ['required', 'string', 'max:200', $alnum],
            'street_number' => ['nullable', 'string', 'max:50', $alnumOptional],
            'street_1'      => ['nullable', 'string', 'max:200', $alnumOptional],
            'street_2'      => ['nullable', 'string', 'max:200', $alnumOptional],
            'address'       => ['nullable', 'string', 'max:200'],
        ];
    }
}
