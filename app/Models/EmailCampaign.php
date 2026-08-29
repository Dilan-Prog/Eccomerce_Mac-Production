<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Envío masivo de una plantilla a una lista de contactos. Ciclo de vida de
 * `status` documentado en la migración create_email_campaigns_table.
 */
class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email_template_id',
        'email_contact_list_id',
        'status',
        'scheduled_at',
        'claimed_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'created_by_admin_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'claimed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(EmailContactList::class, 'email_contact_list_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }
}
