<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Add essential HTTP security headers to all responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(self), display-capture=(self), geolocation=()');

        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
            "font-src 'self' data: https://fonts.gstatic.com; " .
            "img-src 'self' data: blob: https:; " .
            "connect-src 'self' wss: ws: https:; " .
            "media-src 'self' blob: data:; " .
            "frame-ancestors 'self'; " .
            "report-uri /csp-violation-report";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
