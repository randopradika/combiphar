<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        // Closures are resolved lazily at render time, after SetLocale has
        // set the app locale and URL defaults for the {locale} prefix.
        return array_merge(parent::share($request), [
            'locale' => fn () => app()->getLocale(),
            'routeName' => fn () => \App\Support\Localize::base(Route::currentRouteName()),
            't' => fn () => Lang::get('site'),
            'altUrls' => function () use ($request) {
                $base = \App\Support\Localize::base(Route::currentRouteName());
                $params = $request->route() ? $request->route()->parameters() : [];
                $alt = fn (string $loc) => $base
                    ? \App\Support\Localize::url($base, $loc, $params)
                    : url('/' . $loc);

                return ['id' => $alt('id'), 'en' => $alt('en')];
            },
            'nav' => fn () => collect(['about', 'products', 'csr', 'investor', 'news', 'contact', 'terms', 'privacy'])
                ->mapWithKeys(fn ($s) => [$s => \App\Support\Localize::url($s)])->all(),
            'homeUrl' => fn () => \App\Support\Localize::url('home'),
            'flash' => fn () => ['contact_success' => (bool) session('contact_success')],
            'footer' => function () {
                $p = \App\Models\Page::where('slug', 'home')->first();

                return [
                    'socials' => \App\Models\SocialLink::orderBy('sort')->get()->map(fn ($s) => [
                        'name' => $s->name,
                        'url' => $s->url,
                        'icon' => $s->icon ? \Illuminate\Support\Facades\Storage::url($s->icon) : null,
                    ]),
                    'copyright' => $p?->tr('footer_copyright'),
                ];
            },
        ]);
    }
}