<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AspelSync extends Model
{
    use HasFactory;


    protected $table = 'aspel_products';
    protected $fillable = [
        'sku',
        'nombre',
        'price',
        'stock',
        'remote_updated_at',
        'sync_hash' 
    ];

}
