<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\Article;
use App\Models\Award;
use App\Models\CsrProgram;
use App\Models\Facility;
use App\Models\GlobalSite;
use App\Models\ImpactProgram;
use App\Models\Milestone;
use App\Models\Office;
use App\Models\OnlineShop;
use App\Models\Page;
use App\Models\Person;
use App\Models\ProductCategory;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home', [
            'page' => Page::where('slug', 'home')->first(),
            'impacts' => ImpactProgram::orderBy('sort')->get(),
            'milestones' => Milestone::orderBy('sort')->get(),
            'categories' => ProductCategory::orderBy('sort')->get(),
            'articles' => Article::published()->latest('published_at')->take(3)->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about', [
            'page' => Page::where('slug', 'about')->first(),
            'milestones' => Milestone::orderBy('sort')->get(),
            'commissioners' => Person::where('group', 'commissioners')->orderBy('sort')->get(),
            'directors' => Person::where('group', 'directors')->orderBy('sort')->get(),
            'awards' => Award::orderBy('sort')->get(),
            'sites' => GlobalSite::orderBy('sort')->get(),
            'shops' => OnlineShop::orderBy('sort')->get(),
            'facilities' => Facility::orderBy('sort')->get(),
            'accreditations' => Accreditation::orderBy('sort')->get(),
            'offices' => Office::orderBy('sort')->get(),
        ]);
    }

    public function products()
    {
        return view('pages.products', [
            'page' => Page::where('slug', 'products')->first(),
            'categories' => ProductCategory::with(['products' => fn ($q) => $q->orderBy('sort')])->orderBy('sort')->get(),
            'shops' => OnlineShop::orderBy('sort')->get(),
        ]);
    }

    public function news()
    {
        $articles = \App\Models\Article::published()->latest('published_at')->get();

        return view('pages.news', [
            'page' => Page::where('slug', 'news')->first(),
            'health' => $articles->where('category', 'edukasi_gaya_hidup'),
            'corporate' => $articles->where('category', 'pembaruan_korporasi'),
        ]);
    }

    public function newsShow(string $locale, string $slug)
    {
        $article = \App\Models\Article::where('slug', $slug)->firstOrFail();
        $others = \App\Models\Article::published()->where('id', '!=', $article->id)->latest('published_at')->take(4)->get();

        return view('pages.news-detail', ['article' => $article, 'others' => $others]);
    }

    public function investor()
    {
        $docs = \App\Models\InvestorDocument::orderByDesc('year')->orderBy('sort')->get();

        return view('pages.investor', [
            'page' => Page::where('slug', 'investor')->first(),
            'annual' => $docs->where('category', 'annual_report'),
            'sustainability' => $docs->where('category', 'sustainability'),
            'financial' => $docs->where('category', 'financial'),
            'disclosures' => $docs->where('category', 'disclosure'),
        ]);
    }

    public function contact()
    {
        return view('pages.contact', [
            'page' => Page::where('slug', 'contact')->first(),
            'vacancies' => \App\Models\JobVacancy::where('is_open', true)->orderBy('sort')->get(),
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

        return back()->with('contact_success', true)->withFragment('kontak');
    }

    public function csr()
    {
        $csr = CsrProgram::orderBy('sort')->get();

        return view('pages.csr', [
            'page' => Page::where('slug', 'csr')->first(),
            'esg' => $csr->where('category', 'esg'),
            'health' => $csr->where('category', 'health_campaign'),
            'sports' => $csr->where('category', 'sports'),
        ]);
    }
}