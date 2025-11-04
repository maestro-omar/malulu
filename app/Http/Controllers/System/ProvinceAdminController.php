<?php

namespace App\Http\Controllers\System;

use App\Models\Catalogs\Province;
use App\Models\Entities\File;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ProvinceService;
use App\Services\FileService;
use App\Http\Controllers\System\SystemBaseController;
use App\Traits\FileControllerTrait;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProvinceAdminController extends SystemBaseController
{
    use FileControllerTrait;

    protected $provinceService;

    const PICTURE_PATH = 'provincia-imagenes';

    public function __construct(ProvinceService $provinceService, FileService $fileService)
    {
        $this->provinceService = $provinceService;
        $this->fileService = $fileService;
        $this->middleware('auth');
        $this->middleware('can:superadmin');
    }

    public function index()
    {
        return Inertia::render('Provinces/Index', [
            'provinces' => $this->provinceService->getProvinces(),
            'breadcrumbs' => Breadcrumbs::generate('provinces.index'),
        ]);
    }

    public function edit(Province $province)
    {
        return Inertia::render('Provinces/Edit', [
            'province' => $province,
            'breadcrumbs' => Breadcrumbs::generate('provinces.edit', $province),
        ]);
    }

    public function update(Request $request, Province $province)
    {
        try {
            $this->provinceService->updateProvince($province, $request->all());
            return redirect()->route('provinces.show', $province)->with('success', 'Provincia actualizado.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Province $province)
    {
        try {
            $this->provinceService->deleteProvince($province);
            return redirect()->route('provinces.index')
                ->with('success', 'Provincia eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Province $province)
    {
        $loggedUser = auth()->user();
        $files = $this->fileService->getProvinceFiles($province, $loggedUser);
        
        return Inertia::render('Provinces/Show', [
            'province' => $province,
            'files' => $files,
            'breadcrumbs' => \Diglactic\Breadcrumbs\Breadcrumbs::generate('provinces.show', $province),
        ]);
    }

    public function uploadImage(Request $request, Province $province)
    {
        try {
            $request->validate([
                'image' => 'required|image|max:2048', // Max 2MB
                'type' => 'required|in:logo1,logo2'
            ]);

            $image = $request->file('image');
            $type = $request->input('type');

            // Delete old image if exists
            if ($province->$type) {
                $oldPath = str_replace('/storage/', '', $province->$type);
                Storage::disk('public')->delete($oldPath);
            }

            // Generate timestamp and slugged filename
            $timestamp = now()->format('YmdHis');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $sluggedName = Str::slug($originalName);
            $newFilename = $timestamp . '_' . $sluggedName . '.' . $extension;

            // Store new image with custom filename
            $path = $image->storeAs(self::PICTURE_PATH . '/' . $province->code, $newFilename, 'public');

            // Get the full URL for the stored image using the asset helper
            $url = asset('storage/' . $path);

            // Update province with new image path
            $province->update([
                $type => $url
            ]);

            return back()->with('success', 'Imagen subida exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function deleteImage(Request $request, Province $province)
    {
        try {
            $request->validate([
                'type' => 'required|in:logo1,logo2'
            ]);

            $type = $request->input('type');

            // Delete the image file if it exists
            if ($province->$type) {
                $oldPath = str_replace('/storage/', '', $province->$type);
                Storage::disk('public')->delete($oldPath);
                $province->update([$type => null]);
            }

            return back()->with('success', 'Imagen eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // File Management Methods
    public function createForProvince(Request $request, Province $province)
    {
        $subTypes = $this->getSubtypesForContext('province', $province);
        $storeUrl = $this->getStoreUrlForContext('province', $province);
        $cancelUrl = $this->getCancelUrlForContext('province', $province);

        return Inertia::render('Files/byProvince/Create', [
            'subTypes' => $subTypes,
            'context' => 'province',
            'contextId' => $province->id,
            'province' => $province,
            'storeUrl' => $storeUrl,
            'cancelUrl' => $cancelUrl,
            'breadcrumbs' => Breadcrumbs::generate('provinces.file.create', $province),
        ]);
    }

    public function storeForProvince(Request $request, Province $province)
    {
        return $this->storeFile($request, 'province', $province);
    }

    public function showForProvince(Request $request, Province $province, File $file)
    {
        $loggedUser = auth()->user();
        $fileData = $this->fileService->getFileDataForProvince($file, $loggedUser, $province);
        $history = $fileData['history'];
        return Inertia::render('Files/byProvince/Show', [
            'file' => $fileData['file'],
            'province' => $province,
            'history' => $history,
            'breadcrumbs' => Breadcrumbs::generate('provinces.file.show', $province, $file),
        ]);
    }

    public function editForProvince(Request $request, Province $province, File $file)
    {
        $loggedUser = auth()->user();
        $fileData = $this->fileService->getFileDataForProvince($file, $loggedUser, $province);
        $subTypes = $this->getSubtypesForContext('province', $province);
        $updateUrl = route('provinces.file.update', ['province' => $province->code, 'file' => $file->id]);
        $cancelUrl = route('provinces.file.show', ['province' => $province->code, 'file' => $file->id]);

        return Inertia::render('Files/byProvince/Edit', [
            'file' => $fileData['file'],
            'existingFile' => $fileData['file'],
            'province' => $province,
            'subTypes' => $subTypes,
            'updateUrl' => $updateUrl,
            'cancelUrl' => $cancelUrl,
            'breadcrumbs' => Breadcrumbs::generate('provinces.file.edit', $province, $file),
        ]);
    }

    public function updateForProvince(Request $request, Province $province, File $file)
    {
        return $this->updateFile($request, $file, 'province', $province);
    }

    public function replaceForProvince(Request $request, Province $province, File $file)
    {
        if ($request->isMethod('get')) {
            $loggedUser = auth()->user();
            $fileData = $this->fileService->getFileDataForProvince($file, $loggedUser, $province);
            $subTypes = $this->getSubtypesForContext('province', $province);
            $storeUrl = route('provinces.file.replace', ['province' => $province->code, 'file' => $file->id]);
            $cancelUrl = route('provinces.file.show', ['province' => $province->code, 'file' => $file->id]);

            return Inertia::render('Files/byProvince/Replace', [
                'file' => $fileData['file'],
                'existingFile' => $fileData['file'],
                'province' => $province,
                'subTypes' => $subTypes,
                'context' => 'province',
                'contextId' => $province->id,
                'storeUrl' => $storeUrl,
                'cancelUrl' => $cancelUrl,
                'breadcrumbs' => Breadcrumbs::generate('provinces.file.replace', $province, $file),
            ]);
        }

        return $this->replaceFile($request, $file, 'province', $province);
    }
}
