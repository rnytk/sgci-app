<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Enrollment extends Model
{
    protected $fillable = [
        'training_session_id', 
        'user_id', 
        'attended_at', 
        'status'
    ];

    public function trainingSession() 
    { 
        return $this->belongsTo(TrainingSession::class); 
    }
    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
}
