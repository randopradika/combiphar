<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\Article;
use App\Models\Award;
use App\Models\CsrProgram;
use App\Models\Facility;
use App\Models\GlobalSite;
use App\Models\ImpactProgram;
use App\Models\JobVacancy;
use App\Models\Milestone;
use App\Models\Office;
use App\Models\OnlineShop;
use App\Models\Page;
use App\Models\Person;
use App\Models\Product;
use App\Models\ProductCategory;
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

        return Storage::url($path);
    }

    private function page(string $slug): ?array
    {
        $p = Page::where('slug', $slug)->first();
        if (! $p) return null;

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
            'intro' => $p->tr('intro'),
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
            'url' => route('news.show', ['slug' => $a->slug]),
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
            'productBanners' => \App\Models\ProductBanner::orderBy('sort')->take(3)->get()->map(fn ($b) => [
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
            'milestones' => \App\Models\AboutHistory::orderBy('sort')->get()->map(fn ($m) => [
                'year' => $m->year, 'caption' => $m->tr('caption'), 'photo' => $this->img($m->photo),
            ]),
            'commissioners' => Person::where('group', 'commissioners')->orderBy('sort')->get()->map($person),
            'directors' => Person::where('group', 'directors')->orderBy('sort')->get()->map($person),
            'auditCommittee' => Person::where('group', 'audit_committee')->orderBy('sort')->get()->map($person),
            'corporateSecretary' => Person::where('group', 'corporate_secretary')->orderBy('sort')->get()->map($person),
            'awards' => Award::orderBy('sort')->get()->map(fn ($a) => [
                'title' => $a->tr('title'), 'year' => $a->year, 'image' => $this->img($a->image),
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
        $shops = OnlineShop::orderBy('sort')->get();
        $shopArr = fn ($s) => ['name' => $s->name, 'url' => $s->url, 'logo' => $this->img($s->logo), 'color' => $this->shopColor($s->name)];
        // Each product shows only its selected shops; null shop_ids = available at all shops.
        $product = function ($p) use ($shops, $shopArr) {
            $selected = $p->shop_ids;
            $list = is_array($selected) ? $shops->whereIn('id', $selected) : $shops;

            return [
                'slug' => $p->slug,
                'name' => $p->tr('name'),
                'summary' => $p->tr('summary') ?: $p->tr('description'),
                'description' => $p->tr('description'), 'image' => $this->img($p->image),
                'shops' => $list->map($shopArr)->values(),
            ];
        };

        return Inertia::render('Products', [
            'page' => $this->page('products'),
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
        $like = '%'.$q.'%';
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
                'url' => route('products', ['locale' => $locale]).'?product='.$p->slug,
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
                'url' => route('news.show', ['locale' => $locale, 'slug' => $a->slug]),
            ]);

        return response()->json(['products' => $products, 'articles' => $articles]);
    }

    public function csr()
    {
        $all = CsrProgram::whereNull('parent_id')->orderBy('sort')->get();
        $map = fn ($rows) => $rows->values()->map(fn ($p) => [
            'title' => $p->tr('title'), 'body' => $p->tr('body'), 'image' => $this->img($p->image),
            'link' => $p->link,
            'url' => $p->slug ? route('csr.show', ['locale' => app()->getLocale(), 'slug' => $p->slug]) : null,
        ]);

        return Inertia::render('Csr', [
            'page' => $this->page('csr'),
            'esg' => $map($all->where('category', 'esg')),
            'health' => $map($all->where('category', 'health_campaign')),
            'sports' => $map($all->where('category', 'sports')),
        ]);
    }

    public function csrShow(string $locale, string $slug)
    {
        $program = CsrProgram::where('slug', $slug)->firstOrFail();

        if ($program->category === 'sports') {
            return Inertia::render('SportsDetail', [
                'program' => [
                    'title' => $program->tr('title'),
                    'subtitle' => $program->tr('body'),
                    'image' => $this->img($program->image),
                ],
                'teams' => $program->children->map(fn ($c) => [
                    'title' => $c->tr('title'),
                    'body' => $c->tr('content') ?: $c->tr('body'),
                    'logo' => $this->img($c->image),
                    'gallery' => collect($c->gallery ?? [])->map(fn ($g) => $this->img($g))->values(),
                ]),
            ]);
        }

        return Inertia::render('CsrDetail', [
            'program' => [
                'title' => $program->tr('title'),
                'subtitle' => $program->tr('body'),
                'body' => $program->tr('content') ?: $program->tr('body'),
                'image' => $this->img($program->image),
                'category' => $program->category,
                'gallery' => collect($program->gallery ?? [])->map(fn ($g) => $this->img($g))->values(),
                'seeAll' => $program->link,
            ],
            'topics' => $program->children->map(fn ($c) => [
                'title' => $c->tr('title'),
                'body' => $c->tr('content') ?: $c->tr('body'),
                'slug' => $c->slug,
            ]),
        ]);
    }

    public function news()
    {
        $articles = Article::published()->latest('published_at')->get();
        $byCat = fn (string $c) => $articles->where('category', $c)->values()->map(fn ($a) => $this->articleCard($a));

        return Inertia::render('News', [
            'page' => $this->page('news'),
            'investor' => $byCat('pembaruan_korporasi'),
            'health' => $byCat('edukasi_gaya_hidup'),
            'product' => $byCat('informasi_produk'),
            'others' => $byCat('lainnya'),
        ]);
    }

    public function newsShow(string $locale, string $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        return Inertia::render('NewsDetail', [
            'article' => array_merge($this->articleCard($article), [
                'body' => $article->tr('body'),
                'category' => $article->category,
                'datetime' => optional($article->published_at)->translatedFormat('l, j F Y - g:i A'),
            ]),
            'others' => Article::published()->where('id', '!=', $article->id)->latest('published_at')->take(4)->get()
                ->map(fn ($a) => $this->articleCard($a)),
        ]);
    }

    public function investor()
    {
        $docs = \App\Models\InvestorDocument::orderByDesc('year')->orderBy('sort')->get();
        $map = fn ($rows) => $rows->values()->map(fn ($d) => [
            'title' => $d->tr('title'), 'year' => $d->year,
            'fileId' => $this->img($d->file_id), 'fileEn' => $this->img($d->file_en),
        ]);

        return Inertia::render('Investor', [
            'page' => $this->page('investor'),
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
            'faqs' => \App\Models\Faq::orderBy('sort')->get()->map(fn ($f) => [
                'question' => $f->tr('question'), 'answer' => $f->tr('answer'),
            ]),
        ]);
    }

    public function contactSubmit(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:60',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        \App\Models\ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => (! empty($data['phone']) ? 'Phone: ' . $data['phone'] . "\n\n" : '') . $data['message'],
        ]);

        return back()->with('contact_success', true);
    }
}