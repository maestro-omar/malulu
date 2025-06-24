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
use App\Models\GuardianRelationship;
use App\Models\WorkerRelationship;
use App\Models\RoleRelationship;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SchoolLevel;

class FakeUsersSeeder extends Seeder
{
    const DEFAULT_CUE = '740058000'; //LUCIO_LUCERO
    const OTHER_SCHOOLS_LIMIT = 1;
    const FAST_TEST = true;
    private $mathSubject;
    private $course;
    // private $schoolLevels;
    private $allRoles;
    private $faker;

    public function __construct()
    {
        // Get required IDs
        $this->mathSubject = ClassSubject::where('short_name', 'MAT')->first();
        $this->course = Course::first();
        // $this->schoolLevels = SchoolLevel::all(); // Get all school levels
        $this->allRoles = Role::pluck('id', 'code')->toArray();
        $this->faker = Faker::create('es_ES'); // Using Spanish locale for more realistic names
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SchoolLevel::count() === 0) {
            $this->command->error('No school levels found. Please run SchoolLevelSeeder first.');
            return;
        }

        if (!$this->mathSubject) {
            $this->command->error('Mathematics subject not found. Please run ClassSubjectSeeder first.');
            return;
        }

        if (!$this->course) {
            $this->command->error('No courses found. Please run CourseSeeder first.');
            return;
        }

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

        $guardianRoleId = $this->allRoles[Role::GUARDIAN] ?? null;

        // Set the team ID globally for default school
        app(PermissionRegistrar::class)->setPermissionsTeamId($defaultSchool->id);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@lucio.com'],
            [
                'name' => 'Administrador del Lucio',
                'firstname' => 'Admin',
                'lastname' => 'Lucio luceri',
                'id_number' => '12345678',
                'gender' => 'masc',
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
        $adminRoleId = $this->allRoles[Role::ADMIN] ?? null;
        if (empty($adminRoleId)) dd("Sin role ADMIN");
        $this->assignRoleWithRelationship($admin, $adminRoleId, $defaultSchool, null, Role::ADMIN);

        // Create random users
        $usersToCreate = [
            Role::DIRECTOR => 1,
            Role::REGENT => 2,
            Role::SECRETARY => self::FAST_TEST ? 1 : 3,
            Role::GRADE_TEACHER => self::FAST_TEST ? 2 : 30,
            Role::ASSISTANT_TEACHER => self::FAST_TEST ? 2 : 20,
            Role::CURRICULAR_TEACHER => self::FAST_TEST ? 2 : 20,
            Role::SPECIAL_TEACHER => self::FAST_TEST ? 2 : 4,
            Role::PROFESSOR => self::FAST_TEST ? 2 : 20,
            Role::CLASS_ASSISTANT => self::FAST_TEST ? 2 : 6,
            Role::LIBRARIAN => 2,
            Role::GUARDIAN => self::FAST_TEST ? 20 : 200,
            Role::STUDENT => self::FAST_TEST ? 40 : 1000,
            Role::COOPERATIVE => self::FAST_TEST ? 2 : 10,
            Role::FORMER_STUDENT => self::FAST_TEST ? 2 : 20,
        ];

        // Create users with specific counts
        $this->createUsersForSchool($defaultSchool, $usersToCreate, $province, $country);

        // Create a teacher who is also a guardian in the same school
        $teacherGuardian = User::whereHas('roles', function ($query) {
            $query->where('code', Role::GRADE_TEACHER);
        })->first();

        if ($teacherGuardian) {
            // Find a student in the same school
            $student = User::whereHas('roles', function ($query) {
                $query->where('code', Role::STUDENT);
            })->whereHas('roleRelationships', function ($query) use ($defaultSchool) {
                $query->where('school_id', $defaultSchool->id);
            })->first();

            if ($student) {
                // Create role relationship for guardian role
                $roleRelationship = [
                    'user_id' => $teacherGuardian->id,
                    'role_id' => $guardianRoleId,
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
                    'relationship_type' => GuardianRelationship::RELATIONSHIP_PADRE,
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
            $query->where('code', Role::COOPERATIVE);
        })->get();

        foreach ($cooperativeUsers as $user) {
            $this->assignRoleWithRelationship($user, $guardianRoleId, $defaultSchool, null, Role::COOPERATIVE);
        }

        // Make 3 grade teachers also guardians
        $gradeTeachers = User::whereHas('roles', function ($query) {
            $query->where('code', Role::GRADE_TEACHER);
        })->take(3)->get();
        foreach ($gradeTeachers as $i => $user) {
            $this->assignRoleWithRelationship($user, $guardianRoleId, $defaultSchool, null, Role::GUARDIAN, 'Teacher and guardian '  . $i);
        }

        // Make one teacher from default school also be a guardian in another school
        $teacherFromDefaultSchool = User::whereHas('roles', function ($query) {
            $query->where('code', Role::GRADE_TEACHER);
        })->first();

        if ($teacherFromDefaultSchool) {
            // Get a random school that's not the default school
            $otherSchool = School::where('code', '!=', self::DEFAULT_CUE)->inRandomOrder()->first();
            if ($otherSchool) {
                // Assign guardian role in the other school
                $this->assignRoleWithRelationship($teacherFromDefaultSchool, $guardianRoleId, $otherSchool, null, Role::GUARDIAN, 'Teacher and guardian in other school');
            }
        }

        // Create users for other schools
        $otherSchools = School::where('code', '!=', self::DEFAULT_CUE)->limit(self::OTHER_SCHOOLS_LIMIT)->get();
        $availableRoles = array_diff(Role::allCodes(), [Role::SUPERADMIN, Role::ADMIN]);


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
    {        // Common localities in San Luis
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
                // Assign gender based on random choice, using User constants
                $genderOptions = [
                    \App\Models\User::GENDER_MALE,
                    \App\Models\User::GENDER_FEMALE,
                    \App\Models\User::GENDER_TRANS,
                    \App\Models\User::GENDER_FLUID,
                    \App\Models\User::GENDER_NOBINARY,
                    \App\Models\User::GENDER_OTHER,
                ];
                // 80% masc/fem, 20% other
                $gender = $this->faker->randomElement(
                    $this->faker->boolean(80)
                        ? [\App\Models\User::GENDER_MALE, \App\Models\User::GENDER_FEMALE]
                        : array_slice($genderOptions, 2)
                );

                // Pick firstname based on gender
                if ($gender === \App\Models\User::GENDER_MALE) {
                    $firstName = $this->faker->firstNameMale();
                } elseif ($gender === \App\Models\User::GENDER_FEMALE) {
                    $firstName = $this->faker->firstNameFemale();
                } else {
                    // For other genders, pick randomly from male or female names
                    $firstName = $this->faker->randomElement([
                        $this->faker->firstNameMale(),
                        $this->faker->firstNameFemale()
                    ]);
                }

                $lastName = $this->faker->lastName();
                $locality = $this->faker->randomElement($localities);
                $address = $this->faker->randomElement($addresses) . ' ' . $this->faker->numberBetween(100, 9999);

                // Generate realistic phone numbers for San Luis
                $phone = '266' . $this->faker->numberBetween(4000000, 4999999);

                // Generate realistic DNI numbers
                $dni = $this->faker->numberBetween(10000000, 99999999);

                // Adjust birthdate based on role
                $birthdate = $this->getBirthdateForRole($roleCode);

                // Set nationality (80% Argentine, 20% other)
                $nationality = $this->faker->boolean(80) ? 'Argentina' : $this->faker->randomElement([
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
                        'gender' => $gender,
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
                $roleId = $this->allRoles[$roleCode] ?? null;
                if (empty($roleId)) dd("Sin role $roleCode");
                // Determine if this role should have a school level
                $schoolLevelId = null;
                if (in_array($roleCode, [Role::GRADE_TEACHER, Role::ASSISTANT_TEACHER, Role::CURRICULAR_TEACHER, Role::SPECIAL_TEACHER, Role::PROFESSOR, Role::STUDENT])) {
                    // Get school levels for this school
                    $schoolLevels = $school->schoolLevels;
                    if ($schoolLevels->isNotEmpty()) {
                        $schoolLevelId = $schoolLevels->random()->id;
                    }
                }

                $this->assignRoleWithRelationship($user, $roleId, $school, $schoolLevelId, $roleCode);
            }
        }
    }

    /**
     * Get appropriate birthdate range based on role
     */
    private function getBirthdateForRole(string $roleCode): \DateTime
    {
        return match ($roleCode) {
            Role::STUDENT => $this->faker->dateTimeBetween('-18 years', '-5 years'),
            Role::FORMER_STUDENT => $this->faker->dateTimeBetween('-30 years', '-19 years'),
            Role::GUARDIAN => $this->faker->dateTimeBetween('-60 years', '-25 years'),
            Role::GRADE_TEACHER, Role::ASSISTANT_TEACHER, Role::CURRICULAR_TEACHER, Role::SPECIAL_TEACHER, Role::PROFESSOR => $this->faker->dateTimeBetween('-65 years', '-25 years'),
            Role::CLASS_ASSISTANT => $this->faker->dateTimeBetween('-50 years', '-20 years'),
            Role::LIBRARIAN => $this->faker->dateTimeBetween('-65 years', '-30 years'),
            Role::COOPERATIVE => $this->faker->dateTimeBetween('-70 years', '-30 years'),
            default => $this->faker->dateTimeBetween('-70 years', '-30 years'), // For director, regent, secretary
        };
    }

    private function relationshipTypes(): array
    {
        static $types;
        if (empty($types))
            $types = array_keys(GuardianRelationship::relationshipTypes());
        return $types;
    }

    private function jobStatuses(): array
    {
        static $types;
        if (empty($types))
            $types = array_keys(WorkerRelationship::jobStatuses());
        return $types;
    }

    /**
     * Assigns a role to a user for a specific school and creates the corresponding relationship (worker, student, or guardian).
     */
    private function assignRoleWithRelationship(User $user, int $roleId, School $school, ?int $schoolLevelId, string $roleCode, string $note = ''): void
    {
        $user->assignRoleForSchool($roleId, $school->id);

        // Create role relationship
        $roleRelationship = [
            'user_id' => $user->id,
            'role_id' => $roleId,
            'school_id' => $school->id,
            'school_level_id' => $schoolLevelId,
            'start_date' => Carbon::now()->subYears($this->faker->numberBetween(1, 5)),
            'end_date' => null,
            'end_reason_id' => null,
            'notes' => $note ?: "Auto-generated {$roleCode} relationship",
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $roleRelationshipId = DB::table('role_relationships')->insertGetId($roleRelationship);

        // Create specific relationship based on role type
        switch ($roleCode) {
            case Role::GRADE_TEACHER:
            case Role::ASSISTANT_TEACHER:
            case Role::CURRICULAR_TEACHER:
            case Role::SPECIAL_TEACHER:
            case Role::PROFESSOR:
                DB::table('worker_relationships')->insert([
                    'role_relationship_id' => $roleRelationshipId,
                    'class_subject_id' => $this->mathSubject->id,
                    'job_status' => $this->faker->randomElement($this->jobStatuses()),
                    'job_status_date' => Carbon::now()->subYears($this->faker->numberBetween(1, 5)),
                    'decree_number' => 'DEC-' . date('Y') . '-' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
                    'degree_title' => $this->faker->randomElement([
                        'Master in Mathematics',
                        'Bachelor in Education',
                        'Master in Education',
                        'PhD in Education'
                    ]),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                break;

            case Role::STUDENT:
                DB::table('student_relationships')->insert([
                    'role_relationship_id' => $roleRelationshipId,
                    'current_course_id' => $this->course->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                break;

            case Role::GUARDIAN:
                // Find a random student to link this guardian to
                $student = User::whereHas('roles', function ($query) {
                    $query->where('code', Role::STUDENT);
                })->inRandomOrder()->first();

                if ($student) {
                    // Create guardian relationship
                    DB::table('guardian_relationships')->insert([
                        'role_relationship_id' => $roleRelationshipId,
                        'student_id' => $student->id,
                        'relationship_type' => $this->faker->randomElement($this->relationshipTypes()),
                        'is_emergency_contact' => $this->faker->boolean(70),
                        'is_restricted' => $this->faker->boolean(10),
                        'emergency_contact_priority' => $this->faker->numberBetween(1, 3),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    // 30% chance to add a second student relationship
                    if ($this->faker->boolean(30)) {
                        $secondStudent = User::whereHas('roles', function ($query) {
                            $query->where('code', Role::STUDENT);
                        })->where('id', '!=', $student->id)->inRandomOrder()->first();

                        if ($secondStudent) {
                            DB::table('guardian_relationships')->insert([
                                'role_relationship_id' => $roleRelationshipId,
                                'student_id' => $secondStudent->id,
                                'relationship_type' => $this->faker->randomElement($this->relationshipTypes()),
                                'is_emergency_contact' => $this->faker->boolean(70),
                                'is_restricted' => $this->faker->boolean(10),
                                'emergency_contact_priority' => $this->faker->numberBetween(1, 3),
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
                            'relationship_type' => $this->faker->randomElement($this->relationshipTypes()),
                            'is_emergency_contact' => $this->faker->boolean(70),
                            'is_restricted' => $this->faker->boolean(10),
                            'emergency_contact_priority' => $this->faker->numberBetween(1, 3),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
                break;
        }
    }
}
