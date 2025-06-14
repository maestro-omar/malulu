<?php

namespace App\Http\Controllers\System;

use App\Models\FileType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\FileTypeService;

class FileTypeController extends SystemBaseController
{
    protected $fileTypeService;

    public function __construct(FileTypeService $fileTypeService)
    {
        $this->fileTypeService = $fileTypeService;
        $this->middleware('auth');
        $this->middleware('can:superadmin');
    }

    public function index()
    {
        return Inertia::render('FileTypes/Index', [
            'fileTypes' => $this->fileTypeService->getFileTypes()
        ]);
    }

    public function create()
    {
        return Inertia::render('FileTypes/Create', [
            'relateWithOptions' => FileType::relateWithOptions()
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

            $this->fileTypeService->createFileType($validated);
            return redirect()->route('file-types.index')
                ->with('success', 'File type created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(FileType $fileType)
    {
        return Inertia::render('FileTypes/Edit', [
            'fileType' => $fileType->load('fileSubtypes')
        ]);
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

            $this->fileTypeService->updateFileType($fileType, $validated);
            return redirect()->route('file-types.index')
                ->with('success', 'File type updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(FileType $fileType)
    {
        try {
            $this->fileTypeService->deleteFileType($fileType);
            return redirect()->route('file-types.index')
                ->with('success', 'File type deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}