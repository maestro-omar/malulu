<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('academic_events', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g. "Inicio del ciclo lectivo"
            $table->date('date');
            $table->boolean('is_non_working_day')->default(false); // optional, e.g. exam days aren’t necessarily no-class days
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);

            // Scope
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('event_type_id')->constrained('event_types');

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
