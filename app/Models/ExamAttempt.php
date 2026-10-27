<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    public const STATUS_SUBMITTED = 'enviado';
    public const STATUS_PENDING_REVIEW = 'pendiente_calificacion';
    public const STATUS_PASSED = 'aprobado';
    public const STATUS_FAILED = 'reprobado';
    public const STATUS_REPLACED = 'reemplazado';

    protected $fillable = [
        'enrollment_id',
        'status',
        'score',
        'passed',
        'submitted_at',
        'finalized_at',
        'finalized_by',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'passed' => 'boolean',
            'submitted_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
