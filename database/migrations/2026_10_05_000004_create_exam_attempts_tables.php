<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('attempt_number');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('passed')->nullable();
            $table->string('status')->default('in_progress');
            $table->timestamps();

            $table->unique(['enrollment_id', 'attempt_number'], 'exam_attempts_enrollment_number_unique');
            $table->index(['enrollment_id', 'status']);
        });

        Schema::create('exam_attempt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->json('answer')->nullable();
            $table->text('answer_text')->nullable();
            $table->decimal('points_awarded', 5, 2)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('auto_graded')->default(false);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id'], 'exam_attempt_answers_attempt_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_answers');
        Schema::dropIfExists('exam_attempts');
    }
};
