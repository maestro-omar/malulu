<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        return Inertia::render('AcademicYears/Index', [
            'academicYears' => $academicYears
        ]);
    }

    public function create()
    {
        return Inertia::render('AcademicYears/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|unique:academic_years,year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'winter_break_start' => 'required|date|after_or_equal:start_date|before:end_date',
            'winter_break_end' => 'required|date|after:winter_break_start|before:end_date',
        ]);

        AcademicYear::create($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Año académico creado exitosamente.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return Inertia::render('AcademicYears/Edit', [
            'academicYear' => $academicYear
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'winter_break_start' => 'required|date|after_or_equal:start_date|before:end_date',
            'winter_break_end' => 'required|date|after:winter_break_start|before:end_date',
        ]);

        $academicYear->update($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Año académico actualizado exitosamente.');
    }
} 