<?php

namespace Database\Seeders;

use App\Models\JobStatus;
use Illuminate\Database\Seeder;

class JobStatusSeeder extends Seeder
{
    public function run(): void
    {
        $options = JobStatus::vueOptions();
        foreach ($options as $code => $option) {
            JobStatus::create(['code' => $code, 'name' => $option['label']]);
        }
    }
}
