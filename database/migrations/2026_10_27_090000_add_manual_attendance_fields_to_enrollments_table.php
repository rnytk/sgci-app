<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('attendance_status')->default('pendiente')->index()->after('attended_at');
            $table->text('attendance_justification')->nullable()->after('attendance_status');
            $table->timestamp('attendance_recorded_at')->nullable()->after('attendance_justification');
            $table->foreignId('attendance_recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('attendance_recorded_at');
        });

        DB::table('enrollments')
            ->whereNotNull('attended_at')
            ->update([
                'attendance_status' => 'presente',
                'attendance_recorded_at' => DB::raw('attended_at'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['attendance_recorded_by']);
            $table->dropIndex(['attendance_status']);
            $table->dropColumn([
                'attendance_recorded_by',
                'attendance_recorded_at',
                'attendance_justification',
                'attendance_status',
            ]);
        });
    }
};
