<?php

namespace App\Http\Controllers;

use App\Models\FileType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FileTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:superadmin');
    }

    public function index()
    {
        return Inertia::render('FileTypes/Index', [
            'fileTypes' => FileType::all()
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
        $validated = $request->validate([
            'key' => 'required|string|unique:file_types',
            'name' => 'required|string',
            'relate_with' => 'nullable|string',
            'active' => 'boolean'
        ]);

        FileType::create($validated);

        return redirect()->route('file-types.index');
    }

    public function edit(FileType $fileType)
    {
        return Inertia::render('FileTypes/Edit', [
            'fileType' => $fileType
        ]);
    }

    public function update(Request $request, FileType $fileType)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:file_types,key,' . $fileType->id,
            'name' => 'required|string',
            'relate_with' => 'nullable|string',
            'active' => 'boolean'
        ]);

        $fileType->update($validated);

        return redirect()->route('file-types.index');
    }

    public function destroy(FileType $fileType)
    {
        if ($fileType->fileSubtypes()->exists()) {
            return back()->with('error', 'Cannot delete file type because it has related file subtypes.');
        }

        $fileType->delete();
        return redirect()->route('file-types.index');
    }
}