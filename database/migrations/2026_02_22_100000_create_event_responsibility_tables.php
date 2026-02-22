<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_responsibility_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('academic_event_responsibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_event_id')->constrained('academic_events')->onDelete('cascade');
            $table->foreignId('role_relationship_id')->constrained('role_relationships')->onDelete('cascade');
            $table->foreignId('event_responsibility_type_id')->constrained('event_responsibility_types')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['academic_event_id', 'role_relationship_id', 'event_responsibility_type_id'],
                'academic_event_responsibles_event_role_type_unique'
            );
            $table->index('academic_event_id');
            $table->index('role_relationship_id');
            $table->index('event_responsibility_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_event_responsibles');
        Schema::dropIfExists('event_responsibility_types');
    }
};
