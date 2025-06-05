<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FileType;

class FileTypeSeeder extends Seeder
{
    protected array $fileTypes = [
        [
            'key' => 'provincial',
            'name' => 'Provincial',
            'relate_with' => null,
            'active' => true
        ],
        [
            'key' => 'institutional',
            'name' => 'Institucional',
            'relate_with' => null,
            'active' => true
        ],
        [
            'key' => 'course',
            'name' => 'Grupo',
            'relate_with' => 'course',
            'active' => true
        ],
        [
            'key' => 'teacher',
            'name' => 'Docente',
            'relate_with' => 'teacher',
            'active' => true
        ],
        [
            'key' => 'student',
            'name' => 'Alumna/o',
            'relate_with' => 'student',
            'active' => true
        ],
        [
            'key' => 'user',
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