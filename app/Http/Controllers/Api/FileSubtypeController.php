<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileSubtypeService;
use App\Models\Catalogs\FileSubtype;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FileSubtypeController extends Controller
{
    protected $fileSubtypeService;

    public function __construct(FileSubtypeService $fileSubtypeService)
    {
        $this->fileSubtypeService = $fileSubtypeService;
    }

    public function index()
    {
        return response()->json([
            'data' => $this->fileSubtypeService->getFileSubtypes()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'file_type_id' => 'required|exists:file_types,id',
                'code' => 'required|string|unique:file_subtypes',
                'name' => 'required|string',
                'description' => 'nullable|string',
                'new_overwrites' => 'boolean',
                'hidden_for_familiy' => 'boolean',
                'upload_by_familiy' => 'boolean',
                'order' => 'integer'
            ]);

            $fileSubtype = $this->fileSubtypeService->createFileSubtype($validated);

            return response()->json([
                'message' => 'File subtype created successfully',
                'data' => $fileSubtype
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, FileSubtype $fileSubtype)
    {
        try {
            $validated = $request->validate([
                'file_type_id' => 'required|exists:file_types,id',
                'code' => 'required|string|unique:file_subtypes,code,' . $fileSubtype->id,
                'name' => 'required|string',
                'description' => 'nullable|string',
                'new_overwrites' => 'boolean',
                'hidden_for_familiy' => 'boolean',
                'upload_by_familiy' => 'boolean',
                'order' => 'integer'
            ]);

            $fileSubtype = $this->fileSubtypeService->updateFileSubtype($fileSubtype, $validated);

            return response()->json([
                'message' => 'File subtype updated successfully',
                'data' => $fileSubtype
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(FileSubtype $fileSubtype)
    {
        try {
            $this->fileSubtypeService->deleteFileSubtype($fileSubtype);
            return response()->json([
                'message' => 'File subtype deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 