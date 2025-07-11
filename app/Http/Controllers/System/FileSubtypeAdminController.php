<?php

namespace App\Http\Controllers\System;

use App\Models\Catalogs\FileSubtype;
use App\Models\Catalogs\FileType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\FileSubtypeService;
use App\Http\Controllers\System\SystemBaseController;
use Diglactic\Breadcrumbs\Breadcrumbs;

class FileSubtypeAdminController extends SystemBaseController
{
    protected $fileSubtypeService;

    public function __construct(FileSubtypeService $fileSubtypeService)
    {
        $this->fileSubtypeService = $fileSubtypeService;
        $this->middleware('auth');
        $this->middleware('can:superadmin');
    }

    public function index()
    {
        return Inertia::render('FileSubtypes/Index', [
            'fileSubtypes' => $this->fileSubtypeService->getFileSubtypes(),
            'breadcrumbs' => Breadcrumbs::generate('file-subtypes.index'),
        ]);
    }

    public function create()
    {
        return Inertia::render('FileSubtypes/Create', [
            'fileTypes' => FileType::where('active', true)->get(),
            'breadcrumbs' => Breadcrumbs::generate('file-subtypes.create'),
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

            $this->fileSubtypeService->createFileSubtype($validated);
            return redirect()->route('file-subtypes.index')
                ->with('success', 'Subtipo de archivo creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(FileSubtype $fileSubtype)
    {
        return Inertia::render('FileSubtypes/Edit', [
            'fileSubtype' => $fileSubtype,
            'fileTypes' => FileType::where('active', true)->get(),
            'breadcrumbs' => Breadcrumbs::generate('file-subtypes.edit', $fileSubtype),
        ]);
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

            $this->fileSubtypeService->updateFileSubtype($fileSubtype, $validated);
            return redirect()->route('file-subtypes.index')
                ->with('success', 'Subtipo de archivo actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(FileSubtype $fileSubtype)
    {
        try {
            $this->fileSubtypeService->deleteFileSubtype($fileSubtype);
            return redirect()->route('file-subtypes.index')
                ->with('success', 'Subtipo de archivo eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}