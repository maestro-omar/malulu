<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCatalogAttributes
{
    // Campo usado como clave (id, code, etc.)
    protected static string $cacheKeyBy = 'id';

    // Campo mostrado como texto (name, label, etc.)
    protected static string $labelField = 'name';

    public static function allCached()
    {
        $key = static::$cacheKeyBy;
        $cacheKey = static::class . '.all.keyby.' . $key;

        return Cache::rememberForever($cacheKey, fn () => static::all()->keyBy($key));
    }

    public static function getNameById($id): ?string
    {
        return optional(static::allCached()->get($id))->{static::$labelField};
    }

    public static function getNameByCode($code): ?string
    {
        return optional(static::allCached()->firstWhere('code', $code))->{static::$labelField};
    }

    public static function optionsById(): array
    {
        return static::allCached()->mapWithKeys(function ($item) {
            return [$item->{static::$cacheKeyBy} => $item->{static::$labelField}];
        })->toArray();
    }
    public static function optionsByCode(): array
    {
        return static::allCached()->mapWithKeys(function ($item) {
            return [$item->{'code'} => $item->{static::$labelField}];
        })->toArray();
    }

    public static function clearCache()
    {
        Cache::forget(static::class . '.all.keyby.' . static::$cacheKeyBy);
    }

    protected static function bootHasCatalogAttributes()
    {
        static::saved(fn ($model) => $model->clearCache());
        static::deleted(fn ($model) => $model->clearCache());
    }
}
