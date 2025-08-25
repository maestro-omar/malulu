<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\District;
use App\Models\Catalogs\Locality;
use App\Models\Entities\Course;

use App\Models\Entities\User;

function breadcrumbsGetUser(): User
{
    return auth()->user();
}

// 🏠 Dashboard base
Breadcrumbs::for('dashboard', function (Trail $trail) {
    $trail->push('Inicio', route('dashboard'), ['icon' => 'trip_origin']);
});

// 👤 Perfil
Breadcrumbs::for('profile.edit', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Mi perfil', route('profile.edit'));
});

// 👥 Usuarios
Breadcrumbs::for('users.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Usuarios', route('users.index'), ['icon' => 'group']);
});

Breadcrumbs::for('users.create', function (Trail $trail) {
    $trail->parent('users.index');
    $trail->push('Crear usuario');
});

Breadcrumbs::for('users.edit', function (Trail $trail, $user) {
    $trail->parent('users.show', $user);
    $trail->push("Editar");
});

Breadcrumbs::for('users.add.role', function (Trail $trail, $user) {
    $trail->parent('users.show', $user);
    $trail->push("Añadir rol");
});

Breadcrumbs::for('users.show', function (Trail $trail, $user) {
    $trail->parent('users.index');
    $trail->push($user->name, route('users.show', $user));
});

// 🏫 Escuelas
Breadcrumbs::for('schools.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Escuelas', route('schools.index'), ['icon' => 'home']);
});

Breadcrumbs::for('schools.create', function (Trail $trail) {
    $trail->parent('schools.index');
    $trail->push('Crear escuela');
});

Breadcrumbs::for('school.edit', function (Trail $trail, $school) {
    $user = breadcrumbsGetUser();
    if ($user->isSuperadmin())
        $trail->parent('school.show', $school);
    else
        $trail->parent('dashboard');
    $trail->push("Editar");
});

Breadcrumbs::for('school.show', function (Trail $trail, $school) {
    $user = breadcrumbsGetUser();
    if ($user->isSuperadmin())
        $trail->parent('schools.index');
    else
        $trail->parent('dashboard');
    $trail->push($school->short, route('school.show', $school));
});

Breadcrumbs::for('schools.students', function (Trail $trail, $school) {
    $trail->parent('school.show', $school);
    $trail->push('Estudiantes', route('school.students', $school));
});

Breadcrumbs::for('schools.student', function (Trail $trail, $school, $student) {
    $trail->parent('schools.students', $school);
    $trail->push($student['firstname'] . ' ' . $student['lastname'], route('school.student.show', [$school, $student['id'] . '-' . $student['name'] . ' ' . $student['lastname']]));
});
Breadcrumbs::for('schools.student.edit', function (Trail $trail, $school, $student) {
    $trail->parent('schools.student', $school, $student);
    $trail->push('Editar');
});

// 📅 Ciclos lectivos
Breadcrumbs::for('academic-years.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Ciclos lectivos', route('academic-years.index'), ['icon' => 'calendar_month']);
});

Breadcrumbs::for('academic-years.create', function (Trail $trail) {
    $trail->parent('academic-years.index');
    $trail->push('Crear ciclo lectivo');
});


Breadcrumbs::for('academic-years.show', function (Trail $trail, $year) {
    $trail->parent('academic-years.index');
    $trail->push("{$year->year}", route('academic-years.show', $year->id));
});

Breadcrumbs::for('academic-years.edit', function (Trail $trail, $year) {
    $trail->parent('academic-years.show', $year);
    $trail->push("Editar");
});


Breadcrumbs::for('provinces.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Provincias', route('provinces.index'), ['icon' => 'location_city']);
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
    $trail->push('Tipos de archivo', route('file-types.index'), ['icon' => 'topic']);
});

// 📁 Tipos de archivo
Breadcrumbs::for('file-types.edit', function (Trail $trail) {
    $trail->parent('file-types.index');
    $trail->push('Tipos de archivo', route('file-types.index'));
});

// 📂 Subtipos de archivo
Breadcrumbs::for('file-subtypes.index', function (Trail $trail) {
    $trail->parent('file-types.index');
    $trail->push('Subtipos de archivo', route('file-subtypes.index'), ['icon' => 'subdirectory_arrow_right']);
});

// 📂 Subtipos de archivo
Breadcrumbs::for('file-subtypes.edit', function (Trail $trail, $fileSubtype) {
    $trail->parent('file-subtypes.index');
    $trail->push('Subtipo de archivo', route('file-subtypes.edit', $fileSubtype));
});


// 🏫 Niveles de escuela (desde una escuela)
Breadcrumbs::for('school.courses', function (Trail $trail, School $school, SchoolLevel $schoolLevel) {
    $trail->parent('school.show', $school); // usa breadcrumb ya definido para la escuela
    $trail->push('Cursos de ' . $schoolLevel->name, route('school.courses', [$school, $schoolLevel]));
});

Breadcrumbs::for('school.course.create', function (Trail $trail, School $school, SchoolLevel $schoolLevel) {
    $trail->parent('school.courses', $school, $schoolLevel);
    $trail->push('Crear curso');
});

Breadcrumbs::for('school.course.create-next', function (Trail $trail, School $school, SchoolLevel $schoolLevel) {
    $trail->parent('school.courses', $school, $schoolLevel);
    $trail->push('Crear cursos siguientes');
});

Breadcrumbs::for('school.course.show', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course) {
    $trail->parent('school.courses', $school, $schoolLevel);
    $trail->push($course->start_date->format('Y') . ' - ' . $course->nice_name, route('school.course.show', ['school' => $school, 'schoolLevel' => $schoolLevel, 'idAndLabel' => $course->idAndLabel]));
    // $trail->push($course->number . ' º ' . $course->letter, route('school.course.show', [$school, $schoolLevel, $course]));
});

Breadcrumbs::for('school.course.edit', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course) {
    $trail->parent('school.course.show', $school, $schoolLevel, $course);
    $trail->push("Editar {$course->name}");
});




// 🏫 Escuelas públicas (público general)
Breadcrumbs::for('public-schools.index', function (Trail $trail) {
    $trail->push('Escuelas públicas', route('schools.public-index'), ['icon' => 'trip_origin']);
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
