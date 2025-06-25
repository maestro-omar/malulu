<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_relationship_id');
            $table->unsignedBigInteger('course_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('end_reason_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('role_relationship_id')->references('id')->on('role_relationships');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->foreign('end_reason_id')->references('id')->on('student_course_end_reasons');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_courses');
    }
};