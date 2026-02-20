<?php

namespace Database\Seeders\Traits;

use App\Models\Catalogs\Role;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\SchoolShift;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;
use App\Models\Entities\School;
use App\Models\Entities\Course;
use App\Models\Entities\AcademicYear;
use App\Models\Entities\User;
use App\Models\Relations\RoleRelationship;
use App\Services\UserService;
use Carbon\Carbon;
use Database\Seeders\Faker\UserFaker;

/**
 * Trait with shared methods for Initial Staff and Students seeders
 */
trait InitialUsersTrait
{
    protected $faker;
    protected $jsonFolder;
    protected $defaultSchool;
    protected $primaryLevel;
    protected $courses;
    protected $province;
    protected $country;
    protected $countries;
    protected $academicYears;
    protected $allRoles;
    protected $userService;
    protected $creator;
    protected $jsonForYear = 2025;
    protected int $studentsCreatedCount = 0;

    /**
     * Initialize the faker and JSON folder path
     */
    protected function initializeFaker(): void
    {
        if (!$this->faker) {
            // Use the seeders directory (parent of Traits directory)
            $this->jsonFolder = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
            $this->faker = \Faker\Factory::create('es_ES');
            $this->faker->addProvider(new UserFaker($this->faker));
        }
    }

    /**
     * Map gender string to User gender constant
     */
    protected function mapGender($gender)
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
            'otro' => User::GENDER_OTHER,
            default => User::GENDER_FEMALE, // Default fallback
        };
    }

    /**
     * Map shift string to SchoolShift model
     */
    protected function mapShift(string $turno): SchoolShift
    {
        $turno = strtolower(trim($turno));
        $code = match (true) {
            str_contains($turno, 'mañana') => SchoolShift::MORNING,
            str_contains($turno, 'tarde') => SchoolShift::AFTERNOON,
            str_contains($turno, 'ambos') => SchoolShift::BOTH,
            default => 'Mañana', // Default fallback
        };
        return SchoolShift::where('code', $code)->first();
    }

    /**
     * Parse date string in various formats
     */
    protected function parseDate($dateString)
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
                if ($age < 90 && $age > 0) {
                    return $date->format('Y-m-d');
                }
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

    /**
     * Format phone number (remove non-numeric characters)
     */
    protected function formatPhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        return $phone;
    }

    /**
     * Get nationality based on birth place
     */
    protected function nacionalityBasedOnBirthPlace($nacionality, $birth_place)
    {
        if (!empty($nacionality)) {
            return $nacionality;
        }
        if ($this->countries->where('name', $birth_place)->first()) {
            return $birth_place;
        }
        return 'Argentina';
    }

    /**
     * Normalize course strings to course arrays.
     * Optional $onDate (Y-m-d): when resolving number+letter, pick the course that contains this date (for correct cohort).
     */
    protected function normalizeCourses($shift, $agrupamiento, bool $specialAddToNumber = false, $onDate = null)
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

        // 7/8: take only grade 7 (Lucio's grade 7 becomes 8 mid-year; assigning both would reassign same cohort)
        if (str_contains(strtolower($agrupamiento), '7/8') || str_contains(strtolower($agrupamiento), '7mos / 8vos')) {
            return $this->getCoursesByNumbers($shift, [7], false);
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

            $parsed = $this->parseCourseString($shift, $part, $specialAddToNumber, $onDate);
            if ($parsed) {
                $courses[] = $parsed;
            }
        }

        return $courses;
    }

    /**
     * Parse a course string into course data.
     * @param string|null $onDate Y-m-d to resolve cohort (course that contains this date)
     */
    protected function parseCourseString($shift, $courseString, ?bool $specialAddToNumber = false, $onDate = null)
    {
        $courseString = trim($courseString);

        // Handle patterns like "1A", "3c", "6to F", "1°A", "3ero D", etc.
        if (preg_match('/(\d+)(?:°|to|ro|er|mo|vo|vo)?\s*([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter, $shift, $onDate);
        }

        // Handle patterns like "1 agrupamiento b", "1er agrupamiento C"
        if (preg_match('/(\d+)(?:er|ro|mo|vo)?\s+agrupamiento\s+([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter, $shift, $onDate);
        }

        // Handle patterns like "1 agrupamiento division C"
        if (preg_match('/(\d+)\s+agrupamiento\s+division\s+([A-Za-z])/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            $letter = strtoupper($matches[2]);

            return $this->getCourseByNumberAndLetter($number + ($specialAddToNumber && $number > 3 ? 1 : 0), $letter, $shift, $onDate);
        }

        // Handle patterns like "3er agrupamiento"
        if (preg_match('/(\d+)(?:er|ro|mo|vo)?\s+agrupamiento/i', $courseString, $matches)) {
            $number = (int)$matches[1];
            // For this case, we need to get all letters for that number
            return $this->getCoursesByNumbers($shift, [$number + ($specialAddToNumber && $number > 3 ? 1 : 0)], false);
        }

        return null;
    }

    /**
     * Get course by number and letter, optionally for a specific shift (so 6C Mañana vs 6C Tarde is correct).
     * When $onDate (Y-m-d) is provided: first tries the course whose [start_date, end_date] contains $onDate.
     * If none (e.g. enrollment is just before course start, like 2024-09-30 for 6C starting 2024-10-06), returns the course that starts on or immediately after $onDate.
     * If no course found with the given shift (e.g. JSON says Turno=Mañana but Agrupamiento=1D, and at Lucio 1D is Tarde), falls back to resolving by number+letter only so the course is found.
     */
    protected function getCourseByNumberAndLetter($number, $letter, $shift = null, $onDate = null)
    {
        $course = $this->getCourseByNumberAndLetterWithShift($number, $letter, $shift, $onDate);
        if ($course !== null) {
            return $course;
        }
        // Fallback: JSON may have wrong Turno for this letter (e.g. 1D with Turno=Mañana; at Lucio 1D is Tarde). Resolve by number+letter only.
        if ($shift !== null && is_object($shift) && isset($shift->id)) {
            return $this->getCourseByNumberAndLetterWithShift($number, $letter, null, $onDate);
        }
        return null;
    }

    private function getCourseByNumberAndLetterWithShift($number, $letter, $shift, $onDate)
    {
        $filtered = $this->courses->where('number', $number)->where('letter', $letter);
        if ($shift !== null && is_object($shift) && isset($shift->id)) {
            $filtered = $filtered->where('school_shift_id', $shift->id);
        }
        if ($onDate !== null && $onDate !== '') {
            $on = \Carbon\Carbon::parse($onDate)->startOfDay();
            $containing = $filtered->filter(function ($c) use ($on) {
                $start = \Carbon\Carbon::parse($c->start_date)->startOfDay();
                $end = $c->end_date ? \Carbon\Carbon::parse($c->end_date)->startOfDay() : null;
                return $start->lte($on) && ($end === null || $end->gte($on));
            });
            $course = $containing->first();
            if (!$course) {
                $onOrAfter = $filtered->filter(function ($c) use ($on) {
                    $start = \Carbon\Carbon::parse($c->start_date)->startOfDay();
                    return $start->gte($on);
                })->sortBy('start_date')->first();
                $course = $onOrAfter;
            }
        } else {
            $course = $filtered->first();
        }
        return $course ? ['id' => $course->id, 'number' => $course->number, 'letter' => $course->letter] : null;
    }

    /**
     * Get courses by numbers
     */
    protected function getCoursesByNumbers(SchoolShift $shift, $numbers, ?bool $DUMMYspecialAddToNumber = false, ?bool $nextIfInactiveAndExists = true)
    {
        $courses = [];

        foreach ($numbers as $number) {
            $numberCourses = $this->courses->where('number', $number)->where('school_shift_id', $shift->id);
            foreach ($numberCourses as $course) {
                $addCourse = [
                    'id' => $course->id,
                    'number' => $course->number,
                    'letter' => $course->letter
                ];
                if ($nextIfInactiveAndExists && !$course->active) {
                    $nextCourse = $course->nextCourses()->first();
                    if ($nextCourse) {
                        $addCourse = [
                            'id' => $nextCourse->id,
                            'number' => $nextCourse->number,
                            'letter' => $nextCourse->letter
                        ];
                    }
                }
                $courses[] = $addCourse;
            }
        }
        return $courses;
    }

    /**
     * Get student start date based on course number
     */
    protected function getStudentStartDate($courseNumber)
    {
        $startDate = now()->subYears($courseNumber - 1);
        $found = $this->academicYears->where('year', $startDate->format('Y'))->first();
        if ($found) {
            return $found->start_date;
        }
        // 1 of march of startDateYear
        return $startDate->format('Y-03-01');
    }

    /**
     * Start date of the academic year for jsonForYear (for initial import: enrollment in that year).
     */
    protected function getAcademicYearStartForJsonYear()
    {
        $ay = $this->academicYears->where('year', $this->jsonForYear)->first();
        return $ay ? $ay->start_date : now()->toDateString();
    }

    /**
     * Full init for initial students seeders: faker, roles, school, province, country, courses for jsonForYear.
     * Call initializeFaker() and set $this->jsonForYear before or pass $jsonYear to override.
     */
    protected function initStudentsSeeder(?int $jsonYear = null): void
    {
        $this->initializeFaker();
        if ($jsonYear !== null) {
            $this->jsonForYear = $jsonYear;
        }

        $this->allRoles = Role::pluck('id', 'code')->toArray();
        $this->userService = app(UserService::class);
        $this->creator = User::find(1);

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

        $academicYear = AcademicYear::findByYear($this->jsonForYear);
        if (!$academicYear) {
            throw new \Exception("Academic year {$this->jsonForYear} not found. Run AcademicYearSeeder or add the year.");
        }
        $ayStart = $academicYear->start_date->format('Y-m-d');
        $ayEnd = $academicYear->end_date->format('Y-m-d');
        $previousAy = AcademicYear::findByYear($this->jsonForYear - 1);
        $prevStart = $previousAy ? $previousAy->start_date->format('Y-m-d') : null;
        $prevEnd = $previousAy ? $previousAy->end_date->format('Y-m-d') : null;

        $this->courses = Course::where('school_id', $this->defaultSchool->id)
            ->where('school_level_id', $this->primaryLevel->id)
            ->where(function ($q) use ($ayStart, $ayEnd, $prevStart, $prevEnd) {
                $q->where(function ($q2) use ($ayStart, $ayEnd) {
                    $q2->where('start_date', '<=', $ayEnd)
                        ->where(function ($q3) use ($ayStart) {
                            $q3->where('end_date', '>=', $ayStart)->orWhereNull('end_date');
                        });
                });
                if ($prevStart !== null && $prevEnd !== null) {
                    $q->orWhere(function ($q2) use ($prevStart, $prevEnd) {
                        $q2->where('start_date', '<=', $prevEnd)
                            ->where(function ($q3) use ($prevStart) {
                                $q3->where('end_date', '>=', $prevStart)->orWhereNull('end_date');
                            });
                    });
                }
            })
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Load JSON and create students with guardians for each row. Uses $this->jsonFolder.
     */
    protected function createInitialStudentsFromFile(string $fileName): void
    {
        $jsonPath = $this->jsonFolder . $fileName;
        if (!file_exists($jsonPath)) {
            throw new \Exception("JSON file not found: {$jsonPath}. Expected path: " . realpath($this->jsonFolder) . DIRECTORY_SEPARATOR . $fileName);
        }
        $jsonContent = file_get_contents($jsonPath);
        if ($jsonContent === false) {
            throw new \Exception("Failed to read JSON file: {$jsonPath}");
        }
        $jsonData = json_decode($jsonContent, true);
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in file: {$jsonPath}. Error: " . json_last_error_msg());
        }

        $this->studentsCreatedCount = 0;
        foreach ($jsonData as $index => $jsonRowData) {
            $this->createOneStudentWithGuardian($index, $jsonRowData);
        }
    }

    protected function createOneStudentWithGuardian(int $index, array $jsonRowData): void
    {
        // repitio=1: student already in system (repeated grade); skip if already enrolled in the Agrupamiento course
        if ($this->shouldSkipRepitioStudent($index, $jsonRowData)) {
            return;
        }
        $studentUser = $this->createOneStudentUser($index, $jsonRowData);
        if (!$studentUser) {
            return;
        }
        $this->findOrCreateGuardianUser($index, $jsonRowData, $studentUser);
    }

    /**
     * For 2026 JSON: when repitio=1, student was already created from previous import and stayed in grade.
     * Skip if they exist and their current course matches Agrupamiento; otherwise skip with info/warning.
     */
    protected function shouldSkipRepitioStudent(int $index, array $jsonRowData): bool
    {
        $repitio = (int) ($jsonRowData['repitio'] ?? $jsonRowData['Repitio'] ?? 0);
        if ($repitio !== 1) {
            return false;
        }
        $dni = (string) ($jsonRowData['DNI'] ?? '');
        if ($dni === '') {
            if (isset($this->command)) {
                $this->command->info("{$index} repitio=1 but no DNI; skipping.");
            }
            return true;
        }
        $user = User::where('id_number', $dni)->first();
        if (!$user) {
            if (isset($this->command)) {
                $this->command->info("{$index} repitio=1 but user not found (DNI {$dni}); skipping.");
            }
            return true;
        }
        $studentRoleId = $this->allRoles[Role::STUDENT] ?? null;
        if (!$studentRoleId) {
            return false;
        }
        $rr = RoleRelationship::where('user_id', $user->id)
            ->where('school_id', $this->defaultSchool->id)
            ->where('school_level_id', $this->primaryLevel->id)
            ->where('role_id', $studentRoleId)
            ->whereNull('end_date')
            ->whereNull('end_reason_id')
            ->with('studentRelationship.currentCourse')
            ->first();
        $currentCourse = $rr?->studentRelationship?->currentCourse;
        if (!$currentCourse) {
            if (isset($this->command)) {
                $this->command->warn("{$index} repitio=1, user exists (DNI {$dni}) but no active student course in school; skipping.");
            }
            return true;
        }
        $agrup = trim((string) ($jsonRowData['Agrupamiento'] ?? ''));
        if ($agrup === '' || !preg_match('/^(\d+)\s*([A-Za-z])$/i', $agrup, $m)) {
            if (isset($this->command)) {
                $this->command->warn("{$index} repitio=1, invalid Agrupamiento \"{$agrup}\"; skipping.");
            }
            return true;
        }
        $number = (int) $m[1];
        $letter = strtoupper($m[2]);
        $shift = $this->mapShift($jsonRowData['Turno'] ?? 'mañana');
        $onDate = $this->getAcademicYearStartForJsonYear();
        $expected = $this->getCourseByNumberAndLetter($number, $letter, $shift, $onDate);
        if (!$expected) {
            if (isset($this->command)) {
                $this->command->warn("{$index} repitio=1, no course found for Agrupamiento \"{$agrup}\"; skipping.");
            }
            return true;
        }
        if ((int) $currentCourse->id === (int) $expected['id']) {
            if (isset($this->command)) {
                $this->command->info("{$index} Skipping repitio=1 student (DNI {$dni}): already enrolled in {$agrup}.");
            }
            return true;
        }
        if (isset($this->command)) {
            $this->command->warn("{$index} repitio=1, student (DNI {$dni}) in course {$currentCourse->number}{$currentCourse->letter}, JSON says {$agrup}; skipping.");
        }
        return true;
    }

    /**
     * Parse one row from initial students JSON into student user + role details. Returns null to skip (e.g. simple_test_scenario).
     */
    protected function parseStudentUserData(array $userData): ?array
    {
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

        $userDataArray = [
            'name' => $firstName . ' ' . mb_substr($lastName, 0, 1),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'id_number' => (string)$dni,
            'gender' => $gender,
            'birthdate' => $birthdate,
            'phone' => null,
            'address' => $address,
            'locality' => $locality,
            'province_id' => $this->province->id,
            'country_id' => $this->country->id,
            'birth_place' => $birth_place,
            'nationality' => $nationality,
            'critical_info' => $critical_info,
            'occupation' => 'Estudiante',
            'password' => '123123123',
            'password_confirmation' => '123123123',
            'status' => User::STATUS_INACTIVE,
        ];

        $roleCode = Role::STUDENT;
        $roleId = $this->allRoles[$roleCode] ?? null;
        if (!$roleId) {
            throw new \Exception("Role not found for student!");
        }

        if (config('malulu.simple_test_scenario')) {
            $agrup = trim((string) ($userData['Agrupamiento'] ?? ''));
            if ($agrup !== '' && preg_match('/\d(?:°|to|ro|er|mo|vo)?\s*([A-Za-z])\b/i', $agrup, $m)) {
                if (strtoupper($m[1]) !== 'A') {
                    return null;
                }
            }
        }

        $enrollmentStartDate = $this->parseDate($userData['INGRESO AL AGRUP'] ?? '') ?: $this->getAcademicYearStartForJsonYear();
        $courses = $this->normalizeCourses($shift, $userData['Agrupamiento'] ?? '', false, $enrollmentStartDate);
        if (empty($courses)) {
            if (config('malulu.simple_test_scenario')) {
                return null;
            }
            throw new \Exception("No courses found for student: " . print_r($userData, true));
        }
        $course = $courses[0];
        if (config('malulu.simple_test_scenario') && ($course['letter'] ?? null) !== 'A') {
            return null;
        }

        $details = [
            'start_date' => $enrollmentStartDate,
            'notes' => 'Imported from initial data',
        ];
        $details['student_details'] = [
            'current_course_id' => $course['id'],
            'start_date' => Carbon::parse($enrollmentStartDate)->format('Y-m-d'),
        ];
        $permanece = $userData['permanece'] ?? $userData['Permanece'] ?? null;
        if ($permanece !== null && $permanece !== '') {
            $details['student_details']['custom_fields'] = [
                'permanece_2026' => is_string($permanece) ? trim($permanece) : (string) $permanece,
            ];
        }

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

    protected function createOneStudentUser(int $index, array $jsonRowData): ?User
    {
        $dni = (string) ($jsonRowData['DNI'] ?? '');
        if ($dni !== '') {
            $existingUser = User::where('id_number', $dni)->first();
            if ($existingUser) {
                if (isset($this->command)) {
                    $this->command->info($index . " Skipping student with existing dni: " . $dni);
                }
                return $existingUser;
            }
        }
        $studentData = $this->parseStudentUserData($jsonRowData);
        if (!$studentData) {
            return null;
        }
        try {
            $user = $this->userService->createUser($studentData['user_data']);
        } catch (\Exception $e) {
            echo "Error creating student: " . $e->getMessage() . " for user: " . print_r($studentData['user_data'], true) . "\n";
            return null;
        }
        $this->userService->assignRoleWithDetails(
            $user,
            $studentData['school_id'],
            $studentData['role_id'],
            $studentData['school_level_id'],
            $studentData['details'],
            $this->creator
        );
        $this->studentsCreatedCount++;
        if (isset($this->command) && $this->studentsCreatedCount % 50 === 0) {
            $this->command->info('Total students created: ' . $this->studentsCreatedCount);
        }
        return $user;
    }

    protected function parseGuardianUserData(array $jsonRowData, User $student): array
    {
        $firstName = ucwords(strtolower(trim($jsonRowData['TUTOR_Nombres'] ?? '')));
        $lastName = ucwords(strtolower(trim($jsonRowData['TUTOR_Apellidos'] ?? '')));
        $email = strtolower(trim($jsonRowData['TUTOR_email'] ?? ''));
        $dni = $jsonRowData['TUTOR_DNI'] ?? null;
        $gender = in_array($jsonRowData['Vínculo'] ?? '', ['Madre', 'Tía', 'Tia', 'Abuela']) ? User::GENDER_FEMALE : User::GENDER_MALE;
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
            'password' => '123123123',
            'password_confirmation' => '123123123',
        ];

        $roleCode = Role::GUARDIAN;
        $roleId = $this->allRoles[$roleCode] ?? null;
        if (!$roleId) {
            throw new \Exception("Role not found for guardian!");
        }
        $details = [
            'start_date' => $this->parseDate($jsonRowData['Fecha de ingreso a la escuela'] ?? '') ?: now()->subYears(2),
            'notes' => 'Imported from initial data',
        ];
        $details['guardian_details'] = [
            'student_id' => $student->id,
            'relationship_type' => $jsonRowData['Vínculo'] ?? '',
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

    protected function findOrCreateGuardianUser(int $index, array $jsonRowData, User $student): ?User
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
            }
        }
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

    protected function updateGuardianUserMissingFields(User $guardianUser, array $guardianData): void
    {
        try {
            $this->userService->updateUser($guardianUser, $guardianData['user_data']);
        } catch (\Exception $e) {
            echo "Error updating guardian with id: " . $guardianUser->id . " and Data to update: " . print_r($guardianData['user_data'], true) . " : " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
