<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    public const ATTENDANCE_PENDING = 'pendiente';

    public const ATTENDANCE_PRESENT = 'presente';

    public const ATTENDANCE_ABSENT = 'ausente';

    protected $fillable = [
        'training_session_id',
        'user_id',
        'attended_at',
        'attendance_status',
        'attendance_justification',
        'attendance_recorded_at',
        'attendance_recorded_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attended_at' => 'datetime',
            'attendance_recorded_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function manualAttendanceStatuses(): array
    {
        return [self::ATTENDANCE_PRESENT, self::ATTENDANCE_ABSENT];
    }

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendance_recorded_by');
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdministrator()) {
            return $query;
        }

        return $query->whereHas('trainingSession', fn (Builder $sessionQuery) => $sessionQuery->visibleTo($user));
    }
}
