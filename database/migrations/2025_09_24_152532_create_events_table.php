<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date')->nullable(); // still useful for fixed-date events
            $table->boolean('is_recurring')->default(false);
        
            // Recurrence rules
            $table->unsignedTinyInteger('recurrence_month')->nullable();   // 1–12
            $table->unsignedTinyInteger('recurrence_week')->nullable();    // 1 = first, 2 = second, etc.
            $table->unsignedTinyInteger('recurrence_weekday')->nullable(); // 0=Sunday … 6=Saturday
            
            $table->foreignId('event_type_id')->constrained('event_types');

            // Optional scope
            $table->foreignId('province_id')->nullable()->constrained('provinces');
            $table->foreignId('school_id')->nullable()->constrained('schools');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years');

            $table->boolean('is_non_working_day')->default(false); // true = holiday, no classes
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
