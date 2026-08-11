<?php

namespace App\Providers;

use App\Observers\ActivityObserver;
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

        $this->registerActivityLogging();
    }

    /**
     * Model yang perubahannya dicatat ke activity_log.
     *
     * Daftarnya eksplisit, bukan "semua model", karena dua di antaranya akan
     * membanjiri catatan sampai tidak berguna:
     *  - MediaFile ditulis ulang setiap kali pemindaian berjalan (ratusan baris);
     *  - ContactMessage dibuat oleh pengunjung situs, bukan editor, dan sudah
     *    punya menu Pesan Masuk sendiri.
     */
    private function registerActivityLogging(): void
    {
        $models = [
            \App\Models\Page::class,
            \App\Models\Article::class,
            \App\Models\Product::class,
            \App\Models\ProductCategory::class,
            \App\Models\ProductBanner::class,
            \App\Models\CsrProgram::class,
            \App\Models\Person::class,
            \App\Models\Award::class,
            \App\Models\InvestorDocument::class,
            \App\Models\InvestorHubCard::class,
            \App\Models\JobVacancy::class,
            \App\Models\WellnessProgram::class,
            \App\Models\Faq::class,
            \App\Models\NavItem::class,
            \App\Models\LegalPage::class,
            \App\Models\SocialLink::class,
            \App\Models\Office::class,
            \App\Models\OnlineShop::class,
            \App\Models\Facility::class,
            \App\Models\Accreditation::class,
            \App\Models\Milestone::class,
            \App\Models\AboutHistory::class,
            \App\Models\ImpactProgram::class,
            \App\Models\GlobalSite::class,
            // Perubahan peran justru yang paling perlu terlacak. Kata sandi
            // disamarkan di observer, jadi tidak ada hash yang masuk catatan.
            \App\Models\User::class,
        ];

        foreach ($models as $model) {
            $model::observe(ActivityObserver::class);
        }
    }
}
