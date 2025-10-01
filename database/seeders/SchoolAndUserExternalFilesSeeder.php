<?php

namespace Database\Seeders;

use App\Models\Catalogs\FileSubtype;
use App\Models\Entities\School;
use App\Models\Entities\User;
use App\Models\Entities\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class SchoolAndUserExternalFilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user for created_by
        $adminUser = User::where('email', 'omarmatijas@gmail.com')->first();
        if (!$adminUser) {
            $this->command->error('Admin user not found');
            return;
        }

        // Set the admin user as the authenticated user for the seeder
        Auth::login($adminUser);

        // Seed school files
        $this->seedSchoolFiles($adminUser);

        // Seed user files
        $this->seedUserFiles($adminUser);

        Auth::logout();
    }

    /**
     * Seed external files for schools
     */
    private function seedSchoolFiles(User $adminUser): void
    {
        // Get the default school
        $school = School::where('code', School::CUE_LUCIO_LUCERO)->first();
        if (!$school) {
            $this->command->error('Default school not found');
            return;
        }

        // Get institutional file subtypes
        $sampleSubtype = FileSubtype::where('code', 'institutional_sample')->first();
        $formStudentSubtype = FileSubtype::where('code', 'institutional_form_student')->first();
        $noticeSubtype = FileSubtype::where('code', 'institutional_notice')->first();
        $contentSubtype = FileSubtype::where('code', 'institutional_content')->first();

        if (!$sampleSubtype || !$formStudentSubtype || !$noticeSubtype || !$contentSubtype) {
            $this->command->error('Required institutional file subtypes not found');
            return;
        }

        // Define the external files data for schools
        $schoolExternalFiles = [
            [
                'subtype_id' => $sampleSubtype->id,
                'nice_name' => 'Plantilla de autorización de salida',
                'description' => 'Modelo de autorización para salidas educativas',
                'external_url' => 'https://docs.google.com/document/d/1aB2cD3eF4gH5iJ6kL7mN8oP9qR0sT1uV2wX3yZ4/edit',
            ],
            [
                'subtype_id' => $formStudentSubtype->id,
                'nice_name' => 'Formulario de inscripción online',
                'description' => 'Formulario digital para inscripción de alumnos nuevos',
                'external_url' => 'https://forms.gle/aBcDeFgHiJkLmNoPqRsTuV',
            ],
            [
                'subtype_id' => $noticeSubtype->id,
                'nice_name' => 'Protocolo de convivencia escolar',
                'description' => 'Normativas y protocolos de convivencia institucional',
                'external_url' => 'https://drive.google.com/file/d/1xYzWvUtSrQpOnMlKjIhGfEdCbA9/view',
            ],
            [
                'subtype_id' => $contentSubtype->id,
                'nice_name' => 'Recursos pedagógicos compartidos',
                'description' => 'Repositorio de materiales educativos y planificaciones',
                'external_url' => 'https://drive.google.com/drive/folders/1aBcDeFgHiJkLmNoPqRsTuVwXyZ',
            ],
        ];

        // Create the external files for school
        foreach ($schoolExternalFiles as $fileData) {
            // Check if file already exists
            $existingFile = File::where('external_url', $fileData['external_url'])
                ->where('fileable_type', 'school')
                ->where('fileable_id', $school->id)
                ->first();

            if ($existingFile) {
                $this->command->info("Skipping existing school file: {$fileData['nice_name']}");
                continue;
            }

            // Create the file record
            File::create([
                'subtype_id' => $fileData['subtype_id'],
                'user_id' => $adminUser->id,
                'replaced_by_id' => null,
                'fileable_type' => 'school',
                'fileable_id' => $school->id,
                'nice_name' => $fileData['nice_name'],
                'description' => $fileData['description'],
                'original_name' => null,
                'filename' => null,
                'mime_type' => null,
                'size' => null,
                'path' => null,
                'metadata' => null,
                'external_url' => $fileData['external_url'],
                'active' => true,
            ]);

            $this->command->info("Created school external file: {$fileData['nice_name']}");
        }
    }

    /**
     * Seed external files for users
     */
    private function seedUserFiles(User $adminUser): void
    {
        // Get file subtypes for users
        $userIdCardSubtype = FileSubtype::where('code', 'user_id_card')->first();
        $userProvIdCardSubtype = FileSubtype::where('code', 'user_prov_id_card')->first();
        $teacherDecreeSubtype = FileSubtype::where('code', 'teacher_decree')->first();
        $teacherDj02Subtype = FileSubtype::where('code', 'teacher_dj02')->first();
        $teacherEvaluationSubtype = FileSubtype::where('code', 'teacher_evaluation')->first();
        $studentAptitudeSubtype = FileSubtype::where('code', 'student_aptitude_certificate')->first();
        $studentVaccinesSubtype = FileSubtype::where('code', 'student_vaccines')->first();
        $studentNotesSubtype = FileSubtype::where('code', 'student_notes')->first();

        if (!$userIdCardSubtype || !$userProvIdCardSubtype) {
            $this->command->error('Required user file subtypes not found');
            return;
        }

        // Get users (excluding admin)
        $users = User::where('id', '!=', $adminUser->id)
            ->where('email', '!=', 'admin@example.com')
            ->take(100)
            ->get();

        if ($users->isEmpty()) {
            $this->command->error('No users found to assign files');
            return;
        }

        // Counter for created files
        $filesCreated = 0;

        // Predefined external URLs templates
        $urlTemplates = [
            'google_docs' => 'https://docs.google.com/document/d/{id}/edit',
            'google_drive' => 'https://drive.google.com/file/d/{id}/view',
            'google_forms' => 'https://forms.gle/{id}',
            'dropbox' => 'https://www.dropbox.com/s/{id}/file.pdf',
            'onedrive' => 'https://onedrive.live.com/view.aspx?id={id}',
        ];

        // Process each user
        foreach ($users as $index => $user) {
            // Determine number of files for this user (most get 1, some get 2-3)
            $filesCount = 1;
            if ($index % 10 === 0) {
                $filesCount = 2; // Every 10th user gets 2 files
            }
            if ($index % 25 === 0) {
                $filesCount = 3; // Every 25th user gets 3 files
            }

            // Generate files for this user
            for ($i = 0; $i < $filesCount; $i++) {
                // Randomly select a subtype and URL template
                $templateKey = array_rand($urlTemplates);
                $randomId = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 32);
                $externalUrl = str_replace('{id}', $randomId, $urlTemplates[$templateKey]);

                // Select subtype based on file number and randomness
                $subtype = null;
                $niceName = '';
                $description = '';

                if ($i === 0) {
                    // First file: user documents
                    if ($index % 2 === 0) {
                        $subtype = $userIdCardSubtype;
                        $niceName = "DNI - {$user->name}";
                        $description = "Documento Nacional de Identidad digitalizado";
                    } else {
                        $subtype = $userProvIdCardSubtype;
                        $niceName = "CIPE - {$user->name}";
                        $description = "Credencial de Identificación Provincial Educativa";
                    }
                } elseif ($i === 1) {
                    // Second file: teacher or student documents
                    if ($index % 3 === 0 && $teacherDecreeSubtype) {
                        $subtype = $teacherDecreeSubtype;
                        $niceName = "Decreto nombramiento - {$user->name}";
                        $description = "Decreto oficial de nombramiento docente";
                    } elseif ($index % 3 === 1 && $studentAptitudeSubtype) {
                        $subtype = $studentAptitudeSubtype;
                        $niceName = "Certificado apto físico - {$user->name}";
                        $description = "Certificado médico de aptitud física";
                    } else {
                        $subtype = $userIdCardSubtype;
                        $niceName = "DNI (reverso) - {$user->name}";
                        $description = "Reverso del Documento Nacional de Identidad";
                    }
                } else {
                    // Third file: various documents
                    if ($teacherDj02Subtype && $index % 4 === 0) {
                        $subtype = $teacherDj02Subtype;
                        $niceName = "Declaración jurada DJ02 - {$user->name}";
                        $description = "Declaración jurada de situación laboral";
                    } elseif ($studentVaccinesSubtype && $index % 4 === 1) {
                        $subtype = $studentVaccinesSubtype;
                        $niceName = "Certificado vacunación - {$user->name}";
                        $description = "Certificado de esquema de vacunación completo";
                    } elseif ($teacherEvaluationSubtype && $index % 4 === 2) {
                        $subtype = $teacherEvaluationSubtype;
                        $niceName = "Concepto docente - {$user->name}";
                        $description = "Evaluación de desempeño docente";
                    } else {
                        $subtype = $studentNotesSubtype;
                        $niceName = "Libreta escolar - {$user->name}";
                        $description = "Libreta de calificaciones y asistencias";
                    }
                }

                // Skip if subtype is null (in case some subtypes weren't found)
                if (!$subtype) {
                    continue;
                }

                // Check if file already exists
                $existingFile = File::where('external_url', $externalUrl)
                    ->where('fileable_type', 'user')
                    ->where('fileable_id', $user->id)
                    ->first();

                if ($existingFile) {
                    continue;
                }

                // Create the file record
                File::create([
                    'subtype_id' => $subtype->id,
                    'user_id' => $adminUser->id,
                    'replaced_by_id' => null,
                    'fileable_type' => 'user',
                    'fileable_id' => $user->id,
                    'nice_name' => $niceName,
                    'description' => $description,
                    'original_name' => null,
                    'filename' => null,
                    'mime_type' => null,
                    'size' => null,
                    'path' => null,
                    'metadata' => null,
                    'external_url' => $externalUrl,
                    'active' => true,
                ]);

                $filesCreated++;
            }
        }

        $this->command->info("Created {$filesCreated} user external files");
    }
}
