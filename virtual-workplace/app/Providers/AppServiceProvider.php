<?php

namespace App\Providers;

use App\Domains\Projects\Models\Task;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        // MySQL utf8mb4 compatibility — limits varchar index keys to 191 chars
        Schema::defaultStringLength(191);

        Gate::policy(Task::class, TaskPolicy::class);
    }
}
