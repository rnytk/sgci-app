<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdministrator() || $user->hasRole('evaluador')) {
            return $query;
        }

        if ($user->hasRole('administrador_capacitaciones')) {
            return $query->whereHas(
                'enrollment.trainingSession.training',
                fn (Builder $trainingQuery) => $trainingQuery->where('user_id', $user->id),
            );
        }

        return $query->whereHas('enrollment', fn (Builder $enrollmentQuery) => $enrollmentQuery->where('user_id', $user->id));
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->isSuperAdministrator() || $user->hasRole('evaluador')) {
            return true;
        }

        if ($user->hasRole('administrador_capacitaciones')) {
            return $this->enrollment()
                ->whereHas('trainingSession.training', fn (Builder $trainingQuery) => $trainingQuery->where('user_id', $user->id))
                ->exists();
        }

        return $this->enrollment()->where('user_id', $user->id)->exists();
    }
}
