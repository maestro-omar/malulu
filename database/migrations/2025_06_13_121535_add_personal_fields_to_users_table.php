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
        Schema::table('users', function (Blueprint $table) {
            $table->string('firstname')->nullable()->after('name');
            $table->string('lastname')->nullable()->after('firstname');
            $table->string('id_number')->nullable()->after('lastname');
            $table->string('gender')->nullable()->after('id_number');
            $table->date('birthdate')->nullable()->after('gender');
            $table->string('phone')->nullable()->after('birthdate');
            $table->string('address')->nullable()->after('phone');
            $table->string('locality')->nullable()->after('address');
            $table->foreignId('province_id')->nullable()->after('locality')->constrained('provinces');
            $table->foreignId('country_id')->nullable()->after('province_id')->constrained('countries');
            $table->string('nationality')->nullable()->after('country_id');
            $table->string('picture')->nullable()->after('nationality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn([
                'firstname',
                'lastname',
                'id_number',
                'gender',
                'birthdate',
                'phone',
                'address',
                'locality',
                'province_id',
                'country_id',
                'nationality',
                'picture'
            ]);
        });
    }
};
