<?php

namespace App\Providers;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\URL;
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
        // Fallback for hosts whose proxy does not forward X-Forwarded-Proto:
        // set APP_FORCE_HTTPS=true in that server's .env to force https URLs.
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Trust X-Forwarded-* only from config('app.trusted_proxies') — never
        // '*': the app port is published directly, and a wildcard would let any
        // client spoof its IP past every per-IP rate limiter.
        $proxies = (string) config('app.trusted_proxies');
        if ($proxies !== '') {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }
    }
}
