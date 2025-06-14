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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_shift_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('previous_course_id')->nullable();
            $table->unsignedTinyInteger('number');
            $table->string('letter', 1);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Add foreign key for previous_course_id
            $table->foreign('previous_course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('set null');

            // Add indexes for school-related fields
            $table->index(['school_id', 'school_level_id', 'active']);
            $table->index(['school_id', 'school_shift_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
}; 