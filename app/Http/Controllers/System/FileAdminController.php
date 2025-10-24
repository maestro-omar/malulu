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
            'activeSchool' => $loggedUser->activeSchool,
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
}
