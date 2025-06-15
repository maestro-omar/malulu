<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
