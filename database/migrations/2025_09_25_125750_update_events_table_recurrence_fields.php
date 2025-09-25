<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Change recurrence_week to signed integer to support negative values (like -1 for last week)
            $table->integer('recurrence_week')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Revert back to unsignedTinyInteger
            $table->unsignedTinyInteger('recurrence_week')->nullable()->change();
        });
    }
};