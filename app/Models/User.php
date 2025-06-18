<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

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
     * Get the role metadata for the user.
     */
    public function roleMetadata()
    {
        return $this->hasMany(UserRoleMetadata::class);
    }

    /**
     * Get the role metadata for a specific role and school.
     */
    public function getRoleMetadata($roleId, $schoolId)
    {
        return $this->roleMetadata()
            ->where('role_id', $roleId)
            ->where('school_id', $schoolId)
            ->first();
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

        // Log::info('assignRoleForSchool called:', [
        //     'user_id' => $this->id,
        //     'role' => $role,
        //     'school_id' => $schoolId,
        //     'active_school_id' => $activeSchoolId
        // ]);

        try {
            // Get the role ID if a Role object was passed
            $roleId = $role instanceof Role ? $role->id : $role;
            // $roleModel = $role instanceof Role ? $role : Role::find($roleId);

            // Check if role already exists for this school
            $exists = DB::table('model_has_roles')
                ->where('role_id', $roleId)
                ->where('model_type', get_class($this))
                ->where('model_id', $this->id)
                ->where('team_id', $schoolId)
                ->exists();

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

            // // Log after insertion
            // Log::info('After role insertion:', [
            //     'user_id' => $this->id,
            //     'role_id' => $roleId,
            //     'role_code' => $roleModel->code,
            //     'team_id' => $schoolId,
            //     'current_roles' => $this->allRolesAcrossTeams->map(function ($role) {
            //         return [
            //             'id' => $role->id,
            //             'name' => $role->name,
            //             'team_id' => $role->pivot->team_id
            //         ];
            //     })->toArray()
            // ]);

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
}
