<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'title',
        'description',
        'slug',
        'user_id',
    ];
    public function user() 
    { 
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function mediaResources()
    {
        return $this->hasMany(MediaResource::class);
    }
}
