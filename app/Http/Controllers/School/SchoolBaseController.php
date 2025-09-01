<?php

namespace App\Http\Controllers\School;

use App\Models\Entities\Course;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolBaseController extends Controller
{
    public function __construct() {}

    public function render(?Request $request, string $view, array $data) //: \Inertia\Response
    {
        $basicData = $this->basicData();
        return Inertia::render(
            $view,
            $basicData + $data
        );
    }

    public function basicData(): array
    {
        return [];
    }


    public function getCourseFromUrlParameter(string $idAndLabel)
    {
        $id = (int) explode('-', $idAndLabel)[0];
        $course = $id ? Course::where('id', $id)->first() : null;
        if (!$course) {
            // if (!$course) throw new \Exception('Curso no encontrado');
            abort(404, 'Curso no encontrado');
        }
        return $course;
    }
}
