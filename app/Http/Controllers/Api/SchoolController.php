<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Entities\School;
// use App\Services\SchoolService;
// use Illuminate\Http\Request;
// use Illuminate\Validation\ValidationException;

// class SchoolController extends Controller
// {
//     protected $schoolService;

//     public function __construct(SchoolService $schoolService)
//     {
//         $this->schoolService = $schoolService;
//     }

//     public function index(Request $request)
//     {
//         $schools = $this->schoolService->getSchools($request);

//         return response()->json([
//             'data' => $schools,
//             'filters' => $request->only(['search', 'locality_id'])
//         ]);
//     }

//     public function store(Request $request)
//     {
//         try {
//             $school = $this->schoolService->createSchool($request->all());

//             return response()->json([
//                 'message' => 'School created successfully',
//                 'data' => $school
//             ], 201);
//         } catch (ValidationException $e) {
//             return response()->json([
//                 'message' => 'Error en algún campo',
//                 'errors' => $e->errors()
//             ], 422);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     public function update(Request $request, School $school)
//     {
//         try {
//             $school = $this->schoolService->updateSchool($school, $request->all());

//             return response()->json([
//                 'message' => 'School updated successfully',
//                 'data' => $school
//             ]);
//         } catch (ValidationException $e) {
//             return response()->json([
//                 'message' => 'Error en algún campo',
//                 'errors' => $e->errors()
//             ], 422);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     public function destroy(School $school)
//     {
//         try {
//             $this->schoolService->deleteSchool($school);

//             return response()->json([
//                 'message' => 'School deleted successfully'
//             ]);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     public function trashed()
//     {
//         $schools = $this->schoolService->getTrashedSchools();

//         return response()->json([
//             'data' => $schools
//         ]);
//     }

//     public function restore($id)
//     {
//         try {
//             $this->schoolService->restoreSchool($id);

//             return response()->json([
//                 'message' => 'School restored successfully'
//             ]);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     public function forceDelete($id)
//     {
//         try {
//             $this->schoolService->forceDeleteSchool($id);

//             return response()->json([
//                 'message' => 'School permanently deleted'
//             ]);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }
// }