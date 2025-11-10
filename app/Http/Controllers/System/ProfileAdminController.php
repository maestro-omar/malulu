<?php

namespace App\Http\Controllers\System;

use App\Models\Entities\User;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Diglactic\Breadcrumbs\Breadcrumbs;
use App\Services\UserService;

class ProfileAdminController extends SystemBaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function show(): Response
    {
        $user = auth()->user();
        $userData = $this->userService->getFullUserShowData($user);
        return Inertia::render('Users/Show', [
            'user' => $userData,
            'files' => $this->userService->getFiles($user, $user),
            'genders' => User::genders(),
            'breadcrumbs' => Breadcrumbs::generate('profile.show', $user),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = auth()->user();
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'provinces' => \App\Models\Catalogs\Province::orderBy('order')->get(),
            'countries' => \App\Models\Catalogs\Country::orderBy('order')->get(),
            'genders' => \App\Models\Entities\User::genders(),
            'breadcrumbs' => Breadcrumbs::generate('profile.edit', $user),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Upload profile picture for the authenticated user.
     */
    public function uploadImage(Request $request)
    {
        try {
            $user = $request->user();
            if ($this->userService->updateUserImage($user, $request))
                return back()->with('success', 'Imagen subida exitosamente');
            else
                return back()->with('error', 'Hubo un problema inesperado al subir la imagen');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete profile picture for the authenticated user.
     */
    public function deleteImage(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:picture'
            ]);

            $user = $request->user();
            $type = $request->input('type');

            // Delete the image file if it exists
            if ($type === 'picture' && $user->picture) {
                $oldPath = str_replace('/storage/', '', $user->picture);
                Storage::disk('public')->delete($oldPath);
                $user->update(['picture' => null]);
            }

            return back()->with('success', 'Imagen eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
