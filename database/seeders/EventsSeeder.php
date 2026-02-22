<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogs\EventType;
use App\Models\Catalogs\Province;
use App\Models\Entities\RecurrentEvent;
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
            ['code' => EventType::CODE_INICIO_CICLO, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Inicio ciclo lectivo'],
            ['code' => EventType::CODE_INICIO_CLASES, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Inicio de clases'],
            ['code' => EventType::CODE_FIN_CLASES, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Fin del clases'],
            ['code' => EventType::CODE_FIN_CICLO, 'scope' => EventType::SCOPE_PROVINCIAL, 'name' => 'Fin de ciclo lectivo'],
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

        $sanLuisId = Province::where('code', Province::DEFAULT)->first()?->id;

        // Feriados nacionales (recurren todos los años)
        $feriados = [
            //Fijos
            ['title' => 'Año Nuevo', 'date' => '1582-01-01', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Día de la Memoria por la Verdad y la Justicia', 'date' => '1976-03-24', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Día del Veterano y de los Caídos en la Guerra de Malvinas', 'date' => '1982-04-02', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Día del Trabajador', 'date' => '1886-05-01', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Día de la Revolución de Mayo', 'date' => '1810-05-25', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Día de la Independencia', 'date' => '1816-07-09', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Día del Maestro', 'date' => '1888-09-11', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Día del Estudiante / Día de la Primavera', 'date' => '1888-09-21', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Día de la Inmaculada Concepción de María', 'date' => '1854-12-08', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],
            ['title' => 'Navidad', 'date' => '336-12-25', 'non_working_type' => RecurrentEvent::NON_WORKING_FIXED],

            //Movibles
            ['title' => 'Paso a la Inmortalidad del General José de San Martín', 'date' => '1850-08-17', 'non_working_type' => RecurrentEvent::NON_WORKING_FLEXIBLE],
            ['title' => 'Paso a la Inmortalidad del General Don Martín Miguel de Güemes', 'date' => '1821-06-17', 'non_working_type' => RecurrentEvent::NON_WORKING_FLEXIBLE],
            ['title' => 'Paso a la Inmortalidad del General Manuel Belgrano / Día de la Bandera', 'date' => '1820-06-20', 'non_working_type' => RecurrentEvent::NON_WORKING_FLEXIBLE],
            ['title' => 'Día del Respeto a la Diversidad Cultural', 'date' => '1492-10-12', 'non_working_type' => RecurrentEvent::NON_WORKING_FLEXIBLE],
            ['title' => 'Día de la Soberanía Nacional', 'date' => '1845-11-20', 'non_working_type' => RecurrentEvent::NON_WORKING_FLEXIBLE],
        ];


        foreach ($feriados as $feriado) {
            $feriado['event_type_id'] = $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id;
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
            ['date' => '1948-01-30', 'title' => 'Día Internacional de la No Violencia y la Paz', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1916-03-08', 'title' => 'Día Internacional de la Mujer', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1813-03-12', 'title' => 'Día del Escudo Nacional Argentino', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '2007-03-21', 'title' => 'Inicio del Otoño (Equinoccio)', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1963-03-31', 'title' => 'Día Nacional del Agua', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1967-04-02', 'title' => 'Día Internacional del Libro Infantil y Juvenil', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1948-04-07', 'title' => 'Día Mundial de la Salud', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1890-04-14', 'title' => 'Día de las Américas', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1940-04-19', 'title' => 'Día del Aborigen Americano', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1970-04-22', 'title' => 'Día Internacional de la Madre Tierra', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1616-04-23', 'title' => 'Día del Idioma Español', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1908-04-29', 'title' => 'Día del Animal', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1853-05-01', 'title' => 'Día de la Constitución Nacional', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1813-05-11', 'title' => 'Día del Himno Nacional Argentino', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1812-05-18', 'title' => 'Día de la Escarapela Nacional', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1884-06-02', 'title' => 'Día del Bombero Voluntario Argentino', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1972-06-05', 'title' => 'Día Mundial del Medio Ambiente', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1810-06-07', 'title' => 'Día del Periodista', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1829-06-10', 'title' => 'Reafirmación de los Derechos Argentinos sobre las Islas Malvinas, Islas del Atlántico Sur y Sector Antártico Argentino', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1908-06-15', 'title' => 'Día del Libro en Argentina', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1900-07-28', 'title' => 'Día del Árbol', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1894-09-17', 'title' => 'Día del Profesor', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1942-09-13', 'title' => 'Día del Bibliotecario', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1930-09-28', 'title' => 'Día del Director de Escuela', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1925-10-05', 'title' => 'Día del Camino', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1972-10-06', 'title' => 'Día del Aire Puro', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1971-10-10', 'title' => 'Día Nacional de la Danza', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1981-10-16', 'title' => 'Día Mundial de la Alimentación', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1835-11-10', 'title' => 'Día de la Tradición', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '230-11-22',  'title' => 'Día de la Música / Día de Santa Cecilia', 'non_working_type' => RecurrentEvent::WORKING_DAY],
            ['date' => '1983-12-10', 'title' => 'Día de los Derechos Humanos y de la Restauración de la Democracia', 'non_working_type' => RecurrentEvent::WORKING_DAY],
        ];

        foreach ($conmemoraciones as $conmemoracion) {
            $conmemoracion['event_type_id'] = $allTypesByCode[EventType::CODE_CONMEMORACION_NACIONAL]->id;
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
            'title' => 'Fundación de San Luis',
            'date' => '1594-08-25',
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_PROVINCIAL]->id,
            'province_id' => $sanLuisId,
            'school_id' => null,
            'non_working_type' => RecurrentEvent::NON_WORKING_FIXED,
            'notes' => '',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // San Luis: 3 de abril de cada año
        DB::table('recurrent_events')->insert([
            [
                'title' => 'Santo de la Quebrada',
                'date' => '2000-04-03',
                'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_PROVINCIAL]->id,
                'province_id' => $sanLuisId,
                'school_id' => null,
                'non_working_type' => RecurrentEvent::NON_WORKING_FIXED,
                'notes' => 'San Luis - 3 de abril',
                'created_by' => $this->firstUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Señor de Renca',
                'date' => '2000-04-03',
                'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_PROVINCIAL]->id,
                'province_id' => $sanLuisId,
                'school_id' => null,
                'non_working_type' => RecurrentEvent::NON_WORKING_FIXED,
                'notes' => 'San Luis - 3 de abril',
                'created_by' => $this->firstUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // San Luis: 17 de mayo - conmemoración (día laborable)
        DB::table('recurrent_events')->insert([
            'title' => 'Natalicio de Pringles',
            'date' => '1795-05-17',
            'event_type_id' => $allTypesByCode[EventType::CODE_CONMEMORACION_PROVINCIAL]->id,
            'province_id' => $sanLuisId,
            'school_id' => null,
            'non_working_type' => RecurrentEvent::WORKING_DAY,
            'notes' => 'Día laborable',
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
            'non_working_type' => RecurrentEvent::WORKING_DAY,
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
            'non_working_type' => RecurrentEvent::WORKING_DAY,
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
            'non_working_type' => RecurrentEvent::WORKING_DAY,
            'notes' => '2do domingo de agosto',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // Eventos de Semana Santa (recurrencia especial)
        DB::table('recurrent_events')->insert([
            'title' => 'Jueves Santo',
            'date' => null,
            'recurrence_month' => null,
            'recurrence_week' => null,
            'recurrence_weekday' => 4,
            'easter_offset' => -3,
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'non_working_type' => RecurrentEvent::NON_WORKING_FIXED,
            'notes' => 'Jueves Santo (fecha variable según calendario lunar)',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('recurrent_events')->insert([
            'title' => 'Viernes Santo',
            'date' => null,
            'recurrence_month' => null,
            'recurrence_week' => null, // Última semana del mes
            'recurrence_weekday' => 5, // Viernes
            'easter_offset' => -2,
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'non_working_type' => RecurrentEvent::NON_WORKING_FIXED,
            'notes' => 'Viernes Santo (fecha variable según calendario lunar)',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Eventos de Carnaval (recurrencia especial - 47 días antes de Pascua)
        DB::table('recurrent_events')->insert([
            'title' => 'Carnaval',
            'date' => null,
            'recurrence_month' => null,
            'recurrence_week' => null,
            'recurrence_weekday' => 1, // Lunes
            'easter_offset' => -47,
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'non_working_type' => RecurrentEvent::NON_WORKING_FIXED,
            'notes' => 'Lunes de Carnaval (47 días antes de Pascua)',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('recurrent_events')->insert([
            'title' => 'Carnaval',
            'date' => null,
            'recurrence_month' => 0,
            'recurrence_week' => 0,
            'recurrence_weekday' => 2, // Martes
            'easter_offset' => -46,
            'event_type_id' => $allTypesByCode[EventType::CODE_FERIADO_NACIONAL]->id,
            'province_id' => null,
            'school_id' => null,
            'non_working_type' => RecurrentEvent::NON_WORKING_FIXED,
            'notes' => 'Martes de Carnaval (46 días antes de Pascua)',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // Evento escolar (school_id=School::CUE_LUCIO_LUCERO)
        DB::table('recurrent_events')->insert([
            'title' => 'Aniversario de la Escuela',
            'date' => '1987-04-27',
            'event_type_id' => $allTypesByCode[EventType::CODE_CONMEMORACION_ESCOLAR]->id,
            'province_id' => null,
            'school_id' => School::where('code', School::CUE_LUCIO_LUCERO)->first()->id,
            'non_working_type' => RecurrentEvent::WORKING_DAY,
            'notes' => 'Celebración interna de la escuela',
            'created_by' => $this->firstUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
