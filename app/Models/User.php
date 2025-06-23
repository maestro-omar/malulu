<?php

namespace App\Models;

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
use App\Models\Role;
use App\Models\School;
use App\Traits\FilterConstants;

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
        'nationality',
        'email',
        'password',
        'picture',
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

    // Gender type constants
    const GENDER_MALE = 'masc';
    const GENDER_FEMALE = 'fem';
    const GENDER_TRANS = 'trans';
    const GENDER_FLUID = 'fluido';
    const GENDER_NOBINARY = 'no-bin';
    const GENDER_OTHER = 'otro';

    private $permissionBySchoolCache;

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
        return $this->roleRelationships()->active();
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
     * Get the student relationship for the user.
     */
    public function studentRelationship()
    {
        return $this->hasOne(StudentRelationship::class, 'role_relationship_id', 'id')
            ->whereHas('roleRelationship', function ($query) {
                $query->where('user_id', $this->id);
            });
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
    public function students()
    {
        return $this->belongsToMany(User::class, 'guardian_relationships', 'student_id', 'role_relationship_id')
            ->whereHas('roleRelationship', function ($query) {
                $query->where('user_id', $this->id);
            });
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
        $adminRoleId = DB::table('roles')
            ->where('code', Role::ADMIN)
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
        $userRoleTeams = $this->roles()->withPivot('team_id')->get();

        // 2. Group roles by team_id (school)
        $rolesByTeam = $userRoleTeams->groupBy(fn($role) => $role->pivot->team_id);

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
            foreach ($rolesByTeam as $teamId => $roles) {
                foreach ($roles as $role) {
                    $permissions = $rolePermissions->get($role->id);

                    if ($permissions && $permissions->pluck('permission_id')->contains($permId)) {
                        $matrix[$permName][] = $teamId;
                        break; // Avoid duplicate team_id entries
                    }
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
}
