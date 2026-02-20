<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Entities\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;

use Spatie\Activitylog\Models\Activity;

class DatabaseSeeder extends Seeder
{
    private bool $withFakeData = false;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run core data seeder (includes all essential seeders)
        $this->call([
            CoreDataSeeder::class,
        ]);

        $this->call([
            LucioCoursesFixSeeder::class,
            InitialStaffSeeder::class,
            InitialStudentsSeeder::class,
            UpdateDataTo2026Seeder::class,
            InitialStudents2026Seeder::class,
        ]);

        if ($this->withFakeData)
            $this->call([
                FakeAcademicEventsSeeder::class,
                // FakeUsersSeeder::class,
                FakeFilesSeeder::class,
                FakeAttendanceSeeder::class,
                FakeUserDiagnosisSeeder::class,
            ]);

        activity()->enableLogging();
    }
}
