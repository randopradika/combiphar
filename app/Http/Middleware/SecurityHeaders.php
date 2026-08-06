<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security response headers for every route, plus a nonce-based
 * Content-Security-Policy on the public site.
 *
 * The CSP is what makes admin-authored HTML safe to render. Rich text from the
 * CMS reaches the page through dangerouslySetInnerHTML in six components, and
 * any CMS user can upload an SVG — without a policy, either is a straight path
 * to script execution in this origin.
 */
class SecurityHeaders
{
    /** GA4 (loaded in production only — see app.blade.php). */
    private const ANALYTICS = 'https://www.googletagmanager.com https://www.google-analytics.com';

    public function handle(Request $request, Closure $next): Response
    {
        // The nonce has to exist BEFORE the view renders, so it is generated up
        // front and handed to Vite (which stamps its own script/style tags) and
        // shared with Blade for the two hand-written inline scripts. Note this
        // middleware used to call $next() first — too late to influence output.
        $nonce = Str::random(24);
        Vite::useCspNonce($nonce);
        view()->share('cspNonce', $nonce);

        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Advertise HSTS only when the request actually arrived over TLS
        // (directly, or via a trusted proxy / an APP_FORCE_HTTPS host) — never
        // on plain-http local dev.
        // ⚠️ includeSubDomains binds EVERY subdomain of the production domain to
        // https. Confirm none of them is http-only before go-live. 'preload' is
        // deliberately omitted: it is effectively irreversible.
        if ($request->secure() || config('app.force_https')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Filament drives the admin panel with Alpine, which needs 'unsafe-eval'
        // and inline handlers — a policy permissive enough for it would not be
        // worth enforcing. The panel is defended by authentication, roles and a
        // network restriction instead; the public site gets the CSP.
        if (! $request->is('admin', 'admin/*', 'livewire/*')) {
            $headers->set('Content-Security-Policy', $this->policy($nonce));
        }

        return $response;
    }

    private function policy(string $nonce): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' ".self::ANALYTICS,
            // React writes inline style attributes on many components and
            // style-src-attr cannot express that without 'unsafe-inline'.
            // Styles cannot execute, so this concedes far less than it looks.
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            // CMS images are same-origin via Storage::url(); data:/blob: cover
            // inline SVG and client-side previews.
            "img-src 'self' data: blob:",
            "connect-src 'self' ".self::ANALYTICS,
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            'upgrade-insecure-requests',
        ]);
    }
}
