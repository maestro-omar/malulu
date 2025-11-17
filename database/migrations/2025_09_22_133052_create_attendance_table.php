<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();

            // Who is being marked
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Date of attendance (no time part, just the day)
            $table->date('date');

            // Status FK
            $table->foreignId('status_id')->nullable()->constrained('attendance_statuses');

            // Course FK (optional - for course-specific attendance)
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null');
            $table->index('course_id');

            // Optional justification file (DNI scan, medical certificate, etc.)
            $table->foreignId('file_id')->nullable()->constrained('files')->onDelete('set null');

            // Audit: who created/updated
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();

            // Ensure unique record per user/date
            $table->unique(['user_id', 'date']);

            // Performance indexes for queries that will become slower as table grows (130k+ rows per year)
            // Index on date for date range queries (whereBetween, whereIn with dates)
            $table->index('date');
            
            // Index on status_id for joins with attendance_statuses table
            $table->index('status_id');
            
            // Composite index for course-specific date queries
            // Optimizes: where('course_id', X)->where('date', Y) and date ranges
            $table->index(['course_id', 'date']);
            
            // Composite index for date-first queries with user filtering
            // Complements the existing unique index on (user_id, date)
            // Optimizes: whereBetween('date', ...)->whereIn('user_id', ...)
            $table->index(['date', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
