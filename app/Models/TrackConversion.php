<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'gclid',
        'type',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'landing_page',
    ];

}
