<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Entities\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Static catalogs
            JobStatusSeeder::class,
            AttendanceStatusesTableSeeder::class,

            FileTypeSeeder::class,
            FileSubtypeSeeder::class,

            // Locations
            CountrySeeder::class,
            ProvinceSeeder::class,
            DistrictSeeder::class,
            LocalitySeeder::class,

            // School related
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

        // Create initial admin user with explicit password
        User::factory()->create([
            'name' => 'Omar Matijas',
            'firstname' => 'Omar',
            'lastname' => 'Matijas',
            'email' => 'omarmatijas@gmail.com',
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

        $this->call([
            // Roles and permissions
            RoleAndPermissionSeeder::class,

            SchoolPageSeeder::class,

            InitialUsersSeeder::class,

            EventsSeeder::class,
        ]);

        $this->call([
            FakeUsersSeeder::class,
            FakeFilesSeeder::class,
            FakeAttendanceSeeder::class,
            FakeAcademicEventsSeeder::class,
        ]);
    }
}
