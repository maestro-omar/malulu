<?php

namespace Database\Seeders;

use App\Models\SchoolManagementType;
use Illuminate\Database\Seeder;

class SchoolManagementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Estatal', 'code' => 'state'],
            ['name' => 'Privada', 'code' => 'private'],
            ['name' => 'Generativa', 'code' => 'generative'],
            ['name' => 'Autogestionada', 'code' => 'self_managed'],
        ];

        foreach ($types as $type) {
            SchoolManagementType::create($type);
        }
    }
} 