<?php

namespace App\Models;

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
}
