<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_shift_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['school_id', 'school_shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_shift');
    }
};
