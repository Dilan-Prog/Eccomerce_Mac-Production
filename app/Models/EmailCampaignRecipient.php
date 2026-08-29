<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una línea del snapshot de destinatarios de una campaña (ver la migración
 * create_email_campaign_recipients_table: es una copia congelada de la lista
 * al momento de programar, no una consulta en vivo).
 */
class EmailCampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_campaign_id',
        'email',
        'name',
        'company',
        'contact_source',
        'user_id',
        'aspel_client_id',
        'status',
        'sent_at',
        'error_message',
        'attempts',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
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
