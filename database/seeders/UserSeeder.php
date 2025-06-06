<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES'); // Using Spanish locale for more realistic names

        // Create admin users
        for ($i = 1; $i <= 3; $i++) {
            User::firstOrCreate(
                ['email' => "admin{$i}@example.com"],
                [
                    'name' => $faker->name(),
                    'password' => Hash::make('password'),
                ]
            )->assignRole('admin');
        }

        // Create director users
        for ($i = 1; $i <= 4; $i++) {
            User::firstOrCreate(
                ['email' => "director{$i}@example.com"],
                [
                    'name' => $faker->name(),
                    'password' => Hash::make('password'),
                ]
            )->assignRole('director');
        }

        // Create teacher users
        for ($i = 1; $i <= 6; $i++) {
            User::firstOrCreate(
                ['email' => "teacher{$i}@example.com"],
                [
                    'name' => $faker->name(),
                    'password' => Hash::make('password'),
                ]
            )->assignRole('teacher');
        }

        // Create student users
        for ($i = 1; $i <= 7; $i++) {
            User::firstOrCreate(
                ['email' => "student{$i}@example.com"],
                [
                    'name' => $faker->name(),
                    'password' => Hash::make('password'),
                ]
            )->assignRole('student');
        }

        // Soft delete some random users (not admins)
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->inRandomOrder()->take(5)->get();

        foreach ($users as $user) {
            $user->delete();
        }
    }
}
