<?php

namespace App\Models\Entities;

use App\Models\Base\BaseModel as Model;
use App\Models\Entities\User;
use App\Models\Catalogs\FileSubtype;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'original_name',
        'filename',
        'mime_type',
        'size',
        'path',
        'description',
        'metadata',
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

    /**
     * Get the file that this file replaced.
     */
    public function replacedFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'replaced_by_id', 'id');
    }

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
        return asset('storage/' . $this->path);
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

    /**
     * Check if this file has replaced another file.
     */
    public function hasReplacedFile(): bool
    {
        return $this->replacedFile()->exists();
    }
}