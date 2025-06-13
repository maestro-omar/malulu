<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Role;
use App\Models\Province;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class FakeUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES'); // Using Spanish locale for more realistic names
        $school = School::where('key', '740058000')->first();

        if (!$school) {
            throw new \Exception('School with key 740058000 not found. Please run SchoolSeeder first.');
        }

        // Get default province (San Luis) and country (Argentina)
        $province = Province::where('key', Province::DEFAULT)->first();
        $country = Country::where('iso', Country::DEFAULT)->first();

        if (!$province || !$country) {
            throw new \Exception('Default province or country not found. Please run ProvinceSeeder and CountrySeeder first.');
        }

        // Create one user for each role type
        $roles = [
            'director' => 'Director',
            'regent' => 'Regente',
            'secretary' => 'Secretario',
            'professor' => 'Profesor',
            'class_assistant' => 'Preceptor',
            'grade_teacher' => 'Maestra/o de Grado',
            'assistant_teacher' => 'Maestra/o Auxiliar',
            'curricular_teacher' => 'Profesor Curricular',
            'special_teacher' => 'Profesor/a Especial',
            'librarian' => 'Bibliotecario/a',
            'guardian' => 'Responsable',
            'student' => 'Estudiante',
            'cooperative' => 'Cooperadora',
            'former_student' => 'Ex-Estudiante'
        ];

        foreach ($roles as $roleKey => $roleName) {
            $user = User::firstOrCreate(
                ['email' => "{$roleKey}@example.com"],
                [
                    'name' => $faker->name(),
                    'firstname' => $faker->firstName(),
                    'lastname' => $faker->lastName(),
                    'id_number' => $faker->dni(),
                    'birthdate' => $faker->dateTimeBetween('-60 years', '-18 years'),
                    'phone' => $faker->phoneNumber(),
                    'address' => $faker->streetAddress(),
                    'locality' => $faker->city(),
                    'province_id' => $province->id,
                    'country_id' => $country->id,
                    'nationality' => $faker->boolean(80) ? 'Argentina' : $faker->country(),
                    'password' => Hash::make('password'),
                ]
            );

            // Get the role by guard_name
            $role = Role::where('guard_name', $roleKey)->first();
            if ($role) {
                // Assign role with team_id (school)
                $user->assignRole($role, $school->id);
            }
        }

        // Create additional random users for testing
        for ($i = 1; $i <= 20; $i++) {
            $user = User::firstOrCreate(
                ['email' => "random{$i}@example.com"],
                [
                    'name' => $faker->name(),
                    'firstname' => $faker->firstName(),
                    'lastname' => $faker->lastName(),
                    'id_number' => $faker->dni(),
                    'birthdate' => $faker->dateTimeBetween('-60 years', '-18 years'),
                    'phone' => $faker->phoneNumber(),
                    'address' => $faker->streetAddress(),
                    'locality' => $faker->city(),
                    'province_id' => $province->id,
                    'country_id' => $country->id,
                    'nationality' => $faker->boolean(80) ? 'Argentina' : $faker->country(),
                    'password' => Hash::make('password'),
                ]
            );

            // Randomly assign a role from the list
            $randomRoleKey = array_rand($roles);
            $role = Role::where('guard_name', $randomRoleKey)->first();
            if ($role) {
                $user->assignRole($role, $school->id);
            }
        }

        // Soft delete some random users (not admins)
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('guard_name', 'admin');
        })->inRandomOrder()->take(5)->get();

        foreach ($users as $user) {
            $user->delete();
        }
    }
}
