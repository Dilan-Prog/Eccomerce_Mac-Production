<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un paso de una secuencia. `wait_days` se cuenta siempre desde la fecha de
 * inscripción, nunca encadenado desde el paso anterior (ver la migración).
 */
class EmailSequenceStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_sequence_id',
        'email_template_id',
        'step_order',
        'wait_days',
        'name',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(EmailSequence::class, 'email_sequence_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function stepSends(): HasMany
    {
        return $this->hasMany(EmailSequenceStepSend::class);
    }
}
