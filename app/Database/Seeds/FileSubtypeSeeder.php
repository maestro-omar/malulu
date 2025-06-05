<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FileSubtypeSeeder extends Seeder
{
    public function run()
    {
        $rows = [
            // Provincial file types
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 1, 'file_type' => 'provincial', 'key' => 'provincial_form', 'name' => 'Formulario', 'new_overwrites' => false],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => false, 'order' => 2, 'file_type' => 'provincial', 'key' => 'provincial_schedule', 'name' => 'Calendario', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'provincial', 'key' => 'provincial_decree', 'name' => 'Decreto/resolución', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'provincial', 'key' => 'provincial_notice', 'name' => 'Aviso ministerial', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'provincial', 'key' => 'provincial_other', 'name' => 'Otro', 'new_overwrites' => false],

            // Institutional file types
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 1, 'file_type' => 'institutional', 'key' => 'institutional_sample', 'name' => 'Modelo', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 2, 'file_type' => 'institutional', 'key' => 'institutional_form_student', 'name' => 'Formulario para alumno', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'institutional', 'key' => 'institutional_form_teacher', 'name' => 'Formulario para docente', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'institutional', 'key' => 'institutional_notice', 'name' => 'Notificación interna', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'institutional', 'key' => 'institutional_content', 'name' => 'Contenido / Planificación', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 6, 'file_type' => 'institutional', 'key' => 'institutional_other', 'name' => 'Otro', 'new_overwrites' => false],

            // Course file types
            ['hidden_for_familiy' => false, 'upload_by_familiy' => false, 'order' => 1, 'file_type' => 'course', 'key' => 'course_schedule', 'name' => 'Horario', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 2, 'file_type' => 'course', 'key' => 'course_report', 'name' => 'Informe', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'course', 'key' => 'course_project', 'name' => 'Proyecto', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'course', 'key' => 'course_planning', 'name' => 'Planificación', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'course', 'key' => 'course_other', 'name' => 'Otro', 'new_overwrites' => false],

            // Teacher file types
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 1, 'file_type' => 'teacher', 'key' => 'teacher_decree', 'name' => 'Decreto', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 2, 'file_type' => 'teacher', 'key' => 'teacher_dj02', 'name' => 'DJ02', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'teacher', 'key' => 'teacher_dependent', 'name' => 'Familiares a cargo', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'teacher', 'key' => 'teacher_evaluation', 'name' => 'Concepto (evaluación)', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'teacher', 'key' => 'teacher_other', 'name' => 'Otro', 'new_overwrites' => false],

            // Student file types
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true,  'order' => 1, 'file_type' => 'student', 'key' => 'student_aptitude_certificate', 'name' => 'Apto físico', 'new_overwrites' => true],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true,  'order' => 2, 'file_type' => 'student', 'key' => 'student_vaccines', 'name' => 'Cert vacunación', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 3, 'file_type' => 'student', 'key' => 'student_registration_form', 'name' => 'Ficha inscripción', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 4, 'file_type' => 'student', 'key' => 'student_internal_report', 'name' => 'Informe escolar', 'new_overwrites' => false],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 5, 'file_type' => 'student', 'key' => 'student_external_report', 'name' => 'Informe externo (profesional)', 'new_overwrites' => false],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => false, 'order' => 6, 'file_type' => 'student', 'key' => 'student_notes', 'name' => 'Libreta', 'new_overwrites' => true],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true,  'order' => 7, 'file_type' => 'student', 'key' => 'student_medical_certificate', 'name' => 'Certificado médico', 'new_overwrites' => true],
            ['hidden_for_familiy' => true,  'upload_by_familiy' => false, 'order' => 8, 'file_type' => 'student', 'key' => 'student_other', 'name' => 'Otro', 'new_overwrites' => false],

            // User file types
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true, 'order' => 1, 'file_type' => 'user', 'key' => 'user_id_card', 'name' => 'DNI', 'new_overwrites' => true],
            ['hidden_for_familiy' => false, 'upload_by_familiy' => true, 'order' => 2, 'file_type' => 'user', 'key' => 'user_prov_id_card', 'name' => 'CIPE', 'new_overwrites' => true],
        ];

        // Get file type IDs from the database
        $types = $this->db->table('file_type')
                         ->select('id, key')
                         ->get()
                         ->getResultArray();

        $typeMap = array_column($types, 'id', 'key');

        // Map file_type to type_id
        $rows = array_map(function ($row) use ($typeMap) {
            $row['type_id'] = $typeMap[$row['file_type']];
            unset($row['file_type']);
            return $row;
        }, $rows);

        // Insert the data
        $this->db->table('file_subtype')->insertBatch($rows);
    }
}