<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileTypeService;
use App\Models\Catalogs\FileType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FileTypeController extends Controller
{
    protected $fileTypeService;

    public function __construct(FileTypeService $fileTypeService)
    {
        $this->fileTypeService = $fileTypeService;
    }

    public function index()
    {
        return response()->json([
            'data' => $this->fileTypeService->getFileTypes()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:file_types',
                'name' => 'required|string',
                'relate_with' => 'nullable|string',
                'active' => 'boolean'
            ]);

            $fileType = $this->fileTypeService->createFileType($validated);

            return response()->json([
                'message' => 'File type created successfully',
                'data' => $fileType
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error en algún campo',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, FileType $fileType)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:file_types,code,' . $fileType->id,
                'name' => 'required|string',
                'relate_with' => 'nullable|string',
                'active' => 'boolean'
            ]);

            $fileType = $this->fileTypeService->updateFileType($fileType, $validated);

            return response()->json([
                'message' => 'File type updated successfully',
                'data' => $fileType
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error en algún campo',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(FileType $fileType)
    {
        try {
            $this->fileTypeService->deleteFileType($fileType);
            return response()->json([
                'message' => 'File type deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}