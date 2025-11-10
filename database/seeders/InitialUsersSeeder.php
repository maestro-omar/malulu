<?php

namespace Database\Seeders;

use App\Models\Entities\User;
use App\Models\Entities\School;
use App\Models\Catalogs\JobStatus;
use App\Models\Catalogs\Role;
use Illuminate\Database\Seeder;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\ClassSubject;
use App\Models\Catalogs\SchoolShift;
use App\Models\Entities\Course;
use App\Models\Entities\AcademicYear;
use App\Models\Relations\TeacherCourse;
use App\Models\Relations\StudentCourse;
use App\Models\Relations\WorkerRelationship;
use App\Models\Relations\StudentRelationship;
use App\Models\Relations\GuardianRelationship;
use App\Models\Relations\RoleRelationship;
use App\Services\UserService;
use Database\Seeders\Faker\UserFaker;


class InitialUsersSeeder extends Seeder
{
    private bool $deleteExisting = false;
    private $faker;

    private $jsonFolder = 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    private $jsonStaffFileName = 'ce-8_initial_staff.json';
    private $jsonStudentsFileName = 'ce-8_initial_students_with_guardian.json';

    private $userService;

    private $allRoles;

    private $creator;
    private $defaultSchool;
    private $primaryLevel;
    private $courses;
    private $province;
    private $country;
    private $countries;
    private $academicYears;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create('es_ES'); // Using Spanish locale for more realistic names
        $this->faker->addProvider(new UserFaker($this->faker));
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->init();
        if ($this->deleteExisting)
            $this->deleteAllUsersExceptAdmin();
        $this->createInitiaStaffUsers();
        $this->createInitiaStudentsUsers();
    }

    private function init()
    {
        $this->allRoles = Role::pluck('id', 'code')->toArray();
        $this->userService = app(UserService::class);
        $this->creator = User::find(1); // Use admin user as creator

        // Set class properties to avoid repeated database queries
        $this->defaultSchool = School::where('code', School::CUE_LUCIO_LUCERO)->first();
        $this->province = Province::where('code', Province::DEFAULT)->first();
        $this->country = Country::where('iso', Country::DEFAULT)->first();
        $this->countries = Country::all();
        $this->academicYears = AcademicYear::all();
        $this->primaryLevel = SchoolLevel::where('code', SchoolLevel::PRIMARY)->first();
        if (!$this->defaultSchool) {
            throw new \Exception('School with code ' . School::CUE_LUCIO_LUCERO . ' not found. Please run SchoolSeeder first.');
        }

        if (!$this->province || !$this->country) {
            throw new \Exception('Default province or country not found. Please run ProvinceSeeder and CountrySeeder first.');
        }

        // Get all active courses for the default school and primary level
        $this->courses = Course::where('school_id', $this->defaultSchool->id)
            ->where('school_level_id', $this->primaryLevel->id)
            ->where('start_date', '>=', now()->year() . '-01-01')
            ->where('active', true)
            ->get();
    }
    private function createInitiaStaffUsers()
    {
        $jsonPath = $this->jsonFolder . $this->jsonStaffFileName;
        $jsonData = json_decode(file_get_contents($jsonPath), true);

        foreach ($jsonData as $jsonUserData) {
            $this->createOneStaffUser($jsonUserData, true);
        }
        echo "End with " . count($jsonData) . " staff users\n";
    }

    private function createInitiaStudentsUsers()
    {
        $jsonPath = $this->jsonFolder . $this->jsonStudentsFileName;
        $jsonData = json_decode(file_get_contents($jsonPath), true);

        foreach ($jsonData as $index => $jsonRowData) {
            $this->createOneStudentWithGuardian($index, $jsonRowData);
        }
    }

    private function createOneStaffUser($jsonUserData)
    {
        $parsedData = $this->parseStaffUserData($jsonUserData);

        // Check if user with same email already exists
        $existingUser = User::where('email', $parsedData['user_data']['email'])->first();
        if ($existingUser) {
            $this->command->info("Skipping user with existing email: " . $parsedData['user_data']['email']);
            return;
        }

        // Create the user first
        try {
            $user = $this->userService->createUser($parsedData['user_data']);
        } catch (\Exception $e) {
            echo "Error creating user: " . $e->getMessage() . " for user: " . print_r($parsedData['user_data'], true) . "\n";
            return;
        }

        // Then assign the role with details
        $this->userService->assignRoleWithDetails(
            $user,
            $parsedData['school_id'],
            $parsedData['role_id'],
            $parsedData['school_level_id'],
            $parsedData['details'],
            $this->creator
        );

        $this->command->info("Created user: " . $user->name . " (" . $user->email . ")");
    }

    private function createOneStudentWithGuardian($index, $jsonRowData)
    {
        $studentUser = $this->createOneStudentUser($index, $jsonRowData);
        if (!$studentUser) {
            return;
        }
        $guardianUser = $this->findOrCreateGuardianUser($index, $jsonRowData, $studentUser);
    }

    private function parseStudentUserData($userData)
    {
        // Parse basic user data
        $firstName = ucwords(strtolower(trim($userData['Nombres'] ?? '')));
        $lastName = ucwords(strtolower(trim($userData['Apellidos'] ?? '')));
        $email = $this->faker->sanLuisEmail($firstName, $lastName);
        $dni = $userData['DNI'] ?? null;
        $gender = $this->mapGender($userData['Género'] ?? '');
        $birthdate = $this->parseDate($userData['Fecha de nacimiento'] ?? '');
        $address = $userData['Direccion'] ?? null;
        $locality = $userData['Localidad'] ?? null;
        $birth_place = $userData['Lugar de nacimiento'] ?? 'San Luis';
        $nationality = $this->nacionalityBasedOnBirthPlace($userData['Nacionalidad'] ?? '', $birth_place);
        $shift = $this->mapShift($userData['Turno'] ?? 'mañana');
        $critical_info = $userData['Datos críticos'] ?? null;

        // Create user data array
        $userDataArray = [
            'name' => $firstName . ' ' . mb_substr($lastName, 0, 1),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'id_number' => (string)$dni,
            'gender' => $gender,
            'birthdate' => $birthdate,
            'phone' =>  null, // Not provided for students
            'address' => $address,
            'locality' => $locality,
            'province_id' => $this->province->id,
            'country_id' => $this->country->id,
            'birth_place' => $birth_place,
            'nationality' => $nationality,
            'critical_info' => $critical_info,
            'occupation' => 'Estudiante',
            'password' => '123123123', // Default password
            'password_confirmation' => '123123123', // Required for validation
        ];

        // Map role and create role details
        $roleCode = Role::STUDENT;
        $roleId = $this->allRoles[$roleCode] ?? null;

        if (!$roleId) {
            throw new \Exception("Role not found for student!");
        }

        $courses = $this->normalizeCourses($shift, $userData['Agrupamiento'], true);
        if (empty($courses)) {
            throw new \Exception("No courses found for student: " . print_r($userData, true));
        }
        $course = $courses[0];
        $courseNumber = (int) $course['number'];

        $details = [
            'start_date' => $this->parseDate($userData['INGRESO AL AGRUP'] ?? '') ?: $this->getStudentStartDate($courseNumber),
            'notes' => 'Imported from initial data',
        ];
        $details['student_details'] = [
            'current_course_id' => $course['id']
        ];

        return [
            'user_data' => $userDataArray,
            'school_id' => $this->defaultSchool->id,
            'role_id' => $roleId,
            'shift' => $shift,
            'course' => $course,
            'school_level_id' => $this->primaryLevel->id,
            'details' => $details,
        ];
    }

    private function createOneStudentUser($index, $jsonRowData): ?User
    {
        $studentData = $this->parseStudentUserData($jsonRowData);

        // Check if user with same dni already exists
        $existingUser = User::where('id_number', $studentData['user_data']['id_number'])->first();
        if ($existingUser) {
            $this->command->info($index . " Skipping student with existing dni: " . $studentData['user_data']['id_number']);
            return $existingUser;
        }

        // Create the user first
        try {
            $user = $this->userService->createUser($studentData['user_data']);
        } catch (\Exception $e) {
            echo "Error creating student: " . $e->getMessage() . " for user: " . print_r($studentData['user_data'], true) . "\n";
            return null;
        }

        // Then assign the role with details
        $this->userService->assignRoleWithDetails(
            $user,
            $studentData['school_id'],
            $studentData['role_id'],
            $studentData['school_level_id'],
            $studentData['details'],
            $this->creator
        );

        $this->command->info($index . " Created student with dni: " . $studentData['user_data']['id_number']);
        return $user;
    }

    private function parseGuardianUserData($jsonRowData, User $student)
    {
        $firstName = ucwords(strtolower(trim($jsonRowData['TUTOR_Nombres'] ?? '')));
        $lastName = ucwords(strtolower(trim($jsonRowData['TUTOR_Apellidos'] ?? '')));
        $email = strtolower(trim($jsonRowData['TUTOR_email'] ?? ''));
        $dni = $jsonRowData['TUTOR_DNI'] ?? null;
        $gender = in_array($jsonRowData['Vínculo'], ['Madre', 'Tía', 'Tia', 'Abuela']) ? User::GENDER_FEMALE : User::GENDER_MALE;
        $birthdate = $this->parseDate($jsonRowData['TUTOR_Fecha de nacimiento'] ?? '');
        $phone = $this->formatPhone($jsonRowData['TUTOR_Teléfono'] ?? '');
        $birth_place = $student->birth_place;
        $nationality = $student->nationality;
        $address = $userData['Direccion'] ?? null;
        $locality = $userData['Localidad'] ?? null;
        $shift = $this->mapShift($jsonRowData['Turno'] ?? 'mañana');
        $occupation = $jsonRowData['TUTOR_Profesion'] ?? '';
        if (empty($email)) {
            $email = $this->faker->guardianEmail($firstName, $lastName);
        }
        $birthdate = $this->faker->birthdateFromDNI($dni);

        // Create user data array
        $userDataArray = [
            'name' => $firstName . ' ' . mb_substr($lastName, 0, 1),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'id_number' => (string)$dni,
            'gender' => $gender,
            'birthdate' => $birthdate,
            'phone' =>  $phone,
            'occupation' => $occupation,
            'address' => $address,
            'locality' => $locality,
            'province_id' => $this->province->id,
            'country_id' => $this->country->id,
            'birth_place' => $birth_place,
            'nationality' => $nationality,
            'password' => '123123123', // Default password
            'password_confirmation' => '123123123', // Required for validation
        ];

        // Map role and create role details
        $roleCode = Role::GUARDIAN;
        $roleId = $this->allRoles[$roleCode] ?? null;

        if (!$roleId) {
            throw new \Exception("Role not found for student!");
        }
        // Create role details
        $details = [
            'start_date' => $this->parseDate($userData['Fecha de ingreso a la escuela'] ?? '') ?: now()->subYears(2),
            'notes' => 'Imported from initial data',
        ];
        $details['guardian_details'] = [
            'student_id' => $student->id,
            'relationship_type' => $jsonRowData['Vínculo'],
            'is_emergency_contact' => true,
            'is_restricted' => false,
            'emergency_contact_priority' => 1,
        ];

        return [
            'user_data' => $userDataArray,
            'school_id' => $this->defaultSchool->id,
            'role_id' => $roleId,
            'shift' => $shift,
            'school_level_id' => $this->primaryLevel->id,
            'details' => $details,
        ];
    }

    private function createGuardianAndRelateToStudent(array $jsonRowData, User $student) {}

    private function findOrCreateGuardianUser($index, $jsonRowData, User $student): ?User
    {
        $guardianData = $this->parseGuardianUserData($jsonRowData, $student);

        $guardianUser = User::where('id_number', $guardianData['user_data']['id_number'])->first();
        if (!$guardianUser)
            $guardianUser = User::where('email', $guardianData['user_data']['email'])->first();
        if ($guardianUser) {
            $this->updateGuardianUserMissingFields($guardianUser, $guardianData);
        } else {
            try {
                $guardianUser = $this->userService->createUser($guardianData['user_data']);
            } catch (\Exception $e) {
                echo "Error creating guardian: " . $e->getMessage() . " for user: " . print_r($guardianData['user_data'], true) . "\n";
                throw $e;
                return null;
            }
        }

        // Then assign the role with details
        $this->userService->assignRoleWithDetails(
            $guardianUser,
            $guardianData['school_id'],
            $guardianData['role_id'],
            $guardianData['school_level_id'],
            $guardianData['details'],
            $this->creator
        );

        return $guardianUser;
    }

    private function updateGuardianUserMissingFields($guardianUser, $guardianData)
    {
        $this->userService->updateUser($guardianUser, $guardianData['user_data']);
    }

    private function parseStaffUserData($userData)
    {
        // Parse basic user data
        $firstName = ucwords(strtolower(trim($userData['Nombres'] ?? '')));
        $lastName = ucwords(strtolower(trim($userData['Apellido'] ?? '')));
        $email = strtolower(trim($userData['Email'] ?? ''));
        $dni = $userData['DNI'] ?? null;
        $gender = $this->mapGender($userData['Género'] ?? '');
        $birthdate = $this->parseDate($userData['Fecha de nacimiento'] ?? '');
        $phone = $this->formatPhone($userData['Teléfono móvil'] ?? '');
        $birth_place = $userData['Lugar de nacimiento'] ?? 'San Luis';
        $nationality = $userData['Nacionalidad'] ?? 'Argentina';
        $shift = $this->mapShift($userData['Turno'] ?? 'mañana');
        if (empty($email)) {
            $email = $this->faker->sanLuisEmail($firstName, $lastName);
        }
        if (empty($dni) && !empty($birthdate)) {
            $dni = $this->faker->dniFromBirthdate($birthdate, $nationality);
        } elseif (!empty($dni) && empty($birthdate)) {
            $birthdate = $this->faker->birthdateFromDNI($dni);
        }
        // Create user data array
        $userDataArray = [
            'name' => $firstName . ' ' . mb_substr($lastName, 0, 1),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'id_number' => $dni ? (string)$dni : null,
            'gender' => $gender,
            'birthdate' => $birthdate ?: null,
            'phone' => $phone,
            'address' => null, // Not provided in JSON
            'locality' => 'San Luis', // Default locality
            'province_id' => $this->province->id,
            'country_id' => $this->country->id,
            'birth_place' => $birth_place,
            'nationality' => $nationality,
            'password' => '123123123', // Default password
            'password_confirmation' => '123123123', // Required for validation
        ];

        // Map role and create role details
        $roleCode = $this->mapRole($userData['Cargo'] ?? '');
        $roleId = $this->allRoles[$roleCode] ?? null;

        if (!$roleId) {
            throw new \Exception("Role not found for cargo: " . ($userData['Cargo'] ?? 'Unknown'));
        }

        $courses = $this->normalizeCourses($shift, $userData['Agrupamientos']);

        // Create role details
        $details = [
            'start_date' => $this->parseDate($userData['Fecha de ingreso a la escuela'] ?? '') ?: now()->subYears(2),
            'notes' => 'Imported from initial data',
        ];

        // Add worker details if it's a worker role
        if (Role::isWorker($roleCode)) {
            $details['worker_details'] = [
                'job_status_id' => $this->mapJobStatus($userData['Situación de revista'] ?? ''),
                'job_status_date' => $this->parseDate($userData['Fecha de situación de revista'] ?? ''),
                'decree_number' => $userData['Decreto de designación (número/año)'] ?? null,
                'degree_title' => $userData['Título docente'] ?? null,
                'school_shift_id' =>  $shift->id,
                'schedule' => $this->mapSchedule($shift, $roleCode),
                'class_subject_id' => $this->mapSubject($roleCode, $userData['Materia curricular'] ?? ''),
                'courses' => array_map(function ($course) {
                    return $course['id'];
                }, $courses),
            ];
        } elseif ($roleCode == Role::FORMER_STUDENT) {
            // $details['student_details'] = [
            //     'course_id' => $this->mapCourse($userData['Agrupamientos'] ?? ''),
            // ];
        }


        return [
            'user_data' => $userDataArray,
            'school_id' => $this->defaultSchool->id,
            'role_id' => $roleId,
            'shift' => $shift,
            'courses' => $courses,
            'school_level_id' => $this->primaryLevel->id,
            'details' => $details,
        ];
    }

    private function mapGender($gender)
    {
        return match (strtolower(trim($gender))) {
            'masculino' => User::GENDER_MALE,
            'm' => User::GENDER_MALE,
            'masc' => User::GENDER_MALE,
            'femenino' => User::GENDER_FEMALE,
            'femenina' => User::GENDER_FEMALE,
            'f' => User::GENDER_FEMALE,
            'fem' => User::GENDER_FEMALE,
            'no binario' => User::GENDER_NOBINARY,
            'no-binario' => User::GENDER_NOBINARY,
            'no-bin' => User::GENDER_NOBINARY,
            'fluido' => User::GENDER_FLUID,
            'fluído' => User::GENDER_FLUID,
            'trans' => User::GENDER_TRANS,
            'transgénero' => User::GENDER_TRANS,
            'trans' => User::GENDER_TRANS,
            'trans' => User::GENDER_TRANS,
            'otro' => User::GENDER_OTHER,
            default => User::GENDER_FEMALE, // Default fallback
        };
    }

    private function mapRole($cargo)
    {
        $cargo = strtolower(trim($cargo));

        return match (true) {
            str_contains($cargo, 'school admin') => Role::SCHOOL_ADMIN,
            str_contains($cargo, 'estudiante') => Role::STUDENT,
            str_contains($cargo, 'ex-alumno') => Role::FORMER_STUDENT,
            str_contains($cargo, 'tutor') => Role::GUARDIAN,
            str_contains($cargo, 'director') => Role::DIRECTOR,
            str_contains($cargo, 'regente') => Role::REGENT,
            str_contains($cargo, 'secretaria') || str_contains($cargo, 'secretario') => Role::SECRETARY,
            str_contains($cargo, 'maestra') && str_contains($cargo, 'grado') => Role::GRADE_TEACHER,
            str_contains($cargo, 'auxiliar') => Role::ASSISTANT_TEACHER,
            str_contains($cargo, 'profesor') && str_contains($cargo, 'curricular') => Role::CURRICULAR_TEACHER,
            str_contains($cargo, 'especial') => Role::SPECIAL_TEACHER,
            str_contains($cargo, 'profesor') => Role::PROFESSOR,
            str_contains($cargo, 'preceptor') => Role::CLASS_ASSISTANT,
            str_contains($cargo, 'bibliotecario') => Role::LIBRARIAN,
            default => Role::PROFESSOR, // Default fallback
        };
    }

    private function mapJobStatus($situacion)
    {
        $situacion = strtolower(trim($situacion));

        $statusCode = match (true) {
            str_contains($situacion, 'titular') => JobStatus::PERMANENT,
            str_contains($situacion, 'interino') => JobStatus::INTERIM,
            str_contains($situacion, 'suplente') => JobStatus::SUBSTITUTE,
            default => JobStatus::SUBSTITUTE, // Default fallback
        };

        // Get the actual ID from the database
        $jobStatus = JobStatus::where('code', $statusCode)->first();
        return $jobStatus ? $jobStatus->id : null;
    }

    private function mapSchedule(SchoolShift $shift, $roleCode)
    {
        // Define time slots
        $morningTime = ['from' => '8:00', 'to' => '12:00'];
        $afternoonTime = ['from' => '13:30', 'to' => '17:30'];
        $bothTime = ['from' => '8:00', 'to' => '17:30'];

        $morningTimeExt = ['from' => '8:00', 'to' => '16:00'];
        $afternoonTimeExt = ['from' => '9:30', 'to' => '17:30'];

        // Determine if extended time is needed
        $extendedRoles = [
            Role::DIRECTOR,
            Role::REGENT,
            Role::SECRETARY,
            Role::GRADE_TEACHER,
        ];

        // Curricular teacher special case
        if ($roleCode === Role::CURRICULAR_TEACHER) {
            // 2 hours per day, inside shift time
            $schedule = [];
            for ($i = 1; $i <= 5; $i++) {
                if ($shift->code === SchoolShift::MORNING) {
                    $schedule[$i] = rand(0, 1) ? ['from' => '08:00', 'to' => '10:00'] :  ['from' => '10:00', 'to' => '12:00'];
                } elseif ($shift->code === SchoolShift::AFTERNOON) {
                    $schedule[$i] = rand(0, 1) ? ['from' => '13:30', 'to' => '15:30'] :  ['from' => '15:30', 'to' => '17:30'];
                } else { // both or unknown
                    $schedule[$i] = rand(0, 1) ? ['from' => '09:00', 'to' => '11:00'] :  ['from' => '14:00', 'to' => '16:00'];
                }
            }
            return $schedule;
        }

        // Extended time for certain roles
        if (in_array($roleCode, $extendedRoles)) {
            if ($shift->code === SchoolShift::MORNING) {
                $time = $morningTimeExt;
            } elseif ($shift->code === SchoolShift::AFTERNOON) {
                $time = $afternoonTimeExt;
            } else { // both or unknown
                $time = $bothTime;
            }
        } else {
            if ($shift->code === SchoolShift::MORNING) {
                $time = $morningTime;
            } elseif ($shift->code === SchoolShift::AFTERNOON) {
                $time = $afternoonTime;
            } else { // both or unknown
                $time = $bothTime;
            }
        }

        // Build schedule for Monday to Friday
        $schedule = [];
        for ($i = 1; $i <= 5; $i++) {
            $schedule[$i] = $time;
        }
        return $schedule;
    }

    private function mapShift(string $turno): SchoolShift
    {
        $turno = strtolower(trim($turno));
        $code  = match (true) {
            str_contains($turno, 'mañana') => SchoolShift::MORNING,
            str_contains($turno, 'tarde') => SchoolShift::AFTERNOON,
            str_contains($turno, 'ambos') => SchoolShift::BOTH,
            default => 'Mañana', // Default fallback
        };
        return SchoolShift::where('code', $code)->first();
    }

    private function mapSubject($roleCode, $subjectName)
    {
        if (empty($subjectName) || str_contains(strtolower($subjectName), 'no corresponde')) {
            return null;
        }

        $subjectName = ucwords(trim($subjectName));

        // Try to find subject by name or short name
        $subject = ClassSubject::where('name', 'like', "%{$subjectName}%")
            ->orWhere('short_name', 'like', "%{$subjectName}%")
            ->first();

        return $subject ? $subject->id : null;
    }

    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        // Handle different date formats
        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd/m/y',
            'd-m-y',
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                $age = $this->faker->calculateAge($date);
                if ($age < 90 && $age > 0)
                    return $date->format('Y-m-d');
            }
        }

        // Try to parse as timestamp or year only
        if (is_numeric($dateString)) {
            if (strlen($dateString) === 4) {
                // Year only
                return $dateString . '-01-01';
            } elseif (strlen($dateString) >= 8) {
                // Try as timestamp
                $date = new \DateTime();
                $date->setTimestamp($dateString);
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function formatPhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return $phone;
    }

    /**
     * Delete all users and their relations except user with id=1
     */
    private function deleteAllUsersExceptAdmin()
    {
        // Delete related data first to avoid foreign key issues
        $userIds = User::where('id', '!=', 1)->pluck('id');

        // Delete teacher_courses and student_courses (force delete)
        TeacherCourse::whereIn('role_relationship_id', function ($q) use ($userIds) {
            $q->select('id')->from('role_relationships')->whereIn('user_id', $userIds);
        })->withTrashed()->forceDelete();
        StudentCourse::whereIn('role_relationship_id', function ($q) use ($userIds) {
            $q->select('id')->from('role_relationships')->whereIn('user_id', $userIds);
        })->withTrashed()->forceDelete();

        // Delete worker, student, guardian relationships (no soft deletes, normal delete)
        WorkerRelationship::whereIn('role_relationship_id', function ($q) use ($userIds) {
            $q->select('id')->from('role_relationships')->whereIn('user_id', $userIds);
        })->delete();
        StudentRelationship::whereIn('role_relationship_id', function ($q) use ($userIds) {
            $q->select('id')->from('role_relationships')->whereIn('user_id', $userIds);
        })->delete();
        GuardianRelationship::whereIn('role_relationship_id', function ($q) use ($userIds) {
            $q->select('id')->from('role_relationships')->whereIn('user_id', $userIds);
        })->delete();

        // Delete role relationships (force delete)
        RoleRelationship::whereIn('user_id', $userIds)->withTrashed()->forceDelete();

        // Finally, force delete users (except admin)
        User::where('id', '!=', 1)->withTrashed()->forceDelete();

        echo "Deleting users with IDs: ", implode(", ", $userIds->toArray()), "\n";
    }

    private function findCourse($one)
    {
        return $this->courses->where('letter', $one['letter'])
            ->where('number', $one['number'])
            ->first();
    }

    private function normalizeCourses($shift, $agrupamiento, bool $specialAddToNumber = false)
    {
        if (empty($agrupamiento)) {
            return [];
        }

        $agrupamiento = str_replace(' ', '', trim($agrupamiento));

        // Handle special cases first
        if (str_contains(strtolower($agrupamiento), 'segundo ciclo')) {
            return $this->getCoursesByNumbers($shift, [4, 5, 6, 7, 8], false);
        }

        if (str_contains(strtolower($agrupamiento), 'primer y segundo ciclo')) {
            return $this->getCoursesByNumbers($shift, [1, 2, 3, 4, 5, 6, 7, 8], false);
        }

        if (str_contains(strtolower($agrupamiento), 'primer ciclo')) {
            return $this->getCoursesByNumbers($shift, [1, 2, 3], false);
        }

        if (str_contains(strtolower($agrupamiento), '7/8') || str_contains(strtolower($agrupamiento), '7mos / 8vos')) {
            return $this->getCoursesByNumbers($shift, [7, 8], false);
        }

        if (str_contains(strtolower($agrupamiento), '7mos') || str_contains(strtolower($agrupamiento), '7mo')) {
            return $this->getCoursesByNumbers($shift, [7], false);
        }

        if (str_contains(strtolower($agrupamiento), '8vos') || str_contains(strtolower($agrupamiento), '8vo')) {
            return $this->getCoursesByNumbers($shift, [8], false);
        }

        if (str_contains(strtolower($agrupamiento), 'administración')) {
            return []; // Administration doesn't correspond to specific courses
        }

        // Handle numeric values (like just "2")
        if (is_numeric($agrupamiento)) {
            return $this->getCoursesByNumbers($shift, [(int)$agrupamiento], $specialAddToNumber);
        }

        // Handle specific course patterns like "1A", "3c", "6to F", etc.
        $courses = [];

        // Split by common separators
        $parts = preg_split('/[,;]/', $agrupamiento);

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            $parsed = $this->parseCourseString($shift, $part, $specialAddToNumber);
            if ($parsed) {
                $courses[] = $parsed;
            }
        }

        return $courses;
    }

    private function parseCourseString($shift, $courseString, ?bool $specialAddToNumber = false)
    {
        $courseString = trim($courseString);

        // Handle patterns like "1A", "3c", "6to F", "1°A", "3ero D", etc.
        if (preg_match('/(\d+)(?:°|to|ro|er|mo|vo|vo)?\s*([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter);
        }

        // Handle patterns like "1 agrupamiento b", "1er agrupamiento C"
        if (preg_match('/(\d+)(?:er|ro|mo|vo)?\s+agrupamiento\s+([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter);
        }

        // Handle patterns like "1 agrupamiento division C"
        if (preg_match('/(\d+)\s+agrupamiento\s+division\s+([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter);
        }

        // Handle patterns like "3er agrupamiento"
        if (preg_match('/(\d+)(?:er|ro|mo|vo)?\s+agrupamiento/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            // For this case, we need to get all letters for that number
            return $this->getCoursesByNumbers($shift, [$number + ($specialAddToNumber && $number > 3 ? 1 : 0)], false);
        }


        // throw new \Exception("Invalid course string: " . $courseString);

        return null;
    }

    private function getCourseByNumberAndLetter($number, $letter)
    {
        $course = $this->courses->where('number', $number)->where('letter', $letter)->first();
        return $course ? ['id' => $course->id, 'number' => $course->number, 'letter' => $course->letter] : null;
    }


    private function getCoursesByNumbers(SchoolShift $shift, $numbers, ?bool $specialAddToNumber = false)
    {
        $courses = [];

        foreach ($numbers as $number) {
            $numberCourses = $this->courses->where('number', $number)->where('school_shift_id', $shift->id);
            foreach ($numberCourses as $course) {
                $courses[] = [
                    'id' => $course->id,
                    'number' => $course->number,
                    'letter' => $course->letter
                ];
            }
        }
        return $courses;
    }

    private function nacionalityBasedOnBirthPlace($nacionality, $birth_place)
    {
        if (!empty($nacionality)) {
            return $nacionality;
        }
        if ($this->countries->where('name', $birth_place)->first()) {
            return $birth_place;
        }
        return 'Argentina';
    }

    private function getStudentStartDate($courseNumber)
    {
        $startDate = now()->subYears($courseNumber - 1);
        $found = $this->academicYears->where('year', $startDate->format('Y'))->first();
        if ($found) {
            return $found->start_date;
        }
        // 1 of march of startDateYear
        return $startDate->format('Y-03-01');
    }

    //Role::FORMER_STUDENT course to assign: SELECT max(id) FROM `courses` WHERE letter='A' and number='8' and `end_date` < '2025-01-01';
}
