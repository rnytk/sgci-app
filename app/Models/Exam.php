<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'training_id',
        'passing_score',
        'time_limit',
    ];

    public function training() 
    { 
        return $this->belongsTo(Training::class); 
    }
    public function questions() 
    { 
        return $this->hasMany(Question::class); 
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdministrator()) {
            return $query;
        }

        return $query->whereHas('training', fn (Builder $trainingQuery) => $trainingQuery->visibleTo($user));
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->training()->where('user_id', $user->id)->exists();
    }
}
