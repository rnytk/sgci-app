<?php

namespace App\Policies;

use App\Models\ExamAttempt;
use App\Models\User;

class ExamAttemptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('Review:ExamAttempt');
    }

    public function view(User $user, ExamAttempt $attempt): bool
    {
        return $user->can('Review:ExamAttempt');
    }

    public function update(User $user, ExamAttempt $attempt): bool
    {
        return $user->can('Review:ExamAttempt');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, ExamAttempt $attempt): bool
    {
        return false;
    }
}
