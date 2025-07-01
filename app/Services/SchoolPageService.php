<?php

namespace App\Services;

use App\Models\Entities\SchoolPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Models\Entities\School;

class SchoolPageService
{
    /**
     * Get school pages with filters
     */
    public function getSchoolPages(Request $request, ?int $schoolId = null)
    {
        $query = SchoolPage::query()
            ->with(['school', 'creator'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('school', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($schoolId !== null, function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($request->input('active') !== null, function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            });

        return $query->orderBy('name')->paginate(10);
    }

    /**
     * Validate school page data
     */
    public function validateSchoolPageData(array $data, ?SchoolPage $schoolPage = null)
    {
        $rules = [
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('school_pages')->where(function ($query) use ($data, $schoolPage) {
                    $query->where('school_id', $data['school_id']);
                    if ($schoolPage) {
                        $query->where('id', '!=', $schoolPage->id);
                    }
                    return $query;
                })
            ],
            'html_content' => 'required|string',
            'active' => 'boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new school page
     */
    public function createSchoolPage(array $data, int $createdBy)
    {
        $validatedData = $this->validateSchoolPageData($data);
        $validatedData['created_by'] = $createdBy;

        return SchoolPage::create($validatedData)->load(['school', 'creator']);
    }

    /**
     * Update an existing school page
     */
    public function updateSchoolPage(SchoolPage $schoolPage, array $data)
    {
        $validatedData = $this->validateSchoolPageData($data, $schoolPage);
        $schoolPage->update($validatedData);

        return $schoolPage->load(['school', 'creator']);
    }

    /**
     * Delete a school page
     */
    public function deleteSchoolPage(SchoolPage $schoolPage)
    {
        return $schoolPage->delete();
    }

    /**
     * Get active school pages for a school
     */
    public function getActivePagesForSchool(int $schoolId)
    {
        return SchoolPage::where('school_id', $schoolId)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all available pages for a school (database + default pages)
     */
    public function getAllAvailablePagesForSchool(int $schoolId, ?School $school = null)
    {
        $databasePages = $this->getActivePagesForSchool($schoolId);
        
        // Get default pages that don't exist in database
        $defaultSlugs = $this->getDefaultPageSlugs();
        $existingSlugs = $databasePages->pluck('slug')->toArray();
        $missingDefaultSlugs = array_diff($defaultSlugs, $existingSlugs);
        
        $allPages = [];
        
        // Add database pages
        foreach ($databasePages as $page) {
            $allPages[] = [
                'id' => $page->id,
                'name' => $page->name,
                'title' => $page->title,
                'slug' => $page->slug,
                'active' => $page->active,
                'school_id' => $page->school_id,
                'is_default' => false,
                'html_content' => $page->html_content,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ];
        }
        
        // Add default pages that don't exist in database
        if ($school && !empty($missingDefaultSlugs)) {
            foreach ($missingDefaultSlugs as $slug) {
                $allPages[] = [
                    'id' => 'default-' . $slug,
                    'name' => $this->getDefaultPageName($slug),
                    'title' => $this->getDefaultPageTitle($slug),
                    'slug' => $slug,
                    'active' => true,
                    'school_id' => $schoolId,
                    'is_default' => true,
                    'html_content' => null, // Will be generated when accessed
                    'created_at' => null,
                    'updated_at' => null,
                ];
            }
        }
        
        // Sort by name
        usort($allPages, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return collect($allPages);
    }

    /**
     * Get default page name by slug
     */
    private function getDefaultPageName(string $slug): string
    {
        $names = [
            'contacto' => 'Contacto',
            'equipo' => 'Equipo',
            'inicio' => 'Inicio',
        ];
        
        return $names[$slug] ?? ucfirst($slug);
    }

    /**
     * Get default page title by slug
     */
    private function getDefaultPageTitle(string $slug): string
    {
        $titles = [
            'contacto' => 'Información de Contacto',
            'equipo' => 'Nuestro Equipo',
            'inicio' => 'Página de Inicio',
        ];
        
        return $titles[$slug] ?? ucfirst($slug);
    }

    /**
     * Get a school page by slug for a specific school
     */
    public function getPageBySlug(int $schoolId, string $slug)
    {
        return SchoolPage::where('school_id', $schoolId)
            ->where('slug', $slug)
            ->where('active', true)
            ->with(['school', 'creator'])
            ->first();
    }

    /**
     * Check if a slug is a default page
     */
    public function isDefaultPage(string $slug): bool
    {
        $defaultSlugs = ['contacto', 'equipo', 'inicio'];
        return in_array($slug, $defaultSlugs);
    }

    /**
     * Get list of default page slugs
     */
    public function getDefaultPageSlugs(): array
    {
        return ['contacto', 'equipo', 'inicio'];
    }

    /**
     * Generate a unique slug for a school page
     */
    public function generateUniqueSlug(string $title, int $schoolId, ?SchoolPage $excludePage = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (SchoolPage::where('school_id', $schoolId)
            ->where('slug', $slug)
            ->when($excludePage, function ($query) use ($excludePage) {
                return $query->where('id', '!=', $excludePage->id);
            })
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get main school page content (for the school homepage)
     */
    public function getMainSchoolPageContent(School $school): array
    {
        return [
            'id' => 'main-school-page',
            'name' => 'Inicio',
            'title' => 'Bienvenidos a ' . $school->name,
            'slug' => '',
            'html_content' => $this->getMainSchoolContent($school),
            'active' => true,
            'school_id' => $school->id,
            'is_main_page' => true,
        ];
    }

    /**
     * Get main school content
     */
    private function getMainSchoolContent(School $school): string
    {
        $content = '<div class="school-main-content">
            <div class="school-header">
                <h1>' . htmlspecialchars($school->name) . '</h1>';
        
        if ($school->short) {
            $content .= '<p class="school-subtitle">' . htmlspecialchars($school->short) . '</p>';
        }
        
        $content .= '</div>
            
            <div class="school-description">
                <p>Somos una institución educativa comprometida con la excelencia académica y el desarrollo integral de nuestros estudiantes.</p>
            </div>
            
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
        
        if ($school->managementType) {
            $content .= '<p><strong>Gestión:</strong> ' . htmlspecialchars($school->managementType->name) . '</p>';
        }
        
        $content .= '</div>
            
            <div class="call-to-action">
                <h2>¿Interesado en nuestra escuela?</h2>
                <p>Explora nuestras páginas para conocer más sobre nosotros y contacta con nosotros para obtener más información sobre nuestros programas y proceso de inscripción.</p>
            </div>
        </div>';
        
        return $content;
    }
} 