<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollaboratorGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'collaborator_group_user')
            ->withTimestamps();
    }

    public function isCreatedBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }
}
