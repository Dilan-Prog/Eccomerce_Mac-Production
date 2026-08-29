<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Contacto dentro de una lista. `email`/`name`/`company` se guardan
 * desnormalizados a propósito (ver la migración) — un miembro manual no
 * tiene user_id ni aspel_client_id.
 */
class EmailContactListMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_contact_list_id',
        'email',
        'name',
        'company',
        'source',
        'user_id',
        'aspel_client_id',
        'unsubscribed_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(EmailContactList::class, 'email_contact_list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aspelClient(): BelongsTo
    {
        return $this->belongsTo(AspelClient::class);
    }
}
