<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogs\EventType;
use App\Models\Entities\School;
use App\Models\Entities\AcademicYear;
use App\Models\Entities\Course;
use App\Models\Relations\AcademicEvent;
use App\Models\Relations\AcademicEventCourse;
use Faker\Factory as Faker;

class FakeAcademicEventsSeeder extends Seeder
{
    private $faker;
    private $firstUserId;
    private $academicYearId;
    private $lucioLuceroSchoolId;

    public function run(): void
    {
        $this->faker = Faker::create('es_ES');

        // Get the first user ID for created_by field
        $this->firstUserId = DB::table('users')->first()->id ?? 1;

        // Get current academic year
        $this->academicYearId = AcademicYear::getCurrent()?->id ?? AcademicYear::orderBy('year', 'desc')->first()?->id;

        if (!$this->academicYearId) {
            $this->command->warn('No academic year found. Please run AcademicYearSeeder first.');
            return;
        }

        // Get Lucio Lucero school ID
        $this->lucioLuceroSchoolId = School::where('code', School::CUE_LUCIO_LUCERO)->first()?->id;

        if (!$this->lucioLuceroSchoolId) {
            $this->command->warn('Lucio Lucero school not found. Please run SchoolSeeder first.');
            return;
        }

        $this->createSchoolWideEvents();
        $this->createCourseSpecificEvents();
    }

    private function createSchoolWideEvents(): void
    {
        $eventTypes = [
            EventType::CODE_REUNION_DOCENTE,
            EventType::CODE_ENTREGA_LIBRETA,
            EventType::CODE_ACADEMICO_ESCOLAR
        ];

        foreach ($eventTypes as $eventTypeCode) {
            $eventType = EventType::where('code', $eventTypeCode)->first();
            if (!$eventType) {
                $this->command->warn("Event type {$eventTypeCode} not found. Skipping...");
                continue;
            }

            // Create events for Lucio Lucero school
            $this->createEventsForSchool($this->lucioLuceroSchoolId, $eventType, 3, 5);
        }
    }

    private function createCourseSpecificEvents(): void
    {
        $eventTypes = [
            EventType::CODE_REUNION_GRUPAL,
            EventType::CODE_SALIDA_DIDACTICA
        ];

        // Get some courses from Lucio Lucero school
        $courses = Course::where('school_id', $this->lucioLuceroSchoolId)
            ->where('active', true)
            ->inRandomOrder()
            ->limit(8) // Get 8 random courses
            ->get();

        if ($courses->isEmpty()) {
            $this->command->warn('No courses found for Lucio Lucero school. Skipping course-specific events.');
            return;
        }

        foreach ($eventTypes as $eventTypeCode) {
            $eventType = EventType::where('code', $eventTypeCode)->first();
            if (!$eventType) {
                $this->command->warn("Event type {$eventTypeCode} not found. Skipping...");
                continue;
            }

            // Create events for some courses
            $selectedCourses = $courses->random(min(4, $courses->count()));
            $this->createEventsForCourses($selectedCourses, $eventType, 2, 3);
        }
    }

    private function createEventsForSchool(int $schoolId, EventType $eventType, int $minEvents, int $maxEvents): void
    {
        $eventCount = $this->faker->numberBetween($minEvents, $maxEvents);

        for ($i = 0; $i < $eventCount; $i++) {
            $eventData = $this->generateEventData($eventType);

            $academicEvent = AcademicEvent::create(array_merge($eventData, [
                'school_id' => $schoolId,
                'academic_year_id' => $this->academicYearId,
                'event_type_id' => $eventType->id,
                'created_by' => $this->firstUserId,
            ]));

            $this->command->info("Created school event: {$academicEvent->title} for school ID {$schoolId}");
        }
    }

    private function createEventsForCourses($courses, EventType $eventType, int $minEvents, int $maxEvents): void
    {
        foreach ($courses as $course) {
            $eventCount = $this->faker->numberBetween($minEvents, $maxEvents);

            for ($i = 0; $i < $eventCount; $i++) {
                $eventData = $this->generateEventData($eventType);

                $academicEvent = AcademicEvent::create(array_merge($eventData, [
                    'school_id' => $course->school_id,
                    'academic_year_id' => $this->academicYearId,
                    'event_type_id' => $eventType->id,
                    'created_by' => $this->firstUserId,
                ]));

                // Link the event to the course
                AcademicEventCourse::create([
                    'academic_event_id' => $academicEvent->id,
                    'course_id' => $course->id,
                    'created_by' => $this->firstUserId,
                ]);

                $this->command->info("Created course event: {$academicEvent->title} for course {$course->nice_name}");
            }
        }
    }

    private function generateEventData(EventType $eventType): array
    {
        $titles = $this->getEventTitles($eventType->code);
        $title = $this->faker->randomElement($titles);

        // Generate date within the academic year
        $academicYear = AcademicYear::find($this->academicYearId);
        $startDate = $academicYear->start_date;
        $endDate = $academicYear->end_date;

        $eventDate = $this->faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d');

        return [
            'title' => $title,
            'date' => $eventDate,
            'is_non_working_day' => $this->shouldBeNonWorkingDay($eventType->code),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'active' => true,
        ];
    }

    private function getEventTitles(string $eventTypeCode): array
    {
        $titles = [
            EventType::CODE_REUNION_DOCENTE => [
                'Reunión de Equipo Directivo',
                'Reunión de Coordinación Pedagógica',
                'Reunión de Evaluación Institucional',
                'Reunión de Planificación Curricular',
                'Reunión de Seguimiento Académico',
                'Reunión de Coordinación de Áreas',
                'Reunión de Análisis de Resultados',
                'Reunión de Evaluación de Proyectos',
            ],
            EventType::CODE_ENTREGA_LIBRETA => [
                'Entrega de Libretas - 1er Trimestre',
                'Entrega de Libretas - 2do Trimestre',
                'Entrega de Libretas - 3er Trimestre',
                'Entrega de Libretas - Evaluación Final',
                'Entrega de Libretas de Calificaciones',
                'Entrega de Informes de Evaluación',
            ],
            EventType::CODE_ACADEMICO_ESCOLAR => [
                'Feria de Ciencias y Tecnología',
                'Olimpíada de Matemática',
                'Concurso de Ortografía',
                'Exposición de Proyectos',
                'Jornada de Lectura',
                'Festival de Arte y Cultura',
                'Competencia de Deportes',
                'Muestra de Ciencias Naturales',
                'Encuentro de Historia',
                'Festival de Música',
            ],
            EventType::CODE_REUNION_GRUPAL => [
                'Reunión de Padres - 1er Año',
                'Reunión de Padres - 2do Año',
                'Reunión de Padres - 3er Año',
                'Reunión de Padres - 4to Año',
                'Reunión de Padres - 5to Año',
                'Reunión de Padres - 6to Año',
                'Reunión de Padres - 7mo Año',
                'Reunión de Padres - Evaluación',
                'Reunión de Padres - Información General',
            ],
            EventType::CODE_SALIDA_DIDACTICA => [
                'Visita al Museo de Ciencias',
                'Excursión al Parque Natural',
                'Visita a la Biblioteca Municipal',
                'Salida al Teatro',
                'Visita al Planetario',
                'Excursión al Zoológico',
                'Visita a la Estación Meteorológica',
                'Salida al Museo de Historia',
                'Excursión al Observatorio',
                'Visita a la Planta de Reciclaje',
            ],
        ];

        return $titles[$eventTypeCode] ?? ['Evento Académico'];
    }

    private function shouldBeNonWorkingDay(string $eventTypeCode): bool
    {
        // Some events are typically non-working days
        $nonWorkingEventTypes = [
            EventType::CODE_ENTREGA_LIBRETA,
            EventType::CODE_ACADEMICO_ESCOLAR,
        ];

        return in_array($eventTypeCode, $nonWorkingEventTypes) && $this->faker->boolean(60);
    }
}
