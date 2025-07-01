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

class ProvinceAdminController extends SystemBaseController
{
    protected $provinceService;

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
            $validated = $request->validate([
                'code' => 'required|string|unique:provinces,code,' . $province->id,
                'name' => 'required|string',
                'order' => 'nullable|integer',
                'logo1' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'logo2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'title' => 'nullable|string',
                'subtitle' => 'nullable|string',
                'link' => 'nullable|string',
            ]);

            // Handle file uploads
            if ($request->hasFile('logo1')) {
                $logo1Path = $request->file('logo1')->store('province-logos', 'public');
                $validated['logo1'] = '/storage/' . $logo1Path;
            } else {
                $validated['logo1'] = $province->logo1;
            }
            if ($request->hasFile('logo2')) {
                $logo2Path = $request->file('logo2')->store('province-logos', 'public');
                $validated['logo2'] = '/storage/' . $logo2Path;
            } else {
                $validated['logo2'] = $province->logo2;
            }

            $this->provinceService->updateProvince($province, $validated);
            return redirect()->route('provinces.index')
                ->with('success', 'Provincia actualizada correctamente.');
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
            $path = $image->storeAs('province-logos/' . $province->code, $newFilename, 'public');

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
