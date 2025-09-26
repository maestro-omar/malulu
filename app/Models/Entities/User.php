<?php

namespace App\Models\Entities;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogs\Role;
use App\Models\Entities\School;
use App\Traits\FilterConstants;
use App\Models\Catalogs\FileType;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;
use App\Models\Relations\RoleRelationship;
use App\Models\Relations\GuardianRelationship;
use App\Models\Relations\StudentRelationship;
use App\Models\Relations\Attendance;
use App\Notifications\CustomResetPassword;
use App\Models\Entities\Builders\User as Builder;

class User extends Authenticatable
{
    use FilterConstants, HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'id_number',
        'gender',
        'birthdate',
        'phone',
        'address',
        'locality',
        'province_id',
        'country_id',
        'birth_place',
        'nationality',
        'email',
        'password',
        'picture',
        'critical_info',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deleted_at' => 'datetime',
        'birthdate' => 'date',
    ];
    protected $appends = ['short_name'];

    // Gender type constants
    const GENDER_MALE = 'masc';
    const GENDER_FEMALE = 'fem';
    const GENDER_TRANS = 'trans';
    const GENDER_FLUID = 'fluido';
    const GENDER_NOBINARY = 'no-bin';
    const GENDER_OTHER = 'otro';

    private $permissionBySchoolCache;

    /**
     * @return void
     */
    protected static function booted()
    {
        // static::addGlobalScope('enabled', static fn (Builder $q) => $q->where(static::TABLE.'.enabled', true));
    }

    /**
     * @param \Illuminate\Database\Query\Builder $q
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($q)
    {
        return new Builder($q);
    }

    /**
     * Get the province that owns the user.
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the country that owns the user.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function allRolesAcrossTeams()
    {
        return $this->belongsToMany(\Spatie\Permission\Models\Role::class, 'model_has_roles', 'model_id', 'role_id')
            ->where('model_type', self::class)
            ->withPivot('team_id');
    }


    public function allRolesAcrossTeamsParsed()
    {
        $rolesWithPivot = $this->allRolesAcrossTeams->toArray();
        if (empty($rolesWithPivot)) return [];
        $retWithSchool = [];
        $info = [];
        foreach ($rolesWithPivot as $role) {
            $info['role_id'] = $role['id'];
            $info['role_code'] = $role['code'];
            $info['role_short'] = $role['short'];
            $info['role_name'] = $role['name'];
            $info['school_id'] = $role['pivot']['team_id'] ?? '';

            $newKey = $role['id'] . '-' . $info['school_id'];
            $retWithSchool[$newKey] = $info;
        }
        return $retWithSchool;
    }


    /**
     * Get the role relationships for the user.
     */
    public function roleRelationships()
    {
        return $this->hasMany(RoleRelationship::class);
    }

    /**
     * Get active role relationships for the user.
     */
    public function activeRoleRelationships()
    {
        return $this->roleRelationships()->active()->get();
    }

    /**
     * Get role relationships for a specific role.
     */
    public function roleRelationshipsForRole($roleId)
    {
        return $this->roleRelationships()->forRole($roleId);
    }

    /**
     * Get role relationships for a specific school.
     */
    public function roleRelationshipsForSchool($schoolId)
    {
        return $this->roleRelationships()->forSchool($schoolId);
    }

    /**
     * Get active role relationships for a specific role and school.
     */
    public function activeRoleRelationship($roleId, $schoolId)
    {
        return $this->roleRelationships()
            ->forRole($roleId)
            ->forSchool($schoolId)
            ->active()
            ->first();
    }


    /**
     * Get the student relationships for the user.
     */
    public function studentRelationships()
    {
        return $this->hasManyThrough(
            StudentRelationship::class,
            RoleRelationship::class,
            'user_id', // Foreign key on role_relationships table
            'role_relationship_id', // Foreign key on student_relationships table
            'id', // Local key on users table
            'id' // Local key on role_relationships table
        );
    }

    /**
     * Get the active student relationship for the user.
     */
    public function activeStudentRelationship()
    {
        return $this->hasOneThrough(
            StudentRelationship::class,
            RoleRelationship::class,
            'user_id', // Foreign key on role_relationships table
            'role_relationship_id', // Foreign key on student_relationships table
            'id', // Local key on users table
            'id' // Local key on role_relationships table
        )->whereNull('role_relationships.end_date')
            ->whereNull('role_relationships.end_reason_id');
    }

    /**
     * Get the guardian relationships for the user.
     */
    public function guardianRelationships()
    {
        return $this->hasMany(GuardianRelationship::class, 'student_id');
    }

    /**
     * Get the students that this user is a guardian for.
     */
    public function myChildren()
    {
        static $guardianRoleId = null;
        if ($guardianRoleId === null) {
            $guardianRoleId = Role::where('code', Role::GUARDIAN)->first()->id;
        }

        return User::join('guardian_relationships', 'users.id', '=', 'guardian_relationships.student_id')
            ->join('role_relationships', 'guardian_relationships.role_relationship_id', '=', 'role_relationships.id')
            ->where('role_relationships.user_id', $this->id)
            ->where('role_relationships.role_id', $guardianRoleId)
            ->select('users.*')
            ->get();
    }

    /**
     * Get the guardians (parents) for this user.
     */
    public function myParents()
    {
        static $guardianRoleId = null;
        if ($guardianRoleId === null) {
            $guardianRoleId = Role::where('code', Role::GUARDIAN)->first()->id;
        }

        $studentId = $this->id;
        $parents =  User::query()
            // 1) Filtramos solo usuarios que tengan al menos un roleRelationship
            //    de tutor (role_id = 14) vinculado a ESTE estudiante.
            ->whereHas('roleRelationships', function ($q) use ($studentId, $guardianRoleId) {
                $q->where('role_id', $guardianRoleId)
                    ->whereHas('guardianRelationship', function ($q) use ($studentId) {
                        $q->where('student_id', $studentId);
                    });
            })
            // 2) Cargamos SOLO esos roleRelationships (no los demás que el tutor pueda tener).
            ->with([
                'roleRelationships' => function ($q) use ($studentId, $guardianRoleId) {
                    $q->where('role_id', $guardianRoleId)
                        ->whereHas('guardianRelationship', function ($q) use ($studentId) {
                            $q->where('student_id', $studentId);
                        })
                        // 3) Anidamos la relación con guardianRelationships,
                        //    filtrada a ESTE estudiante.
                        ->with(['guardianRelationship' => function ($q) use ($studentId) {
                            $q->where('student_id', $studentId);
                        }]);
                },
            ])
            ->get();

        return $parents;
    }

    public function filesByMe()
    {
        return $this->hasMany(File::class, 'user_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'fileable_id')
            ->whereIn('fileable_type', FileType::relateWithUser())->with(['subtype', 'subtype.fileType']);
    }

    public function assignRoleForSchool(int|Collection|Role $role, ?int $schoolId)
    {
        // $activeSchoolId = app(PermissionRegistrar::class)->getPermissionsTeamId();

        try {
            // Get the role ID if a Role object was passed
            $roleId = $role instanceof Role ? $role->id : $role;
            // $roleModel = $role instanceof Role ? $role : Role::find($roleId);

            // Check if role already exists for this school
            $exists = $this->hasRoleForSchool($roleId, $schoolId);

            if (!$exists) {
                // Direct DB insertion
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => get_class($this),
                    'model_id' => $this->id,
                    'team_id' => $schoolId
                ]);

                // Force refresh the model to ensure we're working with fresh data
                $this->refresh();
            }


            return $this;
        } catch (\Exception $e) {
            // Log::error('Error assigning role:', [
            //     'error' => $e->getMessage(),
            //     'user_id' => $this->id,
            //     'role_id' => $roleId ?? null,
            //     'team_id' => $schoolId
            // ]);
            throw $e;
        }
    }

    public function hasRoleForSchool(int $roleId, int $schoolId): bool
    {
        return DB::table('model_has_roles')
            ->where('role_id', $roleId)
            ->where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->where('team_id', $schoolId)
            ->exists();
    }

    public function isSuperadmin(): bool
    {
        // return $this->can('superadmin'); Is this the same? why not?
        $adminRoleId = DB::table('roles')
            ->where('code', Role::SUPERADMIN)
            ->value('id');
        $globalSchoolId = School::specialGlobalId();
        return DB::table('model_has_roles')
            ->where('model_id', $this->id)
            ->where('model_type', User::class)
            ->where('role_id', $adminRoleId)
            ->where('team_id', $globalSchoolId)
            ->exists();
    }


    public function permissionBySchoolDirect(): array
    {
        if (isset($this->permissionBySchoolCache)) {
            return $this->permissionBySchoolCache;
        }

        // If user is superadmin, grant all permissions for the global school
        if ($this->isSuperadmin()) {
            $allPermissions = Permission::all()->pluck('name');
            $globalSchoolId = School::specialGlobalId();
            $matrix = [];
            foreach ($allPermissions as $permName) {
                $matrix[$permName] = [$globalSchoolId];
            }
            return $matrix;
        }

        // 1. Get all roles for this user with their associated team_id (school)
        // 2. Group roles by team_id (school)
        // for some reason, $this->roles() doesn't work as expected: $userRoleTeams = $this->roles()->withPivot('team_id')->get();
        $rolesAndSchools = $this->allRolesAcrossTeamsParsed();


        // dd($this, $userRoleTeams, $rolesByTeam);
        // 3. Get all permissions in the system (id => name)
        $allPermissions = Permission::all()->pluck('name', 'id');

        // 4. Get permission assignments per role
        $rolePermissions = DB::table('role_has_permissions')
            ->get()
            ->groupBy('role_id');

        // // 5 (OPTIONAL): Get direct permissions assigned to the user
        // $userPermissions = DB::table('model_has_permissions')
        //     ->where('model_id', $this->id)
        //     ->where('model_type', get_class($this))
        //     ->get()
        //     ->groupBy('team_id'); // Might be null if permissions are global

        // 6. Build matrix [permission_name => [team_id1, team_id2, ...]]
        $matrix = [];

        foreach ($allPermissions as $permId => $permName) {
            $matrix[$permName] = [];

            // Check role-based permissions
            foreach ($rolesAndSchools as $info) {
                $permissions = $rolePermissions->get($info['role_id']);

                if ($permissions && $permissions->pluck('permission_id')->contains($permId)) {
                    $matrix[$permName][] = $info['school_id'];
                    break; // Avoid duplicate team_id entries

                }
            }

            // // Check direct permissions (OPTIONAL)
            // foreach ($userPermissions as $teamId => $permissions) {
            //     if ($permissions->pluck('permission_id')->contains($permId)) {
            //         $matrix[$permName][] = $teamId;
            //     }
            // }
        }

        // dd($matrix);
        return $matrix;
    }

    public function hasPermissionToSchool($permission, $schoolId): bool
    {
        $matrix = $this->permissionBySchoolDirect();
        $splited = explode('|', $permission);
        foreach ($splited as $p) {
            if (isset($matrix[$p]) && in_array($schoolId, $matrix[$p])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all available relationship types.
     */
    public static function genders(): array
    {
        $map = [
            self::GENDER_MALE => 'Masculino',
            self::GENDER_FEMALE => 'Femenino',
            self::GENDER_TRANS => 'Trans',
            self::GENDER_FLUID => 'Fluido',
            self::GENDER_NOBINARY => 'No-binario',
            self::GENDER_OTHER => 'Otro'
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? ucfirst($value)])
            ->toArray();
    }

    /**
     * Get all available relationship types.
     */
    public static function gendersShort(): array
    {
        $map = [
            self::GENDER_MALE => 'Masc',
            self::GENDER_FEMALE => 'Fem',
            self::GENDER_TRANS => 'Trans',
            self::GENDER_FLUID => 'Fluido',
            self::GENDER_NOBINARY => 'No-bin',
            self::GENDER_OTHER => 'Otro'
        ];

        return collect(self::getFilteredConstants())
            ->mapWithKeys(fn($value) => [$value => $map[$value] ?? ucfirst($value)])
            ->toArray();
    }

    public static function getGenderName(string $gender, bool $short = false): string
    {
        return $short ? self::gendersShort()[$gender] ?? ucfirst($gender) : self::genders()[$gender] ?? ucfirst($gender);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    /**
     * Get the attendance records for this user.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function getShortNameAttribute()
    {
        $firstNames = explode(' ', $this->firstname);
        $lastNames = explode(' ', $this->lastname);
        $oneName = $firstNames[0];

        // Special handling for María/Maria names
        if (strtolower($oneName) === 'maría' || strtolower($oneName) === 'maria') {
            if (count($firstNames) > 1) {
                $otherNames = array_slice($firstNames, 1);
                $filteredNames = [];

                foreach ($otherNames as $name) {
                    $lowerName = strtolower($name);
                    // Skip short words like "de", "del", "los", "la", "las", "el"
                    if (!in_array($lowerName, ['de', 'del', 'los', 'la', 'las', 'el'])) {
                        $filteredNames[] = $name;
                    }
                }

                if (!empty($filteredNames)) {
                    $otherNamesInitials = ' ' . implode(' ', array_map(function ($name) {
                        return substr($name, 0, 1);
                    }, $filteredNames));
                } else {
                    $otherNamesInitials = '';
                }
            } else {
                $otherNamesInitials = '';
            }
        } else {
            // Regular handling for other names
            if (count($firstNames) > 1) {
                $otherNamesInitials = ' ' . implode(' ', array_map(function ($name) {
                    return substr($name, 0, 1);
                }, array_slice($firstNames, 1)));
            } else {
                $otherNamesInitials = '';
            }
        }

        $lastNamesInitials = ' ' . implode(' ', array_map(function ($name) {
            return substr($name, 0, 1);
        }, $lastNames));
        return $oneName . $otherNamesInitials . $lastNamesInitials;
    }
}
