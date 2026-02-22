<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventResponsibilityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'coordination', 'name' => 'Coordinación', 'description' => 'Coordinación general del evento', 'order' => 10],
            ['code' => 'glossas', 'name' => 'Glosas', 'description' => 'Responsable de preparar cartelería o banners', 'order' => 20],
            ['code' => 'help', 'name' => 'Colaboración', 'description' => 'Colaborador con el coordinador', 'order' => 30],
            ['code' => 'speech', 'name' => 'Discurso / Palabras alusivas', 'description' => 'Responsable de preparar o dar el discurso', 'order' => 20],
            ['code' => 'banner', 'name' => 'Cartelera', 'description' => 'Responsable de las carteleras', 'order' => 50],
            ['code' => 'show', 'name' => 'Acto con estudiantes', 'description' => 'Responsable de preparar el acto o show con alumnos', 'order' => 60],
            ['code' => 'other', 'name' => 'Otro', 'description' => 'Otra responsabilidad', 'order' => 100],
        ];

        foreach ($types as $type) {
            DB::table('event_responsibility_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
