<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateExam = DB::table('exams')
            ->select('training_id')
            ->groupBy('training_id')
            ->havingRaw('count(*) > 1')
            ->exists();

        $duplicateAttempt = DB::table('exam_attempts')
            ->select('enrollment_id')
            ->groupBy('enrollment_id')
            ->havingRaw('count(*) > 1')
            ->exists();

        if ($duplicateExam || $duplicateAttempt) {
            throw new LogicException(
                'No se pueden aplicar las restricciones de unicidad porque existen evaluaciones o intentos duplicados. Revisa los datos históricos antes de continuar.',
            );
        }

        Schema::table('exams', function (Blueprint $table): void {
            $table->unique('training_id');
        });

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->unique('enrollment_id');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropUnique(['enrollment_id']);
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropUnique(['training_id']);
        });
    }
};
