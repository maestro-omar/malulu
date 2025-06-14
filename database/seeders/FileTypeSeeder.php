<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FileType;

class FileTypeSeeder extends Seeder
{
    protected array $fileTypes = [
        [
            'code' => 'provincial',
            'name' => 'Provincial',
            'relate_with' => null,
            'active' => true
        ],
        [
            'code' => 'institutional',
            'name' => 'Institucional',
            'relate_with' => 'school',
            'active' => true
        ],
        [
            'code' => 'course',
            'name' => 'Grupo',
            'relate_with' => 'course',
            'active' => true
        ],
        [
            'code' => 'teacher',
            'name' => 'Docente',
            'relate_with' => 'teacher',
            'active' => true
        ],
        [
            'code' => 'student',
            'name' => 'Alumna/o',
            'relate_with' => 'student',
            'active' => true
        ],
        [
            'code' => 'user',
            'name' => 'Usuario',
            'relate_with' => 'user',
            'active' => true
        ],
    ];

    public function run()
    {
        foreach ($this->fileTypes as $fileType) {
            FileType::create($fileType);
        }
    }
}