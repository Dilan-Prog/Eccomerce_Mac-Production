<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioxProductWeb extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'product_id',
        'key_price',
        'price',
    ];
    

}
