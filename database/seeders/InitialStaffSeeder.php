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
use App\Services\UserService;
use Database\Seeders\Traits\InitialUsersTrait;

/**
 * Initial Staff Seeder
 * 
 * Seeds staff users from ce-8_initial_staff.json
 */
class InitialStaffSeeder extends Seeder
{
    use InitialUsersTrait;

    private $jsonStaffFileName = 'ce-8_initial_staff.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->init();
        $this->createInitiaStaffUsers();
    }

    private function init()
    {
        $this->initializeFaker();

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

        // Get courses that overlap with jsonForYear academic year only
        $academicYear = AcademicYear::findByYear($this->jsonForYear);
        if (!$academicYear) {
            throw new \Exception("Academic year {$this->jsonForYear} not found. Run AcademicYearSeeder or add the year.");
        }
        $ayStart = $academicYear->start_date->format('Y-m-d');
        $ayEnd = $academicYear->end_date->format('Y-m-d');
        $this->courses = Course::where('school_id', $this->defaultSchool->id)
            ->where('school_level_id', $this->primaryLevel->id)
            ->where('start_date', '<=', $ayEnd)
            ->where(function ($q) use ($ayStart) {
                $q->where('end_date', '>=', $ayStart)->orWhereNull('end_date');
            })
            ->get();
    }

    private function createInitiaStaffUsers()
    {
        $jsonPath = $this->jsonFolder . $this->jsonStaffFileName;

        if (!file_exists($jsonPath)) {
            throw new \Exception("JSON file not found: {$jsonPath}. Expected path: " . realpath($this->jsonFolder) . DIRECTORY_SEPARATOR . $this->jsonStaffFileName);
        }

        $jsonContent = file_get_contents($jsonPath);
        if ($jsonContent === false) {
            throw new \Exception("Failed to read JSON file: {$jsonPath}");
        }

        $jsonData = json_decode($jsonContent, true);
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in file: {$jsonPath}. Error: " . json_last_error_msg());
        }

        foreach ($jsonData as $jsonUserData) {
            $this->createOneStaffUser($jsonUserData);
        }
        echo "End with " . count($jsonData) . " staff users\n";
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
            'status' => User::STATUS_ACTIVE,
        ];

        // Map role and create role details
        $roleCode = $this->mapRole($userData['Cargo'] ?? '');
        $roleId = $this->allRoles[$roleCode] ?? null;

        if (!$roleId) {
            throw new \Exception("Role not found for cargo: " . ($userData['Cargo'] ?? 'Unknown'));
        }

        $courses = $this->normalizeCourses($shift, $userData['Agrupamientos'], false);
        // Only enroll in courses that exist (e.g. in simple scenario 4A may not exist)
        $courses = array_values(array_filter($courses, function ($c) {
            return isset($c['id']) && Course::where('id', $c['id'])->exists();
        }));
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
                'school_shift_id' => $shift->id,
                'schedule' => $this->mapSchedule($shift, $roleCode),
                'class_subject_id' => $this->mapSubject($roleCode, $userData['Materia curricular'] ?? ''),
                'courses' => array_map(function ($course) {
                    return $course['id'];
                }, $courses),
            ];
        } elseif ($roleCode == Role::FORMER_STUDENT) {
            // Former student details can be added here if needed
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
            str_contains($cargo, 'administrativo') => Role::ADMINISTRATIVE,
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
            Role::ADMINISTRATIVE,
            Role::GRADE_TEACHER,
        ];

        // Curricular teacher special case
        if ($roleCode === Role::CURRICULAR_TEACHER) {
            // 2 hours per day, inside shift time
            $schedule = [];
            for ($i = 1; $i <= 5; $i++) {
                if ($shift->code === SchoolShift::MORNING) {
                    $schedule[$i] = rand(0, 1) ? ['from' => '08:00', 'to' => '10:00'] : ['from' => '10:00', 'to' => '12:00'];
                } elseif ($shift->code === SchoolShift::AFTERNOON) {
                    $schedule[$i] = rand(0, 1) ? ['from' => '13:30', 'to' => '15:30'] : ['from' => '15:30', 'to' => '17:30'];
                } else { // both or unknown
                    $schedule[$i] = rand(0, 1) ? ['from' => '09:00', 'to' => '11:00'] : ['from' => '14:00', 'to' => '16:00'];
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
}
