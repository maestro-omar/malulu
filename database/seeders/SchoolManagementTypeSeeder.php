<?php

namespace Database\Seeders;

use App\Models\SchoolManagementType;
use Illuminate\Database\Seeder;

class SchoolManagementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Estatal', 'key' => 'state'],
            ['name' => 'Privada', 'key' => 'private'],
            ['name' => 'Generativa', 'key' => 'generative'],
            ['name' => 'Autogestionada', 'key' => 'self_managed'],
        ];

        foreach ($types as $type) {
            SchoolManagementType::create($type);
        }
    }
} 