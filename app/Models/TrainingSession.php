<?php

namespace App\Models;

use App\Enums\TrainingModality;
use App\Services\AuditLogger;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class TrainingSession extends Model
{
    protected $fillable = [
        'training_id',
        'trainer_id',
        'modality',
        'scheduled_at',
        'ends_at',
        'location',
        'meeting_link',
        'require_exam',
        'requires_attendance',
        'passing_score',
        'max_attempts',
        'issue_certificate',
        'exam_enabled',
        'diploma_bg',
    ];

    protected function casts(): array
    {
        return [
            'modality' => TrainingModality::class,
            'scheduled_at' => 'datetime',
            'ends_at' => 'datetime',
            'require_exam' => 'boolean',
            'requires_attendance' => 'boolean',
            'passing_score' => 'decimal:2',
            'max_attempts' => 'integer',
            'issue_certificate' => 'boolean',
            'exam_enabled' => 'boolean',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendanceTokens(): HasMany
    {
        return $this->hasMany(AttendanceToken::class);
    }

    public function isWithinAttendanceWindow(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if ($this->scheduled_at === null || $at->isBefore($this->scheduled_at->copy()->subMinutes(15))) {
            return false;
        }

        return $this->ends_at === null || $at->isBefore($this->ends_at->copy()->addMinutes(15));
    }

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            if ($session->passing_score === null && $session->training_id !== null) {
                $session->passing_score = Training::with('exams')->find($session->training_id)?->exams?->passing_score ?? 70;
            }

            $session->max_attempts = max(1, (int) ($session->max_attempts ?? 1));
        });

        static::saving(function (self $session): void {
            $session->max_attempts ??= 1;

            if ($session->max_attempts < 1) {
                throw ValidationException::withMessages([
                    'max_attempts' => 'El máximo de intentos debe ser al menos 1.',
                ]);
            }

            if ($session->passing_score < 0 || $session->passing_score > 100) {
                throw ValidationException::withMessages([
                    'passing_score' => 'La nota mínima debe estar entre 0 y 100.',
                ]);
            }

            if ($session->scheduled_at !== null && $session->ends_at !== null && $session->ends_at->lessThanOrEqualTo($session->scheduled_at)) {
                throw ValidationException::withMessages([
                    'ends_at' => 'La fecha de fin debe ser posterior al inicio.',
                ]);
            }

            if ($session->modality === TrainingModality::SelfPaced) {
                $session->requires_attendance = false;
                $session->location = null;
                $session->meeting_link = null;
            } elseif ($session->modality === TrainingModality::Virtual) {
                $session->location = null;
            } elseif ($session->modality === TrainingModality::InPerson) {
                $session->meeting_link = null;
            }
        });

        static::created(function (self $session): void {
            app(AuditLogger::class)->record('training_session.created', $session);
        });

        static::updated(function (self $session): void {
            app(AuditLogger::class)->record('training_session.updated', $session, [
                'changes' => $session->getChanges(),
                'rule_fields' => array_intersect_key($session->getChanges(), array_flip([
                    'modality',
                    'ends_at',
                    'location',
                    'require_exam',
                    'requires_attendance',
                    'passing_score',
                    'max_attempts',
                    'issue_certificate',
                ])),
            ]);
        });
    }
}
