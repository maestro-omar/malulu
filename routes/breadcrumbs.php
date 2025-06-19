<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;
use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\Course;


// 🏠 Dashboard base
Breadcrumbs::for('dashboard', function (Trail $trail) {
    $trail->push('Inicio', route('dashboard'));
});

// 👤 Perfil
Breadcrumbs::for('profile.edit', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Mi perfil', route('profile.edit'));
});

// 👥 Usuarios
Breadcrumbs::for('users.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Usuarios', route('users.index'));
});

Breadcrumbs::for('users.create', function (Trail $trail) {
    $trail->parent('users.index');
    $trail->push('Crear usuario');
});

Breadcrumbs::for('users.edit', function (Trail $trail, $user) {
    $trail->parent('users.index');
    $trail->push("Editar {$user->name}");
});

Breadcrumbs::for('users.show', function (Trail $trail, $user) {
    $trail->parent('users.index');
    $trail->push($user->name);
});

// 🏫 Escuelas
Breadcrumbs::for('schools.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Escuelas', route('schools.index'));
});

Breadcrumbs::for('schools.create', function (Trail $trail) {
    $trail->parent('schools.index');
    $trail->push('Crear escuela');
});

Breadcrumbs::for('schools.edit', function (Trail $trail, $school) {
    $trail->parent('schools.index');
    $trail->push("Editar {$school->name}");
});

Breadcrumbs::for('schools.show', function (Trail $trail, $school) {
    $trail->parent('schools.index');
    $trail->push($school->name);
});

// 📅 Ciclos escolares
Breadcrumbs::for('academic-years.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Ciclos escolares', route('academic-years.index'));
});

Breadcrumbs::for('academic-years.create', function (Trail $trail) {
    $trail->parent('academic-years.index');
    $trail->push('Crear ciclo escolar');
});

Breadcrumbs::for('academic-years.edit', function (Trail $trail, $year) {
    $trail->parent('academic-years.index');
    $trail->push("Editar {$year->name}");
});

// 📁 Tipos de archivo
Breadcrumbs::for('file-types.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Tipos de archivo', route('file-types.index'));
});

// 📂 Subtipos de archivo
Breadcrumbs::for('file-subtypes.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Subtipos de archivo', route('file-subtypes.index'));
});


// 🏫 Niveles de escuela (desde una escuela)
Breadcrumbs::for('courses.index', function (Trail $trail, School $school, SchoolLevel $schoolLevel) {
    $trail->parent('schools.show', $school); // usa breadcrumb ya definido para la escuela
    $trail->push($schoolLevel->label ?? 'Nivel', route('courses.index', [$school, $schoolLevel]));
});

Breadcrumbs::for('courses.create', function (Trail $trail, School $school, SchoolLevel $schoolLevel) {
    $trail->parent('courses.index', $school, $schoolLevel);
    $trail->push('Crear curso');
});

Breadcrumbs::for('courses.edit', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course) {
    $trail->parent('courses.index', $school, $schoolLevel);
    $trail->push("Editar {$course->name}");
});
