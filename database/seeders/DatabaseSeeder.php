<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create initial admin user with explicit password
        $admin = User::factory()->create([
            'name' => 'Omar ADMIN',
            'email' => 'omarmatijas@gmail.com',
            'password' => Hash::make('123123123'), // Set your desired password
        ]);

        // \App\Models\User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            CountrySeeder::class,
            ProvinceSeeder::class,
            DistrictSeeder::class,
            LocalitySeeder::class,
            SchoolLevelSeeder::class,
            SchoolSeeder::class,
            ProfileSeeder::class,
            AcademicYearSeeder::class,
            FileTypeSeeder::class,
        ]);

        $this->call([
            PermissionSeeder::class,
        ]);

        // Get admin profile by key and attach it
        $adminProfile = Profile::where('key', Profile::ADMIN)->first();
        $admin->profiles()->attach($adminProfile->id);
    }
}
