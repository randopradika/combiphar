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
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PageController extends Controller
{
    private function img(?string $path): ?string
    {
        return $path ? Storage::url($path) : null;
    }

    private function page(string $slug): ?array
    {
        $p = Page::where('slug', $slug)->first();
        if (! $p) return null;

        return [
            'metaTitle' => $p->tr('meta_title'),
            'metaDescription' => $p->tr('meta_description'),
            'bannerTitle' => $p->tr('banner_title'),
            'bannerSubtitle' => $p->tr('banner_subtitle'),
            'bannerImage' => $this->img($p->banner_image),
            'heroImage' => $this->img($p->hero_image),
            'heroLine1' => $p->tr('hero_line1'),
            'heroLine2' => $p->tr('hero_line2'),
            'manifestoImage' => $this->img($p->manifesto_image),
            'manifestoTitle' => $p->tr('manifesto_title'),
            'manifestoVideo' => $p->manifesto_video,
            'ctaImage' => $this->img($p->cta_image),
            'ctaTitle' => $p->tr('cta_title'),
            'intro' => $p->tr('intro'),
            'vision' => $p->tr('vision'),
            'mission' => $p->tr('mission'),
            'values' => $p->tr('values'),
            'stat1Value' => $p->stat1_value,
            'stat1Label' => $p->tr('stat1_label'),
            'stat2Value' => $p->stat2_value,
            'stat2Label' => $p->tr('stat2_label'),
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
            'milestones' => Milestone::orderBy('sort')->get()->map(fn ($m) => [
                'year' => $m->year, 'caption' => $m->tr('caption'), 'photo' => $this->img($m->photo),
            ]),
            'commissioners' => Person::where('group', 'commissioners')->orderBy('sort')->get()->map($person),
            'directors' => Person::where('group', 'directors')->orderBy('sort')->get()->map($person),
            'awards' => Award::orderBy('sort')->get()->map(fn ($a) => [
                'title' => $a->tr('title'), 'year' => $a->year, 'image' => $this->img($a->image),
            ]),
            'shops' => $this->shops(),
            'facilities' => Facility::orderBy('sort')->get()->map(fn ($f) => [
                'name' => $f->name, 'region' => $f->region, 'plants' => $f->plants,
                'area' => $f->area, 'detail' => $f->tr('detail'), 'image' => $this->img($f->image),
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

    public function products()
    {
        return Inertia::render('Products', [
            'page' => $this->page('products'),
            'categories' => ProductCategory::with(['products' => fn ($q) => $q->orderBy('sort')])->orderBy('sort')->get()
                ->map(fn ($c) => [
                    'slug' => $c->slug, 'name' => $c->tr('name'), 'description' => $c->tr('description'),
                    'products' => $c->products->map(fn ($p) => [
                        'name' => $p->tr('name'), 'description' => $p->tr('description'), 'image' => $this->img($p->image),
                    ]),
                ]),
            'shops' => $this->shops(),
        ]);
    }

    public function csr()
    {
        $all = CsrProgram::orderBy('sort')->get();
        $map = fn ($rows) => $rows->values()->map(fn ($p) => [
            'title' => $p->tr('title'), 'body' => $p->tr('body'), 'image' => $this->img($p->image),
        ]);

        return Inertia::render('Csr', [
            'page' => $this->page('csr'),
            'esg' => $map($all->where('category', 'esg')),
            'health' => $map($all->where('category', 'health_campaign')),
            'sports' => $map($all->where('category', 'sports')),
        ]);
    }

    public function news()
    {
        $articles = Article::published()->latest('published_at')->get();

        return Inertia::render('News', [
            'page' => $this->page('news'),
            'health' => $articles->where('category', 'edukasi_gaya_hidup')->values()->map(fn ($a) => $this->articleCard($a)),
            'corporate' => $articles->where('category', 'pembaruan_korporasi')->values()->map(fn ($a) => $this->articleCard($a)),
        ]);
    }

    public function newsShow(string $locale, string $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        return Inertia::render('NewsDetail', [
            'article' => array_merge($this->articleCard($article), [
                'body' => $article->tr('body'),
                'category' => $article->category,
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
        ]);
    }

    public function contact()
    {
        return Inertia::render('Contact', [
            'page' => $this->page('contact'),
            'vacancies' => JobVacancy::where('is_open', true)->orderBy('sort')->get()->map(fn ($v) => [
                'title' => $v->tr('title'), 'department' => $v->tr('department'),
                'location' => $v->location, 'description' => $v->tr('description'),
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