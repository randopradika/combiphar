<?php

namespace App\Http\Controllers;

use App\Models\AboutHistory;
use App\Models\Accreditation;
use App\Models\Article;
use App\Models\Award;
use App\Models\ContactMessage;
use App\Models\CsrProgram;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\ImpactProgram;
use App\Models\InvestorDocument;
use App\Models\InvestorHubCard;
use App\Models\JobVacancy;
use App\Models\LegalPage;
use App\Models\Milestone;
use App\Models\Office;
use App\Models\OnlineShop;
use App\Models\Page;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductBanner;
use App\Models\ProductCategory;
use App\Models\SocialLink;
use App\Models\WellnessProgram;
use App\Support\Localize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PageController extends Controller
{
    private function img(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // Imported products may store an absolute image URL (e.g. combiphar.com) — use as-is.
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Seeded defaults that ship in public/ (e.g. "/img/wellness/football.png")
        // are already web paths; only storage-disk paths need Storage::url().
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::url($path);
    }

    private function page(string $slug): ?array
    {
        $p = Page::where('slug', $slug)->first();
        if (! $p) {
            return null;
        }

        return [
            'metaTitle' => $p->tr('meta_title'),
            'metaDescription' => $p->tr('meta_description'),
            'bannerTitle' => $p->tr('banner_title'),
            'bannerTitle2' => $p->tr('banner_title2'),
            'bannerSubtitle' => $p->tr('banner_subtitle'),
            'bannerImage' => $this->img($p->banner_image),
            'heroImage' => $this->img($p->hero_image),
            'heroLine1' => $p->tr('hero_line1'),
            'heroLine2' => $p->tr('hero_line2'),
            'manifestoImage' => $this->img($p->manifesto_image),
            'manifestoTitle' => $p->tr('manifesto_title'),
            'manifestoVideo' => $p->manifesto_video_file ? $this->img($p->manifesto_video_file) : $p->manifesto_video,
            'ctaImage' => $this->img($p->cta_image),
            'ctaTitle' => $p->tr('cta_title'),
            'underDevelopment' => (bool) $p->under_development,
            'intro' => $p->tr('intro'),
            'healthTitle' => $p->tr('health_title'),
            'healthDesc' => $p->tr('health_desc'),
            'wellnessTitle' => $p->tr('wellness_title'),
            'wellnessDesc' => $p->tr('wellness_desc'),
            // Recruitment-scam pop-up (Karir tab). `enabled` is an admin toggle,
            // so the warning can be retired without a deploy.
            'scamPopup' => [
                'enabled' => (bool) $p->scam_popup_enabled,
                'title' => $p->tr('scam_title'),
                'note' => $p->tr('scam_note'),
                'items' => collect($p->scam_items ?? [])
                    ->map(fn ($i) => [
                        'icon' => $this->img($i['icon'] ?? null),
                        'text' => trim($i['text_'.app()->getLocale()] ?? '') ?: trim($i['text_id'] ?? ''),
                    ])
                    ->filter(fn ($i) => $i['text'] !== '' || $i['icon'])
                    ->values()
                    ->all(),
            ],
            'fraudTitle' => $p->tr('fraud_title'),
            // Each note is a bilingual pair; hand the page the active locale
            // (falling back to ID) and drop notes an admin left empty.
            'fraudItems' => collect($p->fraud_items ?? [])
                ->map(fn ($i) => trim($i['text_'.app()->getLocale()] ?? '') ?: trim($i['text_id'] ?? ''))
                ->filter()
                ->values()
                ->all(),
            'vision' => $p->tr('vision'),
            'mission' => $p->tr('mission'),
            'values' => $p->tr('values'),
            'presenceDesc' => $p->tr('presence_desc'),
            'presenceImage' => $this->img($p->presence_image),
            'presencePopupText' => $p->tr('presence_popup_text'),
            'stat1Value' => $p->stat1_value,
            'stat1Label' => $p->tr('stat1_label'),
            'stat2Value' => $p->stat2_value,
            'stat2Label' => $p->tr('stat2_label'),
            'manufacturingTitle' => $p->tr('manufacturing_title'),
            'manufacturingBody' => $p->tr('manufacturing_body'),
            'manufacturingImage' => $this->img($p->manufacturing_image),
            'internationalTitle' => $p->tr('international_title'),
            'internationalBody' => $p->tr('international_body'),
            'internationalImage' => $this->img($p->international_image),
        ];
    }

    private function articleCard(Article $a): array
    {
        return [
            'slug' => $a->slug,
            'title' => $a->tr('title'),
            'excerpt' => $a->tr('excerpt'),
            'cover' => $this->img($a->cover_image),
            'date' => optional($a->published_at)->translatedFormat('j F Y'),
            // Per-locale slug (slug on /id, slug_en on /en), like combiphar.com.
            'url' => Localize::url('news.show', null, ['slug' => $a->slugFor()]),
        ];
    }

    /**
     * Bundel SEO untuk halaman detail (artikel, program CSR) yang tidak punya
     * baris `Page` sendiri. Tanpa ini SEMUA halaman detail memakai satu
     * deskripsi generik yang sama di SiteLayout — dan preload LCP-nya ikut
     * mati, karena keduanya membaca prop yang sama.
     */
    private function seo(?string $title, ?string $description, ?string $image, string $type = 'website', ?string $published = null): array
    {
        return array_filter([
            'metaTitle' => $title ? $title.' — Combiphar' : null,
            'metaDescription' => $this->metaSnippet($description),
            'bannerImage' => $image,
            'ogType' => $type,
            'publishedTime' => $published,
        ], fn ($v) => $v !== null);
    }

    /**
     * Ringkas isi editor menjadi snippet meta description: buang HTML, rapatkan
     * spasi, potong di batas kata. Batas 160 sama dengan ambang yang dipakai
     * halaman Ringkasan SEO di panel.
     */
    private function metaSnippet(?string $html): ?string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= 160) {
            return $text;
        }

        $cut = mb_substr($text, 0, 157);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > 100) {
            $cut = mb_substr($cut, 0, $space);
        }

        // rtrim hanya atas karakter ASCII — memangkas byte multibyte di sini
        // bisa merusak karakter terakhir.
        return rtrim($cut, ' ,.;:-').'…';
    }

    /** Jadikan path storage/publik menjadi URL absolut untuk JSON-LD & og:image. */
    private function absUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : url($path);
    }

    /**
     * BreadcrumbList untuk halaman detail. Remah visualnya sudah lama ada di
     * CsrDetail (`.banner__crumb`); ini pasangan terstrukturnya.
     */
    private function breadcrumbs(array $trail): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($trail)->values()
                ->map(fn ($item, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ])->all(),
        ];
    }

    public function home()
    {
        return Inertia::render('Home', [
            'page' => $this->page('home'),
            'impacts' => ImpactProgram::orderBy('sort')->get()->map(fn ($p) => [
                'title' => $p->tr('title'), 'body' => $p->tr('body'), 'image' => $this->img($p->image),
            ]),
            'milestones' => Milestone::orderBy('sort')->get()->map(fn ($m) => [
                'year' => $m->year, 'caption' => $m->tr('caption'), 'photo' => $this->img($m->photo),
            ]),
            'categories' => ProductCategory::orderBy('sort')->get()->map(fn ($c) => [
                'name' => $c->tr('name'), 'image' => $this->img($c->image),
            ]),
            'productBanners' => ProductBanner::orderBy('sort')->take(3)->get()->map(fn ($b) => [
                'title' => $b->tr('title'), 'image' => $this->img($b->image), 'link' => $b->link,
            ]),
            'articles' => Article::published()->latest('published_at')->take(3)->get()->map(fn ($a) => $this->articleCard($a)),
        ]);
    }

    public function about()
    {
        $person = fn (Person $p) => [
            'name' => $p->name, 'role' => $p->tr('role'), 'bio' => $p->tr('bio'), 'photo' => $this->img($p->photo),
        ];

        return Inertia::render('About', [
            'page' => $this->page('about'),
            'milestones' => AboutHistory::orderBy('sort')->get()->map(fn ($m) => [
                'year' => $m->year, 'caption' => $m->tr('caption'), 'photo' => $this->img($m->photo),
            ]),
            'commissioners' => Person::visible()->where('group','commissioners')->ordered()->get()->map($person),
            'directors' => Person::visible()->where('group','directors')->ordered()->get()->map($person),
            'auditCommittee' => Person::visible()->where('group','audit_committee')->ordered()->get()->map($person),
            'corporateSecretary' => Person::visible()->where('group','corporate_secretary')->ordered()->get()->map($person),
            'awards' => Award::orderBy('sort')->get()->map(fn ($a) => [
                'title' => $a->tr('title'), 'year' => $a->year, 'image' => $this->img($a->image),
                'is_hero' => (bool) $a->is_hero,
            ]),
            'shops' => $this->shops(),
            'facilities' => Facility::orderBy('sort')->get()->map(fn ($f) => [
                'name' => $f->name, 'region' => $f->region, 'plants' => $f->plants,
                'area' => $f->area, 'category' => $f->tr('category'), 'detail' => $f->tr('detail'),
                'image' => $this->img($f->image),
            ]),
            'accreditations' => Accreditation::orderBy('sort')->get()->map(fn ($a) => [
                'name' => $a->name, 'issuer' => $a->issuer,
            ]),
            'offices' => Office::orderBy('sort')->get()->map(fn ($o) => [
                'name' => $o->name, 'city' => $o->city, 'category' => $o->category,
                'description' => $o->tr('description'), 'phone' => $o->phone,
            ]),
        ]);
    }

    private function shops()
    {
        return OnlineShop::orderBy('sort')->get()->map(fn ($s) => [
            'name' => $s->name, 'url' => $s->url, 'logo' => $this->img($s->logo),
        ]);
    }

    // Brand colours for the marketplace tiles (mirror of About.jsx SHOP_COLORS, Figma 577:2954).
    private function shopColor(?string $name): string
    {
        $colors = ['shopee' => '#F1592D', 'blibli' => '#1B91D0', 'tokopedia' => '#84C468', 'lazada' => '#0C0F84', 'tiktok' => '#000000', 'orami' => '#FF5556'];
        $n = strtolower($name ?? '');
        foreach ($colors as $key => $hex) {
            if (str_contains($n, $key)) {
                return $hex;
            }
        }

        return '#5B2D8E';
    }

    public function products()
    {
        // combiphar.com's product menu links /produk?cat_id={id}; ours is ?cat=[&sub=].
        $legacy = request()->query('cat_id');
        if ($legacy !== null && ! request()->has('cat') && isset(self::LEGACY_PRODUCT_CATEGORIES[(int) $legacy])) {
            return redirect()->to(Localize::url('products').'?'.http_build_query(self::LEGACY_PRODUCT_CATEGORIES[(int) $legacy]), 301);
        }

        $shops = OnlineShop::orderBy('sort')->get();
        $shopArr = fn ($s) => ['name' => $s->name, 'url' => $s->url, 'logo' => $this->img($s->logo), 'color' => $this->shopColor($s->name)];
        // Each product shows only its selected shops; null shop_ids falls back
        // to the default marketplaces (Tokopedia/Shopee/Blibli).
        $defaultIds = OnlineShop::defaultIds();
        $product = function ($p) use ($shops, $shopArr, $defaultIds) {
            $selected = $p->shop_ids;
            $list = $shops->whereIn('id', is_array($selected) ? $selected : $defaultIds);

            return [
                'slug' => $p->slug,
                'name' => $p->tr('name'),
                'summary' => $p->tr('summary') ?: $p->tr('description'),
                'description' => $p->tr('description'), 'image' => $this->img($p->image),
                'shops' => $list->map($shopArr)->values(),
                // Official website + social links (rendered only when non-empty).
                'website' => $p->website_url,
                'instagram' => $p->instagram_url,
                'facebook' => $p->facebook_url,
            ];
        };

        // Product popup social icons come from the matching SocialLink's
        // product_icon (colored logo, distinct from the footer icon). Matched by
        // platform name; null when no such link / no product icon is uploaded,
        // in which case the popup falls back to a built-in glyph.
        $socialIcon = fn ($keyword) => $this->img(
            optional(SocialLink::where('name', 'like', '%'.$keyword.'%')->first())->product_icon
        );

        return Inertia::render('Products', [
            'page' => $this->page('products'),
            'socialIcons' => [
                'instagram' => $socialIcon('instagram'),
                'facebook' => $socialIcon('facebook'),
            ],
            'categories' => ProductCategory::whereNull('parent_id')
                ->with([
                    'products' => fn ($q) => $q->orderBy('sort'),
                    'children' => fn ($q) => $q->orderBy('sort'),
                    'children.products' => fn ($q) => $q->orderBy('sort'),
                ])
                ->orderBy('sort')->get()
                ->map(fn ($c) => [
                    'slug' => $c->slug, 'name' => $c->tr('name'), 'description' => $c->tr('description'),
                    'image' => $this->img($c->image),
                    'products' => $c->products->map($product),
                    'children' => $c->children->map(fn ($ch) => [
                        'slug' => $ch->slug, 'name' => $ch->tr('name'), 'description' => $ch->tr('description'),
                        'image' => $this->img($ch->image),
                        'products' => $ch->products->map($product),
                    ]),
                ]),
        ]);
    }

    /** Live site search across products + articles (JSON). */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['products' => [], 'articles' => []]);
        }
        // Escape LIKE wildcards so a literal % or _ in the query doesn't scan everything.
        $like = '%'.addcslashes($q, '%_\\').'%';
        $locale = app()->getLocale();

        $products = Product::where(fn ($w) => $w
            ->where('name_id', 'like', $like)
            ->orWhere('name_en', 'like', $like))
            ->orderBy('name_id')
            ->limit(8)
            ->get()
            ->map(fn ($p) => [
                'type' => 'product',
                'title' => $p->tr('name'),
                'image' => $this->img($p->image),
                'url' => Localize::url('products', $locale).'?product='.$p->slug,
            ]);

        $articles = Article::published()
            ->where(fn ($w) => $w
                ->where('title_id', 'like', $like)
                ->orWhere('title_en', 'like', $like))
            ->latest('published_at')
            ->limit(8)
            ->get()
            ->map(fn ($a) => [
                'type' => 'article',
                'title' => $a->tr('title'),
                'image' => $this->img($a->cover_image),
                'url' => Localize::url('news.show', $locale, ['slug' => $a->slugFor($locale)]),
            ]);

        return response()->json(['products' => $products, 'articles' => $articles]);
    }

    public function csr()
    {
        // Kartu daftar selalu disaring, bahkan saat pratinjau: draf ditinjau
        // lewat halaman detailnya sendiri, bukan dengan menyelipkannya ke daftar.
        $all = CsrProgram::published()->whereNull('parent_id')->orderBy('sort')->get();
        $map = fn ($rows) => $rows->values()->map(fn ($p) => [
            'title' => $p->tr('title'), 'body' => $p->tr('body'), 'image' => $this->img($p->image),
            'link' => $p->link, 'slug' => $p->slug,
            'url' => $p->slug ? Localize::url('csr.show', app()->getLocale(), ['slug' => $p->slug]) : null,
        ]);

        $health = $map($all->where('category', 'health_campaign'));
        // "Kampanye Hidup Sehat" (healthy-living) is informational — clear its
        // detail url so the card shows no button; the Education card keeps its
        // "Pelajari Lebih Lanjut" link.
        $health->transform(function ($c) {
            if ($c['slug'] === 'healthy-living') {
                $c['url'] = null;
            }

            return $c;
        });

        return Inertia::render('Csr', [
            'page' => $this->page('csr'),
            'esg' => $map($all->where('category', 'esg')),
            'health' => $health,
            // Sports cards read as a plain alphabetical list (Basketball →
            // Tennis). Sorted here rather than by renumbering `sort`, because
            // that column is hidden from admins and auto-appends — so a sport
            // added later slots in alphabetically on its own.
            'sports' => $map($all->where('category', 'sports'))
                ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
        ]);
    }

    /**
     * Slug program CSR yang dulu dipakai di sini => slug combiphar.com (baris
     * di-rename oleh migrasi 2026_08_18_000003; peta ini menjaga tautan lama
     * tetap hidup lewat 301 — dan hanya dipakai bila slug lamanya sudah tidak
     * ada, jadi lingkungan yang belum ter-migrasi tetap merender, bukan mati).
     */
    private const LEGACY_CSR_SLUGS = [
        'environmental' => 'environmental-care-action',
        'social' => 'social-care-action',
    ];

    /** Tautan lama combiphar.com: /berita?id={kategori} => tab halaman Berita kita. */
    private const LEGACY_NEWS_TABS = [
        1 => 'health',    // Info Kesehatan
        2 => 'investor',  // Siaran Pers (tab disembunyikan → News.jsx jatuh ke health)
    ];

    /** Tautan lama combiphar.com: /produk?cat_id={kategori} => ?cat= / ?sub= kita. */
    private const LEGACY_PRODUCT_CATEGORIES = [
        2 => ['cat' => 'pharmaceutical'],
        3 => ['cat' => 'consumer-health'],
        5 => ['cat' => 'nutrition-herbal', 'sub' => 'nutrition-herbal-serealsnack'],
        6 => ['cat' => 'nutrition-herbal', 'sub' => 'nutrition-herbal-honey'],
        7 => ['cat' => 'nutrition-herbal', 'sub' => 'nutrition-herbal-herbal'],
    ];

    public function csrShow(string $slug)
    {
        $program = CsrProgram::where('slug', $slug)->first();

        if (! $program && isset(self::LEGACY_CSR_SLUGS[$slug])) {
            return redirect()->to(Localize::url('csr.show', null, ['slug' => self::LEGACY_CSR_SLUGS[$slug]]), 301);
        }

        abort_unless($program, 404);

        // Program yang belum terbit harus benar-benar 404 bagi publik, bukan
        // tampil kosong -- kecuali pemintanya memegang tautan pratinjau.
        if (! $program->is_published && ! $this->previewing()) {
            abort(404);
        }

        // Komite Audit has no standalone page — it lives as a tab on the
        // Governance sub-menu. Redirect the old detail URL to that tab.
        if ($program->slug === 'komite-audit') {
            return redirect()->route('csr.show.'.app()->getLocale(), ['slug' => 'governance', 'topic' => 'komite-audit']);
        }

        // Sama seperti halaman artikel: tanpa prop `seo` setiap program CSR
        // memakai deskripsi generik yang sama, dan preload LCP-nya tidak jalan.
        // Dihitung sekali di sini karena kedua cabang tata letak memakainya.
        $csrSeo = $this->seo(
            $program->tr('title'),
            $program->tr('body') ?: $program->tr('content'),
            $this->img($program->image),
        );
        $csrJsonLd = [$this->breadcrumbs([
            ['name' => 'Combiphar', 'url' => Localize::url('home')],
            ['name' => __('site.nav.csr'), 'url' => Localize::url('csr')],
            ['name' => $program->tr('title'), 'url' => Localize::url('csr.show', null, ['slug' => $program->slug])],
        ])];

        // Every sports programme uses this layout, and any other programme can
        // opt into it from the CMS ("Galeri Tim") — that is how Environmental
        // and Education get Basketball's banner + paginated 6-per-page grid.
        if ($program->category === 'sports' || $program->layout === 'sports') {
            // Sports pages carry team rows as children. A programme that opted
            // in from the CMS has none, so it becomes its own single block —
            // with no title or logo, which would only repeat the banner above.
            // Gated on the opt-in: a sport with no team rows kept rendering as
            // banner-only before this, and must keep doing so.
            $isSelfBlock = $program->children->isEmpty() && $program->layout === 'sports';
            $blocks = $isSelfBlock ? collect([$program]) : $program->children;

            return Inertia::render('SportsDetail', [
                'program' => [
                    'title' => $program->tr('title'),
                    'subtitle' => $program->tr('body'),
                    'image' => $this->img($program->image),
                ],
                'teams' => $blocks->map(fn ($c) => [
                    'title' => $isSelfBlock ? null : $c->tr('title'),
                    'body' => $c->tr('content') ?: $c->tr('body'),
                    'logo' => $isSelfBlock ? null : $this->img($c->image),
                    // Sports galleries are plain path strings; CSR galleries are
                    // {image, caption_*} objects. This layout shows no captions,
                    // so reduce both to a list of URLs.
                    'gallery' => collect($c->gallery ?? [])
                        ->map(fn ($g) => $this->img(is_array($g) ? ($g['image'] ?? null) : $g))
                        ->filter()
                        ->values(),
                ]),
                'seo' => $csrSeo,
            ])->withViewData(['jsonLd' => $csrJsonLd]);
        }

        $person = fn (Person $p) => [
            'name' => $p->name, 'role' => $p->tr('role'), 'bio' => $p->tr('bio'), 'photo' => $this->img($p->photo),
        ];
        $isBoard = $program->layout === 'board'
            || $program->children->contains(fn ($c) => $c->layout === 'board');

        return Inertia::render('CsrDetail', [
            'program' => [
                'title' => $program->tr('title'),
                'subtitle' => $program->tr('body'),
                'body' => $program->tr('content') ?: $program->tr('body'),
                // Raw "Isi Halaman Detail" (may be empty) — rendered above the
                // photo grid in the gallery layout so the detail content shows
                // there too, not only in the default/slider layouts.
                'content' => $program->tr('content'),
                'image' => $this->img($program->image),
                'category' => $program->category,
                'layout' => $program->layout,
                'gallery' => collect($program->gallery ?? [])->map(function ($g) {
                    // New shape: {image, caption_id, caption_en}; legacy: plain path string.
                    if (is_array($g)) {
                        $loc = app()->getLocale();
                        $caption = $g['caption_'.$loc] ?? null;
                        if (! $caption) {
                            $caption = $g['caption_'.config('app.fallback_locale')] ?? null;
                        }

                        return ['image' => $this->img($g['image'] ?? null), 'caption' => $caption];
                    }

                    return ['image' => $this->img($g), 'caption' => null];
                })->values(),
                'seeAll' => $program->link,
                // Second rich block rendered below the member grids (board layout).
                'content2' => $program->tr('content2'),
            ],
            'topics' => $program->children->map(fn ($c) => [
                'title' => $c->tr('title'),
                'body' => $c->tr('content') ?: $c->tr('body'),
                'slug' => $c->slug,
                // A "board" sub-topic (mis. Komite Audit) renders its member grids
                // inline in this sub-menu tab instead of an article + contact form.
                'layout' => $c->layout,
                'content2' => $c->tr('content2'),
            ]),
            // Slider layout (mis. Social Care): each sub-program becomes a slide.
            'slides' => $program->children->map(fn ($c) => [
                'image' => $this->img($c->image),
                'title' => $c->tr('title'),
                'body' => $c->tr('body') ?: trim(strip_tags((string) $c->tr('content'))),
            ])->values(),
            // "Board" layout (Figma 967:78 — Komite Audit): reuse the About page
            // Audit Committee + Corporate Secretary member grids.
            'auditCommittee' => $isBoard ? Person::visible()->where('group','audit_committee')->ordered()->get()->map($person) : [],
            'corporateSecretary' => $isBoard ? Person::visible()->where('group','corporate_secretary')->ordered()->get()->map($person) : [],
            'seo' => $csrSeo,
        ])->withViewData(['jsonLd' => $csrJsonLd]);
    }

    public function terms()
    {
        return $this->legal('terms');
    }

    public function privacy()
    {
        return $this->legal('privacy');
    }

    private function legal(string $key)
    {
        $lp = LegalPage::where('key', $key)->firstOrFail();

        return Inertia::render('Legal', [
            'title' => $lp->tr('title'),
            'body' => $lp->tr('body'),
        ]);
    }

    /**
     * Renders the 404 page. Reached from the fallback routes (unmatched paths)
     * and from the 404 handler in bootstrap/app.php (e.g. an unknown slug).
     */
    public function notFound(Request $request)
    {
        return Inertia::render('NotFound')->toResponse($request)->setStatusCode(404);
    }

    /**
     * Bolehkah permintaan ini melihat konten yang belum terbit?
     *
     * Dua jalan masuk, sengaja: tautan pratinjau bertanda tangan (kedaluwarsa,
     * dan bisa dikirim ke orang yang tidak punya akun CMS), atau sesi panel yang
     * sedang login. Satu-satunya akun di aplikasi ini adalah akun panel, jadi
     * auth()->check() memang berarti "editor".
     */
    private function previewing(): bool
    {
        return request()->hasValidSignature() || auth()->check();
    }

    /**
     * Non-production hosts must not advertise the sitemap. Crawling stays
     * allowed (no blanket Disallow) so the NoIndexNonProduction X-Robots-Tag
     * header is actually fetched and honoured — a robots-blocked page can
     * still be indexed URL-only, which is how webdev.combiphar.com ended up
     * in Google. /admin is the one path no crawler needs on any host.
     * (A controller method rather than a route closure so route:cache works.
     * The old combiphar.com has no robots.txt to inherit — /robots.txt there
     * returns the SPA's HTML catch-all; checked 2026-08-14.)
     */
    public function robots()
    {
        $lines = "User-agent: *\nDisallow: /admin\n";

        if (app()->environment('production')) {
            $lines .= "\nSitemap: ".url('/sitemap.xml')."\n";
        }

        return response($lines, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * 301 for the old combiphar.com product-detail URLs. Product pages don't
     * exist here — products open as a modal on the listing page via
     * ?product={slug}; the slugs match because both sites share the combiphar
     * API. A method rather than a route closure so route:cache works; the
     * locale comes off the path prefix, keeping {slug} the only route param.
     */
    public function productRedirect(Request $request, string $slug)
    {
        $locale = str_starts_with($request->path(), 'en/') ? 'en' : 'id';

        return redirect()->to(Localize::url('products', $locale, ['product' => $slug]), 301);
    }

    public function sitemap()
    {
        $articles = Article::published()->get(['slug', 'slug_en', 'published_at', 'updated_at']);
        $programs = CsrProgram::published()->whereNotNull('slug')->get(['slug', 'updated_at']);
        $pages = Page::query()->get(['slug', 'show_in_menu', 'updated_at'])->keyBy('slug');

        // Halaman yang dimatikan dari menu lewat CMS — saat ini Investor, yang
        // isinya masih "Segera Hadir" — tidak perlu diminta untuk diindeks.
        // Halaman tanpa baris `Page` (syarat & ketentuan, kebijakan privasi)
        // tetap masuk: tidak punya toggle bukan berarti disembunyikan.
        $names = collect(['home', 'about', 'products', 'csr', 'news', 'investor', 'contact', 'terms', 'privacy'])
            ->reject(function ($name) use ($pages) {
                $row = $pages->get($name);

                return $row && ! $row->show_in_menu;
            });

        // $params boleh berupa closure(locale) — slug artikel berbeda per bahasa
        // (slug vs slug_en), jadi loc dan tiap alternate harus memakai slug
        // bahasanya masing-masing.
        $entry = function (string $base, string $loc, array|\Closure $params, string $priority, $lastmod) {
            $p = fn (string $l) => $params instanceof \Closure ? $params($l) : $params;
            $alt = [
                'id' => Localize::url($base, 'id', $p('id')),
                'en' => Localize::url($base, 'en', $p('en')),
            ];

            return [
                'loc' => Localize::url($base, $loc, $p($loc)),
                'priority' => $priority,
                'lastmod' => $lastmod ? $lastmod->toAtomString() : null,
                // Pasangan id/en sudah dinyatakan di <head> lewat hreflang;
                // sitemap harus menyatakan hubungan yang sama, bukan dua URL
                // yang tampak tidak berkaitan.
                'alternates' => $alt + ['x-default' => $alt['en']],
            ];
        };

        $urls = [];

        foreach (['id', 'en'] as $loc) {
            foreach ($names as $name) {
                $urls[] = $entry($name, $loc, [], $name === 'home' ? '1.0' : '0.8', optional($pages->get($name))->updated_at);
            }
            foreach ($articles as $a) {
                $urls[] = $entry('news.show', $loc, fn (string $l) => ['slug' => $a->slugFor($l)], '0.6', $a->updated_at ?: $a->published_at);
            }
            foreach ($programs as $p) {
                $urls[] = $entry('csr.show', $loc, ['slug' => $p->slug], '0.6', $p->updated_at);
            }
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function news()
    {
        // combiphar.com's news menu links /berita?id={kategori}; ours is ?tab=.
        $legacy = request()->query('id');
        if ($legacy !== null && ! request()->has('tab') && isset(self::LEGACY_NEWS_TABS[(int) $legacy])) {
            return redirect()->to(Localize::url('news').'?tab='.self::LEGACY_NEWS_TABS[(int) $legacy], 301);
        }

        $articles = Article::published()->latest('published_at')->get();
        $byCat = fn (string $c) => $articles->where('category', $c)->values()->map(fn ($a) => $this->articleCard($a));

        return Inertia::render('News', [
            'page' => $this->page('news'),
            // The "Investor Update" tab mirrors the Investor page's toggle.
            'investorUnderDevelopment' => (bool) optional(Page::where('slug', 'investor')->first())->under_development,
            // Whole-tab gate (default hidden) — flipped from the Artikel list's
            // header action; HandleInertiaRequests hides the matching nav item.
            'showInvestorTab' => (bool) optional(Page::where('slug', 'news')->first())->show_investor_tab,
            'investor' => $byCat('pembaruan_korporasi'),
            'health' => $byCat('edukasi_gaya_hidup'),
            'product' => $byCat('informasi_produk'),
            'others' => $byCat('lainnya'),
        ]);
    }

    public function newsShow(string $slug)
    {
        $locale = app()->getLocale();

        // Slug per bahasa seperti combiphar.com: /id/berita/{slug} dan
        // /en/news/{slug_en}. Slug bahasa lain tetap dikenali (tautan lama
        // /en/news/{slug-indonesia} tidak mati), tetapi dialihkan 301 ke slug
        // kanonis locale ini supaya satu artikel tidak terindeks di dua URL.
        $article = Article::findBySlug($slug, $locale) ?? abort(404);
        $canonicalSlug = $article->slugFor($locale);

        if ($slug !== $canonicalSlug && ! request()->hasValidSignature()) {
            $query = request()->getQueryString();

            return redirect()->to(
                Localize::url('news.show', $locale, ['slug' => $canonicalSlug]).($query ? '?'.$query : ''),
                301,
            );
        }

        // Sebelumnya halaman ini TIDAK menyaring status terbit sama sekali:
        // artikel draf maupun yang dijadwalkan tayang bulan depan sudah bisa
        // dibuka siapa pun yang menebak slug-nya, walau tidak muncul di daftar.
        if (! $article->isPublished() && ! $this->previewing()) {
            abort(404);
        }

        // Tombol ganti bahasa + canonical/hreflang membaca `altUrls`, yang
        // secara default memakai parameter route apa adanya — di sini slug-nya
        // berbeda per bahasa, jadi ditimpa dengan pasangan yang benar.
        Inertia::share('altUrls', [
            'id' => Localize::url('news.show', 'id', ['slug' => $article->slugFor('id')]),
            'en' => Localize::url('news.show', 'en', ['slug' => $article->slugFor('en')]),
        ]);

        $cover = $this->img($article->cover_image);
        $summary = $article->tr('excerpt') ?: $article->tr('body');
        $url = Localize::url('news.show', null, ['slug' => $canonicalSlug]);

        return Inertia::render('NewsDetail', [
            'article' => array_merge($this->articleCard($article), [
                'body' => $article->tr('body'),
                'category' => $article->category,
                'datetime' => optional($article->published_at)->translatedFormat('l, j F Y - g:i A'),
            ]),
            'others' => Article::published()->where('id', '!=', $article->id)->latest('published_at')->take(4)->get()
                ->map(fn ($a) => $this->articleCard($a)),
            // Halaman ini tidak punya baris `Page`, jadi tanpa prop ini setiap
            // artikel mewarisi deskripsi generik yang sama.
            'seo' => $this->seo(
                $article->tr('title'),
                $summary,
                $cover,
                'article',
                optional($article->published_at)->toAtomString(),
            ),
        ])->withViewData(['jsonLd' => [
            array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $article->tr('title'),
                'description' => $this->metaSnippet($summary),
                'image' => $cover ? [$this->absUrl($cover)] : null,
                'datePublished' => optional($article->published_at)->toAtomString(),
                'dateModified' => optional($article->updated_at)->toAtomString(),
                'mainEntityOfPage' => $url,
                'inLanguage' => app()->getLocale(),
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Combiphar',
                    'logo' => ['@type' => 'ImageObject', 'url' => url('/img/logo-header.svg')],
                ],
            ], fn ($v) => $v !== null),
            $this->breadcrumbs([
                ['name' => 'Combiphar', 'url' => Localize::url('home')],
                ['name' => __('site.nav.news'), 'url' => Localize::url('news')],
                ['name' => $article->tr('title'), 'url' => $url],
            ]),
        ]]);
    }

    public function investor()
    {
        $docs = InvestorDocument::orderByDesc('year')->orderBy('sort')->get();
        $map = fn ($rows) => $rows->values()->map(fn ($d) => [
            'title' => $d->tr('title'), 'year' => $d->year,
            'fileId' => $this->img($d->file_id), 'fileEn' => $this->img($d->file_en),
        ]);

        return Inertia::render('Investor', [
            'page' => $this->page('investor'),
            // Hub sub-menu cards; `key` selects the section the card opens.
            'hubCards' => InvestorHubCard::where('is_visible', true)
                ->orderBy('sort')->get()
                ->map(fn ($c) => [
                    'key' => $c->key,
                    'label' => $c->tr('title'),
                    'image' => $this->img($c->image),
                ]),
            'annual' => $map($docs->where('category', 'annual_report')),
            'sustainability' => $map($docs->where('category', 'sustainability')),
            'financial' => $map($docs->where('category', 'financial')),
            'disclosures' => $map($docs->where('category', 'disclosure')),
            'presentations' => $map($docs->where('category', 'presentation')),
        ]);
    }

    public function contact()
    {
        return Inertia::render('Contact', [
            'page' => $this->page('contact'),
            'vacancies' => JobVacancy::where('is_open', true)->orderBy('sort')->get()->map(fn ($v) => [
                'title' => $v->tr('title'), 'department' => $v->tr('department'),
                'location' => $v->location, 'summary' => $v->tr('summary'),
                'description' => $v->tr('description'),
                'requirements' => $v->tr('requirements'), 'applyUrl' => $v->apply_url,
            ]),
            'faqs' => Faq::orderBy('sort')->get()->map(fn ($f) => [
                'question' => $f->tr('question'), 'answer' => $f->tr('answer'),
            ]),
            'wellness' => WellnessProgram::orderBy('sort')->get()->map(fn ($w) => [
                'icon' => $this->img($w->icon), 'title' => $w->tr('title'), 'body' => $w->tr('body'),
            ]),
        ]);
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:60',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => (! empty($data['phone']) ? 'Phone: '.$data['phone']."\n\n" : '').$data['message'],
        ]);

        return back()->with('contact_success', true);
    }
}
