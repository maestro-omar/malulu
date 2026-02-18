<?php

namespace Database\Seeders;

use App\Models\Entities\User;
use App\Models\Entities\School;
use App\Models\Catalogs\Role;
use Illuminate\Database\Seeder;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Entities\Course;
use App\Models\Entities\AcademicYear;
use App\Services\UserService;
use Database\Seeders\Traits\InitialUsersTrait;

/**
 * Initial Students Seeder
 * 
 * Seeds students and their guardians from ce-8_initial_students_with_guardian.json
 */
class InitialStudentsSeeder extends Seeder
{
    use InitialUsersTrait;

    private $jsonStudentsFileName = 'ce-8_initial_students_with_guardian.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->init();
        $this->createInitiaStudentsUsers();
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

    private function createInitiaStudentsUsers()
    {
        $jsonPath = $this->jsonFolder . $this->jsonStudentsFileName;

        if (!file_exists($jsonPath)) {
            throw new \Exception("JSON file not found: {$jsonPath}. Expected path: " . realpath($this->jsonFolder) . DIRECTORY_SEPARATOR . $this->jsonStudentsFileName);
        }

        $jsonContent = file_get_contents($jsonPath);
        if ($jsonContent === false) {
            throw new \Exception("Failed to read JSON file: {$jsonPath}");
        }

        $jsonData = json_decode($jsonContent, true);
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in file: {$jsonPath}. Error: " . json_last_error_msg());
        }

        foreach ($jsonData as $index => $jsonRowData) {
            $this->createOneStudentWithGuardian($index, $jsonRowData);
        }
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
            'phone' => null, // Not provided for students
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
            'status' => User::STATUS_INACTIVE,
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
        $courseNumber = (int)$course['number'];

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
        $address = $jsonRowData['Direccion'] ?? $jsonRowData['TUTOR_Direccion'] ?? null;
        $locality = $jsonRowData['Localidad'] ?? $jsonRowData['TUTOR_Localidad'] ?? null;
        $shift = $this->mapShift($jsonRowData['Turno'] ?? 'mañana');
        $occupation = $jsonRowData['TUTOR_Profesion'] ?? '';
        
        if (empty($email)) {
            $email = $this->faker->guardianEmail($firstName, $lastName);
        }
        if (empty($birthdate) && !empty($dni)) {
            $birthdate = $this->faker->birthdateFromDNI($dni);
        }

        // Create user data array
        $userDataArray = [
            'name' => $firstName . ' ' . mb_substr($lastName, 0, 1),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'id_number' => (string)$dni,
            'gender' => $gender,
            'birthdate' => $birthdate,
            'phone' => $phone,
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
            throw new \Exception("Role not found for guardian!");
        }
        
        // Create role details
        $details = [
            'start_date' => $this->parseDate($jsonRowData['Fecha de ingreso a la escuela'] ?? '') ?: now()->subYears(2),
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

    private function findOrCreateGuardianUser($index, $jsonRowData, User $student): ?User
    {
        $guardianData = $this->parseGuardianUserData($jsonRowData, $student);

        $guardianUser = User::where('id_number', $guardianData['user_data']['id_number'])->first();
        if (!$guardianUser) {
            $guardianUser = User::where('email', $guardianData['user_data']['email'])->first();
        }
        
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
}
