<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Attendance statuses catalog
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. 'present', 'late', 'absent_justified'
            $table->string('name');           // Human-readable, can be localized in UI
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_justified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_statuses');
    }
};
