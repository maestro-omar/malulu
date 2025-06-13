<?php

namespace App\Services;

use App\Models\FileSubtype;

class FileSubtypeService
{
    public function getFileSubtypes()
    {
        return FileSubtype::with('fileType')->get()->map(function ($fileSubtype) {
            return [
                'id' => $fileSubtype->id,
                'file_type_id' => $fileSubtype->file_type_id,
                'file_type' => $fileSubtype->fileType->name,
                'key' => $fileSubtype->key,
                'name' => $fileSubtype->name,
                'description' => $fileSubtype->description,
                'new_overwrites' => $fileSubtype->new_overwrites,
                'hidden_for_familiy' => $fileSubtype->hidden_for_familiy,
                'upload_by_familiy' => $fileSubtype->upload_by_familiy,
                'order' => $fileSubtype->order,
                'can_delete' => !$fileSubtype->files()->exists()
            ];
        });
    }

    public function createFileSubtype(array $data)
    {
        return FileSubtype::create($data);
    }

    public function updateFileSubtype(FileSubtype $fileSubtype, array $data)
    {
        return $fileSubtype->update($data);
    }

    public function deleteFileSubtype(FileSubtype $fileSubtype)
    {
        if ($fileSubtype->files()->exists()) {
            throw new \Exception('Cannot delete file subtype because it has related files.');
        }

        return $fileSubtype->delete();
    }
} 