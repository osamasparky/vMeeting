<?php

namespace App\Providers;

use App\Domains\Projects\Models\Task;
use App\Policies\TaskPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->configureRateLimiting();
    }

    /**
     * Named rate limiters, referenced from routes as `throttle:<name>`.
     * Signed-in callers are limited per user id; anonymous callers (guests,
     * public token lookups) per IP. See Architecture Audit §10/§15.
     */
    private function configureRateLimiting(): void
    {
        $key = fn (Request $request) => $request->user()?->id ?: $request->ip();

        // Safety net for the whole authenticated API — deliberately generous
        // so realtime/presence clients are never affected.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(300)->by($key($request)));

        // Endpoints that cost money or open outbound connections (OpenAI
        // image generation, SMTP/OpenAI connectivity tests).
        RateLimiter::for('expensive', fn (Request $request) => Limit::perMinute(5)->by($key($request)));

        // File uploads.
        RateLimiter::for('uploads', fn (Request $request) => Limit::perMinute(20)->by($key($request)));

        // Chat message sends.
        RateLimiter::for('chat', fn (Request $request) => Limit::perMinute(60)->by($key($request)));

        // Wave / door-knock notifications — cheap to send, easy to spam.
        RateLimiter::for('notifications', fn (Request $request) => Limit::perMinute(30)->by($key($request)));

        // Public guest-invitation token lookups — slows token guessing.
        RateLimiter::for('guest-token', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        // Routes reachable without a session (invited guests).
        RateLimiter::for('public', fn (Request $request) => Limit::perMinute(120)->by($key($request)));
    }
}
