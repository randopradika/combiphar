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
            'routeName' => fn () => Route::currentRouteName(),
            't' => fn () => Lang::get('site'),
            'altUrls' => function () use ($request) {
                $routeName = Route::currentRouteName();
                $params = $request->route() ? $request->route()->parameters() : [];
                $alt = fn (string $loc) => $routeName
                    ? route($routeName, array_merge($params, ['locale' => $loc]))
                    : url('/' . $loc);

                return ['id' => $alt('id'), 'en' => $alt('en')];
            },
            'nav' => fn () => collect(['about', 'products', 'csr', 'investor', 'news', 'contact'])
                ->mapWithKeys(fn ($s) => [$s => route($s, ['locale' => app()->getLocale()])])->all(),
            'homeUrl' => fn () => url('/'),
            'flash' => fn () => ['contact_success' => (bool) session('contact_success')],
            'footer' => function () {
                $p = \App\Models\Page::where('slug', 'home')->first();

                return [
                    'facebook' => $p?->facebook_url,
                    'instagram' => $p?->instagram_url,
                    'youtube' => $p?->youtube_url,
                    'linkedin' => $p?->linkedin_url,
                    'tiktok' => $p?->tiktok_url,
                    'copyright' => $p?->tr('footer_copyright'),
                ];
            },
        ]);
    }
}