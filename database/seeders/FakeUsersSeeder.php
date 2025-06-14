<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Province;
use App\Models\Country;

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

        // Get default province (San Luis) and country (Argentina)
        $province = Province::where('key', Province::DEFAULT)->first();
        $country = Country::where('iso', Country::DEFAULT)->first();

        if (!$province || !$country) {
            throw new \Exception('Default province or country not found. Please run ProvinceSeeder and CountrySeeder first.');
        }

        // Set the team ID globally for default school
        app(PermissionRegistrar::class)->setPermissionsTeamId($defaultSchool->id);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador del Sistema',
                'firstname' => 'Admin',
                'lastname' => 'Sistema',
                'id_number' => '12345678',
                'birthdate' => '1980-01-01',
                'phone' => '2664123456',
                'address' => 'Av. Illia 123',
                'locality' => 'San Luis',
                'province_id' => $province->id,
                'country_id' => $country->id,
                'nationality' => 'Argentina',
                'password' => Hash::make('password'),
            ]
        );

        // Assign admin role
        $adminRole = Role::where('key', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        // Create users with specific counts
        $this->createUsersForSchool($defaultSchool, [
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
        ], $province, $country);

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

        // Make one teacher from default school also be a guardian in another school
        $teacherFromDefaultSchool = User::whereHas('roles', function ($query) {
            $query->where('key', 'grade_teacher');
        })->first();

        if ($teacherFromDefaultSchool) {
            // Get a random school that's not the default school
            $otherSchool = School::where('key', '!=', '740058000')->inRandomOrder()->first();
            if ($otherSchool) {
                // Set the team ID for the other school
                app(PermissionRegistrar::class)->setPermissionsTeamId($otherSchool->id);
                // Assign guardian role in the other school
                $teacherFromDefaultSchool->assignRole($guardianRole);
            }
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
            ], $province, $country);

            // Create 2 random roles
            $randomRoles = array_rand(array_flip($availableRoles), 2);
            foreach ($randomRoles as $role) {
                $this->createUsersForSchool($school, [
                    $role => 1
                ], $province, $country);
            }
        }
    }

    /**
     * Create users for a specific school with given role counts
     */
    private function createUsersForSchool(School $school, array $roleCounts, Province $province, Country $country): void
    {
        $faker = Faker::create('es_ES');

        // Common localities in San Luis
        $localities = [
            'San Luis',
            'Villa Mercedes',
            'La Punta',
            'Juana Koslay',
            'Potrero de los Funes',
            'El Trapiche',
            'La Toma',
            'Merlo',
            'Concarán',
            'Quines',
            'Santa Rosa del Conlara',
            'Buena Esperanza',
            'San Francisco del Monte de Oro',
            'Luján',
            'Nueva Galia'
        ];

        // Common addresses in San Luis
        $addresses = [
            'Pringles',
            'Av. Juan D. Perón',
            'Av. Illia',
            'Av. Lafinur',
            'Av. España',
            'La Rioja',
            'Riobamba',
            'Mitre',
            'Av. Justo Daract',
            'Av. Juan B. Justo',
            '25 de Mayo',
            '9 de Julio',
            'San Martín',
            'Rivadavia',
            'Colón'
        ];

        foreach ($roleCounts as $roleKey => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $firstName = $faker->firstName();
                $lastName = $faker->lastName();
                $locality = $faker->randomElement($localities);
                $address = $faker->randomElement($addresses) . ' ' . $faker->numberBetween(100, 9999);

                // Generate realistic phone numbers for San Luis
                $phone = '266' . $faker->numberBetween(4000000, 4999999);

                // Generate realistic DNI numbers
                $dni = $faker->numberBetween(10000000, 99999999);

                // Adjust birthdate based on role
                $birthdate = $this->getBirthdateForRole($roleKey, $faker);

                // Set nationality (80% Argentine, 20% other)
                $nationality = $faker->boolean(80) ? 'Argentina' : $faker->randomElement([
                    'Chile',
                    'Bolivia',
                    'Paraguay',
                    'Perú',
                    'Uruguay',
                    'Brasil',
                    'Colombia',
                    'Venezuela'
                ]);

                $user = User::firstOrCreate(
                    ['email' => "{$roleKey}_{$school->key}_{$i}@example.com"],
                    [
                        'name' => $firstName . ' ' . $lastName,
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'id_number' => $dni,
                        'birthdate' => $birthdate,
                        'phone' => $phone,
                        'address' => $address,
                        'locality' => $locality,
                        'province_id' => $province->id,
                        'country_id' => $country->id,
                        'nationality' => $nationality,
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

    /**
     * Get appropriate birthdate range based on role
     */
    private function getBirthdateForRole(string $roleKey, $faker): \DateTime
    {
        return match ($roleKey) {
            'student' => $faker->dateTimeBetween('-18 years', '-5 years'),
            'former_student' => $faker->dateTimeBetween('-30 years', '-19 years'),
            'guardian' => $faker->dateTimeBetween('-60 years', '-25 years'),
            'grade_teacher', 'assistant_teacher', 'curricular_teacher', 'special_teacher', 'professor' =>
            $faker->dateTimeBetween('-65 years', '-25 years'),
            'class_assistant' => $faker->dateTimeBetween('-50 years', '-20 years'),
            'librarian' => $faker->dateTimeBetween('-65 years', '-30 years'),
            'cooperative' => $faker->dateTimeBetween('-70 years', '-30 years'),
            default => $faker->dateTimeBetween('-70 years', '-30 years'), // For director, regent, secretary
        };
    }
}
