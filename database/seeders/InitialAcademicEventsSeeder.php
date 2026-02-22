<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Province;
use App\Models\Entities\AcademicEvent;
use App\Models\Entities\AcademicYear;

/**
 * Seeds real academic events (inicio/fin de clases, días no laborables, etc.)
 * for the initial database. Runs as part of CoreDataSeeder after EventsSeeder.
 */
class InitialAcademicEventsSeeder extends Seeder
{
    private $firstUserId;

    public function run(): void
    {
        $this->firstUserId = \Illuminate\Support\Facades\DB::table('users')->first()->id ?? 1;

        $academicYear2026 = AcademicYear::findByYear(2026);
        if (!$academicYear2026) {
            $this->command->warn('Academic year 2026 not found. Skipping InitialAcademicEventsSeeder.');
            return;
        }

        $eventTypes = EventType::all()->keyBy('code');
        if ($eventTypes->isEmpty()) {
            $this->command->warn('No event types found. Run EventsSeeder first.');
            return;
        }

        $this->seedNationalEvents($academicYear2026, $eventTypes);
        $this->seedSanLuisProvincialEvents($academicYear2026, $eventTypes);
    }

    private function seedNationalEvents(AcademicYear $academicYear2026, $eventTypes): void
    {
        // Día no laborable a nivel nacional (2026-03-23)
        $suspensionNacional = $eventTypes->get(EventType::CODE_SUSPENCION_NACIONAL);
        if ($suspensionNacional) {
            AcademicEvent::create([
                'title' => 'Día no laborable',
                'date' => '2026-03-23',
                'is_non_working_day' => true,
                'notes' => 'Día no laborable a nivel nacional',
                'province_id' => null,
                'school_id' => null,
                'academic_year_id' => $academicYear2026->id,
                'event_type_id' => $suspensionNacional->id,
                'recurrent_event_id' => null,
                'active' => true,
                'created_by' => $this->firstUserId,
            ]);
            $this->command->info('Created academic event: Día no laborable (2026-03-23)');
        }
    }

    private function seedSanLuisProvincialEvents(AcademicYear $academicYear2026, $eventTypes): void
    {
        $sanLuis = Province::where('code', Province::DEFAULT)->first();
        if (!$sanLuis) {
            $this->command->warn('San Luis province not found. Skipping provincial academic events.');
            return;
        }

        $inicioClases = $eventTypes->get(EventType::CODE_INICIO_CLASES);
        if ($inicioClases) {
            AcademicEvent::create([
                'title' => 'Inicio de clases (San Luis 2026)',
                'date' => '2026-02-23',
                'is_non_working_day' => false,
                'notes' => 'Inicio de clases provincia San Luis, año lectivo 2026',
                'province_id' => $sanLuis->id,
                'school_id' => null,
                'academic_year_id' => $academicYear2026->id,
                'event_type_id' => $inicioClases->id,
                'recurrent_event_id' => null,
                'active' => true,
                'created_by' => $this->firstUserId,
            ]);
            $this->command->info('Created academic event: Inicio de clases San Luis (2026-02-23)');
        }

        $finClases = $eventTypes->get(EventType::CODE_FIN_CLASES);
        if ($finClases) {
            AcademicEvent::create([
                'title' => 'Fin de clases (San Luis 2026)',
                'date' => '2026-12-18',
                'is_non_working_day' => false,
                'notes' => 'Fin de clases provincia San Luis, año lectivo 2026',
                'province_id' => $sanLuis->id,
                'school_id' => null,
                'academic_year_id' => $academicYear2026->id,
                'event_type_id' => $finClases->id,
                'recurrent_event_id' => null,
                'active' => true,
                'created_by' => $this->firstUserId,
            ]);
            $this->command->info('Created academic event: Fin de clases San Luis (2026-12-18)');
        }
    }
}
