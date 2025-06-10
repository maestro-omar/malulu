<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('file_subtypes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_type_id')->unsigned()->constrained('file_types')->onDelete('cascade');
            $table->string('key', 31)->unique();
            $table->string('name', 63);
            $table->text('description')->nullable();
            $table->boolean('new_overwrites')->default(false);
            $table->boolean('hidden_for_familiy')->default(false);
            $table->boolean('upload_by_familiy')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_subtypes');
    }
};