<?php

namespace App\Services;

use App\Models\Catalogs\FileType;

class FileTypeService
{
    public function getFileTypes()
    {
        return FileType::all()->map(function ($fileType) {
            return [
                'id' => $fileType->id,
                'code' => $fileType->code,
                'name' => $fileType->name,
                'relate_with' => $fileType->relate_with,
                'active' => $fileType->active,
                'can_delete' => !$fileType->fileSubtypes()->exists()
            ];
        });
    }

    public function createFileType(array $data)
    {
        return FileType::create($data);
    }

    public function updateFileType(FileType $fileType, array $data)
    {
        return $fileType->update($data);
    }

    public function deleteFileType(FileType $fileType)
    {
        if ($fileType->fileSubtypes()->exists()) {
            throw new \Exception('Cannot delete file type because it has related file subtypes.');
        }

        return $fileType->delete();
    }
}