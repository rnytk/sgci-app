<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'exam_id',
        'question_text',
        'type',
        'points',
    ];
    public function exam() 
    { 
        return $this->belongsTo(Exam::class); 
    }

    public function questionOptions() 
    { 
        return $this->hasMany(QuestionOption::class); 
    }
}
