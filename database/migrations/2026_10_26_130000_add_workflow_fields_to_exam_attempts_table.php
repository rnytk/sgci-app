<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->string('status')->default('enviado')->after('enrollment_id');
            $table->decimal('score', 5, 2)->nullable()->change();
            $table->boolean('passed')->nullable()->change();
            $table->timestamp('submitted_at')->nullable()->after('passed');
            $table->timestamp('finalized_at')->nullable()->after('submitted_at');
            $table->foreignId('finalized_by')->nullable()->after('finalized_at')
                ->constrained('users')->nullOnDelete();
        });

        DB::table('exam_attempts')
            ->orderBy('enrollment_id')
            ->orderByDesc('id')
            ->get()
            ->groupBy('enrollment_id')
            ->each(function ($attempts): void {
                $currentAttempt = $attempts->shift();

                DB::table('exam_attempts')
                    ->whereIn('id', $attempts->pluck('id'))
                    ->update(['status' => 'reemplazado']);

                DB::table('exam_attempts')
                    ->where('id', $currentAttempt->id)
                    ->update([
                        'status' => $currentAttempt->passed ? 'aprobado' : 'reprobado',
                        'submitted_at' => $currentAttempt->created_at,
                        'finalized_at' => $currentAttempt->updated_at,
                    ]);
            });

        DB::statement("CREATE UNIQUE INDEX exam_attempts_one_current_per_enrollment ON exam_attempts (enrollment_id) WHERE status <> 'reemplazado'");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS exam_attempts_one_current_per_enrollment');

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('finalized_by');
            $table->dropColumn(['status', 'submitted_at', 'finalized_at']);
            $table->decimal('score', 5, 2)->nullable(false)->change();
            $table->boolean('passed')->nullable(false)->change();
        });
    }
};
