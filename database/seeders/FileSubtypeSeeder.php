<?php

namespace Database\Seeders;

use App\Models\FileType;
use App\Models\FileSubtype;
use Illuminate\Database\Seeder;

class FileSubtypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getData() as $data) {
            $fileType = FileType::where('code', $data['file_type'])->first();
            $data['file_type_id'] = $fileType->id;
            unset($data['file_type']);

            FileSubtype::create($data);
        }
    }

    private function getData(): array
    {
        return [
            // Provincial file types
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 1, 'file_type' => 'provincial', 'code' => 'provincial_form', 'name' => 'Formulario', 'new_overwrites' => false],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => false, 'order' => 2, 'file_type' => 'provincial', 'code' => 'provincial_schedule', 'name' => 'Calendario', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'provincial', 'code' => 'provincial_decree', 'name' => 'Decreto/resolución', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'provincial', 'code' => 'provincial_notice', 'name' => 'Aviso ministerial', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'provincial', 'code' => 'provincial_other', 'name' => 'Otro', 'new_overwrites' => false],

            // Institutional file types
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 1, 'file_type' => 'institutional', 'code' => 'institutional_sample', 'name' => 'Modelo', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 2, 'file_type' => 'institutional', 'code' => 'institutional_form_student', 'name' => 'Formulario para alumno', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'institutional', 'code' => 'institutional_form_teacher', 'name' => 'Formulario para docente', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'institutional', 'code' => 'institutional_notice', 'name' => 'Notificación interna', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'institutional', 'code' => 'institutional_content', 'name' => 'Contenido / Planificación', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 6, 'file_type' => 'institutional', 'code' => 'institutional_other', 'name' => 'Otro', 'new_overwrites' => false],

            // Course file types
            ['hidden_for_familiy' => false, 'upload_by_familiy' => false, 'order' => 1, 'file_type' => 'course', 'code' => 'course_schedule', 'name' => 'Horario', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 2, 'file_type' => 'course', 'code' => 'course_report', 'name' => 'Informe', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'course', 'code' => 'course_project', 'name' => 'Proyecto', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'course', 'code' => 'course_planning', 'name' => 'Planificación', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'course', 'code' => 'course_other', 'name' => 'Otro', 'new_overwrites' => false],

            // Teacher file types
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 1, 'file_type' => 'teacher', 'code' => 'teacher_decree', 'name' => 'Decreto', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 2, 'file_type' => 'teacher', 'code' => 'teacher_dj02', 'name' => 'DJ02', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'teacher', 'code' => 'teacher_dependent', 'name' => 'Familiares a cargo', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'teacher', 'code' => 'teacher_evaluation', 'name' => 'Concepto (evaluación)', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'teacher', 'code' => 'teacher_other', 'name' => 'Otro', 'new_overwrites' => false],

            // Student file types
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true,  'order' => 1, 'file_type' => 'student', 'code' => 'student_aptitude_certificate', 'name' => 'Apto físico', 'new_overwrites' => true],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true,  'order' => 2, 'file_type' => 'student', 'code' => 'student_vaccines', 'name' => 'Cert vacunación', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'student', 'code' => 'student_registration_form', 'name' => 'Ficha inscripción', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'student', 'code' => 'student_internal_report', 'name' => 'Informe escolar', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'student', 'code' => 'student_external_report', 'name' => 'Informe externo (profesional)', 'new_overwrites' => false],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => false, 'order' => 6, 'file_type' => 'student', 'code' => 'student_notes', 'name' => 'Libreta', 'new_overwrites' => true],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true,  'order' => 7, 'file_type' => 'student', 'code' => 'student_medical_certificate', 'name' => 'Certificado médico', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 8, 'file_type' => 'student', 'code' => 'student_other', 'name' => 'Otro', 'new_overwrites' => false],

            // User file types
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true, 'order' => 1, 'file_type' => 'user', 'code' => 'user_id_card', 'name' => 'DNI', 'new_overwrites' => true],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true, 'order' => 2, 'file_type' => 'user', 'code' => 'user_prov_id_card', 'name' => 'CIPE', 'new_overwrites' => true],
        ];
    }
}