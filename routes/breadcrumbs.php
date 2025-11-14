<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\District;
use App\Models\Catalogs\Locality;
use App\Models\Entities\Course;
use App\Models\Entities\File;
use App\Models\Entities\User;
use App\Models\Entities\RecurrentEvent;
use Illuminate\Support\Str;


function breadcrumbsGetUser(): User
{
    return auth()->user();
}

// 🏠 Dashboard base
Breadcrumbs::for('dashboard', function (Trail $trail) {
    $trail->push('Inicio', route('dashboard'), ['icon' => 'trip_origin']);
});

// 👤 Perfil
Breadcrumbs::for('profile.show', function (Trail $trail, $user) {
    $trail->parent('dashboard');
    $trail->push('Mi perfil', route('profile.edit'));
});

// 👤 Perfil
Breadcrumbs::for('profile.edit', function (Trail $trail, $user) {
    $trail->parent('profile.show', $user);
    $trail->push('Editar', route('profile.edit'));
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

Breadcrumbs::for('users.edit-diagnoses', function (Trail $trail, $user) {
    $trail->parent('users.show', $user);
    $trail->push("Diagnósticos");
});

Breadcrumbs::for('users.file.create', function (Trail $trail, $user) {
    $trail->parent('users.show', $user);
    $trail->push("Nuevo archivo para {$user->first_name} {$user->lastname}");
});

Breadcrumbs::for('users.file.show', function (Trail $trail, $user, $file) {
    $trail->parent('users.show', $user);
    // $trail->push("Archivos", null, ['icon' => 'folder']);
    $trail->push("Archivo: {$file->nice_name}", route('users.file.show', [$user, $file]));
});

Breadcrumbs::for('users.file.edit', function (Trail $trail, $user, $file) {
    $trail->parent('users.show', $user);
    $trail->push("Editar archivo");
});

Breadcrumbs::for('users.file.replace', function (Trail $trail, $user, $file) {
    $trail->parent('users.show', $user);
    $trail->push("Reemplazar archivo");
});

Breadcrumbs::for('users.add.role', function (Trail $trail, $user) {
    $trail->parent('users.show', $user);
    $trail->push("Añadir rol");
});

Breadcrumbs::for('users.show', function (Trail $trail, $user) {
    $trail->parent('users.index');
    $trail->push($user->name, route('users.show', $user), ['icon' => 'face']);
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
    $trail->parent('school.show', $school);
    if ($user->isSuperadmin()) {
        $trail->push("Editar");
    } else {
        $trail->push("Editar los datos de mi escuela");
    }
});

Breadcrumbs::for('school.show', function (Trail $trail, $school) {
    $user = breadcrumbsGetUser();
    if ($user->isSuperadmin())
        $trail->parent('schools.index');
    else
        $trail->parent('dashboard');
    $trail->push($school->short, route('school.show', $school));
});

Breadcrumbs::for('school.province.show', function (Trail $trail, $school, $province) {
    $trail->parent('school.show', $school);
    $trail->push('Provincia', route('provinces.show', $province));
});

Breadcrumbs::for('schools.staff', function (Trail $trail, $school) {
    $trail->parent('school.show', $school);
    $trail->push('Staff', route('school.staff', $school));
});

Breadcrumbs::for('schools.staff.show', function (Trail $trail, $school, $staff) {
    $trail->parent('schools.staff', $school);
    $trail->push($staff['firstname'] . ' ' . $staff['lastname'], route('school.staff.show', [$school, $staff['id'] . '-' . $staff['name'] . ' ' . $staff['lastname']]));
});

Breadcrumbs::for('schools.staff.edit', function (Trail $trail, $school, $staff) {
    $trail->parent('schools.staff.show', $school, $staff);
    $trail->push('Editar');
});

Breadcrumbs::for('schools.staff.edit-diagnoses', function (Trail $trail, $school, $staff) {
    $trail->parent('schools.staff.show', $school, $staff);
    $trail->push('Diagnósticos');
});

Breadcrumbs::for('schools.students', function (Trail $trail, $school) {
    $trail->parent('school.show', $school);
    $trail->push('Estudiantes', route('school.students', $school));
});

Breadcrumbs::for('schools.student', function (Trail $trail, $school, $student) {
    $trail->parent('schools.students', $school);
    $trail->push(
        $student['firstname'] . ' ' . $student['lastname'],
        route('school.student.show', [$school, $student['id'] . '-' . Str::slug($student['name'] . ' ' . $student['lastname'])])
    );
});
Breadcrumbs::for('schools.student.edit', function (Trail $trail, $school, $student) {
    $trail->parent('schools.student', $school, $student);
    $trail->push('Editar');
});

Breadcrumbs::for('schools.student.edit-diagnoses', function (Trail $trail, $school, $student) {
    $trail->parent('schools.student', $school, $student);
    $trail->push("Diagnósticos");
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

// 🔁 Eventos recurrentes
Breadcrumbs::for('recurrent-events.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Eventos recurrentes', route('recurrent-events.index'), ['icon' => 'event_repeat']);
});

Breadcrumbs::for('recurrent-events.create', function (Trail $trail) {
    $trail->parent('recurrent-events.index');
    $trail->push('Crear evento recurrente');
});

Breadcrumbs::for('recurrent-events.show', function (Trail $trail, RecurrentEvent $recurrentEvent) {
    $trail->parent('recurrent-events.index');
    $trail->push($recurrentEvent->title, route('recurrent-events.show', $recurrentEvent));
});

Breadcrumbs::for('recurrent-events.edit', function (Trail $trail, RecurrentEvent $recurrentEvent) {
    $trail->parent('recurrent-events.show', $recurrentEvent);
    $trail->push('Editar');
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

// Province File Breadcrumbs
Breadcrumbs::for('provinces.file.create', function (Trail $trail, Province $province) {
    $trail->parent('provinces.show', $province);
    $trail->push("Nuevo archivo para {$province->name}");
});

Breadcrumbs::for('provinces.file.show', function (Trail $trail, Province $province, File $file) {
    $trail->parent('provinces.show', $province);
    $trail->push("Archivo: {$file->nice_name}", route('provinces.file.show', [$province->code, $file->id]));
});

Breadcrumbs::for('provinces.file.edit', function (Trail $trail, Province $province, File $file) {
    $trail->parent('provinces.file.show', $province, $file);
    $trail->push("Editar");
});

Breadcrumbs::for('provinces.file.replace', function (Trail $trail, Province $province, File $file) {
    $trail->parent('provinces.file.show', $province, $file);
    $trail->push("Reemplazar");
});


// 🏥 Diagnósticos
Breadcrumbs::for('diagnoses.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Diagnósticos', route('diagnoses.index'), ['icon' => 'medical_services']);
});

Breadcrumbs::for('diagnoses.create', function (Trail $trail) {
    $trail->parent('diagnoses.index');
    $trail->push('Crear diagnóstico');
});

Breadcrumbs::for('diagnoses.show', function (Trail $trail, $diagnosis) {
    $trail->parent('diagnoses.index');
    $trail->push($diagnosis->name, route('diagnoses.show', $diagnosis->id));
});

Breadcrumbs::for('diagnoses.edit', function (Trail $trail, $diagnosis) {
    $trail->parent('diagnoses.show', $diagnosis);
    $trail->push('Editar');
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

// 📁 Archivos
Breadcrumbs::for('files.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('Documentación', route('files.index'), ['icon' => 'folder']);
});

Breadcrumbs::for('files.create', function (Trail $trail) {
    $trail->parent('files.index');
    $trail->push('Nuevo archivo');
});


// 🏫 Niveles de escuela (desde una escuela)
Breadcrumbs::for('school.courses', function (Trail $trail, School $school, SchoolLevel $schoolLevel, bool $hide = false) {
    $trail->parent('school.show', $school); // usa breadcrumb ya definido para la escuela
    if ($hide)
        $trail->push('Cursos', route('school.courses', [$school, $schoolLevel]));
    else
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
    $trail->parent('school.courses', $school, $schoolLevel, true);
    $trail->push($course->start_date->format('Y') . ' - ' . $course->nice_name, route('school.course.show', ['school' => $school, 'schoolLevel' => $schoolLevel, 'idAndLabel' => $course->idAndLabel]));
    // $trail->push($course->number . ' º ' . $course->letter, route('school.course.show', [$school, $schoolLevel, $course]));
});

Breadcrumbs::for('school.course.edit', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course) {
    $trail->parent('school.course.show', $school, $schoolLevel, $course);
    $trail->push("Editar {$course->name}");
});

Breadcrumbs::for('school.course.attendanceDayEdit', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course, ?DateTime $date) {
    $trail->parent('school.course.show', $school, $schoolLevel, $course);
    $trail->push("Asistencia del dia " . ($date ? $date->format('d/m/Y') : '(Error)'));
});

Breadcrumbs::for('school.course.file.create', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course) {
    $trail->parent('school.course.show', $school, $schoolLevel, $course);
    $trail->push("Nuevo archivo para {$course->nice_name}");
});

Breadcrumbs::for('school.course.file.show', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course, File $file) {
    $trail->parent('school.course.show', $school, $schoolLevel, $course);
    $trail->push("Archivo: {$file->nice_name}", route('school.course.file.show', [$school->slug, $schoolLevel->code, $course->idAndLabel, $file->id]));
});

Breadcrumbs::for('school.course.file.replace', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course, File $file) {
    $trail->parent('school.course.file.show', $school, $schoolLevel, $course, $file);
    $trail->push("Reemplazar");
});

Breadcrumbs::for('school.course.file.edit', function (Trail $trail, School $school, SchoolLevel $schoolLevel, Course $course, File $file) {
    $trail->parent('school.course.file.show', $school, $schoolLevel, $course, $file);
    $trail->push("Editar");
});
// School file breadcrumbs
Breadcrumbs::for('school.file.create', function (Trail $trail, School $school) {
    $trail->parent('school.show', $school);
    $trail->push("Nuevo archivo");
});

Breadcrumbs::for('school.file.show', function (Trail $trail, School $school, File $file) {
    $trail->parent('school.show', $school);
    $trail->push("Archivo: {$file->nice_name}", route('school.file.show', [$school->slug, $file->id]));
});

Breadcrumbs::for('school.file.edit', function (Trail $trail, School $school, File $file) {
    $trail->parent('school.file.show', $school, $file);
    $trail->push("Editar");
});

Breadcrumbs::for('school.file.replace', function (Trail $trail, School $school, File $file) {
    $trail->parent('school.file.show', $school, $file);
    $trail->push("Reemplazar");
});

// School User Files Breadcrumbs
Breadcrumbs::for('school.student.file.create', function (Trail $trail, School $school, User $user) {
    $trail->parent('schools.student', $school, $user);
    $trail->push("Crear archivo");
});

Breadcrumbs::for('school.student.file.show', function (Trail $trail, School $school, User $user, File $file) {
    $trail->parent('schools.student', $school, $user);
    $trail->push("Archivo: {$file->nice_name}", route('school.student.file.show', [$school->slug, $user->id, $file->id]));
});

Breadcrumbs::for('school.student.file.edit', function (Trail $trail, School $school, User $user, File $file) {
    $trail->parent('school.student.file.show', $school, $user, $file);
    $trail->push("Editar archivo");
});

Breadcrumbs::for('school.student.file.replace', function (Trail $trail, School $school, User $user, File $file) {
    $trail->parent('school.student.file.show', $school, $user, $file);
    $trail->push("Reemplazar archivo");
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
