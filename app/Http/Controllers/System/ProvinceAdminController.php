<?php

namespace App\Http\Controllers\System;

use App\Models\Catalogs\Province;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ProvinceService;
use App\Http\Controllers\System\SystemBaseController;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProvinceAdminController extends SystemBaseController
{
    protected $provinceService;

    const PICTURE_PATH = 'provincia-imagenes';

    public function __construct(ProvinceService $provinceService)
    {
        $this->provinceService = $provinceService;
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
        return Inertia::render('Provinces/Show', [
            'province' => $province,
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
}
