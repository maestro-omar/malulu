<?php

namespace App\Providers;

use App\Observers\ActivityLogObserver;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if (! config('activitylog.enabled', true)) {
            return;
        }

        $watchlist = config('activitylog_watchlist.models', []);

        if (empty($watchlist)) {
            return;
        }

        foreach ($watchlist as $model => $options) {
            if (is_int($model)) {
                $model = $options;
                $options = [];
            }

            if (! class_exists($model)) {
                continue;
            }

            if (($options['enabled'] ?? true) === false) {
                continue;
            }

            $observerOptions = $this->mergeWithDefaults((array) $options);

            $observer = $this->resolveObserver($observerOptions);

            $model::observe($observer);
        }
    }

    /**
     * @param  array<string, mixed>  $options
     */
    protected function mergeWithDefaults(array $options): array
    {
        $defaults = config('activitylog_watchlist.defaults', []);

        return array_replace_recursive($defaults, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    protected function resolveObserver(array $options): object
    {
        $observerClass = $options['observer'] ?? ActivityLogObserver::class;
        $observerOptions = Arr::except($options, ['observer']);

        if (is_object($observerClass)) {
            return $observerClass;
        }

        return app()->make($observerClass, ['options' => $observerOptions]);
    }
}


