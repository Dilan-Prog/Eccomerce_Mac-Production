<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lista de contactos del módulo de Email Marketing — el "a quién" de una
 * campaña (ver App\Models\EmailCampaign).
 */
class EmailContactList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by_admin_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(EmailContactListMember::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }
}
