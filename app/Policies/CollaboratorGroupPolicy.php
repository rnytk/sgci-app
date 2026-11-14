<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CollaboratorGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollaboratorGroupPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdministrator() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:CollaboratorGroup');
    }

    public function view(User $user, CollaboratorGroup $group): bool
    {
        return $user->can('View:CollaboratorGroup');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:CollaboratorGroup');
    }

    public function update(User $user, CollaboratorGroup $group): bool
    {
        return $user->can('Update:CollaboratorGroup') && $group->isCreatedBy($user);
    }

    public function delete(User $user, CollaboratorGroup $group): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, CollaboratorGroup $group): bool
    {
        return false;
    }

    public function forceDelete(User $user, CollaboratorGroup $group): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, CollaboratorGroup $group): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }
}
