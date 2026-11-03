<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrainingSessionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdministrator() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->can('ViewAny:TrainingSession'); }
    public function view(User $user, TrainingSession $session): bool { return $user->can('View:TrainingSession') && $session->isOwnedBy($user); }
    public function create(User $user): bool { return $user->can('Create:TrainingSession'); }
    public function update(User $user, TrainingSession $session): bool { return $user->can('Update:TrainingSession') && $session->isOwnedBy($user); }
    public function delete(User $user, TrainingSession $session): bool { return $user->can('Delete:TrainingSession') && $session->isOwnedBy($user); }
    public function deleteAny(User $user): bool { return $user->can('DeleteAny:TrainingSession'); }
    public function restore(User $user, TrainingSession $session): bool { return $user->can('Restore:TrainingSession') && $session->isOwnedBy($user); }
    public function forceDelete(User $user, TrainingSession $session): bool { return $user->can('ForceDelete:TrainingSession') && $session->isOwnedBy($user); }
    public function forceDeleteAny(User $user): bool { return $user->can('ForceDeleteAny:TrainingSession'); }
    public function restoreAny(User $user): bool { return $user->can('RestoreAny:TrainingSession'); }
    public function replicate(User $user, TrainingSession $session): bool { return $user->can('Replicate:TrainingSession') && $session->isOwnedBy($user); }
    public function reorder(User $user): bool { return $user->can('Reorder:TrainingSession'); }
}
