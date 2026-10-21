<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->string('modality')->default('in_person')->after('trainer_id');
            $table->dateTime('ends_at')->nullable()->after('scheduled_at');
            $table->string('location')->nullable()->after('meeting_link');
            $table->boolean('requires_attendance')->default(false)->after('require_exam');
            $table->decimal('passing_score', 5, 2)->default(70)->after('requires_attendance');
            $table->unsignedSmallInteger('max_attempts')->default(1)->after('passing_score');
        });

        // Preserve the historical default while snapshotting the exam rule when it exists.
        DB::table('training_sessions')
            ->select(['id', 'training_id'])
            ->orderBy('id')
            ->get()
            ->each(function (object $session): void {
                $passingScore = DB::table('exams')
                    ->where('training_id', $session->training_id)
                    ->value('passing_score');

                if ($passingScore !== null) {
                    DB::table('training_sessions')
                        ->where('id', $session->id)
                        ->update(['passing_score' => $passingScore]);
                }
            });

        Schema::table('enrollments', function (Blueprint $table): void {
            $table->timestamp('assigned_at')->nullable()->after('user_id');
            $table->timestamp('started_at')->nullable()->after('assigned_at');
            $table->timestamp('completed_at')->nullable()->after('attended_at');
            $table->string('attendance_source')->nullable()->after('attended_at');
            $table->string('absence_reason')->nullable()->after('status');
            $table->unique(['training_session_id', 'user_id'], 'enrollments_session_user_unique');
        });

        DB::table('enrollments')
            ->where('status', 'enrolled')
            ->update(['status' => 'assigned']);

        DB::table('enrollments')
            ->whereNull('assigned_at')
            ->update(['assigned_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropUnique('enrollments_session_user_unique');
            $table->dropColumn([
                'assigned_at',
                'started_at',
                'completed_at',
                'attendance_source',
                'absence_reason',
            ]);
        });

        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->dropColumn([
                'modality',
                'ends_at',
                'location',
                'requires_attendance',
                'passing_score',
                'max_attempts',
            ]);
        });
    }
};
