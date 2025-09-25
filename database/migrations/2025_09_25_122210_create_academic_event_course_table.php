<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('academic_event_course', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_event_id')
                ->constrained('academic_events')
                ->cascadeOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['academic_event_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_event_course');
    }
};
