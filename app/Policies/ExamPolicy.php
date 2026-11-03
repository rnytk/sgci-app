<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdministrator() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->can('ViewAny:Exam'); }
    public function view(User $user, Exam $exam): bool { return $user->can('View:Exam') && $exam->isOwnedBy($user); }
    public function create(User $user): bool { return $user->can('Create:Exam'); }
    public function update(User $user, Exam $exam): bool { return $user->can('Update:Exam') && $exam->isOwnedBy($user); }
    public function delete(User $user, Exam $exam): bool { return $user->can('Delete:Exam') && $exam->isOwnedBy($user); }
    public function deleteAny(User $user): bool { return $user->can('DeleteAny:Exam'); }
    public function restore(User $user, Exam $exam): bool { return $user->can('Restore:Exam') && $exam->isOwnedBy($user); }
    public function forceDelete(User $user, Exam $exam): bool { return $user->can('ForceDelete:Exam') && $exam->isOwnedBy($user); }
    public function forceDeleteAny(User $user): bool { return $user->can('ForceDeleteAny:Exam'); }
    public function restoreAny(User $user): bool { return $user->can('RestoreAny:Exam'); }
    public function replicate(User $user, Exam $exam): bool { return $user->can('Replicate:Exam') && $exam->isOwnedBy($user); }
    public function reorder(User $user): bool { return $user->can('Reorder:Exam'); }
}
