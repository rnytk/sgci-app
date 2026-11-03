<?php

namespace App\Policies;

use App\Models\ExamAttempt;
use App\Models\User;

class ExamAttemptPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdministrator() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->canReviewAttempts($user);
    }

    public function view(User $user, ExamAttempt $attempt): bool
    {
        return $this->canReviewAttempts($user) && $attempt->isVisibleTo($user);
    }

    public function update(User $user, ExamAttempt $attempt): bool
    {
        return $this->canReviewAttempts($user) && $attempt->isVisibleTo($user);
    }

    public function create(User $user): bool { return false; }
    public function delete(User $user, ExamAttempt $attempt): bool { return false; }

    private function canReviewAttempts(User $user): bool
    {
        return $user->hasAnyRole(['evaluador', 'administrador_capacitaciones'])
            && $user->can('Review:ExamAttempt');
    }
}
