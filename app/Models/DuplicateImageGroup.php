<?php

namespace App\Models;

use App\Enums\DuplicateGroupStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DuplicateImageGroup extends Model
{
    protected $fillable = [
        'group_key',
        'status',
        'member_count',
        'total_bytes',
        'recoverable_bytes',
        'first_seen_at',
        'last_seen_at',
        'discarded_at',
        'resolved_at',
    ];

    protected $casts = [
        'status' => DuplicateGroupStatus::class,
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'discarded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(DuplicateImageGroupMember::class);
    }
}
