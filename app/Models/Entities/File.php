<?php

namespace App\Models\Entities;

use App\Models\Base\BaseModel as Model;
use App\Models\Entities\User;
// use App\Models\Relations\FileVisibility;
use App\Models\Relations\Attendance;
use App\Models\Catalogs\FileSubtype;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subtype_id',
        'user_id',
        'replaced_by_id',
        'fileable_type',
        'fileable_id',
        'nice_name',
        'original_name',
        'filename',
        'mime_type',
        'size',
        'path',
        'description',
        'metadata',
        'external_url',
        'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtype_id' => 'integer',
        'user_id' => 'integer',
        'replaced_by_id' => 'integer',
        'size' => 'integer',
        'metadata' => 'array',
        'active' => 'boolean'
    ];

    protected $dates = [
        'valid_from',
        'valid_until',
    ];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_external',
        'is_currently_valid'
    ];

    /**
     * Get the subtype that owns the file.
     */
    public function subtype(): BelongsTo
    {
        return $this->belongsTo(FileSubtype::class, 'subtype_id');
    }

    /**
     * Get the user that uploaded the file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the file that replaced this file.
     */
    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(File::class, 'replaced_by_id');
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'file_id');
    }

    /**
     * Get the file that this file replaced.
     */
    public function replacedFile(): HasMany
    {
        return $this->hasMany(File::class, 'replaced_by_id')->withTrashed();
    }

    public function historyFlattened(int $level = 1)
    {
        $replaced = $this->replacedFile;
        $return = [];
        foreach ($replaced as $file) {
            $file->load('subtype', 'subtype.fileType', 'user');
            $return[] = ['level' => $level, 'file' => $file];
            $myParents = $file->historyFlattened($level + 1);
            $return = array_merge($return, $myParents);
        }
        return $return;
    }

    // public function visibility(): HasMany
    // {
    //     return $this->hasMany(FileVisibility::class);
    // }

    /**
     * Get the parent fileable model (school, course, teacher, student, user).
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the file type through the subtype relationship.
     */
    public function fileType()
    {
        return $this->subtype->fileType;
    }

    /**
     * Get the full URL of the file.
     */
    public function getUrlAttribute(): string
    {
        if ($this->is_external) {
            return $this->external_url;
        }
        return asset('storage/' . $this->path . '/' . $this->filename);
    }

    /**
     * Check if this file is an external URL.
     */
    public function getIsExternalAttribute(): bool
    {
        return !is_null($this->external_url) && !empty($this->external_url);
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    /**
     * Check if this file has been replaced by another file.
     */
    public function isReplaced(): bool
    {
        return !is_null($this->replaced_by_id);
    }

    
    public function getIsCurrentlyValidAttribute(): bool
    {
        if (!$this->subtype->requires_expiration) return true;
        $today = now()->toDateString();
        return (!$this->valid_from || $this->valid_from <= $today)
            && (!$this->valid_until || $this->valid_until >= $today);
    }
    

    /**
     * Check if this file has replaced another file.
     */
    public function hasReplacedFile(): bool
    {
        return $this->replacedFile()->exists();
    }
}
