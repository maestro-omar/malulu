<?php

namespace App\Http\Controllers\School;

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
}
