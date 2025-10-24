<?php

namespace App\Traits;

use App\Models\Entities\File;
use App\Models\Entities\User;
use App\Models\Entities\School;
use App\Models\Entities\Course;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\FileType;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

trait FileControllerTrait
{
    protected FileService $fileService;

    /**
     * Get the file service instance
     */
    protected function getFileService(): FileService
    {
        if (!isset($this->fileService)) {
            $this->fileService = app(FileService::class);
        }
        return $this->fileService;
    }

    /**
     * Store a file for any context
     */
    public function storeFile(Request $request, string $context, $contextModel)
    {
        $validated = $request->validate([
            'subtype_id' => 'required|exists:file_subtypes,id',
            'nice_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'context' => 'required|string|in:user,school,course,province',
            'context_id' => 'required',
            'file' => 'nullable|file|max:10240', // 10MB max, nullable for external URLs
            'external_url' => 'nullable|url|max:500', // nullable for file uploads
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from'
        ]);

        // Custom validation: ensure either file or external_url is provided
        if (empty($validated['file']) && empty($validated['external_url'])) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['file' => ['Debe proporcionar un archivo o una URL externa.']]
            );
        }

        try {
            $userId = Auth::id();
            $fileService = $this->getFileService();

            // Prepare file data
            $fileData = [
                'subtype_id' => $validated['subtype_id'],
                'user_id' => $userId,
                'nice_name' => $validated['nice_name'],
                'description' => $validated['description'] ?? '',
                'active' => true,
                'metadata' => []
            ];

            // Set context-specific foreign key
            $this->setContextForeignKey($fileData, $context, $contextModel);

            // Handle file upload or external URL
            if (!empty($validated['file']) && $request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $this->handleFileUpload($fileData, $uploadedFile, $context, $contextModel);
            } elseif (!empty($validated['external_url'])) {
                $this->handleExternalUrl($fileData, $validated['external_url']);
            }

            // Add expiration dates if provided
            if (isset($validated['valid_from'])) {
                $fileData['valid_from'] = $validated['valid_from'];
            }
            if (isset($validated['valid_until'])) {
                $fileData['valid_until'] = $validated['valid_until'];
            }

            // Create the file record
            $file = $fileService->createFile($fileData);

            return $this->getSuccessResponse($file, $context, $contextModel);
        } catch (\Exception $e) {
            return $this->getErrorResponse($e, $context, $contextModel);
        }
    }

    /**
     * Set the appropriate foreign key based on context
     */
    protected function setContextForeignKey(array &$fileData, string $context, $contextModel): void
    {
        switch ($context) {
            case 'user':
                $fileData['target_user_id'] = $contextModel instanceof User ? $contextModel->id : $contextModel;
                break;
            case 'school':
                $fileData['school_id'] = $contextModel instanceof School ? $contextModel->id : $contextModel;
                break;
            case 'course':
                $fileData['course_id'] = $contextModel instanceof Course ? $contextModel->id : $contextModel;
                break;
            case 'province':
                $fileData['province_id'] = $contextModel instanceof Province ? $contextModel->id : $contextModel;
                break;
        }
    }

    /**
     * Handle file upload
     */
    protected function handleFileUpload(array &$fileData, UploadedFile $uploadedFile, string $context, $contextModel): void
    {
        $originalExtension = $uploadedFile->getClientOriginalExtension();
        $originalNameWithoutExt = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

        $slugify = function ($text) {
            $slug = Str::slug($text);
            return $slug !== '' ? $slug : 'file';
        };

        $slugifiedFileName = $slugify($originalNameWithoutExt);
        $finalFileName = $slugifiedFileName . '.' . $originalExtension;

        // Determine storage path based on context
        $basePath = 'files' . DIRECTORY_SEPARATOR . $context;
        $storagePath = 'public' . DIRECTORY_SEPARATOR . $basePath;
        $publicPath = str_replace(DIRECTORY_SEPARATOR, '/', $basePath);

        $storedFullPathAndName = $uploadedFile->storeAs($storagePath, $finalFileName);

        if (!$storedFullPathAndName) {
            throw new \Exception('Error storing file');
        }

        $fileData['original_name'] = $uploadedFile->getClientOriginalName();
        $fileData['filename'] = $finalFileName;
        $fileData['mime_type'] = $uploadedFile->getClientMimeType();
        $fileData['size'] = $uploadedFile->getSize();
        $fileData['path'] = $publicPath;
    }

    /**
     * Handle external URL
     */
    protected function handleExternalUrl(array &$fileData, string $externalUrl): void
    {
        $fileData['external_url'] = $externalUrl;
        $fileData['original_name'] = basename(parse_url($externalUrl, PHP_URL_PATH)) ?: 'Archivo externo';
        $fileData['filename'] = null;
        $fileData['mime_type'] = null;
        $fileData['size'] = null;
        $fileData['path'] = null;
    }

    /**
     * Get success response based on context
     */
    protected function getSuccessResponse(File $file, string $context, $contextModel)
    {
        $message = 'Archivo creado exitosamente.';

        switch ($context) {
            case 'user':
                return redirect()->route('users.file.show', [
                    'user' => $contextModel instanceof User ? $contextModel->id : $contextModel,
                    'file' => $file->id
                ])->with('success', $message);

            case 'school':
                $school = $contextModel instanceof School ? $contextModel : School::find($contextModel);
                return redirect()->route('school.file.show', [
                    'school' => $school->slug,
                    'file' => $file->id
                ])->with('success', $message);

            case 'course':
                // Assuming course files are managed through school context
                $course = $contextModel instanceof Course ? $contextModel : Course::find($contextModel);
                $school = $course->school;
                return redirect()->route('school.course.file.show', [
                    'school' => $school->slug,
                    'schoolLevel' => $course->schoolLevel->code,
                    'idAndLabel' => $course->idAndLabel,
                    'file' => $file->id
                ])->with('success', $message);

            case 'province':
                return redirect()->route('provinces.show', $contextModel instanceof Province ? $contextModel->id : $contextModel)
                    ->with('success', $message);
        }
    }

    /**
     * Get error response based on context
     */
    protected function getErrorResponse(\Exception $e, string $context, $contextModel)
    {
        $message = 'Error al crear el archivo: ' . $e->getMessage();

        switch ($context) {
            case 'user':
                return redirect()->route('users.file.create', [
                    'user' => $contextModel instanceof User ? $contextModel->id : $contextModel
                ])->withErrors(['error' => $message])->withInput();

            case 'school':
                $school = $contextModel instanceof School ? $contextModel : School::find($contextModel);
                return redirect()->route('school.file.create', [
                    'school' => $school->slug
                ])->withErrors(['error' => $message])->withInput();

            case 'course':
                $course = $contextModel instanceof Course ? $contextModel : Course::find($contextModel);
                $school = $course->school;
                return redirect()->route('school.course.file.create', [
                    'school' => $school->slug,
                    'schoolLevel' => $course->schoolLevel->code,
                    'idAndLabel' => $course->idAndLabel
                ])->withErrors(['error' => $message])->withInput();

            case 'province':
                return redirect()->route('provinces.edit', $contextModel instanceof Province ? $contextModel->id : $contextModel)
                    ->withErrors(['error' => $message])->withInput();
        }
    }

    /**
     * Get subtypes for a specific context
     */
    protected function getSubtypesForContext(string $context, $contextModel)
    {
        $fileService = $this->getFileService();

        switch ($context) {
            case 'user':
                $user = $contextModel instanceof User ? $contextModel : User::find($contextModel);
                return $fileService->getSubtypesForUser($user);

            case 'school':
                $school = $contextModel instanceof School ? $contextModel : School::find($contextModel);
                return $fileService->getSubtypesForSchool($school);

            case 'course':
                $course = $contextModel instanceof Course ? $contextModel : Course::find($contextModel);
                return $fileService->getSubtypesForCourse($course);

            case 'province':
                // For provinces, we might want to get all provincial file types
                return \App\Models\Catalogs\FileSubtype::byFileTypeCode(FileType::PROVINCIAL)->get();

            default:
                return collect();
        }
    }

    /**
     * Get the store URL for a specific context
     */
    protected function getStoreUrlForContext(string $context, $contextModel): string
    {
        switch ($context) {
            case 'user':
                return route('users.file.store', [
                    'user' => $contextModel instanceof User ? $contextModel->id : $contextModel
                ]);

            case 'school':
                $school = $contextModel instanceof School ? $contextModel : School::find($contextModel);
                return route('school.file.store', [
                    'school' => $school->slug
                ]);

            case 'course':
                $course = $contextModel instanceof Course ? $contextModel : Course::find($contextModel);
                $school = $course->school;
                return route('school.course.file.store', [
                    'school' => $school->slug,
                    'schoolLevel' => $course->schoolLevel->code,
                    'idAndLabel' => $course->idAndLabel
                ]);
            case 'province':
                return route('provinces.files.store', $contextModel instanceof Province ? $contextModel->id : $contextModel);

            default:
                throw new \InvalidArgumentException("Unknown context: {$context}");
        }
    }

    /**
     * Get the cancel URL for a specific context
     */
    protected function getCancelUrlForContext(string $context, $contextModel): string
    {
        switch ($context) {
            case 'user':
                return route('users.show', $contextModel instanceof User ? $contextModel->id : $contextModel);

            case 'school':
                $school = $contextModel instanceof School ? $contextModel : School::find($contextModel);
                return route('school.show', ['school' => $school->slug]);

            case 'course':
                $course = $contextModel instanceof Course ? $contextModel : Course::find($contextModel);
                $school = $course->school;
                return route('school.course.show', [
                    'school' => $school->slug,
                    'schoolLevel' => $course->schoolLevel->code,
                    'idAndLabel' => $course->idAndLabel
                ]);

            case 'province':
                return route('provinces.show', $contextModel instanceof Province ? $contextModel->id : $contextModel);

            default:
                throw new \InvalidArgumentException("Unknown context: {$context}");
        }
    }

    /**
     * Update file information
     */
    public function updateFile(Request $request, File $file, string $context, $contextModel)
    {
        $validated = $request->validate([
            'subtype_id' => 'required|exists:file_subtypes,id',
            'nice_name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $file->update($validated);
            
            return $this->getUpdateSuccessResponse($file, $context, $contextModel);
        } catch (\Exception $e) {
            return $this->getUpdateErrorResponse($e, $context, $contextModel);
        }
    }

    /**
     * Replace file (upload new file or set external URL)
     */
    public function replaceFile(Request $request, File $file, string $context, $contextModel)
    {
        $validated = $request->validate([
            'subtype_id' => 'required|exists:file_subtypes,id',
            'nice_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max, nullable for external URLs
            'external_url' => 'nullable|url|max:500', // nullable for file uploads
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from'
        ]);

        // Custom validation: ensure either file or external_url is provided
        if (empty($validated['file']) && empty($validated['external_url'])) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['file' => ['Debe proporcionar un archivo o una URL externa.']]
            );
        }

        try {
            // Update basic file information
            $file->update([
                'subtype_id' => $validated['subtype_id'],
                'nice_name' => $validated['nice_name'],
                'description' => $validated['description'] ?? ''
            ]);

            // Handle file upload or external URL
            if (!empty($validated['file']) && $request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $fileData = [];
                $this->handleFileUpload($fileData, $uploadedFile, $context, $contextModel);
                $file->update($fileData);
            } elseif (!empty($validated['external_url'])) {
                $fileData = [];
                $this->handleExternalUrl($fileData, $validated['external_url']);
                $file->update($fileData);
            }

            // Handle expiration dates if provided
            if (isset($validated['valid_from']) || isset($validated['valid_until'])) {
                $file->update([
                    'valid_from' => $validated['valid_from'] ?? null,
                    'valid_until' => $validated['valid_until'] ?? null
                ]);
            }

            return $this->getReplaceSuccessResponse($file, $context, $contextModel);
        } catch (\Exception $e) {
            return $this->getReplaceErrorResponse($e, $context, $contextModel);
        }
    }

    /**
     * Get update success response based on context
     */
    protected function getUpdateSuccessResponse(File $file, string $context, $contextModel)
    {
        $message = 'Archivo actualizado exitosamente.';

        switch ($context) {
            case 'user':
                return redirect()->route('users.file.show', [
                    'user' => $contextModel instanceof User ? $contextModel->id : $contextModel,
                    'file' => $file->id
                ])->with('success', $message);

            case 'school':
                $school = $contextModel instanceof School ? $contextModel : School::find($contextModel);
                return redirect()->route('school.file.show', [
                    'school' => $school->slug,
                    'file' => $file->id
                ])->with('success', $message);

            case 'course':
                $course = $contextModel instanceof Course ? $contextModel : Course::find($contextModel);
                $school = $course->school;
                return redirect()->route('school.course.file.show', [
                    'school' => $school->slug,
                    'schoolLevel' => $course->schoolLevel->code,
                    'idAndLabel' => $course->idAndLabel,
                    'file' => $file->id
                ])->with('success', $message);

            case 'province':
                return redirect()->route('provinces.show', $contextModel instanceof Province ? $contextModel->id : $contextModel)
                    ->with('success', $message);
        }
    }

    /**
     * Get replace success response based on context
     */
    protected function getReplaceSuccessResponse(File $file, string $context, $contextModel)
    {
        $message = 'Archivo reemplazado exitosamente.';

        switch ($context) {
            case 'user':
                return redirect()->route('users.file.show', [
                    'user' => $contextModel instanceof User ? $contextModel->id : $contextModel,
                    'file' => $file->id
                ])->with('success', $message);

            case 'school':
                $school = $contextModel instanceof School ? $contextModel : School::find($contextModel);
                return redirect()->route('school.file.show', [
                    'school' => $school->slug,
                    'file' => $file->id
                ])->with('success', $message);

            case 'course':
                $course = $contextModel instanceof Course ? $contextModel : Course::find($contextModel);
                $school = $course->school;
                return redirect()->route('school.course.file.show', [
                    'school' => $school->slug,
                    'schoolLevel' => $course->schoolLevel->code,
                    'idAndLabel' => $course->idAndLabel,
                    'file' => $file->id
                ])->with('success', $message);

            case 'province':
                return redirect()->route('provinces.show', $contextModel instanceof Province ? $contextModel->id : $contextModel)
                    ->with('success', $message);
        }
    }

    /**
     * Get update error response based on context
     */
    protected function getUpdateErrorResponse(\Exception $exception, string $context, $contextModel)
    {
        $message = 'Error al actualizar el archivo: ' . $exception->getMessage();

        switch ($context) {
            case 'user':
                return redirect()->back()->with('error', $message);

            case 'school':
                return redirect()->back()->with('error', $message);

            case 'course':
                return redirect()->back()->with('error', $message);

            case 'province':
                return redirect()->back()->with('error', $message);
        }
    }

    /**
     * Get replace error response based on context
     */
    protected function getReplaceErrorResponse(\Exception $exception, string $context, $contextModel)
    {
        $message = 'Error al reemplazar el archivo: ' . $exception->getMessage();

        switch ($context) {
            case 'user':
                return redirect()->back()->with('error', $message);

            case 'school':
                return redirect()->back()->with('error', $message);

            case 'course':
                return redirect()->back()->with('error', $message);

            case 'province':
                return redirect()->back()->with('error', $message);
        }
    }
}
