<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AspelApiToken extends Model
{
    use HasFactory;

    protected $hidden = ['secret_hash'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'status' => 'boolean',
    ];
}
