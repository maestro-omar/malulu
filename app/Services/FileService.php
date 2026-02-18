<?php

namespace App\Services;

use App\Models\Entities\File;
use App\Models\Entities\User;
use App\Models\Entities\School;
use App\Models\Entities\Course;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\FileSubtype;
use App\Models\Catalogs\FileType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FileService
{
    public function createSeederFile(string $fileInDisk, string $folder, int $fileSubTypeId, string $relatedWith = '', ?int $relatedWithId = null, ?int $replacingFileId = null)
    {
        $userId = Auth::id() ?: 1;

        $niceName = basename($fileInDisk);
        $localUploadedFile = new UploadedFile(
            $fileInDisk,                      // full path to the file
            $niceName,            // original file name
            mime_content_type($fileInDisk),   // mime type
            null,                             // size (null will auto-detect)
            true                              // mark as test mode (skip is_uploaded_file)
        );

        $niceName = 'HH ' . explode('.', $niceName)[0];
        $this->setNewFile($localUploadedFile, $folder, $userId, $fileSubTypeId, $niceName, '', $relatedWith, $relatedWithId, '', true, [], [], $replacingFileId);
    }


    public function createFile(array $data)
    {
        $validatedData = $this->validateFileData($data);
        return File::create($validatedData);
    }

    public function createExternalUrlFile(array $data)
    {
        $validatedData = $this->validateFileData($data);
        return File::create($validatedData);
    }

    /**
     * Create a file for any context (user, school, course, province)
     * Handles validation, file uploads, and external URLs
     */
    public function createFileForContext(
        array $data,
        ?UploadedFile $uploadedFile,
        ?string $externalUrl,
        string $context,
        $contextModel,
        int $createdByUserId
    ): File {
        // Validate input data
        $validator = Validator::make($data + ['external_url' => $externalUrl], [
            'subtype_id' => [
                'required',
                Rule::exists('file_subtypes', 'id')
            ],
            'nice_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_url' => ['nullable', 'url'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date']
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        // Custom validation: ensure either file or external_url is provided
        if (!$uploadedFile && empty($externalUrl)) {
            throw ValidationException::withMessages([
                'file' => ['Debe proporcionar un archivo o una URL externa.']
            ]);
        }

        // Validate valid_until is after valid_from if both are provided
        if (isset($validated['valid_from']) && isset($validated['valid_until'])) {
            $validator = Validator::make($validated, [
                'valid_until' => ['after_or_equal:valid_from']
            ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }

        // Get context ID
        $contextId = $this->getContextId($context, $contextModel);

        // Create file based on type
        if ($uploadedFile) {
            // Handle file upload
            $file = $this->setNewFile(
                $uploadedFile,
                $context,
                $createdByUserId,
                (int)$validated['subtype_id'],
                $validated['nice_name'],
                '',
                $context,
                $contextId,
                $validated['description'] ?? '',
                true,
                [],
                [],
                null // Not replacing a file
            );
        } else {
            // Handle external URL
            $file = $this->setExternalUrlFile(
                $createdByUserId,
                (int)$validated['subtype_id'],
                $validated['nice_name'],
                $context,
                $contextId,
                $validated['description'] ?? '',
                (string)$externalUrl,
                true,
                [],
                null // Not replacing a file
            );
        }

        // Handle expiration dates if provided
        if (isset($validated['valid_from']) || isset($validated['valid_until'])) {
            $file->update([
                'valid_from' => $validated['valid_from'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null
            ]);
        }

        return $file;
    }

    /**
     * Update file metadata (does not change file content or URL)
     */
    public function updateFileMetadata(File $file, array $data): File
    {
        $rules = [
            'subtype_id' => [
                'required',
                Rule::exists('file_subtypes', 'id')
            ],
            'nice_name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date']
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        $file->update($validated);
        return $file;
    }

    /**
     * Create an External URL file entry, optionally marking another file as replaced.
     */
    public function setExternalUrlFile(
        int $createdByUserId,
        int $fileSubTypeId,
        string $niceName,
        ?string $relatedWith,
        ?int $relatedWithId,
        string $description = '',
        string $externalUrl = '',
        bool $active = true,
        array $metadata = [],
        $replacingFileId = null
    ): File {
        $fileData = [];
        $fileData['subtype_id'] = $fileSubTypeId;
        $fileData['user_id'] = $createdByUserId;
        $fileData['replaced_by_id'] = null;

        switch ($relatedWith) {
            case 'province':
                $fileData['province_id'] = $relatedWithId;
                break;
            case 'school':
                $fileData['school_id'] = $relatedWithId;
                break;
            case 'course':
                $fileData['course_id'] = $relatedWithId;
                break;
            case 'user':
            case 'profile':
                $fileData['target_user_id'] = $relatedWithId;
                break;
        }

        $fileData['nice_name'] = $niceName;
        $fileData['description'] = $description;
        $fileData['active'] = $active;
        $fileData['metadata'] = $metadata;
        $fileData['external_url'] = $externalUrl;

        $file = $this->createExternalUrlFile($fileData);

        // Ensure we have a fresh instance with the new ID
        $newFileId = $file->id;

        if ($replacingFileId) {
            // Only update the old file if we have a different ID
            if ($newFileId != $replacingFileId) {
                File::where('id', $replacingFileId)->update(['replaced_by_id' => $newFileId, 'active' => false]);
            } else {
                throw new \RuntimeException('Error: Cannot replace file - new file has same ID as file to replace.');
            }
        }

        // Return a fresh instance to avoid any potential caching issues
        return File::find($newFileId);
    }

    /**
     * Replace a file for any context (user, school, course, province).
     * It creates a new File entry and deactivates the previous one, linking it via replaced_by_id.
     */
    public function replaceFile(
        File $fileToReplace,
        array $data,
        ?UploadedFile $uploadedFile,
        ?string $externalUrl,
        string $context,
        $contextModel,
        int $createdByUserId
    ): File {
        // Validate minimal metadata
        $validator = Validator::make($data + ['external_url' => $externalUrl], [
            'subtype_id' => [
                'required',
                Rule::exists('file_subtypes', 'id')
            ],
            'nice_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_url' => ['nullable', 'url'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date']
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $validated = $validator->validated();

        // Ensure either upload or external URL is present
        if (!$uploadedFile && empty($externalUrl)) {
            throw ValidationException::withMessages([
                'file' => ['Debe proporcionar un archivo o una URL externa.']
            ]);
        }

        // Get context ID
        $contextId = $this->getContextId($context, $contextModel);

        // Store the old file ID to ensure we're creating a new one
        $oldFileId = $fileToReplace->id;

        // Create replacement file
        if ($uploadedFile) {
            $newFile = $this->setNewFile(
                $uploadedFile,
                $context,
                $createdByUserId,
                (int)$validated['subtype_id'],
                $validated['nice_name'],
                '',
                $context,
                $contextId,
                $validated['description'] ?? '',
                true,
                [],
                [],
                $oldFileId
            );
        } else {
            $newFile = $this->setExternalUrlFile(
                $createdByUserId,
                (int)$validated['subtype_id'],
                $validated['nice_name'],
                $context,
                $contextId,
                $validated['description'] ?? '',
                (string)$externalUrl,
                true,
                [],
                $oldFileId
            );
        }

        // Verify we created a new file (not updating the old one)
        if ($newFile->id === $oldFileId) {
            throw new \RuntimeException('Error: A new file was not created. The existing file would have been updated.');
        }

        // Get a fresh instance to ensure we're not working with a cached or shared instance
        $freshFile = File::find($newFile->id);
        if (!$freshFile || $freshFile->id === $oldFileId) {
            throw new \RuntimeException('Error: Could not retrieve the newly created file or it has the same ID as the file being replaced.');
        }

        // Handle expiration dates if provided
        if (isset($validated['valid_from']) || isset($validated['valid_until'])) {
            $freshFile->update([
                'valid_from' => $validated['valid_from'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null
            ]);
            // Reload after update to ensure we have latest data
            $freshFile->refresh();
        }

        return $freshFile;
    }

    /**
     * Replace a user's file (convenience method that calls replaceFile).
     */
    public function replaceUserFile(
        User $targetUser,
        File $fileToReplace,
        array $data,
        ?UploadedFile $uploadedFile,
        ?string $externalUrl,
        int $createdByUserId
    ): File {
        return $this->replaceFile(
            $fileToReplace,
            $data,
            $uploadedFile,
            $externalUrl,
            'user',
            $targetUser,
            $createdByUserId
        );
    }

    /**
     * Get the context ID from a context model
     */
    private function getContextId(string $context, $contextModel): int
    {
        switch ($context) {
            case 'user':
            case 'profile':
                return $contextModel instanceof User ? $contextModel->id : $contextModel;
            case 'school':
                return $contextModel instanceof School ? $contextModel->id : $contextModel;
            case 'course':
                return $contextModel instanceof Course ? $contextModel->id : $contextModel;
            case 'province':
                return $contextModel instanceof Province ? $contextModel->id : $contextModel;
            default:
                throw new \InvalidArgumentException("Unknown context: {$context}");
        }
    }

    /**
     * Get the newly created file that replaced the given file
     */
    private function getReplacedFile(File $fileToReplace): File
    {
        // Refresh to get updated replaced_by_id
        $fileToReplace->refresh();

        if ($fileToReplace->replaced_by_id) {
            $newFile = File::find($fileToReplace->replaced_by_id);
            if ($newFile) {
                return $newFile;
            }
        }

        // Fallback: find by query (in case refresh didn't work)
        $newFile = File::where('replaced_by_id', null)
            ->where('id', '>', $fileToReplace->id)
            ->latest('id')
            ->first();

        if (!$newFile) {
            throw new \RuntimeException('No se pudo encontrar el archivo reemplazado.');
        }

        return $newFile;
    }

    private function validateFileData(array $data)
    {
        $rules = [
            'subtype_id' => [
                'required',
                Rule::exists('file_subtypes', 'id')
            ],
            'user_id' => [
                Rule::exists('users', 'id')
            ],
            'replaced_by_id' => [
                'nullable',
                Rule::exists('file', 'id')
            ],
            'province_id' => ['nullable', 'integer'],
            'school_id' => ['nullable', 'integer'],
            'course_id' => ['nullable', 'integer'],
            'target_user_id' => ['nullable', 'integer'],
            'nice_name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'external_url' => ['nullable', 'url'],
            'active' => ['boolean']
        ];

        // For file uploads, require file-specific fields
        if (empty($data['external_url'])) {
            $rules['original_name'] = ['required', 'string'];
            $rules['filename'] = ['required', 'string'];
            $rules['mime_type'] = ['required', 'string'];
            $rules['size'] = ['required', 'integer'];
            $rules['path'] = ['required', 'string'];
        }

        // For external URLs, make file-specific fields optional
        if (!empty($data['external_url'])) {
            $rules['original_name'] = ['nullable', 'string'];
            $rules['filename'] = ['nullable', 'string'];
            $rules['mime_type'] = ['nullable', 'string'];
            $rules['size'] = ['nullable', 'integer'];
            $rules['path'] = ['nullable', 'string'];
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function setNewFile(
        UploadedFile $localUploadedFile,
        string $destinationSubFolder,
        int $createdByUserId,
        int $fileSubTypeId,
        string $niceName,
        string $customFileName = '',
        ?string $relatedWith = null,
        ?int $relatedWithId = null,
        string $description = '',
        bool $active = true,
        array $metadata = [],
        array $visibilityOptions = [],
        $replacingFileId = null
    ) {
        // Prepare filename: use $customFileName if provided, else original name (without extension)
        $originalExtension = $localUploadedFile->getClientOriginalExtension();
        $originalNameWithoutExt = pathinfo($localUploadedFile->getClientOriginalName(), PATHINFO_FILENAME);


        $slugify = function ($text) {
            $slug = Str::slug($text);
            return $slug !== '' ? $slug : 'file';
        };

        $baseFileName = $customFileName !== '' ? $customFileName : $originalNameWithoutExt;
        $slugifiedFileName = $slugify($baseFileName);
        $finalFileName = $slugifiedFileName . '.' . $originalExtension;

        $basePath = 'files' . DIRECTORY_SEPARATOR . trim(trim($destinationSubFolder, '/'), '\\');
        $storagePath = 'public' . DIRECTORY_SEPARATOR . $basePath;
        $publicPath = str_replace(DIRECTORY_SEPARATOR, '/', $basePath);
        $storedFullPathAndName = $localUploadedFile->storeAs($storagePath, $finalFileName);
        if (!$storedFullPathAndName)
            throw new \Exception('Error storing file');


        $fileData = [];
        $fileData['subtype_id'] = $fileSubTypeId;
        $fileData['user_id'] = $createdByUserId;
        $fileData['replaced_by_id'] = null;

        // Set the appropriate foreign key based on the related type
        switch ($relatedWith) {
            case 'province':
                $fileData['province_id'] = $relatedWithId;
                break;
            case 'school':
                $fileData['school_id'] = $relatedWithId;
                break;
            case 'course':
                $fileData['course_id'] = $relatedWithId;
                break;
            case 'user':
            case 'profile':
                $fileData['target_user_id'] = $relatedWithId;
                break;
        }
        $fileData['nice_name'] = $niceName;
        $fileData['original_name'] = $localUploadedFile->getClientOriginalName();
        $fileData['filename'] = $finalFileName;
        $fileData['mime_type'] = $localUploadedFile->getClientMimeType();
        $fileData['size'] = $localUploadedFile->getSize();
        $fileData['path'] = $publicPath;
        $fileData['description'] = $description;
        $fileData['active'] = $active;
        $fileData['metadata'] = $metadata;

        $newFile = $this->createFile($fileData);

        // Ensure we have a fresh instance with the new ID
        $newFileId = $newFile->id;

        if ($replacingFileId) {
            // Only update the old file if we have a different ID
            if ($newFileId != $replacingFileId) {
                File::where('id', $replacingFileId)->update(['replaced_by_id' => $newFileId, 'active' => false]);
            } else {
                throw new \RuntimeException('Error: Cannot replace file - new file has same ID as file to replace.');
            }
        }

        $this->setVisibility($newFile, $createdByUserId, $visibilityOptions);

        // Return a fresh instance to avoid any potential caching issues
        return File::find($newFileId);
    }

    public function setVisibility($file, $userId, $visibilityOptions)
    {
        if (empty($visibilityOptions)) return; //public
        throw new \Exception('TODO OMAR - Implementar permisos de visibilidad por archivo');
    }

    public function getUserFiles(User $user, User $loggedUser, bool $onlyActive = true)
    {
        $files = $user->files;
        if ($onlyActive) {
            $files = $files->where('active', true);
        }
        $files = $this->checkAndFormatEach($files, $loggedUser);
        return $files ?: null;
    }

    public function getSchoolFiles(School $school, User $loggedUser)
    {
        $files = $school->files;
        $files = $this->checkAndFormatEach($files, $loggedUser);
        return $files ?: null;
    }

    public function getProvinceFiles(Province $province, User $loggedUser, bool $onlyActive = true)
    {
        $query = File::where('province_id', $province->id)->with(['subtype', 'subtype.fileType']);
        if ($onlyActive) {
            $query->where('active', true);
        }
        $files = $query->get();
        $files = $this->checkAndFormatEach($files, $loggedUser);
        return $files;
    }

    private function checkFileVisibility(File $file, User $loggedUser, bool $throwException)
    {
        return true; //OMAR TODO 
    }

    private function checkAndFormatEach($files, User $loggedUser)
    {
        if (empty($files)) return null;
        $files = $files->map(function ($file) use ($loggedUser) {
            if ($this->checkFileVisibility($file, $loggedUser, false)) {
                return $this->formatFileForTable($file);
            } else {
                return null;
            }
        });
        return array_values($files->filter()->toArray()); //need to reset indexes
    }

    private function formatFileForTable(File $file)
    {
        $replaces = $file->replacedFile;
        $data = [
            'id' => $file->id,
            'nice_name' => $file->nice_name,
            'description' => $file->description,
            'original_name' => $file->original_name,
            'subtype' => $file->subtype->name,
            'type' => $file->subtype->filetype->name,
            'created_at' => $file->created_at->format('d/m/Y H:i'),
            'created_by' => $file->user->firstname . ' ' . $file->user->lastname,
            'replaces' =>  $replaces->pluck('nice_name')->join(', '),
            'url' =>  $file->url,
            'is_external' => $file->is_external
        ];
        return $data;
    }

    private function formatFileForHistoryTable(int $level, File $file)
    {
        $data = [
            'level' => $level,
            'id' => $file->id,
            'nice_name' => $file->nice_name,
            'subtype' => $file->subtype->name,
            'created_at' => $file->created_at->format('d/m/Y H:i'),
            'created_by' => $file->user->firstname . ' ' . $file->user->lastname,
            'url' =>  $file->url,
            'is_external' => $file->is_external
        ];
        return $data;
    }

    public function getSubtypesForCourse(Course $dummyByNow)
    {
        return FileSubtype::byFileTypeCode(FileType::COURSE)->get();
    }

    public function getSubtypesForUser(User $dummyByNow)
    {
        return FileSubtype::byFileTypeCode(FileType::USER)->get();
    }

    public function getSubtypesForStudent(User $dummyByNow)
    {
        return FileSubtype::byFileTypeCode(FileType::STUDENT)->get();
    }

    public function getSubtypesForTeacher(User $dummyByNow)
    {
        return FileSubtype::byFileTypeCode(FileType::TEACHER)->get();
    }

    public function getSubtypesForSchool(School $dummyByNow)
    {
        return FileSubtype::byFileTypeCode(FileType::INSTITUTIONAL)->get();
    }

    public function getSubtypesForProvince(Province $dummyByNow)
    {
        return FileSubtype::byFileTypeCode(FileType::PROVINCIAL)->get();
    }

    public function getCourseFiles(Course $course, User $loggedUser, bool $onlyActive = true)
    {
        $query = File::where('course_id', $course->id)->with(['subtype', 'subtype.fileType']);
        if ($onlyActive) {
            $query->where('active', true);
        }
        $files = $query->get();
        $files = $this->checkAndFormatEach($files, $loggedUser);
        return $files;
    }

    public function getFileDataForUser(File $file, User $loggedUser, User $user)
    {
        $this->checkFileVisibility($file, $loggedUser, true);
        if ($file->target_user_id != $user->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Este archivo no pertenece al usuario indicado');
        }
        $file->load('subtype', 'subtype.fileType', 'user');
        $history = $file->historyFlattened();
        $history = array_map(function ($item) use ($loggedUser) {
            return $this->formatFileForHistoryTable($item['level'], $item['file']);
        }, $history);

        return ['file' => $file, 'history' => $history];
    }

    public function getFileDataForCourse(File $file, User $loggedUser, Course $course)
    {
        $this->checkFileVisibility($file, $loggedUser, true);
        if ($file->course_id != $course->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Este archivo no pertenece al curso indicado');
        }
        $file->load('subtype', 'subtype.fileType', 'course', 'user');
        $history = $file->historyFlattened();
        $history = array_map(function ($item) use ($loggedUser) {
            return $this->formatFileForHistoryTable($item['level'], $item['file']);
        }, $history);

        return ['file' => $file, 'history' => $history];
    }

    public function getFileDataForSchool(File $file, User $loggedUser, School $school)
    {
        $this->checkFileVisibility($file, $loggedUser, true);
        if ($file->school_id != $school->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Este archivo no pertenece a la institución indicada');
        }
        $file->load('subtype', 'subtype.fileType', 'school', 'user');
        $history = $file->historyFlattened();

        $history = array_map(function ($item) use ($loggedUser) {
            return $this->formatFileForHistoryTable($item['level'], $item['file']);
        }, $history);

        return ['file' => $file, 'history' => $history];
    }

    public function getFileDataForProvince(File $file, User $loggedUser, Province $province)
    {
        $this->checkFileVisibility($file, $loggedUser, true);
        if ($file->province_id != $province->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Este archivo no pertenece a la provincia indicada');
        }
        $file->load('subtype', 'subtype.fileType', 'province', 'user');
        $history = $file->historyFlattened();

        $history = array_map(function ($item) use ($loggedUser) {
            return $this->formatFileForHistoryTable($item['level'], $item['file']);
        }, $history);

        return ['file' => $file, 'history' => $history];
    }

    public function getAllFilesForUser(User $loggedUser)
    {
        // UserContextService::load();
        $schools = UserContextService::relatedSchools();
        $schoolIds = array_keys($schools);

        // Get files related to user's province (PROVINCIAL type)
        $provincialFiles = File::whereHas('subtype.fileType', function ($query) {
            $query->where('code', FileType::PROVINCIAL);
        })
            ->where('province_id', $loggedUser->province_id)
            ->with(['subtype', 'subtype.fileType', 'user', 'province'])
            ->get();


        // Get files related to user's schools (INSTITUTIONAL type)
        $institutionalFiles = File::whereHas('subtype.fileType', function ($query) {
            $query->where('code', FileType::INSTITUTIONAL);
        })
            ->whereIn('school_id', $schoolIds)
            ->with(['subtype', 'subtype.fileType', 'user', 'school'])
            ->get();

        // Get files related to the user directly (USER type)
        $userFiles = File::whereHas('subtype.fileType', function ($query) {
            $query->where('code', FileType::USER);
        })
            ->where('target_user_id', $loggedUser->id)
            ->with(['subtype', 'subtype.fileType', 'user', 'targetUser'])
            ->get();

        // Combine all files
        $allFiles = $provincialFiles->concat($institutionalFiles)->concat($userFiles);

        // Format files for display (use profile.* URLs for current user's own files)
        $formattedFiles = $allFiles->map(function ($file) use ($loggedUser) {
            $formatted = $this->formatFileForTable($file);
            // Add file type context for display
            $formatted['file_type_context'] = $this->getFileTypeContext($file);
            $formatted['edit_url'] = $this->getEditUrl($file, $loggedUser);
            $formatted['replace_url'] = $this->getReplaceUrl($file, $loggedUser);
            $formatted['show_url'] = $this->getShowUrl($file, $loggedUser);
            return $formatted;
        });

        return $formattedFiles->toArray();
    }

    /**
     * Get only the current user's personal files (USER type) for profile context, with profile.* URLs.
     */
    public function getOwnProfileFiles(User $user): array
    {
        $userFiles = File::whereHas('subtype.fileType', function ($query) {
            $query->where('code', FileType::USER);
        })
            ->where('target_user_id', $user->id)
            ->with(['subtype', 'subtype.fileType', 'user', 'targetUser'])
            ->get();

        $formattedFiles = $userFiles->map(function ($file) {
            $formatted = $this->formatFileForTable($file);
            $formatted['file_type_context'] = $this->getFileTypeContext($file);
            $formatted['edit_url'] = route('profile.file.edit', ['file' => $file->id]);
            $formatted['replace_url'] = route('profile.file.replace', ['file' => $file->id]);
            $formatted['show_url'] = route('profile.file.show', ['file' => $file->id]);
            return $formatted;
        });

        return $formattedFiles->toArray();
    }

    private function getFileTypeContext(File $file)
    {
        switch ($file->subtype->fileType->code) {
            case FileType::PROVINCIAL:
                return 'Provincial';
            case FileType::INSTITUTIONAL:
                return 'Institucional';
            case FileType::USER:
                return 'Usuario';
            default:
                return $file->subtype->fileType->name;
        }
    }

    private function getEditUrl(File $file, ?User $currentUser = null)
    {
        switch ($file->subtype->fileType->code) {
            case FileType::PROVINCIAL:
                $province = $file->province;
                return $province ? route('provinces.file.edit', [$province, $file]) : null;
            case FileType::INSTITUTIONAL:
                // Use the loaded school relationship
                $school = $file->school;
                return $school ? route('school.file.edit', ['school' => $school->slug, 'file' => $file->id]) : null;
            case FileType::USER:
                if ($currentUser && (int) $file->target_user_id === (int) $currentUser->id) {
                    return route('profile.file.edit', ['file' => $file->id]);
                }
                return route('users.file.edit', ['user' => $file->target_user_id, 'file' => $file->id]);
            default:
                return null;
        }
    }

    private function getReplaceUrl(File $file, ?User $currentUser = null)
    {
        switch ($file->subtype->fileType->code) {
            case FileType::PROVINCIAL:
                $province = $file->province;
                return $province ? route('provinces.file.replace', [$province, $file]) : null;
            case FileType::INSTITUTIONAL:
                // Use the loaded school relationship
                $school = $file->school;
                return $school ? route('school.file.replace', ['school' => $school->slug, 'file' => $file->id]) : null;
            case FileType::USER:
                if ($currentUser && (int) $file->target_user_id === (int) $currentUser->id) {
                    return route('profile.file.replace', ['file' => $file->id]);
                }
                return route('users.file.replace', ['user' => $file->target_user_id, 'file' => $file->id]);
            default:
                return null;
        }
    }

    private function getShowUrl(File $file, ?User $currentUser = null)
    {
        switch ($file->subtype->fileType->code) {
            case FileType::PROVINCIAL:
                $province = $file->province;
                return $province ? route('provinces.file.show', [$province, $file]) : null;
            case FileType::INSTITUTIONAL:
                // Use the loaded school relationship
                $school = $file->school;
                return $school ? route('school.file.show', ['school' => $school->slug, 'file' => $file->id]) : null;
            case FileType::USER:
                if ($currentUser && (int) $file->target_user_id === (int) $currentUser->id) {
                    return route('profile.file.show', ['file' => $file->id]);
                }
                return route('users.file.show', ['user' => $file->target_user_id, 'file' => $file->id]);
            default:
                return null;
        }
    }
}
