<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Secuencia de seguimiento de cotizaciones. La lógica que inscribe, saca y
 * vence pasos vive en App\Support\SequenceProcessor, no aquí.
 */
class EmailSequence extends Model
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

    public function steps(): HasMany
    {
        return $this->hasMany(EmailSequenceStep::class)->orderBy('step_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(EmailSequenceEnrollment::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }
}
