<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    protected $academicYearService;

    public function __construct(AcademicYearService $academicYearService)
    {
        $this->academicYearService = $academicYearService;
    }

    public function index()
    {
        $academicYears = $this->academicYearService->getAcademicYears();
        return response()->json(['data' => $academicYears]);
    }

    public function store(Request $request)
    {
        try {
            $academicYear = $this->academicYearService->createAcademicYear($request->all());
            return response()->json([
                'message' => 'Academic year created successfully',
                'data' => $academicYear
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        try {
            $academicYear = $this->academicYearService->updateAcademicYear($academicYear, $request->all());
            return response()->json([
                'message' => 'Academic year updated successfully',
                'data' => $academicYear
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(AcademicYear $academicYear)
    {
        try {
            $this->academicYearService->deleteAcademicYear($academicYear);
            return response()->json([
                'message' => 'Academic year deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function trashed()
    {
        $academicYears = $this->academicYearService->getTrashedAcademicYears();
        return response()->json(['data' => $academicYears]);
    }

    public function restore($id)
    {
        try {
            $this->academicYearService->restoreAcademicYear($id);
            return response()->json([
                'message' => 'Academic year restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $this->academicYearService->forceDeleteAcademicYear($id);
            return response()->json([
                'message' => 'Academic year permanently deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}