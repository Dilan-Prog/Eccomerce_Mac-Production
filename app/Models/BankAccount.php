<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A list of bank accounts shown in the cotizaciones PDF, configurable from
 * the admin panel. Deliberately separate from Transfer (a single account
 * shared with the customer-facing checkout) — this supports more than one.
 */
class BankAccount extends Model
{
    protected $table = 'bank_accounts';

    protected $fillable = [
        'banco',
        'titular',
        'numero_cuenta',
        'numero_tarjeta',
        'clabe',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
