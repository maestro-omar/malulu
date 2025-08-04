<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\District;
use App\Models\Catalogs\Locality;
use App\Models\Entities\Course;


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
    $user = auth()->user();
    if ($user->isSuperadmin())
        $trail->parent('schools.show', $school);
    else
        $trail->parent('dashboard');
    $trail->push("Editar {$school->short}");
});

Breadcrumbs::for('schools.show', function (Trail $trail, $school) {
    $user = auth()->user();
    if ($user->isSuperadmin())
        $trail->parent('schools.index');
    else
        $trail->parent('dashboard');
    $trail->push($school->short, route('schools.show', $school));
});

Breadcrumbs::for('schools.students', function (Trail $trail, $school) {
    $trail->parent('schools.show', $school);
    $trail->push('Estudiantes', route('school.students', $school));
});

Breadcrumbs::for('schools.student', function (Trail $trail, $school, $student) {
    $trail->parent('schools.students', $school);
    $trail->push($student['firstname'] . ' ' . $student['lastname'], route('school.student.show', [$school, $student['id'] . '-' . $student['name'] . ' ' . $student['lastname']]));
});

// 📅 Ciclos lectivos
Breadcrumbs::for('academic-years.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Ciclos lectivos', route('academic-years.index'));
});

Breadcrumbs::for('academic-years.create', function (Trail $trail) {
    $trail->parent('academic-years.index');
    $trail->push('Crear ciclo lectivo');
});

Breadcrumbs::for('academic-years.edit', function (Trail $trail, $year) {
    $trail->parent('academic-years.index');
    $trail->push("Editar {$year->name}");
});

Breadcrumbs::for('provinces.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Provincias', route('provinces.index'));
});

Breadcrumbs::for('provinces.show', function (Trail $trail, $province) {
    $trail->parent('provinces.index');
    $trail->push($province->name, route('provinces.show', $province->code));
});

Breadcrumbs::for('provinces.edit', function (Trail $trail, $province) {
    $trail->parent('provinces.show', $province);
    $trail->push('Editar', route('provinces.edit', $province->code));
});


// 📁 Tipos de archivo
Breadcrumbs::for('file-types.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Tipos de archivo', route('file-types.index'));
});

// 📁 Tipos de archivo
Breadcrumbs::for('file-types.edit', function (Trail $trail) {
    $trail->parent('file-types.index');
    $trail->push('Tipos de archivo', route('file-types.index'));
});

// 📂 Subtipos de archivo
Breadcrumbs::for('file-subtypes.index', function (Trail $trail) {
    $trail->parent('file-types.index');
    $trail->push('Subtipos de archivo', route('file-subtypes.index'));
});


// 🏫 Niveles de escuela (desde una escuela)
Breadcrumbs::for('courses.index', function (Trail $trail, School $school, SchoolLevel $schoolLevel) {
    $trail->parent('schools.show', $school); // usa breadcrumb ya definido para la escuela
    $trail->push('Cursos de ' . $schoolLevel->name, route('courses.index', [$school, $schoolLevel]));
});

Breadcrumbs::for('courses.create', function (Trail $trail, School $school, SchoolLevel $schoolLevel) {
    $trail->parent('courses.index', $school, $schoolLevel);
    $trail->push('Crear curso');
});

Breadcrumbs::for('courses.show', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course) {
    $trail->parent('courses.index', $school, $schoolLevel);
    $trail->push($course->start_date->format('Y') . ' - ' . $course->number . ' º ' . $course->letter, route('courses.show', [$school, $schoolLevel, $course]));
});

Breadcrumbs::for('courses.edit', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course) {
    $trail->parent('courses.show', $school, $schoolLevel, $course);
    $trail->push("Editar {$course->name}");
});




// 🏫 Escuelas públicas (público general)
Breadcrumbs::for('public-schools.index', function (Trail $trail) {
    $trail->push('Escuelas públicas', route('schools.public-index'));
});

Breadcrumbs::for('public-schools.byProvince', function (Trail $trail, $province) {
    $trail->parent('public-schools.index');
    $provinceObj = $province instanceof Province ? $province : Province::where('code', $province)->first();
    $trail->push($provinceObj ? $provinceObj->name : $province, route('schools.public-byProvince', $province));
});

Breadcrumbs::for('public-schools.byDistrict', function (Trail $trail, $district) {
    $districtObj = $district instanceof District ? $district : District::find($district);
    if ($districtObj && $districtObj->province) {
        $trail->parent('public-schools.byProvince', $districtObj->province->code);
        $trail->push($districtObj->long ?? $districtObj->name, route('schools.public-byProvince', [$districtObj->province->code, 'district_id' => $districtObj->id]));
    } else {
        $trail->parent('public-schools.index');
        $trail->push('Departamento', route('schools.public-byProvince', ['province' => null, 'district_id' => $districtObj ? $districtObj->id : $district]));
    }
});

// Optionally, for localities:
Breadcrumbs::for('public-schools.byLocality', function (Trail $trail, $locality) {
    $localityObj = $locality instanceof Locality ? $locality : Locality::find($locality);
    if ($localityObj && $localityObj->district && $localityObj->district->province) {
        $trail->parent('public-schools.byProvince', $localityObj->district->province->code);
        $trail->push($localityObj->district->long ?? $localityObj->district->name, route('schools.public-byProvince', [$localityObj->district->province->code, 'district_id' => $localityObj->district->id]));
        $trail->push($localityObj->name, '#');
    } else {
        $trail->parent('public-schools.index');
        $trail->push('Localidad', '#');
    }
});
