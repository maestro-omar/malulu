<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entities\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;
use Spatie\Activitylog\Models\Activity;

/**
 * Core Data Seeder
 * 
 * This seeder groups all essential seeders that set up the core application data.
 * It can be run independently or as part of DatabaseSeeder.
 * 
 * Includes:
 * - Static catalogs (job statuses, attendance statuses, file types, diagnoses)
 * - Location data (countries, provinces, districts, localities)
 * - School-related data (academic years, levels, shifts, management types, schools, subjects, courses)
 * - End reason catalogs
 * - Admin user creation
 * - Roles and permissions
 * - School pages
 * - Events
 * - Provincial external files
 */
class CoreDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        activity()->disableLogging();

        // Static catalogs
        $this->call([
            JobStatusSeeder::class,
            AttendanceStatusesTableSeeder::class,
            FileTypeSeeder::class,
            FileSubtypeSeeder::class,
            DiagnosesSeeder::class,
        ]);

        // Locations
        $this->call([
            CountrySeeder::class,
            ProvinceSeeder::class,
            DistrictSeeder::class,
            LocalitySeeder::class,
        ]);

        // School related
        $this->call([
            AcademicYearSeeder::class,
            SchoolLevelSeeder::class,
            SchoolShiftSeeder::class,
            SchoolManagementTypeSeeder::class,
            SchoolSeeder::class,
            ClassSubjectSeeder::class,
            CourseSeeder::class,
            StudentCourseEndReasonSeeder::class,
            RoleRelationshipEndReasonSeeder::class,
        ]);

        activity()->enableLogging();

        // Create initial admin user with explicit password
        // Check if admin user already exists
        $adminEmail = 'omarmatijas@gmail.com';
        $adminUser = User::where('email', $adminEmail)->first();
        
        if (!$adminUser) {
            User::factory()->create([
                'name' => 'Omar Matijas',
                'firstname' => 'Omar',
                'lastname' => 'Matijas',
                'email' => $adminEmail,
                'id_number' => '30334915',
                'gender' => 'masc',
                'birthdate' => '1983-06-07',
                'phone' => '2665103445',
                'address' => 'Basilio Bustos 569',
                'locality' => 'Juan Koslay',
                'province_id' => Province::where('code', Province::DEFAULT)->first()->id,
                'country_id' => Country::where('iso', Country::DEFAULT)->first()->id,
                'nationality' => 'Argentina',
                'password' => Hash::make('123123123'),
            ]);
            $this->command->info('Admin user created: ' . $adminEmail);
        } else {
            $this->command->info('Admin user already exists: ' . $adminEmail);
        }

        activity()->disableLogging();
        
        // Additional core data
        $this->call([
            RoleAndPermissionSeeder::class,
            SchoolPageSeeder::class,
            EventsSeeder::class,
            ProvincialExternalFilesSeeder::class,
        ]);
        
        activity()->enableLogging();
    }
}
