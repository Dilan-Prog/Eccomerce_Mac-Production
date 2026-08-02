<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageHashCache extends Model
{
    protected $table = 'image_hash_cache';

    protected $fillable = [
        'path_hash',
        'physical_path',
        'file_size',
        'file_mtime',
        'image_hash',
        'hash_algo_version',
        'last_hashed_at',
    ];

    protected $casts = [
        'last_hashed_at' => 'datetime',
    ];
}
