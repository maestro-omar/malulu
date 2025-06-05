<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(CountrySeeder::class);
        $this->call(ProvinceSeeder::class);
        $this->call(DistrictSeeder::class);

        // Then run LocalitySeeder which depends on Districts
        $this->call(LocalitySeeder::class);

        // Then run SchoolLevelSeeder which is needed for Schools
        $this->call(SchoolLevelSeeder::class);

        // Then run SchoolSeeder which depends on Localities and SchoolLevels
        $this->call(SchoolSeeder::class);

        $this->call(ProfileSeeder::class);
    }
}
