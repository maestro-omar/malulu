<?php

namespace Database\Seeders;

use App\Models\Catalogs\SchoolManagementType;
use Illuminate\Database\Seeder;

class SchoolManagementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Estatal', 'code' => SchoolManagementType::PUBLIC],
            ['name' => 'Privada', 'code' => SchoolManagementType::PRIVATE],
            ['name' => 'Generativa', 'code' => SchoolManagementType::GENERATIVE],
            ['name' => 'Autogestionada', 'code' => SchoolManagementType::SELF_MANAGED],
        ];

        foreach ($types as $type) {
            SchoolManagementType::create($type);
        }
    }
}
