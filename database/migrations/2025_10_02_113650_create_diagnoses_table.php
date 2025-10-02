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
    Schema::create('diagnoses', function (Blueprint $table) {
      $table->id();
      $table->string('code')->nullable(); // opcional, ej: "F84.0"
      $table->string('name'); // Ej: "Dislexia", "Diabetes tipo 1"
      $table->string('category'); // Ej: "Trastornos del aprendizaje", "Condiciones crónicas", "Discapacidad"
      $table->text('description')->nullable();
      $table->boolean('active')->default(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('diagnoses');
  }
};
