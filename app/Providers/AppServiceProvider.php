<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register morph map for polymorphic relationships
        Relation::enforceMorphMap([
            'province' => 'App\Models\Catalogs\Province',
            'school' => 'App\Models\Entities\School',
            'course' => 'App\Models\Entities\Course',
            'user' => 'App\Models\Entities\User',
        ]);
    }
}
