<?php

namespace App\Services;

use App\Models\Entities\File;
use App\Models\Entities\User;
use App\Models\Entities\Course;
use App\Models\Catalogs\FileSubtype;
use App\Models\Catalogs\FileType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FileService
{
    public function createSeederFile(string $fileInDisk, string $folder, int $fileSubTypeId, string $relatedWith = '', ?int $relatedWithId = null, ?int $replacingFileId = null)
    {
        $userId = Auth::id() ?: 1;

        $niceName = basename($fileInDisk);
        $localUploadedFile = new UploadedFile(
            $fileInDisk,                      // full path to the file
            $niceName,            // original file name
            mime_content_type($fileInDisk),   // mime type
            null,                             // size (null will auto-detect)
            true                              // mark as test mode (skip is_uploaded_file)
        );

        $niceName = 'HH ' . explode('.', $niceName)[0];
        $this->setNewFile($localUploadedFile, $folder, $userId, $fileSubTypeId, $niceName, '', $relatedWith, $relatedWithId, '', true, [], [], $replacingFileId);
    }


    public function createFile(array $data)
    {
        $validatedData = $this->validateFileData($data);
        return File::create($validatedData);
    }

    private function validateFileData(array $data)
    {
        $rules = [
            'subtype_id' => [
                'required',
                Rule::exists('file_subtypes', 'id')
            ],
            'user_id' => [
                Rule::exists('users', 'id')
            ],
            'replaced_by_id' => [
                'nullable',
                Rule::exists('file', 'id')
            ],
            'fileable_type' => ['nullable', 'string'],
            'fileable_id' => ['nullable', 'integer'],
            'nice_name' => ['string'],
            'original_name' => ['string'],
            'filename' => ['string'],
            'mime_type' => ['string'],
            'size' => ['integer'],
            'path' => ['string'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'active' => [
                'boolean'
            ]
        ];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function setNewFile(
        UploadedFile $localUploadedFile,
        string $destinationSubFolder,
        int $createdByUserId,
        int $fileSubTypeId,
        string $niceName,
        string $customFileName = '',
        ?string $relatedWith = null,
        ?int $relatedWithId = null,
        string $description = '',
        bool $active = true,
        array $metadata = [],
        array $visibilityOptions = [],
        $replacingFileId = null
    ) {
        // Prepare filename: use $customFileName if provided, else original name (without extension)
        $originalExtension = $localUploadedFile->getClientOriginalExtension();
        $originalNameWithoutExt = pathinfo($localUploadedFile->getClientOriginalName(), PATHINFO_FILENAME);


        $slugify = function ($text) {
            $slug = Str::slug($text);
            return $slug !== '' ? $slug : 'file';
        };

        $baseFileName = $customFileName !== '' ? $customFileName : $originalNameWithoutExt;
        $slugifiedFileName = $slugify($baseFileName);
        $finalFileName = $slugifiedFileName . '.' . $originalExtension;

        $basePath = 'files' . DIRECTORY_SEPARATOR . trim(trim($destinationSubFolder, '/'), '\\');
        $storagePath = 'public' . DIRECTORY_SEPARATOR . $basePath;
        $publicPath = str_replace(DIRECTORY_SEPARATOR, '/', $basePath);
        $storedFullPathAndName = $localUploadedFile->storeAs($storagePath, $finalFileName);
        if (!$storedFullPathAndName)
            throw new \Exception('Error storing file');


        $fileData = [];
        $fileData['subtype_id'] = $fileSubTypeId;
        $fileData['user_id'] = $createdByUserId;
        $fileData['replaced_by_id'] = null;
        $fileData['fileable_type'] = $relatedWith;
        $fileData['fileable_id'] = $relatedWithId;
        $fileData['nice_name'] = $niceName;
        $fileData['original_name'] = $localUploadedFile->getClientOriginalName();
        $fileData['filename'] = $finalFileName;
        $fileData['mime_type'] = $localUploadedFile->getClientMimeType();
        $fileData['size'] = $localUploadedFile->getSize();
        $fileData['path'] = $publicPath;
        $fileData['description'] = $description;
        $fileData['active'] = $active;
        $fileData['metadata'] = $metadata;
        $file = $this->createFile($fileData);

        if ($replacingFileId)
            File::where('id', $replacingFileId)->update(['replaced_by_id' => $file->id, 'active' => false]);

        $this->setVisibility($file, $createdByUserId, $visibilityOptions);
    }

    public function setVisibility($file, $userId, $visibilityOptions)
    {
        if (empty($visibilityOptions)) return; //public
        throw new \Exception('TODO OMAR - Implementar permisos de visibilidad por archivo');
    }

    public function getUserFiles(User $user, User $loggedUser)
    {
        $files = $user->files;
        $files = $this->checkAndFormatEach($files, $loggedUser);
        return $files ?: null;
    }

    private function checkFileVisibility(File $file, User $loggedUser, bool $throwException)
    {
        return true; //OMAR TODO 
    }

    private function checkAndFormatEach($files, User $loggedUser)
    {
        $files = $files->map(function ($file) use ($loggedUser) {
            if ($this->checkFileVisibility($file, $loggedUser, false)) {
                return $this->formatFileForTable($file);
            } else {
                return null;
            }
        });
        return $files->filter()->toArray();
    }

    private function formatFileForTable(File $file)
    {
        $replaces = $file->replacedFile;
        $data = [
            'id' => $file->id,
            'nice_name' => $file->nice_name,
            'description' => $file->description,
            'original_name' => $file->original_name,
            'subtype' => $file->subtype->name,
            'type' => $file->subtype->filetype->name,
            'created_at' => $file->created_at->format('d/m/Y H:i'),
            'created_by' => $file->user->firstname . ' ' . $file->user->lastname,
            'replaces' =>  $replaces->pluck('nice_name')->join(', '),
            'url' =>  $file->url
        ];
        return $data;
    }

    private function formatFileForHistoryTable(int $level, File $file)
    {
        $data = [
            'level' => $level,
            'id' => $file->id,
            'nice_name' => $file->nice_name,
            'subtype' => $file->subtype->name,
            'created_at' => $file->created_at->format('d/m/Y'),
            'deleted_at' => $file->deleted_at ? $file->deleted_at->format('d/m/Y') : '',
            'created_by' => $file->user->firstname . ' ' . $file->user->lastname,
            'url' =>  $file->url
        ];
        return $data;
    }

    public function getSubtypesForCourse(Course $dummyByNow)
    {
        return FileSubtype::byFileTypeCode(FileType::COURSE)->get();
    }

    public function getSubtypesForUser(User $dummyByNow)
    {
        return FileSubtype::byFileTypeCode(FileType::USER)->get();
    }

    public function getCourseFiles(Course $course, User $loggedUser)
    {
        $files = File::where('fileable_type', 'course')->where('fileable_id', $course->id)->with(['subtype', 'subtype.fileType'])->get();
        $files = $this->checkAndFormatEach($files, $loggedUser);
        return $files ?: null;
    }

    public function getFileDataForUser(File $file, User $loggedUser, User $user)
    {
        $this->checkFileVisibility($file, $loggedUser, true);
        if ($file->fileable_id != $user->id || !in_array($file->fileable_type, FileType::userRelatedCodes())) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Este archivo no pertenece al usuario indicado');
        }
        $file->load('subtype', 'subtype.fileType', 'user');
        $history = $file->historyFlattened();
        $history = array_map(function ($item) use ($loggedUser) {
            return $this->formatFileForHistoryTable($item['level'], $item['file']);
        }, $history);

        return ['file' => $file, 'history' => $history];
    }
}
