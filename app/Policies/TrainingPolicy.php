<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Training;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrainingPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdministrator() ? true : null;
    }

    public function viewAny(User $user): bool { return $user->can('ViewAny:Training'); }
    public function view(User $user, Training $training): bool { return $user->can('View:Training') && $training->isOwnedBy($user); }
    public function create(User $user): bool { return $user->can('Create:Training'); }
    public function update(User $user, Training $training): bool { return $user->can('Update:Training') && $training->isOwnedBy($user); }
    public function delete(User $user, Training $training): bool { return $user->can('Delete:Training') && $training->isOwnedBy($user); }
    public function deleteAny(User $user): bool { return $user->can('DeleteAny:Training'); }
    public function restore(User $user, Training $training): bool { return $user->can('Restore:Training') && $training->isOwnedBy($user); }
    public function forceDelete(User $user, Training $training): bool { return $user->can('ForceDelete:Training') && $training->isOwnedBy($user); }
    public function forceDeleteAny(User $user): bool { return $user->can('ForceDeleteAny:Training'); }
    public function restoreAny(User $user): bool { return $user->can('RestoreAny:Training'); }
    public function replicate(User $user, Training $training): bool { return $user->can('Replicate:Training') && $training->isOwnedBy($user); }
    public function reorder(User $user): bool { return $user->can('Reorder:Training'); }
}
