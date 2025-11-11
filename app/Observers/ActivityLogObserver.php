<?php

namespace App\Observers;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(private array $options = [])
    {
    }

    public function created(Model $model): void
    {
        $this->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->record($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        $this->record($model, 'restored');
    }

    protected function record(Model $model, string $event): void
    {
        if (! $this->shouldRecordEvent($event)) {
            return;
        }

        $properties = $this->buildProperties($model, $event);
        $description = $this->resolveDescription($model, $event, $properties);

        $activity = activity($this->determineLogName())
            ->performedOn($model)
            ->causedBy($this->resolveCauser($model, $event))
            ->withProperties($properties)
            ->event($event);

        $activity->log($description ?? $event);
    }

    protected function shouldRecordEvent(string $event): bool
    {
        $recordEvents = $this->options['record_events']
            ?? config('activitylog_watchlist.defaults.record_events', ['created', 'updated', 'deleted']);

        if ($recordEvents === '*') {
            return true;
        }

        return in_array($event, (array) $recordEvents, true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildProperties(Model $model, string $event): array
    {
        $attributeKeys = $this->resolveAttributeKeys($model);
        $current = $this->extractAttributes($model, $attributeKeys);
        $original = $this->extractOriginalAttributes($model, $attributeKeys);

        $properties = [
            'attributes' => [],
            'old' => [],
        ];

        if ($event === 'created' || $event === 'restored') {
            $properties['attributes'] = $current;
        } elseif ($event === 'updated') {
            $changedKeys = array_keys($model->getChanges());
            $trackedKeys = $attributeKeys ?? $changedKeys;
            $keys = array_values(array_intersect($trackedKeys, $changedKeys));

            $properties['attributes'] = Arr::only($current, $keys);
            $properties['old'] = Arr::only($original, $keys);
        } else { // deleted or others
            $properties['old'] = $current ?: $original;
        }

        if ($this->shouldIncludeContext()) {
            $context = $this->resolveRequestContext();
            if (! empty($context)) {
                $properties['context'] = $context;
            }
        }

        if (isset($this->options['properties'])) {
            $properties = array_merge($properties, $this->resolveCustomProperties($model, $event, $properties));
        }

        return $properties;
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    protected function resolveCustomProperties(Model $model, string $event, array $properties): array
    {
        $custom = $this->options['properties'];

        if ($custom instanceof Closure) {
            return $custom($model, $event, $properties) ?? [];
        }

        return (array) $custom;
    }

    protected function resolveDescription(Model $model, string $event, array $properties): ?string
    {
        $description = $this->options['description'] ?? null;

        if ($description instanceof Closure) {
            return $description($model, $event, $properties);
        }

        if (is_string($description)) {
            return $description;
        }

        return sprintf('%s %s', class_basename($model), $event);
    }

    protected function determineLogName(): ?string
    {
        return $this->options['log_name']
            ?? config('activitylog_watchlist.defaults.log_name')
            ?? config('activitylog.default_log_name');
    }

    protected function resolveCauser(Model $model, string $event): mixed
    {
        if ($resolver = $this->options['causer'] ?? null) {
            return $resolver instanceof Closure ? $resolver($model, $event) : $resolver;
        }

        return Auth::user();
    }

    /**
     * @return array<int, string>|null
     */
    protected function resolveAttributeKeys(Model $model): ?array
    {
        $attributesOption = $this->options['attributes']
            ?? config('activitylog_watchlist.defaults.attributes', 'fillable');

        if ($attributesOption === 'fillable' && method_exists($model, 'getFillable')) {
            $fillable = $model->getFillable();
            if (! empty($fillable)) {
                return $this->filterIgnored($fillable);
            }
        }

        if ($attributesOption === 'all' || $attributesOption === '*') {
            return $this->filterIgnored(array_keys($model->getAttributes()));
        }

        if (is_array($attributesOption)) {
            return $this->filterIgnored($attributesOption);
        }

        return null;
    }

    /**
     * @param  array<int, string>|null  $attributeKeys
     * @return array<string, mixed>
     */
    protected function extractAttributes(Model $model, ?array $attributeKeys): array
    {
        $attributes = $model->getAttributes();

        if ($attributeKeys !== null) {
            $attributes = Arr::only($attributes, $attributeKeys);
        }

        return $this->filterIgnoredAssoc($attributes);
    }

    /**
     * @param  array<int, string>|null  $attributeKeys
     * @return array<string, mixed>
     */
    protected function extractOriginalAttributes(Model $model, ?array $attributeKeys): array
    {
        $original = $model->getOriginal();

        if ($attributeKeys !== null) {
            $original = Arr::only($original, $attributeKeys);
        }

        return $this->filterIgnoredAssoc($original);
    }

    protected function shouldIncludeContext(): bool
    {
        return (bool) ($this->options['with_context']
            ?? config('activitylog_watchlist.defaults.with_context', true));
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveRequestContext(): array
    {
        $request = request();

        if (! $request) {
            return [];
        }

        return array_filter([
            // 'ip' => $request->ip(),
            // 'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ]);
    }

    /**
     * @param  array<int, string>|null  $keys
     * @return array<int, string>|null
     */
    protected function filterIgnored(?array $keys): ?array
    {
        $ignored = (array) ($this->options['ignore']
            ?? config('activitylog_watchlist.defaults.ignore', []));

        if (empty($ignored) || $keys === null) {
            return $keys;
        }

        return array_values(array_diff($keys, $ignored));
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function filterIgnoredAssoc(array $attributes): array
    {
        $ignored = (array) ($this->options['ignore']
            ?? config('activitylog_watchlist.defaults.ignore', []));

        if (empty($ignored)) {
            return $attributes;
        }

        return Arr::except($attributes, $ignored);
    }
}


