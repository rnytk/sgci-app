<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttemptAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'answer',
        'answer_text',
        'points_awarded',
        'is_correct',
        'auto_graded',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'answer' => 'array',
            'points_awarded' => 'decimal:2',
            'is_correct' => 'boolean',
            'auto_graded' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
