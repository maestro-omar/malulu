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
use App\Models\ClassSubject;
use App\Models\Course;
use App\Models\RoleRelationship;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FakeUsersSeeder extends Seeder
{
    const DEFAULT_CUE = '740058000'; //LUCIO_LUCERO
    const OTHER_SCHOOLS_LIMIT = 1;
    const FAST_TEST = true;
    private $mathSubject;
    private $course;

    public function __construct()
    {
        // Get required IDs
        $this->mathSubject = ClassSubject::where('short_name', 'MAT')->first();
        $this->course = Course::first();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!$this->mathSubject) {
            $this->command->error('Mathematics subject not found. Please run ClassSubjectSeeder first.');
            return;
        }

        if (!$this->course) {
            $this->command->error('No courses found. Please run CourseSeeder first.');
            return;
        }

        $faker = Faker::create('es_ES'); // Using Spanish locale for more realistic names

        // Create users for default school (740058000)
        $defaultSchool = School::where('code', self::DEFAULT_CUE)->first();
        if (!$defaultSchool) {
            throw new \Exception('School with code 740058000 not found. Please run SchoolSeeder first.');
        }

        // Get default province (San Luis) and country (Argentina)
        $province = Province::where('code', Province::DEFAULT)->first();
        $country = Country::where('iso', Country::DEFAULT)->first();

        if (!$province || !$country) {
            throw new \Exception('Default province or country not found. Please run ProvinceSeeder and CountrySeeder first.');
        }

        $guardianRole = Role::where('code', 'guardian')->first();

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
        $adminRole = Role::where('code', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        // Create users with specific counts
        $this->createUsersForSchool($defaultSchool, [
            'director' => 1,
            'regent' => 2,
            'secretary' => self::FAST_TEST ? 1 : 3,
            'grade_teacher' => self::FAST_TEST ? 2 : 30,
            'assistant_teacher' => self::FAST_TEST ? 2 : 20,
            'curricular_teacher' => self::FAST_TEST ? 2 : 20,
            'special_teacher' => self::FAST_TEST ? 2 : 4,
            'professor' => self::FAST_TEST ? 2 : 20,
            'class_assistant' => self::FAST_TEST ? 2 : 6,
            'librarian' => 2,
            'guardian' => self::FAST_TEST ? 20 : 200,
            'student' => self::FAST_TEST ? 40 : 1000,
            'cooperative' => self::FAST_TEST ? 2 : 10,
            'former_student' => self::FAST_TEST ? 2 : 20,
        ], $province, $country);

        // Create a teacher who is also a guardian in the same school
        $teacherGuardian = User::whereHas('roles', function ($query) {
            $query->where('code', 'grade_teacher');
        })->first();

        if ($teacherGuardian) {
            // Find a student in the same school
            $student = User::whereHas('roles', function ($query) {
                $query->where('code', 'student');
            })->whereHas('roleRelationships', function ($query) use ($defaultSchool) {
                $query->where('school_id', $defaultSchool->id);
            })->first();

            if ($student) {
                // Create role relationship for guardian role
                $roleRelationship = [
                    'user_id' => $teacherGuardian->id,
                    'role_id' => $guardianRole->id,
                    'school_id' => $defaultSchool->id,
                    'start_date' => Carbon::now()->subYears(2),
                    'end_date' => null,
                    'end_reason_id' => null,
                    'notes' => "Auto-generated guardian relationship for teacher",
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                $roleRelationshipId = DB::table('role_relationships')->insertGetId($roleRelationship);

                // Create guardian relationship
                DB::table('guardian_relationships')->insert([
                    'role_relationship_id' => $roleRelationshipId,
                    'student_id' => $student->id,
                    'relationship_type' => RoleRelationship::RELATIONSHIP_FATHER,
                    'is_emergency_contact' => true,
                    'is_restricted' => false,
                    'emergency_contact_priority' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $this->command->info('teacher and guardian: ' . $teacherGuardian->id);
            } else {
                throw new \Exception('Cant find student to by a child.');
            }
        } else {
            throw new \Exception('Cant find guardian to assign a child.');
        }

        // Make cooperative members also guardians
        $cooperativeUsers = User::whereHas('roles', function ($query) {
            $query->where('code', 'cooperative');
        })->get();

        foreach ($cooperativeUsers as $user) {
            $user->assignRole($guardianRole);
        }

        // Make 3 grade teachers also guardians
        $gradeTeachers = User::whereHas('roles', function ($query) {
            $query->where('code', 'grade_teacher');
        })->take(3)->get();
        foreach ($gradeTeachers as $user) {
            $user->assignRole($guardianRole);
        }

        // Make one teacher from default school also be a guardian in another school
        $teacherFromDefaultSchool = User::whereHas('roles', function ($query) {
            $query->where('code', 'grade_teacher');
        })->first();

        if ($teacherFromDefaultSchool) {
            // Get a random school that's not the default school
            $otherSchool = School::where('code', '!=', self::DEFAULT_CUE)->inRandomOrder()->first();
            if ($otherSchool) {
                // Set the team ID for the other school
                app(PermissionRegistrar::class)->setPermissionsTeamId($otherSchool->id);
                // Assign guardian role in the other school
                $teacherFromDefaultSchool->assignRole($guardianRole);
            }
        }

        // Create users for other schools
        $otherSchools = School::where('code', '!=', self::DEFAULT_CUE)->limit(self::OTHER_SCHOOLS_LIMIT)->get();
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

        foreach ($roleCounts as $roleCode => $count) {
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
                $birthdate = $this->getBirthdateForRole($roleCode, $faker);

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
                    ['email' => "{$roleCode}_{$school->code}_{$i}@example.com"],
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

                // Get the role by code
                $role = Role::where('code', $roleCode)->first();
                if ($role) {
                    $user->assignRole($role);

                    // Create role relationship
                    $roleRelationship = [
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'school_id' => $school->id,
                        'start_date' => Carbon::now()->subYears($faker->numberBetween(1, 5)),
                        'end_date' => null,
                        'end_reason_id' => null,
                        'notes' => "Auto-generated {$roleCode} relationship",
                        'created_by' => 1,
                        'updated_by' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];

                    $roleRelationshipId = DB::table('role_relationships')->insertGetId($roleRelationship);

                    // Create specific relationship based on role type
                    switch ($roleCode) {
                        case 'grade_teacher':
                        case 'assistant_teacher':
                        case 'curricular_teacher':
                        case 'special_teacher':
                        case 'professor':
                            DB::table('worker_relationships')->insert([
                                'role_relationship_id' => $roleRelationshipId,
                                'class_subject_id' => $this->mathSubject->id,
                                'job_status' => $faker->randomElement($this->jobStatuses()),
                                'job_status_date' => Carbon::now()->subYears($faker->numberBetween(1, 5)),
                                'decree_number' => 'DEC-' . date('Y') . '-' . str_pad($faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
                                'degree_title' => $faker->randomElement([
                                    'Master in Mathematics',
                                    'Bachelor in Education',
                                    'Master in Education',
                                    'PhD in Education'
                                ]),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                            break;

                        case 'student':
                            DB::table('student_relationships')->insert([
                                'role_relationship_id' => $roleRelationshipId,
                                'current_course_id' => $this->course->id,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                            break;

                        case 'guardian':
                            // Find a random student to link this guardian to
                            $student = User::whereHas('roles', function ($query) {
                                $query->where('code', 'student');
                            })->inRandomOrder()->first();

                            if ($student) {
                                // Create guardian relationship
                                DB::table('guardian_relationships')->insert([
                                    'role_relationship_id' => $roleRelationshipId,
                                    'student_id' => $student->id,
                                    'relationship_type' => $faker->randomElement($this->relationshipTypes()),
                                    'is_emergency_contact' => $faker->boolean(70),
                                    'is_restricted' => $faker->boolean(10),
                                    'emergency_contact_priority' => $faker->numberBetween(1, 3),
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                ]);

                                // 30% chance to add a second student relationship
                                if ($faker->boolean(30)) {
                                    $secondStudent = User::whereHas('roles', function ($query) {
                                        $query->where('code', 'student');
                                    })->where('id', '!=', $student->id)->inRandomOrder()->first();

                                    if ($secondStudent) {
                                        DB::table('guardian_relationships')->insert([
                                            'role_relationship_id' => $roleRelationshipId,
                                            'student_id' => $secondStudent->id,
                                            'relationship_type' => $faker->randomElement($this->relationshipTypes()),
                                            'is_emergency_contact' => $faker->boolean(70),
                                            'is_restricted' => $faker->boolean(10),
                                            'emergency_contact_priority' => $faker->numberBetween(1, 3),
                                            'created_at' => Carbon::now(),
                                            'updated_at' => Carbon::now(),
                                        ]);
                                    }
                                }
                            } else {
                                // If no student is found, create a relationship with a random user
                                $randomUser = User::inRandomOrder()->first();
                                if ($randomUser) {
                                    DB::table('guardian_relationships')->insert([
                                        'role_relationship_id' => $roleRelationshipId,
                                        'student_id' => $randomUser->id,
                                        'relationship_type' => $faker->randomElement($this->relationshipTypes()),
                                        'is_emergency_contact' => $faker->boolean(70),
                                        'is_restricted' => $faker->boolean(10),
                                        'emergency_contact_priority' => $faker->numberBetween(1, 3),
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ]);
                                }
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * Get appropriate birthdate range based on role
     */
    private function getBirthdateForRole(string $roleCode, $faker): \DateTime
    {
        return match ($roleCode) {
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

    private function relationshipTypes(): array
    {
        static $types;
        if (empty($types))
            $types = array_keys(RoleRelationship::relationshipTypes());
        return $types;
    }

    private function jobStatuses(): array
    {
        static $types;
        if (empty($types))
            $types = array_keys(RoleRelationship::jobStatuses());
        return $types;
    }
}
