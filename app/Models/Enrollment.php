<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    protected $fillable = [
        'training_session_id',
        'user_id',
        'assigned_at',
        'started_at',
        'attended_at',
        'attendance_source',
        'completed_at',
        'absence_reason',
        'status',
    ];

    protected $attributes = [
        'status' => EnrollmentStatus::Assigned->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'attended_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function markAttended(string $source = 'manual'): self
    {
        if ($this->status === EnrollmentStatus::Cancelled) {
            return $this;
        }

        $now = now();
        $hasPassedAttempt = $this->examAttempts()->where('passed', true)->exists();
        $this->forceFill([
            'attended_at' => $this->attended_at ?? $now,
            'attendance_source' => $source,
            'started_at' => $this->started_at ?? $now,
            'status' => $this->trainingSession->require_exam && ! $hasPassedAttempt
                ? EnrollmentStatus::InProgress
                : EnrollmentStatus::Completed,
            'completed_at' => $this->trainingSession->require_exam && ! $hasPassedAttempt
                ? null
                : ($this->completed_at ?? $now),
        ])->save();

        return $this;
    }

    protected static function booted(): void
    {
        static::creating(function (self $enrollment): void {
            $enrollment->assigned_at ??= now();
            $enrollment->status ??= EnrollmentStatus::Assigned;
        });

        static::created(function (self $enrollment): void {
            app(AuditLogger::class)->record('enrollment.assigned', $enrollment, [
                'training_session_id' => $enrollment->training_session_id,
                'user_id' => $enrollment->user_id,
            ]);
        });

        static::updated(function (self $enrollment): void {
            app(AuditLogger::class)->record('enrollment.updated', $enrollment, [
                'changes' => $enrollment->getChanges(),
            ]);
        });
    }
}
