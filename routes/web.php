<?php

use App\Http\Controllers\PageController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

/*
 * Every route in this file must stay closure-free — deploy.sh runs
 * `route:cache`, and Laravel refuses to cache a closure route. Redirects use
 * Route::redirect / permanentRedirect; anything with logic lives on a
 * controller (robots.txt and the product 301s are on PageController).
 */
Route::redirect('/', '/'.config('app.locale'));

Route::get('sitemap.xml', [PageController::class, 'sitemap']);
Route::get('robots.txt', [PageController::class, 'robots']);

/*
 * Localized path per page: [id, en]. Both locales mirror combiphar.com's page
 * slugs (its /back/api/v1/pages table, checked 2026-08-18) — the URLs Google and
 * external links already know. Investor has no counterpart there. Route names
 * are suffixed with the locale (e.g. "about.id" / "about.en"); generate URLs via
 * App\Support\Localize::url(), never by hand.
 */
$slugs = [
    'about' => ['id' => 'tentang-kami', 'en' => 'about-us'],
    'products' => ['id' => 'produk', 'en' => 'products'],
    'csr' => ['id' => 'csr-komunitas', 'en' => 'csr-community'],
    'news' => ['id' => 'berita', 'en' => 'news'],
    'investor' => ['id' => 'investor', 'en' => 'investor'],
    'contact' => ['id' => 'kontak-kami', 'en' => 'contact-us'],
    'terms' => ['id' => 'syarat-ketentuan', 'en' => 'terms-of-use'],
    'privacy' => ['id' => 'kebijakan-privasi', 'en' => 'privacy-policy'],
];

foreach (SetLocale::SUPPORTED as $loc) {
    Route::prefix($loc)
        ->middleware(SetLocale::class.':'.$loc)
        ->group(function () use ($loc, $slugs) {
            Route::get('/', [PageController::class, 'home'])->name("home.$loc");
            Route::get($slugs['about'][$loc], [PageController::class, 'about'])->name("about.$loc");
            Route::get($slugs['products'][$loc], [PageController::class, 'products'])->name("products.$loc");
            Route::get($slugs['csr'][$loc], [PageController::class, 'csr'])->name("csr.$loc");
            Route::get($slugs['csr'][$loc].'/{slug}', [PageController::class, 'csrShow'])->name("csr.show.$loc");
            Route::get($slugs['news'][$loc], [PageController::class, 'news'])->name("news.$loc");
            Route::get($slugs['news'][$loc].'/{slug}', [PageController::class, 'newsShow'])->name("news.show.$loc");
            Route::get('search', [PageController::class, 'search'])->name("search.$loc")
                ->middleware('throttle:60,1');
            Route::get($slugs['investor'][$loc], [PageController::class, 'investor'])->name("investor.$loc");
            Route::get($slugs['contact'][$loc], [PageController::class, 'contact'])->name("contact.$loc");
            Route::post($slugs['contact'][$loc], [PageController::class, 'contactSubmit'])->name("contact.submit.$loc")
                ->middleware('throttle:5,1');
            Route::get($slugs['terms'][$loc], [PageController::class, 'terms'])->name("terms.$loc");
            Route::get($slugs['privacy'][$loc], [PageController::class, 'privacy'])->name("privacy.$loc");

            /*
             * Unknown path under this locale prefix. Registering it inside the group
             * (rather than only handling the 404 exception) keeps SetLocale and the web
             * middleware in play, so the page renders in the right locale with the
             * shared nav/footer props. Left unnamed on purpose: a null route name makes
             * HandleInertiaRequests fall altUrls back to the locale homes.
             */
            Route::fallback([PageController::class, 'notFound']);
        });
}

/*
 * 301 redirects: this site's own earlier paths (English-style Indonesian slugs,
 * /en/about, /en/contact, /csr) => the combiphar.com-mirroring slugs above.
 */
Route::redirect('id/about', '/id/tentang-kami', 301);
Route::redirect('id/products', '/id/produk', 301);
Route::redirect('id/csr', '/id/csr-komunitas', 301);
Route::redirect('id/news', '/id/berita', 301);
Route::redirect('id/contact', '/id/kontak-kami', 301);
Route::redirect('id/terms-of-use', '/id/syarat-ketentuan', 301);
Route::redirect('id/privacy-notice', '/id/kebijakan-privasi', 301);
Route::permanentRedirect('id/csr/{slug}', '/id/csr-komunitas/{slug}');
Route::permanentRedirect('id/news/{slug}', '/id/berita/{slug}');
Route::redirect('en/about', '/en/about-us', 301);
Route::redirect('en/contact', '/en/contact-us', 301);
Route::redirect('en/csr', '/en/csr-community', 301);
Route::redirect('en/privacy-notice', '/en/privacy-policy', 301);
Route::permanentRedirect('en/csr/{slug}', '/en/csr-community/{slug}');

/*
 * 301s for combiphar.com URLs that have no path of their own here:
 * - Career is a page there (/id/karir, /en/career); here it is the Karir tab of
 *   Kontak.
 * - Home also answers to its page slug there (/id/beranda, /en/home).
 * - Product detail pages don't exist on this site — products open as a modal on
 *   the listing page via ?product={slug} (an even older combiphar.com build had
 *   /product/{slug} pages that Google still lists).
 * (Two CSR programme slugs were renamed to match combiphar.com by migration
 * 2026_08_18_000003; their old slugs 301 inside PageController::csrShow(), since a
 * static path here would lose to the earlier-registered csr-community/{slug}.
 * Legacy ?id= / ?cat_id= query links are mapped in news() / products().)
 */
Route::redirect('id/karir', '/id/kontak-kami?tab=karir', 301);
Route::redirect('en/career', '/en/contact-us?tab=karir', 301);
Route::redirect('id/beranda', '/id', 301);
Route::redirect('en/home', '/en', 301);
Route::get('id/product/{slug}', [PageController::class, 'productRedirect']);
Route::get('id/produk/{slug}', [PageController::class, 'productRedirect']);
Route::get('en/product/{slug}', [PageController::class, 'productRedirect']);

/*
 * Unknown path with no locale prefix (e.g. /nope). Renders in the default locale;
 * the per-locale fallbacks above are registered first, so they win for /id/* and /en/*.
 */
Route::fallback([PageController::class, 'notFound']);
