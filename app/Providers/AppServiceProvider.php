<?php

namespace App\Providers;

use App\Models\AboutHistory;
use App\Models\Accreditation;
use App\Models\Article;
use App\Models\Award;
use App\Models\CsrProgram;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\FooterSetting;
use App\Models\GlobalSite;
use App\Models\ImpactProgram;
use App\Models\InvestorDocument;
use App\Models\InvestorHubCard;
use App\Models\JobVacancy;
use App\Models\LegalPage;
use App\Models\Milestone;
use App\Models\NavItem;
use App\Models\Office;
use App\Models\OnlineShop;
use App\Models\Page;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductBanner;
use App\Models\ProductCategory;
use App\Models\SocialLink;
use App\Models\User;
use App\Models\WellnessProgram;
use App\Observers\ActivityObserver;
use Filament\Tables\Table;
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

        // WP-01 (defense in depth): a debug error page must NEVER render on a
        // hosted environment, whatever the box .env says — it leaks source
        // paths, versions and stack traces. Debug is allowed only for genuine
        // local development: APP_ENV=local over plain HTTP. Any non-local env,
        // or a TLS / APP_FORCE_HTTPS host, forces it off. This is keyed on
        // server-side config, not the Host header, so it cannot be spoofed.
        // (CLI/tinker keep debug; config:cache is not affected.)
        if (! $this->app->runningInConsole()
            && config('app.debug')
            && ! self::debugAllowed($this->app->environment('local'), (bool) config('app.force_https'))) {
            config(['app.debug' => false]);
        }

        // Trust X-Forwarded-* only from config('app.trusted_proxies') — never
        // '*': the app port is published directly, and a wildcard would let any
        // client spoof its IP past every per-IP rate limiter.
        $proxies = (string) config('app.trusted_proxies');
        if ($proxies !== '') {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        $this->registerActivityLogging();
        $this->registerFilamentTableDefaults();
    }

    /**
     * WP-01: may debug error pages render? Only in genuine local development —
     * APP_ENV=local served over plain HTTP. A hosted context (non-local env, or
     * a TLS / APP_FORCE_HTTPS host) is never allowed to expose stack traces.
     * Pure predicate so the truth table is unit-testable.
     */
    public static function debugAllowed(bool $isLocalEnv, bool $forceHttps): bool
    {
        return $isLocalEnv && ! $forceHttps;
    }

    /**
     * Setelan tabel yang berlaku untuk SELURUH panel.
     *
     * Ditulis sekali di sini, bukan disalin ke 26 resource: filter, pencarian
     * dan urutan sebelumnya hilang setiap kali editor meninggalkan halaman --
     * termasuk saat menekan "kembali" sesudah membuka satu record -- sehingga
     * filter harus dipasang ulang terus-menerus dan lama-lama tidak dipakai.
     *
     * Resource tetap bisa menimpanya: callback ini berjalan saat tabel dibuat,
     * sebelum method table() milik resource dijalankan.
     */
    private function registerFilamentTableDefaults(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table
                ->persistFiltersInSession()
                ->persistSearchInSession()
                ->persistSortInSession();
        });
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
            Page::class,
            Article::class,
            Product::class,
            ProductCategory::class,
            ProductBanner::class,
            CsrProgram::class,
            Person::class,
            Award::class,
            InvestorDocument::class,
            InvestorHubCard::class,
            JobVacancy::class,
            WellnessProgram::class,
            Faq::class,
            NavItem::class,
            LegalPage::class,
            SocialLink::class,
            Office::class,
            OnlineShop::class,
            Facility::class,
            Accreditation::class,
            Milestone::class,
            AboutHistory::class,
            ImpactProgram::class,
            GlobalSite::class,
            FooterSetting::class,
            // Perubahan peran justru yang paling perlu terlacak. Kata sandi
            // disamarkan di observer, jadi tidak ada hash yang masuk catatan.
            User::class,
        ];

        foreach ($models as $model) {
            $model::observe(ActivityObserver::class);
        }
    }
}
