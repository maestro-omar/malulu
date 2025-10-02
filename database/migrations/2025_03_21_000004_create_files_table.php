<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subtype_id')->unsigned()->constrained('file_subtypes')->onDelete('cascade');
            $table->foreignId('user_id')->unsigned()->constrained('users')->onDelete('cascade'); //created by
            $table->foreignId('replaced_by_id')->nullable()->unsigned()->constrained('files')->onDelete('set null');
            // Direct foreign key relationships
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            
            // Add foreign key constraints for direct relationships
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('target_user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('nice_name');
            $table->text('description')->nullable();
            $table->date('valid_from')->nullable(); // fecha de inicio de validez
            $table->date('valid_until')->nullable(); // fecha de expiración

            // Uploaded file fields
            $table->string('original_name')->nullable();
            $table->string('filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->string('path')->nullable();
            $table->json('metadata')->nullable();
            
            // External file field (link)
            $table->string('external_url')->nullable();
            
            
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('files');
    }
};
