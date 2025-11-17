<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Recurrent events
        Schema::table('recurrent_events', function (Blueprint $table) {
            $table->unsignedTinyInteger('non_working_type')->default(0)->after('school_id');
        });

        DB::table('recurrent_events')->update([
            'non_working_type' => DB::raw('CASE WHEN is_non_working_day = 1 THEN 1 ELSE 0 END')
        ]);

        Schema::table('recurrent_events', function (Blueprint $table) {
            $table->dropColumn('is_non_working_day');
        });

        // Academic events
        Schema::table('academic_events', function (Blueprint $table) {
            $table->unsignedTinyInteger('non_working_type')->default(0)->after('school_id');
        });

        DB::table('academic_events')->update([
            'non_working_type' => DB::raw('CASE WHEN is_non_working_day = 1 THEN 1 ELSE 0 END')
        ]);

        Schema::table('academic_events', function (Blueprint $table) {
            $table->dropColumn('is_non_working_day');
        });
    }

    public function down(): void
    {
        // Recurrent events
        Schema::table('recurrent_events', function (Blueprint $table) {
            $table->boolean('is_non_working_day')->default(false)->after('school_id');
        });

        DB::table('recurrent_events')->update([
            'is_non_working_day' => DB::raw('CASE WHEN non_working_type > 0 THEN 1 ELSE 0 END')
        ]);

        Schema::table('recurrent_events', function (Blueprint $table) {
            $table->dropColumn('non_working_type');
        });

        // Academic events
        Schema::table('academic_events', function (Blueprint $table) {
            $table->boolean('is_non_working_day')->default(false)->after('school_id');
        });

        DB::table('academic_events')->update([
            'is_non_working_day' => DB::raw('CASE WHEN non_working_type > 0 THEN 1 ELSE 0 END')
        ]);

        Schema::table('academic_events', function (Blueprint $table) {
            $table->dropColumn('non_working_type');
        });
    }
};



