<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una línea del calendario materializado de una inscripción: el paso N de la
 * secuencia para esa cotización, con su fecha de vencimiento ya calculada.
 * Estados documentados en la migración create_email_sequence_step_sends_table.
 */
class EmailSequenceStepSend extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_sequence_enrollment_id',
        'email_sequence_step_id',
        'status',
        'due_at',
        'sent_at',
        'error_message',
        'attempts',
        'claimed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'sent_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EmailSequenceEnrollment::class, 'email_sequence_enrollment_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(EmailSequenceStep::class, 'email_sequence_step_id');
    }
}
