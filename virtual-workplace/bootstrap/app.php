<?php

use App\Http\Middleware\EnsureOrganizationMember;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocaleMiddleware::class,
            SecurityHeadersMiddleware::class,
        ]);

        $middleware->api(append: [
            SecurityHeadersMiddleware::class,
        ]);

        // Default per-user/IP limit for every /api route; see the 'api'
        // limiter in AppServiceProvider.
        $middleware->throttleApi('api');

        $middleware->alias([
            'org.member' => EnsureOrganizationMember::class,
            'permission' => EnsurePermission::class,
            'superadmin' => SuperAdminMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'csp-violation-report',
            'csp-violation-report/*',
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every /api route answers errors as JSON (401/403/404/422/429),
        // whether or not the client remembered an Accept header — instead
        // of redirecting API callers to the HTML login page.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
