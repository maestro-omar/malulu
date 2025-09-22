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
            $table->foreignId('status_id')->constrained('attendance_statuses');

            // Optional justification file (DNI scan, medical certificate, etc.)
            $table->foreignId('file_id')->nullable()->constrained('files')->onDelete('set null');

            // Audit: who created/updated
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();

            // Ensure unique record per user/date
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
