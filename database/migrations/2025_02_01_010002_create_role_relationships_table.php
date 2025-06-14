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
        Schema::create('role_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The user who has this role
            $table->foreignId('role_id')->constrained()->onDelete('cascade'); // The role type (teacher, student, etc.)
            $table->foreignId('school_id')->constrained()->onDelete('cascade'); // The school where this role applies

            // Relationship lifecycle
            $table->date('start_date'); // When the role relationship begins
            $table->date('end_date')->nullable(); // When the role relationship ends (null if ongoing)
            $table->foreignId('end_reason_id')->nullable()->constrained('role_relationship_end_reasons')->nullOnDelete(); // Why the relationship ended
            $table->text('notes')->nullable(); // Additional notes or comments

            // Custom fields for school-specific or experimental data
            // Use this field only for:
            // 1. School-specific customizations
            // 2. Temporary or experimental features
            // 3. Data that doesn't warrant a schema change
            // 4. Truly dynamic data that can't be predicted
            // DO NOT use for:
            // 1. Core role functionality
            // 2. Data that should be queryable
            // 3. Data that needs validation
            // 4. Data that needs indexing
            $table->json('custom_fields')->nullable();

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users'); // User who created this relationship
            $table->foreignId('updated_by')->nullable()->constrained('users'); // User who last updated this relationship
            $table->foreignId('deleted_by')->nullable()->constrained('users'); // User who deleted this relationship
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'school_id', 'role_id']);
            $table->index('start_date');
        });

        // Teacher relationships
        Schema::create('teacher_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_relationship_id')->constrained()->onDelete('cascade'); // Link to the main role relationship
            $table->foreignId('class_subject_id')->constrained()->onDelete('cascade'); // Link to the subject
            $table->string('job_status'); // Employment status (titular, interino, suplente)
            $table->date('job_status_date')->nullable(); // Employment status date
            $table->string('decree_number')->nullable(); // Decree number and year that assings to this job 
            $table->foreignId('decree_file_id')->nullable()->constrained()->onDelete('cascade'); // Link to the file
            $table->json('schedule')->nullable();
            $table->string('degree_title'); // Academic degree title
            $table->timestamps();
            $table->softDeletes();
        });

        // Guardian relationships
        Schema::create('guardian_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_relationship_id')->constrained()->onDelete('cascade'); // Link to the main role relationship
            $table->foreignId('student_id')->constrained()->onDelete('cascade'); // Relation tu user table
            $table->string('relationship_type'); // Type of relationship (padre, madre, tutor, otro)
            $table->boolean('is_legal')->default(true); // Legal guardian status (legal, no_legal)
            $table->boolean('is_restricted')->default(false); // If there is any legal restriction
            $table->tinyInteger('emergency_contact_priority')->default(1)->unsigned(); // Priority level (1-10)
            $table->timestamps();
            $table->softDeletes();

            // Add constraint for emergency_contact_priority
            $table->check('emergency_contact_priority >= 1 AND emergency_contact_priority <= 10');
        });

        // Student relationships
        Schema::create('student_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_relationship_id')->constrained()->onDelete('cascade'); // Link to the main role relationship
            $table->foreignId('current_course_id')->nullable()->constrained()->onDelete('cascade'); // Link to the current course
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_relationships');
        Schema::dropIfExists('guardian_relationships');
        Schema::dropIfExists('teacher_relationships');
        Schema::dropIfExists('role_relationships');
    }
};
