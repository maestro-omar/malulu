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
    Schema::create('user_diagnosis', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->foreignId('diagnosis_id')->constrained('diagnoses')->onDelete('cascade');
      $table->date('diagnosed_at')->nullable(); // fecha de diagnóstico
      $table->text('notes')->nullable(); // notas médicas, observaciones
      $table->timestamps();
      $table->softDeletes(); // permite dar de baja diagnósticos sin perder historial
    });
  } 

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_diagnosis');
  }
};
