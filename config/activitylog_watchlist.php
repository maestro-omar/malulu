<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model Watchlist
    |--------------------------------------------------------------------------
    |
    | List the models that should be monitored by the global activity logger.
    | Keys are fully-qualified model class names; values are per-model options.
    | Use the "attributes" key to control which fields are recorded:
    |   - array: explicit list of attributes
    |   - "fillable": use the model's fillable array
    |   - "all": take all current attributes (after casting)
    |
    */
    'models' => [
        // Entities
        App\Models\Entities\User::class => [
            'log_name' => 'entities-user',
            'ignore' => ['password', 'remember_token'],
            'record_events' => ['updated', 'deleted'], // skip "created"
        ],
        App\Models\Entities\File::class => [
            'log_name' => 'entities-file',
        ],
        App\Models\Entities\School::class => [
            'log_name' => 'entities-school',
        ],
        App\Models\Entities\Course::class => [
            'log_name' => 'entities-course',
        ],
        App\Models\Entities\RecurrentEvent::class => [
            'log_name' => 'entities-recurrent-event',
        ],
        App\Models\Entities\AcademicEvent::class => [
            'log_name' => 'entities-academic-event',
        ],
        App\Models\Entities\SchoolPage::class => [
            'log_name' => 'entities-school-page',
        ],
        App\Models\Entities\AcademicYear::class => [
            'log_name' => 'entities-academic-year',
        ],

        // Catalogs
        App\Models\Catalogs\AttendanceStatus::class => [
            'log_name' => 'catalogs-attendance-status',
        ],
        App\Models\Catalogs\ClassSubject::class => [
            'log_name' => 'catalogs-class-subject',
        ],
        App\Models\Catalogs\Country::class => [
            'log_name' => 'catalogs-country',
        ],
        App\Models\Catalogs\Diagnosis::class => [
            'log_name' => 'catalogs-diagnosis',
        ],
        App\Models\Catalogs\District::class => [
            'log_name' => 'catalogs-district',
        ],
        App\Models\Catalogs\EventType::class => [
            'log_name' => 'catalogs-event-type',
        ],
        App\Models\Catalogs\FileSubtype::class => [
            'log_name' => 'catalogs-file-subtype',
        ],
        App\Models\Catalogs\FileType::class => [
            'log_name' => 'catalogs-file-type',
        ],
        App\Models\Catalogs\JobStatus::class => [
            'log_name' => 'catalogs-job-status',
        ],
        App\Models\Catalogs\Locality::class => [
            'log_name' => 'catalogs-locality',
        ],
        App\Models\Catalogs\Province::class => [
            'log_name' => 'catalogs-province',
        ],
        App\Models\Catalogs\Role::class => [
            'log_name' => 'catalogs-role',
        ],
        App\Models\Catalogs\RoleRelationshipEndReason::class => [
            'log_name' => 'catalogs-role-relationship-end-reason',
        ],
        App\Models\Catalogs\SchoolLevel::class => [
            'log_name' => 'catalogs-school-level',
        ],
        App\Models\Catalogs\SchoolManagementType::class => [
            'log_name' => 'catalogs-school-management-type',
        ],
        App\Models\Catalogs\SchoolShift::class => [
            'log_name' => 'catalogs-school-shift',
        ],
        App\Models\Catalogs\StudentCourseEndReason::class => [
            'log_name' => 'catalogs-student-course-end-reason',
        ],

        // Relations
        App\Models\Relations\AcademicEventCourse::class => [
            'log_name' => 'relations-academic-event-course',
        ],
        App\Models\Relations\Attendance::class => [
            'log_name' => 'relations-attendance',
            'record_events' => ['updated', 'deleted'], // skip "created"
        ],
        App\Models\Relations\GuardianRelationship::class => [
            'log_name' => 'relations-guardian-relationship',
        ],
        App\Models\Relations\RoleRelationship::class => [
            'log_name' => 'relations-role-relationship',
        ],
        App\Models\Relations\StudentCourse::class => [
            'log_name' => 'relations-student-course',
        ],
        App\Models\Relations\StudentRelationship::class => [
            'log_name' => 'relations-student-relationship',
        ],
        App\Models\Relations\TeacherCourse::class => [
            'log_name' => 'relations-teacher-course',
        ],
        App\Models\Relations\UserDiagnosis::class => [
            'log_name' => 'relations-user-diagnosis',
        ],
        App\Models\Relations\WorkerRelationship::class => [
            'log_name' => 'relations-worker-relationship',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | These options apply to every watched model unless overridden above.
    |
    */
    'defaults' => [
        'record_events' => ['created', 'updated', 'deleted'],
        'attributes' => 'fillable',
        'ignore' => ['created_at', 'updated_at', 'deleted_at'],
        'log_name' => null,
        'with_context' => false,
    ],
];
