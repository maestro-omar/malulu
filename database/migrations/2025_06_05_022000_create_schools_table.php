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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('short')->nullable();
            $table->string('slug')->unique();
            $table->string('cue')->nullable()->unique();
            $table->string('logo')->nullable();
            $table->string('picture')->nullable();
            $table->foreignId('locality_id')->constrained();
            $table->string('address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('coordinates')->nullable();
            $table->foreignId('management_type_id')->nullable()->constrained('school_management_types');
            $table->json('social')->nullable();
            $table->json('extra')->nullable();
            $table->text('announcements')->nullable();
            $table->text('relevant_information')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
