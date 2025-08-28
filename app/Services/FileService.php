<?php

namespace App\Services;

use App\Models\Entities\File;
use App\Models\Entities\User;
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

        $localUploadedFile = new UploadedFile(
            $fileInDisk,                      // full path to the file
            basename($fileInDisk),            // original file name
            mime_content_type($fileInDisk),   // mime type
            null,                             // size (null will auto-detect)
            true                              // mark as test mode (skip is_uploaded_file)
        );
        $this->setNewFile($localUploadedFile, $folder, $userId, $fileSubTypeId, '', $relatedWith, $relatedWithId, '', true, [], [], $replacingFileId);
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
            'original_name' => ['nullable', 'string'],
            'filename' => ['nullable', 'string'],
            'mime_type' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
            'path' => ['nullable', 'string'],
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
        $publicPath = 'storage/' . str_replace(DIRECTORY_SEPARATOR, '/', $basePath);
        $storedFullPathAndName = $localUploadedFile->storeAs($storagePath, $finalFileName);
        if (!$storedFullPathAndName)
            throw new \Exception('Error storing file');


        $fileData = [];
        $fileData['subtype_id'] = $fileSubTypeId;
        $fileData['user_id'] = $createdByUserId;
        $fileData['replaced_by_id'] = null;
        $fileData['fileable_type'] = $relatedWith;
        $fileData['fileable_id'] = $relatedWithId;
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
        $files->filter(function ($file) use ($loggedUser) {
            return $this->checkFileVisibility($file, $loggedUser);
        });
        return $files;
    }

    private function checkFileVisibility(File $file, User $loggedUser)
    {
        return true; //OMAR TODO 
    }
}
