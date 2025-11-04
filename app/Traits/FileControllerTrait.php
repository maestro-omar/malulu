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
        try {
            $data = $request->only(['subtype_id', 'nice_name', 'description']);
            $uploadedFile = $request->hasFile('file') ? $request->file('file') : null;
            $externalUrl = $request->input('external_url');

            // Add expiration dates to data if provided
            if ($request->has('valid_from')) {
                $data['valid_from'] = $request->input('valid_from');
            }
            if ($request->has('valid_until')) {
                $data['valid_until'] = $request->input('valid_until');
            }

            $fileService = $this->getFileService();
            $file = $fileService->createFileForContext(
                $data,
                $uploadedFile,
                $externalUrl,
                $context,
                $contextModel,
                Auth::id()
            );

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
                $province = $contextModel instanceof Province ? $contextModel : Province::find($contextModel);
                return redirect()->route('provinces.file.show', [
                    'province' => $province->code,
                    'file' => $file->id
                ])->with('success', $message);
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
                $province = $contextModel instanceof Province ? $contextModel : Province::find($contextModel);
                return redirect()->route('provinces.file.create', [
                    'province' => $province->code
                ])->withErrors(['error' => $message])->withInput();
        }
    }

    /**
     * Get subtypes for a specific context
     */
    protected function getSubtypesForContext(array|string $context, $contextModel)
    {
        $fileService = $this->getFileService();
        if (is_array($context)) {
            $r = collect();
            foreach ($context as $c) {
                $r = $r->merge($this->getSubtypesForContext($c, $contextModel));
            }
            return $r;
        }

        switch ($context) {
            case 'user':
                $user = $contextModel instanceof User ? $contextModel : User::find($contextModel);
                return $fileService->getSubtypesForUser($user);

            case 'student':
                $student = $contextModel instanceof User ? $contextModel : User::find($contextModel);
                return $fileService->getSubtypesForStudent($student);

            case 'teacher':
                $teacher = $contextModel instanceof User ? $contextModel : User::find($contextModel);
                return $fileService->getSubtypesForTeacher($teacher);

            case 'school':
                $school = $contextModel instanceof School ? $contextModel : School::find($contextModel);
                return $fileService->getSubtypesForSchool($school);

            case 'course':
                $course = $contextModel instanceof Course ? $contextModel : Course::find($contextModel);
                return $fileService->getSubtypesForCourse($course);

            case 'province':
                $province = $contextModel instanceof Province ? $contextModel : Province::find($contextModel);
                return $fileService->getSubtypesForProvince($province);

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
                $province = $contextModel instanceof Province ? $contextModel : Province::find($contextModel);
                return route('provinces.file.store', $province->code);

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
                $province = $contextModel instanceof Province ? $contextModel : Province::find($contextModel);
                return route('provinces.show', $province->code);

            default:
                throw new \InvalidArgumentException("Unknown context: {$context}");
        }
    }

    /**
     * Update file information
     */
    public function updateFile(Request $request, File $file, string $context, $contextModel)
    {
        try {
            $data = $request->only(['subtype_id', 'nice_name', 'description', 'valid_from', 'valid_until']);
            $fileService = $this->getFileService();
            $fileService->updateFileMetadata($file, $data);

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
        try {
            $data = $request->only(['subtype_id', 'nice_name', 'description']);
            $uploadedFile = $request->hasFile('file') ? $request->file('file') : null;
            $externalUrl = $request->input('external_url');

            // Add expiration dates to data if provided
            if ($request->has('valid_from')) {
                $data['valid_from'] = $request->input('valid_from');
            }
            if ($request->has('valid_until')) {
                $data['valid_until'] = $request->input('valid_until');
            }

            $fileService = $this->getFileService();
            $newFile = $fileService->replaceFile(
                $file,
                $data,
                $uploadedFile,
                $externalUrl,
                $context,
                $contextModel,
                Auth::id()
            );

            return $this->getReplaceSuccessResponse($newFile, $context, $contextModel);
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
                $province = $contextModel instanceof Province ? $contextModel : Province::find($contextModel);
                return redirect()->route('provinces.file.show', [
                    'province' => $province->code,
                    'file' => $file->id
                ])->with('success', $message);
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
                $province = $contextModel instanceof Province ? $contextModel : Province::find($contextModel);
                return redirect()->route('provinces.file.show', [
                    'province' => $province->code,
                    'file' => $file->id
                ])->with('success', $message);
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
