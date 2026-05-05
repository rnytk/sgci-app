<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingSession extends Model
{
    protected $fillable = [
        'training_id', 'trainer_id', 'scheduled_at', 
        'meeting_link', 'require_exam', 'issue_certificate', 
        'exam_enabled', 'diploma_bg'
    ];

    public function training(): BelongsTo 
    { 
        return $this->belongsTo(Training::class); 
    }

    public function trainer(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'trainer_id'); 
    }
}


