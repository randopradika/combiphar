<?php

namespace App\Providers;

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
        if (env('APP_FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }
}
