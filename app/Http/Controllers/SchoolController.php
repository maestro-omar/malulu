<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with(['locality', 'schoolLevels'])
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Schools/Index', [
            'schools' => $schools
        ]);
    }
} 