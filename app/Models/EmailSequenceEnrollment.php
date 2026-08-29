<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Inscripción de una cotización en una secuencia — la unidad de seguimiento
 * es la cotización, no el cliente (ver la migración).
 */
class EmailSequenceEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_sequence_id',
        'cotizacion_id',
        'user_id',
        'status',
        'enrolled_at',
        'exited_at',
        'exit_reason',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(EmailSequence::class, 'email_sequence_id');
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stepSends(): HasMany
    {
        return $this->hasMany(EmailSequenceStepSend::class);
    }
}
