<?php

namespace Database\Seeders;

use App\Models\Catalogs\FileSubtype;
use App\Models\Catalogs\Province;
use App\Models\Entities\User;
use App\Models\Entities\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class ProvincialExternalFilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the default province (San Luis)
        $province = Province::where('code', Province::DEFAULT)->first();
        if (!$province) {
            $this->command->error('Default province not found');
            return;
        }

        // Get admin user for created_by
        $adminUser = User::where('email', 'omarmatijas@gmail.com')->first();
        if (!$adminUser) {
            $this->command->error('Admin user not found');
            return;
        }

        // Set the admin user as the authenticated user for the seeder
        Auth::login($adminUser);

        // Get file subtypes
        $permanentInfoSubtype = FileSubtype::where('code', 'provincial_permanent_info')->first();
        $scheduleSubtype = FileSubtype::where('code', 'provincial_schedule')->first();
        $otherSubtype = FileSubtype::where('code', 'provincial_other')->first();
        $profDevSubtype = FileSubtype::where('code', 'provincial_prof_dev')->first();

        if (!$permanentInfoSubtype || !$scheduleSubtype || !$otherSubtype || !$profDevSubtype) {
            $this->command->error('Required file subtypes not found');
            return;
        }

        // Define the external files data
        $externalFiles = [
            [
                'subtype_id' => $permanentInfoSubtype->id,
                'nice_name' => 'Portal docentes',
                'description' => 'Portal oficial de docentes de la provincia de San Luis',
                'external_url' => 'https://sanluis.gov.ar/educacion/docentes/',
            ],
            [
                'subtype_id' => $otherSubtype->id,
                'nice_name' => 'Guía única e integral de abordaje rápido para la salud escolar',
                'description' => 'Guía oficial para el abordaje de la salud escolar',
                'external_url' => 'https://sanluis.gov.ar/_uploads/educacion/guiarse.pdf',
            ],
            [
                'subtype_id' => $scheduleSubtype->id,
                'nice_name' => 'Calendario escolar',
                'description' => 'Calendario escolar oficial de la provincia',
                'external_url' => 'https://drive.google.com/drive/folders/1NHUjWFi7prx5Cjk3XDlyz5BYtdhP0q7F',
            ],
            [
                'subtype_id' => $profDevSubtype->id,
                'nice_name' => 'Queremos resolver - Matemática',
                'description' => 'Portal educativo para resolución de problemas matemáticos',
                'external_url' => 'https://matematica.sanluis.edu.ar/',
            ],
            [
                'subtype_id' => $profDevSubtype->id,
                'nice_name' => 'Queremos aprender - Alfabetización',
                'description' => 'Portal educativo para alfabetización',
                'external_url' => 'https://alfabetizacion.sanluis.edu.ar/',
            ],
        ];

        // Create the external files
        foreach ($externalFiles as $fileData) {
            // Check if file already exists
            $existingFile = File::where('external_url', $fileData['external_url'])
                ->where('province_id', $province->id)
                ->first();

            if ($existingFile) {
                $this->command->info("Skipping existing file: {$fileData['nice_name']}");
                continue;
            }

            // Create the file record
            $file = File::create([
                'subtype_id' => $fileData['subtype_id'],
                'user_id' => $adminUser->id,
                'replaced_by_id' => null,
                'province_id' => $province->id,
                'school_id' => null,
                'course_id' => null,
                'target_user_id' => null,
                'nice_name' => $fileData['nice_name'],
                'description' => $fileData['description'],
                'valid_from' => null,
                'valid_until' => null,
                'original_name' => null,
                'filename' => null,
                'mime_type' => null,
                'size' => null,
                'path' => null,
                'metadata' => null,
                'external_url' => $fileData['external_url'],
                'active' => true,
            ]);

            $this->command->info("Created external file: {$fileData['nice_name']}");
        }

        Auth::logout();
    }
}
