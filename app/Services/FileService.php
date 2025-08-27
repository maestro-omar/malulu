<?php

namespace App\Services;

use App\Models\Entities\File;
// use App\Models\Catalogs\FileSubtype;
// use App\Models\Catalogs\FileType;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FileService
{
    public function createSeederFile(string $fileInDisk, string $folder, int $fileSubTypeId, string $relatedWith = '', ?int $relatedWithId = null)
    {
        $userId = Auth::id() ?: 1;

        $localUploadedFile = new UploadedFile(
            $fileInDisk,                      // full path to the file
            basename($fileInDisk),            // original file name
            mime_content_type($fileInDisk),   // mime type
            null,                             // size (null will auto-detect)
            true                              // mark as test mode (skip is_uploaded_file)
        );
        $this->createFile($localUploadedFile, $folder, $userId, $fileSubTypeId, '', $relatedWith, $relatedWithId, '', true, [], null);
    }

    public function createFile(
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

        $file = new File();
        $file->subtype_id = $fileSubTypeId;
        $file->user_id = $createdByUserId;
        $file->replaced_by_id = $replacingFileId;
        $file->fileable_type = $relatedWith;
        $file->fileable_id = $relatedWithId;
        $file->original_name = $localUploadedFile->getClientOriginalName();
        $file->filename = $finalFileName;
        $file->mime_type = $localUploadedFile->getClientMimeType();
        $file->size = $localUploadedFile->getSize();
        $file->path = $publicPath;
        $file->description = $description;
        $file->active = $active;
        $file->metadata = $metadata;
        $file->save();
    }
}
