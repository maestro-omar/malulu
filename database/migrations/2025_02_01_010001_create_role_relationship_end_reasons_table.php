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
        Schema::create('role_relationship_end_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Unique identifier for the reason
            $table->string('name'); // Human-readable name
            $table->text('description')->nullable(); // Detailed description
            $table->json('applicable_roles'); // Array of role IDs this reason applies to
            $table->boolean('is_active')->default(true); // Whether this reason is currently active
            $table->text('notes')->nullable(); // Additional notes or context
            $table->timestamps();
            $table->softDeletes();
        });

        // Add foreign key to role_relationships table
        Schema::table('role_relationships', function (Blueprint $table) {
            $table->foreignId('end_reason_id')->nullable()->constrained('role_relationship_end_reasons')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_relationships', function (Blueprint $table) {
            $table->dropForeign(['end_reason_id']);
            $table->dropColumn('end_reason_id');
        });

        Schema::dropIfExists('role_relationship_end_reasons');
    }
}; 