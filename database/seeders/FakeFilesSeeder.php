<?php

namespace Database\Seeders;

use App\Models\Catalogs\FileSubtype;
use App\Models\Catalogs\FileType;
use App\Models\Catalogs\Province;
use App\Models\Entities\User;
use App\Models\Entities\School;
use App\Models\Entities\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FakeFilesSeeder extends Seeder
{
    const OTHER_SCHOOLS_LIMIT = 1;
    const FAST_TEST = true;
    private $types;
    private $subtypes;
    private $faker;
    private $fileService;

    public function __construct()
    {
        // Get required IDs
        $this->types = FileType::all();
        $this->subtypes = FileSubtype::all();
        $this->faker = Faker::create('es_ES'); // Using Spanish locale for more realistic names
        $this->fileService = new FileService();
        // dd($this->types, $this->subtypes);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->deleteAllFiles();
        $filesData = $this->getFilesInSeeder();
        foreach ($filesData as $fileData) {
            $this->fileService->createSeederFile(
                $fileData['file'],
                $fileData['folder'],
                $fileData['fileSubTypeId'],
                $fileData['fileableType'],
                $fileData['fileableId']
            );
        }
    }

    private function deleteAllFiles()
    {
        DB::table('files')->delete();
    }

    private function getFilesInSeeder()
    {
        $basePath = database_path('seeders/files');
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                // Store relative path from 'database/seeders/files'
                $relativePath = ltrim(str_replace($basePath, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                $files[] = $relativePath;
            }
        }

        // Group files into a matrix by their slug path (nested folders as nested arrays)
        $filesData = [];
        foreach ($files as $file) {
            $oneData = $this->getOneFileData($file);
            $filesData[] = $oneData;
        }
        return $filesData;
    }

    private function getOneFileData($file)
    {
        $oneData = ['file' => '', 'folder' => '', 'fileableType' => '', 'fileableId' => '', 'fileSubTypeId' => ''];
        $parts = explode(DIRECTORY_SEPARATOR, $file);
        if (count($parts) !== 3) throw new \Exception('File ' . $file . ' has not valid subfolders');
        switch ($parts[0]) {
            case 'provincial':
                // parts[1] is the province code
                $province = Province::where('code', $parts[1])->first();
                if (!$province) throw new \Exception("Province with code {$parts[1]} not found for file $file");
                $oneData['fileableType'] = 'province';
                $oneData['fileableId'] = $province->id;
                $fileType = $this->types->where('code', FileType::PROVINCIAL)->first();
                break;
            case 'school':
                // parts[1] is the school id
                $school  = School::where('code', $parts[1])->first();
                if (!$school) throw new \Exception("School with id {$parts[1]} not found for file $file");
                $oneData['fileableType'] = 'school';
                $oneData['fileableId'] = $school->id;
                $fileType = $this->types->where('code', FileType::INSTITUTIONAL)->first();
                break;
            case 'course':
                // parts[1] is the course id
                $course  = Course::find($parts[1]);
                if (!$course) throw new \Exception("Course with id {$parts[1]} not found for file $file");
                $oneData['fileableType'] = 'course';
                $oneData['fileableId'] = $course->id;
                $fileType = $this->types->where('code', FileType::COURSE)->first();
                break;
            case 'teacher':
                // parts[1] is the teacher id
                $teacher  = User::find($parts[1]);
                if (!$teacher) throw new \Exception("Teacher with id {$parts[1]} not found for file $file");
                $oneData['fileableType'] = 'teacher';
                $oneData['fileableId'] = $teacher->id;
                $fileType = $this->types->where('code', FileType::TEACHER)->first();
                break;
            case 'student':
                // parts[1] is the student id
                $student  = User::find($parts[1]);
                if (!$student) throw new \Exception("Student with id {$parts[1]} not found for file $file");
                $oneData['fileableType'] = 'student';
                $oneData['fileableId'] = $student->id;
                $fileType = $this->types->where('code', FileType::STUDENT)->first();
                break;
            case 'user':
                // parts[1] is the user id
                $user = User::find($parts[1]);
                if (!$user) throw new \Exception("User with id {$parts[1]} not found for file $file");
                $oneData['fileableType'] = 'user';
                $oneData['fileableId'] = $user->id;
                $fileType = $this->types->where('code', FileType::USER)->first();
                break;
            default:
                throw new \Exception('File ' . $file . ' has not valid main folder');
        }

        // Set file path and folder
        $oneData['file'] = database_path('seeders/files') . DIRECTORY_SEPARATOR . $file;
        $oneData['folder'] = $parts[0] . DIRECTORY_SEPARATOR . $parts[1];

        // Get a random file_subtype for this file_type
        if (!$fileType) throw new \Exception("FileType for {$parts[0]} not found for file $file");
        $subtypes = $this->subtypes->where('file_type_id', $fileType->id)->values();
        if ($subtypes->isEmpty()) throw new \Exception("No FileSubtype for FileType {$fileType->code} for file $file");
        $oneData['fileSubTypeId'] = $subtypes->random()->id;
        return $oneData;
    }
}
