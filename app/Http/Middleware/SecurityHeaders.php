<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security response headers for every route (public site + admin).
 * No Content-Security-Policy yet — the GA and JSON-LD inline scripts plus the
 * Vite chunks need nonce plumbing before a CSP can be enforced.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Advertise HSTS only when the request actually arrived over TLS
        // (directly, or via a trusted proxy / an APP_FORCE_HTTPS host) — never
        // on plain-http local dev.
        if ($request->secure() || config('app.force_https')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
