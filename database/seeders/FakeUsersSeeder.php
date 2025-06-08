<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Spatie\Permission\PermissionRegistrar;

class FakeUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES'); // Using Spanish locale for more realistic names
        
        // Create users for default school (740058000)
        $defaultSchool = School::where('key', '740058000')->first();
        if (!$defaultSchool) {
            throw new \Exception('School with key 740058000 not found. Please run SchoolSeeder first.');
        }

        // Set the team ID globally for default school
        app(PermissionRegistrar::class)->setPermissionsTeamId($defaultSchool->id);

        // Create users with specific counts
        $this->createUsersForSchool($defaultSchool, [
            'admin' => 1,
            'director' => 1,
            'regent' => 2,
            'secretary' => 3,
            'grade_teacher' => 30,
            'assistant_teacher' => 20,
            'curricular_teacher' => 20,
            'special_teacher' => 4,
            'professor' => 20,
            'class_assistant' => 6,
            'librarian' => 2,
            'guardian' => 200,
            'student' => 1000,
            'cooperative' => 10,
            'former_student' => 20,
        ]);

        // Make cooperative members also guardians
        $cooperativeUsers = User::whereHas('roles', function ($query) {
            $query->where('key', 'cooperative');
        })->get();
        $guardianRole = Role::where('key', 'guardian')->first();
        foreach ($cooperativeUsers as $user) {
            $user->assignRole($guardianRole);
        }

        // Make 3 grade teachers also guardians
        $gradeTeachers = User::whereHas('roles', function ($query) {
            $query->where('key', 'grade_teacher');
        })->take(3)->get();
        foreach ($gradeTeachers as $user) {
            $user->assignRole($guardianRole);
        }

        // Create users for other schools
        $otherSchools = School::where('key', '!=', '740058000')->get();
        $availableRoles = [
            'regent',
            'secretary',
            'grade_teacher',
            'assistant_teacher',
            'curricular_teacher',
            'special_teacher',
            'professor',
            'class_assistant',
            'librarian',
            'guardian',
            'student',
            'cooperative',
            'former_student'
        ];

        foreach ($otherSchools as $school) {
            // Set the team ID globally for this school
            app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

            // Create director
            $this->createUsersForSchool($school, [
                'director' => 1
            ]);

            // Create 2 random roles
            $randomRoles = array_rand(array_flip($availableRoles), 2);
            foreach ($randomRoles as $role) {
                $this->createUsersForSchool($school, [
                    $role => 1
                ]);
            }
        }
    }

    /**
     * Create users for a specific school with given role counts
     */
    private function createUsersForSchool(School $school, array $roleCounts): void
    {
        $faker = Faker::create('es_ES');

        foreach ($roleCounts as $roleKey => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $user = User::firstOrCreate(
                    ['email' => "{$roleKey}_{$school->key}_{$i}@example.com"],
                    [
                        'name' => $faker->name(),
                        'password' => Hash::make('password'),
                    ]
                );

                // Get the role by key
                $role = Role::where('key', $roleKey)->first();
                if ($role) {
                    $user->assignRole($role);
                }
            }
        }
    }
}
