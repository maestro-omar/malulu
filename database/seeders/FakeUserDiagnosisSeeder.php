<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Entities\User;
use App\Models\Catalogs\Diagnosis;
use Carbon\Carbon;

class FakeUserDiagnosisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Get all attendance statuses
        $diagnoses = Diagnosis::all();
        if ($diagnoses->isEmpty()) {
            $this->command->warn('No diagnosis statuses found. Please run DiagnosesSeeder first.');
            return;
        }

        // 2) Get some users
        $users = User::whereDoesntHave('diagnoses')->inRandomOrder()->limit(200)->get();
        if ($users->isEmpty()) {
            $this->command->warn('No active users found, whitout diagnosis records.');
            return;
        }

        $this->command->info("Generating diagnoses for {$users->count()} users");

        $processedCount = 0;

        foreach ($users as $user) {
            //3) Get some random diagnoses to assign to the user
            $someDiagnoses = $diagnoses->shuffle()->take(rand(1, 2));

            foreach ($someDiagnoses as $diagnosis) {
                $user->diagnoses()->attach($diagnosis->id, [
                    'diagnosed_at' => now(),
                    'notes' => 'Diagnóstico generado aleatoriamente',
                ]);

                $processedCount++;
            }
        }

        if (!empty($processedCount)) {
            $this->command->info("Successfully created {$processedCount} diagnosis records!");
        } else {
            $this->command->info("No new diagnosis records to create.");
        }
    }
}
