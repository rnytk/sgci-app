<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agency_id')
                ->nullable()
                ->after('password')
                ->constrained('agencies')
                ->nullOnDelete();
                
            $table->foreignId('job_position_id')
                ->nullable()
                ->after('agency_id')
                ->constrained('job_positions')
                ->nullOnDelete();

            $table->boolean('is_active')
                ->default(true)
                ->after('job_position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
