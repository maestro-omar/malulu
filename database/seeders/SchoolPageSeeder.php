<?php

namespace Database\Seeders;

use App\Models\Entities\School;
use App\Models\Entities\SchoolPage;
use App\Models\Entities\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchoolPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = School::where('code', '!=', School::GLOBAL)->get();
        $users = User::all();

        if ($schools->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No schools or users found. Skipping SchoolPage seeding.');
            return;
        }

        //will be some automatic pages: home, staff, contact
        $samplePages = [
            [
                'name' => 'Acerca de Nosotros',
                'title' => 'Acerca de Nuestra Escuela',
                'html_content' => '<h1>Acerca de Nuestra Escuela</h1><p>Somos una institución educativa comprometida con la excelencia académica y el desarrollo integral de nuestros estudiantes.</p><p>Nuestra misión es formar ciudadanos responsables y preparados para los desafíos del futuro.</p>',
            ],
            [
                'name' => 'Información Académica',
                'title' => 'Información Académica y Programas',
                'html_content' => '<h1>Información Académica</h1><p>Ofrecemos una educación de calidad con programas innovadores que preparan a nuestros estudiantes para el éxito.</p><ul><li>Programa de estudios actualizado</li><li>Docentes altamente calificados</li><li>Instalaciones modernas</li></ul>',
            ],
            [
                'name' => 'Actividades Extracurriculares',
                'title' => 'Actividades Extracurriculares y Deportes',
                'html_content' => '<h1>Actividades Extracurriculares</h1><p>Fomentamos el desarrollo de habilidades sociales y físicas a través de diversas actividades:</p><ul><li>Deportes: Fútbol, Básquet, Vóley</li><li>Arte y Música</li><li>Club de Ciencias</li><li>Programa de Liderazgo</li></ul>',
            ],
            [
                'name' => 'Noticias y Eventos',
                'title' => 'Noticias y Eventos Escolares',
                'html_content' => '<h1>Noticias y Eventos</h1><p>Mantente informado sobre las últimas noticias y eventos de nuestra escuela.</p><div class="news-section"><h2>Próximos Eventos</h2><ul><li>Feria de Ciencias - 15 de Noviembre</li><li>Festival de Arte - 20 de Noviembre</li><li>Ceremonia de Graduación - 10 de Diciembre</li></ul></div>',
            ],
        ];

        foreach ($schools as $school) {
            foreach ($samplePages as $pageData) {
                $slug = Str::slug($pageData['name']);
                
                // Make slug unique within the school
                $counter = 1;
                $originalSlug = $slug;
                while (SchoolPage::where('school_id', $school->id)->where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                SchoolPage::create([
                    'school_id' => $school->id,
                    'name' => $pageData['name'],
                    'title' => $pageData['title'],
                    'slug' => $slug,
                    'html_content' => $pageData['html_content'],
                    'active' => true,
                    'created_by' => $users->random()->id,
                ]);
            }
        }

        $this->command->info('School pages seeded successfully!');
    }
} 