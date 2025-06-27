<?php

namespace App\Models\Catalogs;

use App\Models\Base\BaseCatalogModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileSubtype extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_type_id',
        'code',
        'name',
        'description',
        'new_overwrites',
        'hidden_for_familiy',
        'upload_by_familiy',
        'order',
        'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_type_id' => 'integer',
        'new_overwrites' => 'boolean',
        'hidden_for_familiy' => 'boolean',
        'upload_by_familiy' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Get the file type that owns this subtype
     */
    public function fileType(): BelongsTo
    {
        return $this->belongsTo(FileType::class, 'file_type_id');
    }

    /**
     * Get the files that belong to this subtype
     */
    public function files()
    {
        return $this->hasMany(File::class, 'subtype_id');
    }
}