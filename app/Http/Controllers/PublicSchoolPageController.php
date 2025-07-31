<?php

namespace App\Http\Controllers;

use App\Models\Entities\School;
use App\Models\Entities\SchoolPage;
use App\Services\SchoolPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicSchoolPageController extends Controller
{
    protected $schoolPageService;

    public function __construct(SchoolPageService $schoolPageService)
    {
        $this->schoolPageService = $schoolPageService;
    }

    /**
     * Display the main school page with school info and navigation
     */
    public function showSchool(School $school)
    {
        // $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']);

        // if (is_string($school->extra)) {
        //     $school->extra = json_decode($school->extra, true);
        // }
        // dd('aaazzz');
        $schoolPages = $this->schoolPageService->getAllAvailablePagesForSchool($school->id, $school);
        $mainPage = $this->schoolPageService->getMainSchoolPageContent($school);

        return Inertia::render('Schools/Public/Show', [
            'school' => $school,
            'schoolPages' => $schoolPages,
            'mainPage' => null, //$mainPage,
            'isMainPage' => true,
        ]);
    }

    /**
     * Display a listing of active school pages for a school
     */
    public function index(School $school)
    {
        dd('INDEX FOR SCHOOL!');
        // $schoolPages = $this->schoolPageService->getAllAvailablePagesForSchool($school->id, $school);
    }

    /**
     * Display the specified school page
     */
    public function show(School $school, string $slug = '')
    {
        if (!$slug) $slug = 'inicio';
        // First, try to find the page in the database
        $schoolPage = $this->schoolPageService->getPageBySlug($school->id, $slug);

        // If page doesn't exist, check if it's a default page
        if (!$schoolPage) {
            $defaultPage = $this->getDefaultPage($school, $slug);

            if ($defaultPage) {
                return Inertia::render('Schools/Public/Page', [
                    'school' => $school,
                    'schoolPage' => $defaultPage,
                    'isDefaultPage' => true,
                ]);
            }

            // If not a default page and doesn't exist, return 404
            return Inertia::render('Errors/404')
                // ->toResponse($request)
                ->setStatusCode(404);
            // abort(404);
        }

        return Inertia::render('Schools/Public/Page', [
            'school' => $school,
            'schoolPage' => $schoolPage,
            'isDefaultPage' => false,
        ]);
    }

    /**
     * Get default page content for predefined slugs
     */
    private function getDefaultPage(School $school, string $slug): ?array
    {
        $defaultPages = [
            'contacto' => [
                'name' => 'Contacto',
                'title' => 'Información de Contacto',
                'slug' => 'contacto',
                'html_content' => $this->getDefaultContactContent($school),
                'active' => true,
                'created_by' => null,
                'school_id' => $school->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'equipo' => [
                'name' => 'Equipo',
                'title' => 'Nuestro Equipo',
                'slug' => 'equipo',
                'html_content' => $this->getDefaultTeamContent($school),
                'active' => true,
                'created_by' => null,
                'school_id' => $school->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            'inicio' => [
                'name' => 'Inicio',
                'title' => 'Página de Inicio',
                'slug' => 'inicio',
                'html_content' => $this->getDefaultHomeContent($school),
                'active' => true,
                'created_by' => null,
                'school_id' => $school->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        return $defaultPages[$slug] ?? null;
    }

    /**
     * Get default contact page content
     */
    private function getDefaultContactContent(School $school): string
    {
        $content = '<h1>Información de Contacto</h1>
        <div class="contact-info">
            <p>Estamos aquí para ayudarte. No dudes en contactarnos:</p>

            <div class="contact-details">
                <h2>Datos de la Escuela</h2>
                <p><strong>Nombre:</strong> ' . htmlspecialchars($school->name) . '</p>';

        if ($school->address) {
            $content .= '<p><strong>Dirección:</strong> ' . htmlspecialchars($school->address) . '</p>';
        }

        if ($school->phone) {
            $content .= '<p><strong>Teléfono:</strong> ' . htmlspecialchars($school->phone) . '</p>';
        }

        if ($school->email) {
            $content .= '<p><strong>Email:</strong> <a href="mailto:' . htmlspecialchars($school->email) . '">' . htmlspecialchars($school->email) . '</a></p>';
        }

        if ($school->locality) {
            $content .= '<p><strong>Localidad:</strong> ' . htmlspecialchars($school->locality->name) . '</p>';
        }

        $content .= '<p><strong>Horarios de atención:</strong> Lunes a Viernes de 8:00 a 16:00</p>
            </div>

            <div class="contact-form">
                <h2>Envíanos un mensaje</h2>
                <p>Para consultas específicas, por favor contacta directamente a la escuela.</p>
            </div>
        </div>';

        return $content;
    }

    /**
     * Get default team page content
     */
    private function getDefaultTeamContent(School $school): string
    {
        return '<h1>Nuestro Equipo</h1>
        <div class="team-info">
            <p>Conoce al equipo que hace posible la excelencia educativa en ' . htmlspecialchars($school->name) . '.</p>

            <div class="team-sections">
                <div class="team-section">
                    <h2>Dirección</h2>
                    <p>Nuestro equipo directivo trabaja para mantener los más altos estándares de calidad educativa.</p>
                </div>

                <div class="team-section">
                    <h2>Docentes</h2>
                    <p>Contamos con un equipo de docentes altamente calificados y comprometidos con la educación de nuestros estudiantes.</p>
                </div>

                <div class="team-section">
                    <h2>Personal Administrativo</h2>
                    <p>Nuestro personal administrativo está disponible para ayudarte con cualquier consulta.</p>
                </div>

                <div class="team-section">
                    <h2>Personal de Mantenimiento</h2>
                    <p>Mantienen nuestras instalaciones en excelente estado para el desarrollo de las actividades educativas.</p>
                </div>
            </div>

            <div class="team-note">
                <p><em>Para información específica sobre algún miembro del equipo, contacta directamente a la escuela.</em></p>
            </div>
        </div>';
    }

    /**
     * Get default home page content
     */
    private function getDefaultHomeContent(School $school): string
    {
        $content = '<h1>Bienvenidos a ' . htmlspecialchars($school->name) . '</h1>
        <div class="welcome-content">
            <p>Somos una institución educativa comprometida con la excelencia académica y el desarrollo integral de nuestros estudiantes.</p>

            <div class="school-highlights">
                <h2>¿Por qué elegirnos?</h2>
                <ul>
                    <li>Educación de calidad con programas innovadores</li>
                    <li>Docentes altamente calificados</li>
                    <li>Instalaciones modernas y seguras</li>
                    <li>Actividades extracurriculares variadas</li>
                    <li>Compromiso con el desarrollo integral</li>
                </ul>
            </div>

            <div class="school-info">
                <h2>Información General</h2>';

        if ($school->locality) {
            $content .= '<p><strong>Ubicación:</strong> ' . htmlspecialchars($school->locality->name);
            if ($school->locality->district) {
                $content .= ', ' . htmlspecialchars($school->locality->district->long);
            }
            $content .= '</p>';
        }

        if ($school->schoolLevels && $school->schoolLevels->count() > 0) {
            $content .= '<p><strong>Niveles:</strong> ' . $school->schoolLevels->pluck('name')->implode(', ') . '</p>';
        }

        $content .= '</div>

            <div class="call-to-action">
                <h2>¿Interesado en nuestra escuela?</h2>
                <p>Contacta con nosotros para obtener más información sobre nuestros programas y proceso de inscripción.</p>
            </div>
        </div>';

        return $content;
    }
}
