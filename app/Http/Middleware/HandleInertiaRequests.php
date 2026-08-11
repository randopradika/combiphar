<?php

namespace App\Http\Middleware;

use App\Models\FooterSetting;
use App\Models\NavItem;
use App\Models\Page;
use App\Models\SocialLink;
use App\Support\Localize;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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
            'routeName' => fn () => Localize::base(Route::currentRouteName()),
            // Menu navigasi kini berasal dari CMS. Bentuk `nav.menus` sengaja
            // dibuat identik dengan yang dulu ditulis tangan di berkas bahasa,
            // jadi tidak ada komponen React yang perlu berubah.
            't' => function () {
                $t = Lang::get('site');
                $menus = $this->navMenus();

                if ($menus) {
                    $t['nav']['menus'] = $menus;
                }

                return $t;
            },
            'altUrls' => function () use ($request) {
                $base = Localize::base(Route::currentRouteName());
                $params = $request->route() ? $request->route()->parameters() : [];
                $alt = fn (string $loc) => $base
                    ? Localize::url($base, $loc, $params)
                    : url('/'.$loc);

                return ['id' => $alt('id'), 'en' => $alt('en')];
            },
            'nav' => fn () => collect(['about', 'products', 'csr', 'investor', 'news', 'contact', 'terms', 'privacy'])
                ->mapWithKeys(fn ($s) => [$s => Localize::url($s)])->all(),
            // Menu items in site order, minus any page switched off in the CMS.
            // Only this list is filtered — hiding an item never takes its route
            // away, so direct links and the sitemap keep working.
            'menuSections' => function () {
                $sections = ['about', 'products', 'csr', 'investor', 'news', 'contact'];
                $hidden = Page::where('show_in_menu', false)->pluck('slug')->all();

                return array_values(array_diff($sections, $hidden));
            },
            'homeUrl' => fn () => Localize::url('home'),
            'flash' => fn () => ['contact_success' => (bool) session('contact_success')],
            // Seluruh isi footer berasal dari satu baris footer_settings, disunting
            // di Halaman -> Footer. try/catch-nya sama alasannya dengan navMenus():
            // lingkungan yang belum menjalankan migrasinya harus tetap merender
            // situs, bukan 500 di setiap halaman.
            'footer' => function () {
                try {
                    $f = FooterSetting::query()->orderBy('id')->first();
                } catch (\Throwable) {
                    $f = null;
                }

                return [
                    'socials' => SocialLink::orderBy('sort')->get()->map(fn ($s) => [
                        'name' => $s->name,
                        'url' => $s->url,
                        'icon' => $s->icon ? Storage::url($s->icon) : null,
                    ]),
                    'followLabel' => $f?->tr('follow_label'),
                    'copyright' => $f?->tr('copyright'),
                    'logos' => collect($f?->logos ?? [])
                        ->filter(fn ($l) => ! empty($l['image']))
                        ->map(fn ($l) => [
                            'src' => Storage::url($l['image']),
                            'alt' => $l['alt'] ?? '',
                            // Tinggi pada 1920px; site.css yang menyusutkannya.
                            'height' => (int) ($l['height'] ?: 82),
                        ])
                        ->values(),
                ];
            },
        ]);
    }

    /**
     * Menu navigasi dari CMS, dalam bentuk yang sama persis dengan berkas
     * bahasa: bagian => daftar item {label, suffix, base?, head?, children?}.
     *
     * Mengembalikan array kosong bila tabelnya belum ada atau masih kosong,
     * sehingga berkas bahasa tetap menjadi cadangan — penting untuk lingkungan
     * yang belum menjalankan migrasinya, dan agar menu tidak pernah hilang
     * hanya karena tabelnya kosong.
     */
    private function navMenus(): array
    {
        try {
            $items = NavItem::orderBy('sort')->get();
        } catch (\Throwable) {
            return [];
        }

        if ($items->isEmpty()) {
            return [];
        }

        return $items
            ->whereNull('parent_id')
            ->groupBy('section')
            ->map(fn ($sectionItems) => $this->navItemsTree($sectionItems, $items))
            ->all();
    }

    /**
     * @param  Collection<int, NavItem>  $items
     * @param  Collection<int, NavItem>  $all
     * @return array<int, array<string, mixed>>
     */
    private function navItemsTree($items, $all): array
    {
        return $items->values()->map(function ($item) use ($all) {
            $entry = [
                'label' => $item->tr('label'),
                'suffix' => $item->suffix ?? '',
            ];

            // Kunci opsional hanya ditulis bila terpakai, supaya payload-nya
            // sama dengan berkas bahasa dan React tidak melihat 'head' => false
            // di tempat yang dulu tidak punya kunci itu sama sekali.
            if (filled($item->base)) {
                $entry['base'] = $item->base;
            }

            if ($item->is_head) {
                $entry['head'] = true;
            }

            $children = $all->where('parent_id', $item->id);

            if ($children->isNotEmpty()) {
                $entry['children'] = $this->navItemsTree($children, $all);
            }

            return $entry;
        })->all();
    }
}
