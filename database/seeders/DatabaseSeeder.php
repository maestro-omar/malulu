<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create initial admin user with explicit password
        User::factory()->create([
            'name' => 'Omar ADMIN',
            'email' => 'omarmatijas@gmail.com',
            'password' => Hash::make('123123123'), // Set your desired password
        ]);

        // \App\Models\User::factory(10)->create();

        $this->call([
            //locations
            CountrySeeder::class,
            ProvinceSeeder::class,
            DistrictSeeder::class,
            LocalitySeeder::class,

            //static catalogs
            FileTypeSeeder::class,
            FileSubtypeSeeder::class,
            
            //initial data
            SchoolLevelSeeder::class,
            SchoolSeeder::class,
            RoleAndPermissionSeeder::class,
            AcademicYearSeeder::class,
            FakeUsersSeeder::class,
        ]);
    }
}
