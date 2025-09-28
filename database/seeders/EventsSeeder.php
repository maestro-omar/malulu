<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogs\EventType;
use App\Models\Entities\School;

class EventsSeeder extends Seeder
{
    private $firstUserId;

    public function run(): void
    {
        // Get the first user ID for created_by field
        $this->firstUserId = DB::table('users')->first()->id ?? 1;
        $this->eventTypes();
        $this->recursiveEvents();
    }

    private function eventTypes(): void
    {
        // Tipos de eventos
        $eventTypes = [
            ['code' => EventType::CODE_FERIADO_NACIONAL, 'scope' => EventType::SCOPE_NACIONAL, 'name' => 'Feriado'],
            ['code' => EventType::CODE_CONMEMORACION_NACIONAL, 'scope' => EventType::SCOPE_NACIONAL, 'name' => 'Conmemoración'],
            ['code' => EventType::CODE_SUSPENCION_NACIONAL, 'scope' => EventType::SCOPE_NACIONAL, 'name' => 'Suspensión de clases'],

            ['code' => EventType::CODE_FERIADO_PROVINCIAL, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Feriado provincial'],
            ['code' => EventType::CODE_CONMEMORACION_PROVINCIAL, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Conmemoración provincial'],
            ['code' => EventType::CODE_SUSPENCION_PROVINCIAL, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Suspensión de clases'],
            ['code' => EventType::CODE_ACADEMICO_PROVINCIAL, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Evento académico'],

            ['code' => EventType::CODE_CONMEMORACION_ESCOLAR, 'scope' => EventType::SCOPE_ESCOLAR, 'name' => 'Conmemoración escolar'],
            ['code' => EventType::CODE_SUSPENCION_ESCOLAR, 'scope' => EventType::SCOPE_ESCOLAR, 'name' => 'Suspensión de clases'],
            ['code' => EventType::CODE_INICIO_ESCOLAR, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Inicio del ciclo escolar'],
            ['code' => EventType::CODE_FIN_ESCOLAR, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Fin del ciclo escolar'],
            ['code' => EventType::CODE_INICIO_INVIERNO, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Inicio de las vacaciones de invierno'],
            ['code' => EventType::CODE_FIN_INVIERNO, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Fin de las vacaciones de invierno'],
            ['code' => EventType::CODE_ACADEMICO_ESCOLAR, 'scope' => EventType::SCOPE_ESCOLAR, 'name' => 'Evento académico'],
            ['code' => EventType::CODE_EXAMEN, 'scope' => EventType::SCOPE_ESCOLAR, 'name' => 'Examen'],
            ['code' => EventType::CODE_ENTREGA_LIBRETA, 'scope' => EventType::SCOPE_ESCOLAR, 'name' => 'Entrega de libreta'],
            ['code' => EventType::CODE_REUNION_DOCENTE, 'scope' => EventType::SCOPE_ESCOLAR, 'name' => 'Reunión docente'],
            ['code' => EventType::CODE_REUNION_GRUPAL, 'scope' => EventType::SCOPE_ESCOLAR, 'name' => 'Reunión grupal'],
            ['code' => EventType::CODE_SALIDA_DIDACTICA, 'scope' => EventType::SCOPE_ESCOLAR, 'name' => 'Salida didáctica'],
        ];

        foreach ($eventTypes as $eventType) {
            $row = array_merge($eventType, [
                'created_by' => $this->firstUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('event_types')->insert($row);
        }
    }

    private function recursiveEvents(): void
    {
        $allTypesByCode = EventType::all()->keyBy('code');
        if ($allTypesByCode->count() === 0) {
            throw new \Exception('No se encontraron tipos de eventos');
        }

        // Feriados nacionales (recurren todos los años)
        $feriados = [
            ['title' => 'Año Nuevo', 'date' => '2025-01-01', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Día de la Memoria por la Verdad y la Justicia', 'date' => '2025-03-24', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Día del Veterano y de los Caídos en la Guerra de Malvinas', 'date' => '2025-04-02', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Día del Trabajador', 'date' => '2025-05-01', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Día de la Revolución de Mayo', 'date' => '2025-05-25', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Paso a la Inmortalidad del General Don Martín Miguel de Güemes', 'date' => '2025-06-17', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Paso a la Inmortalidad del General Manuel Belgrano / Día de la Bandera', 'date' => '2025-06-20', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Día de la Independencia', 'date' => '2025-07-09', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Paso a la Inmortalidad del General José de San Martín', 'date' => '2025-08-17', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Día del Respeto a la Diversidad Cultural', 'date' => '2025-10-12', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Día de la Soberanía Nacional', 'date' => '2025-11-20', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Día de la Inmaculada Concepción de María', 'date' => '2025-12-08', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
            ['title' => 'Navidad', 'date' => '2025-12-25', 'event_type_code' => 'feriado_nacional', 'is_non_working_day' => true],
        ];


        foreach ($feriados as $feriado) {
            $type = $allTypesByCode[$feriado['event_type_code']];
            $feriado['event_type_id'] = $type->id;
            unset($feriado['event_type_code']);
            DB::table('recurrent_events')->insert(array_merge($feriado, [
                'province_id' => null,
                'school_id' => null,
                'notes' => 'Feriado nacional',
                'created_by' => $this->firstUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $conmemoraciones = [
            ['title' => 'Día Internacional de la No Violencia y la Paz', 'date' => '2025-01-30', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día del Aborigen Americano', 'date' => '2025-04-19', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día de la Constitución Nacional', 'date' => '2025-05-01', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día del Periodista', 'date' => '2025-06-07', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día del Profesor', 'date' => '2025-09-17', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día del Estudiante / Día de la Primavera', 'date' => '2025-09-21', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día del Director de Escuela', 'date' => '2025-09-28', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día Nacional de la Danza', 'date' => '2025-10-10', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día de la Tradición', 'date' => '2025-11-10', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día de la Música / Día de Santa Cecilia', 'date' => '2025-11-22', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día de los Derechos Humanos y de la Restauración de la Democracia', 'date' => '2025-12-10', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
            ['title' => 'Día del Maestro', 'date' => '2025-09-11', 'event_type_code' => 'conmemoracion_nacional', 'is_non_working_day' => false],
        ];

        foreach ($conmemoraciones as $conmemoracion) {
            $type = $allTypesByCode[$conmemoracion['event_type_code']];
            $conmemoracion['event_type_id'] = $type->id;
            unset($conmemoracion['event_type_code']);
            DB::table('recurrent_events')->insert(array_merge($conmemoracion, [
                'province_id' => null,
                'school_id' => null,
                'notes' => 'Conmemoración nacional',
                'created_by' => $this->firstUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Evento provincial 
        DB::table('recurrent_events')->insert([
            'title' => 'Fundación de la Provincia de San Luis',
            'date' => '2025-08-25',
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_PROVINCIAL]->id,
            'province_id' => 1,
            'school_id' => null,
            'is_non_working_day' => true,
            'notes' => '',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Eventos recurrentes especiales (Día del Padre, Madre, Niño)
        DB::table('recurrent_events')->insert([
            'title' => 'Día del Padre',
            'date' => null,
            'recurrence_month' => 6,
            'recurrence_week' => 3,
            'recurrence_weekday' => 0, // Domingo
            'event_type_id' => $allTypesByCode[EventType::CODE_CONMEMORACION_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'is_non_working_day' => false,
            'notes' => '3er domingo de junio',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('recurrent_events')->insert([
            'title' => 'Día de la Madre',
            'date' => null,
            'recurrence_month' => 10,
            'recurrence_week' => 3,
            'recurrence_weekday' => 0, // Domingo
            'event_type_id' => $allTypesByCode[EventType::CODE_CONMEMORACION_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'is_non_working_day' => false,
            'notes' => '3er domingo de octubre',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('recurrent_events')->insert([
            'title' => 'Día del Niño',
            'date' => null,
            'recurrence_month' => 8,
            'recurrence_week' => 2,
            'recurrence_weekday' => 0, // Domingo
            'event_type_id' => $allTypesByCode[EventType::CODE_CONMEMORACION_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'is_non_working_day' => false,
            'notes' => '2do domingo de agosto',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Eventos de Semana Santa (recurrencia especial)
        DB::table('recurrent_events')->insert([
            'title' => 'Jueves Santo',
            'date' => null,
            'recurrence_month' => 4,
            'recurrence_week' => -1, // Última semana del mes
            'recurrence_weekday' => 4, // Jueves
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'is_non_working_day' => true,
            'notes' => 'Jueves Santo (fecha variable según calendario lunar)',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('recurrent_events')->insert([
            'title' => 'Viernes Santo',
            'date' => null,
            'recurrence_month' => 4,
            'recurrence_week' => -1, // Última semana del mes
            'recurrence_weekday' => 5, // Viernes
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'is_non_working_day' => true,
            'notes' => 'Viernes Santo (fecha variable según calendario lunar)',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Eventos de Carnaval (recurrencia especial - 47 días antes de Pascua)
        DB::table('recurrent_events')->insert([
            'title' => 'Carnaval',
            'date' => null,
            'recurrence_month' => 2,
            'recurrence_week' => -1, // Última semana del mes (puede ser febrero o marzo)
            'recurrence_weekday' => 1, // Lunes
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'is_non_working_day' => true,
            'notes' => 'Lunes de Carnaval (47 días antes de Pascua)',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('recurrent_events')->insert([
            'title' => 'Carnaval',
            'date' => null,
            'recurrence_month' => 2,
            'recurrence_week' => -1, // Última semana del mes (puede ser febrero o marzo)
            'recurrence_weekday' => 2, // Martes
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'is_non_working_day' => true,
            'notes' => 'Martes de Carnaval (46 días antes de Pascua)',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // Evento escolar (school_id=School::CUE_LUCIO_LUCERO)
        DB::table('recurrent_events')->insert([
            'title' => 'Aniversario de la Escuela',
            'date' => '2025-09-01',
            'event_type_id' => $allTypesByCode[EventType::CODE_CONMEMORACION_ESCOLAR]->id,
            'province_id' => null,
            'school_id' => School::where('code', School::CUE_LUCIO_LUCERO)->first()->id,
            'is_non_working_day' => false,
            'notes' => 'Celebración interna de la escuela',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
