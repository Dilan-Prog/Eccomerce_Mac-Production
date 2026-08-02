<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateImageGroupMember extends Model
{
    protected $fillable = [
        'duplicate_image_group_id',
        'path_hash',
        'physical_path',
        'image_hash',
        'file_size',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DuplicateImageGroup::class, 'duplicate_image_group_id');
    }
}
