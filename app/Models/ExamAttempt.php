<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'enrollment_id',
        'exam_id',
        'attempt_number',
        'started_at',
        'expires_at',
        'submitted_at',
        'score',
        'passed',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'passed' => 'boolean',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress' && $this->submitted_at === null;
    }

    public function hasExpired(): bool
    {
        return $this->isInProgress() && now()->greaterThanOrEqualTo($this->expires_at);
    }
}
