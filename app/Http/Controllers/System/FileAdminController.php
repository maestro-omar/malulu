<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Services\FileService;
use App\Services\CourseService;
use App\Services\UserService;
use App\Models\Entities\User;
use App\Models\Entities\File;
use App\Models\Catalogs\FileType;
use App\Models\Catalogs\FileSubtype;
use App\Traits\FileControllerTrait;
use Inertia\Inertia;

use Diglactic\Breadcrumbs\Breadcrumbs;

class FileAdminController extends SystemBaseController
{
    use FileControllerTrait;
    
    protected $courseService;
    protected $userService;

    public function __construct(UserService $userService, CourseService $courseService, FileService $fileService)
    {
        $this->userService = $userService;
        $this->courseService = $courseService;
        $this->fileService = $fileService;
    }

    public function create(Request $request)
    {
        $loggedUser = auth()->user();

        return Inertia::render('Files/Create', [
            'user' => $loggedUser,
            'activeSchool' => \App\Services\UserContextService::activeSchool(),
            'breadcrumbs' => Breadcrumbs::generate('files.create'),
        ]);
    }

    public function createForUser(Request $request, User $user)
    {
        $subTypes = $this->getSubtypesForContext('user', $user);
        $storeUrl = $this->getStoreUrlForContext('user', $user);
        $cancelUrl = $this->getCancelUrlForContext('user', $user);

        return Inertia::render('Files/byUser/Create', [
            'subTypes' => $subTypes,
            'context' => 'user',
            'contextId' => $user->id,
            'storeUrl' => $storeUrl,
            'cancelUrl' => $cancelUrl,
            'breadcrumbs' => Breadcrumbs::generate('users.file.create', $user),
        ]);
    }

    public function storeForUser(Request $request, User $user)
    {
        return $this->storeFile($request, 'user', $user);
    }

    public function showForUser(Request $request, User $user, File $file)
    {
        $loggedUser = auth()->user();
        $fileData = $this->fileService->getFileDataForUser($file, $loggedUser, $user);
        $history = $fileData['history'];
        return Inertia::render('Files/byUser/Show', [
            'file' => $fileData['file'],
            'user' => $user,
            'history' => $history,
            'breadcrumbs' => Breadcrumbs::generate('users.file.show', $user, $file),
        ]);
    }

    public function editForUser(Request $request, User $user, File $file)
    {
        $loggedUser = auth()->user();
        $fileData = $this->fileService->getFileDataForUser($file, $loggedUser, $user);
        $subTypes = $this->fileService->getSubtypesForUser($user);

        return Inertia::render('Files/byUser/Edit', [
            'file' => $fileData,
            'user' => $user,
            'subTypes' => $subTypes,
            'breadcrumbs' => Breadcrumbs::generate('users.file.edit', $user, $file),
        ]);
    }

    public function replaceForUser(Request $request, User $user, File $file)
    {
        if ($request->isMethod('get')) {
            $loggedUser = auth()->user();
            $fileData = $this->fileService->getFileDataForUser($file, $loggedUser, $user);
            $subTypes = $this->fileService->getSubtypesForUser($user);

            return Inertia::render('Files/byUser/Replace', [
                'file' => $fileData['file'],
                'user' => $user,
                'subTypes' => $subTypes,
                'breadcrumbs' => Breadcrumbs::generate('users.file.replace', $user, $file),
            ]);
        }

        return $this->replaceFile($request, $file, 'user', $user);
    }

    public function updateForUser(Request $request, User $user, File $file)
    {
        return $this->updateFile($request, $file, 'user', $user);
    }

    public function index(Request $request)
    {
        $loggedUser = auth()->user();
        $files = $this->fileService->getAllFilesForUser($loggedUser);

        return Inertia::render('Files/Index', [
            'files' => $files,
            'types' => FileType::all(),
            'subtypes' => FileSubtype::all(),
            'breadcrumbs' => Breadcrumbs::generate('files.index'),
        ]);
    }

    // Profile (own) files — permission: profile.edit, always for auth user

    public function indexForProfile(Request $request)
    {
        $user = auth()->user();
        $files = $this->fileService->getOwnProfileFiles($user);

        return Inertia::render('Files/Index', [
            'files' => $files,
            'types' => FileType::all(),
            'subtypes' => FileSubtype::all(),
            'breadcrumbs' => Breadcrumbs::generate('profile.files'),
        ]);
    }

    public function createForProfile(Request $request)
    {
        $user = auth()->user();
        $subTypes = $this->getSubtypesForContext('profile', $user);
        $storeUrl = $this->getStoreUrlForContext('profile', $user);
        $cancelUrl = $this->getCancelUrlForContext('profile', $user);

        return Inertia::render('Files/byUser/Create', [
            'subTypes' => $subTypes,
            'context' => 'profile',
            'contextId' => $user->id,
            'storeUrl' => $storeUrl,
            'cancelUrl' => $cancelUrl,
            'breadcrumbs' => Breadcrumbs::generate('profile.files.create'),
        ]);
    }

    public function storeForProfile(Request $request)
    {
        return $this->storeFile($request, 'profile', auth()->user());
    }

    public function showForProfile(Request $request, File $file)
    {
        $this->authorizeProfileFile($file);
        $user = auth()->user();
        $fileData = $this->fileService->getFileDataForUser($file, $user, $user);
        $history = $fileData['history'];

        return Inertia::render('Files/byUser/Show', [
            'file' => $fileData['file'],
            'user' => $user,
            'history' => $history,
            'context' => 'profile',
            'breadcrumbs' => Breadcrumbs::generate('profile.file.show', $file),
        ]);
    }

    public function editForProfile(Request $request, File $file)
    {
        $this->authorizeProfileFile($file);
        $user = auth()->user();
        $fileData = $this->fileService->getFileDataForUser($file, $user, $user);
        $subTypes = $this->fileService->getSubtypesForUser($user);

        return Inertia::render('Files/byUser/Edit', [
            'file' => $fileData,
            'user' => $user,
            'subTypes' => $subTypes,
            'context' => 'profile',
            'breadcrumbs' => Breadcrumbs::generate('profile.file.edit', $file),
        ]);
    }

    public function updateForProfile(Request $request, File $file)
    {
        $this->authorizeProfileFile($file);
        return $this->updateFile($request, $file, 'profile', auth()->user());
    }

    public function replaceForProfile(Request $request, File $file)
    {
        $this->authorizeProfileFile($file);
        $user = auth()->user();

        if ($request->isMethod('get')) {
            $fileData = $this->fileService->getFileDataForUser($file, $user, $user);
            $subTypes = $this->fileService->getSubtypesForUser($user);

            return Inertia::render('Files/byUser/Replace', [
                'file' => $fileData['file'],
                'user' => $user,
                'subTypes' => $subTypes,
                'context' => 'profile',
                'breadcrumbs' => Breadcrumbs::generate('profile.file.replace', $file),
            ]);
        }

        return $this->replaceFile($request, $file, 'profile', $user);
    }

    private function authorizeProfileFile(File $file): void
    {
        if ($file->target_user_id !== auth()->id()) {
            abort(403);
        }
    }
}
