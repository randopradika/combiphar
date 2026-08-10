<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Sidebar groups, in the order an editor works rather than the order the
     * site is laid out.
     *
     * These used to be one group per frontend page, which meant the sidebar
     * grew a section for every new page, content shown on two pages had no
     * correct home, and anything belonging to no page (footer, social links,
     * legal text) was filed under whichever page was least wrong. Grouping by
     * the KIND OF WORK keeps the list stable as the site grows: a new page adds
     * a row to "Halaman", not a section to this list.
     */
    public const GROUPS = [
        'Halaman',
        'Konten',
        'Profil Perusahaan',
        'Formulir & Pesan',
        'Pengaturan',
        'Pengguna & Akses',
    ];

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Combiphar CMS')
            ->brandLogo(asset('img/logo-header.svg'))
            ->brandLogoHeight('2.4rem')
            ->favicon(asset('img/logo-combiphar.svg'))
            ->colors([
                // Aligned with the public site (resources/css/site.css):
                //   primary  purple  #5B2D8E   accent  magenta #DF0077
                'primary' => Color::hex('#5B2D8E'),
                'magenta' => Color::hex('#DF0077'),
                // Purple-tinted neutral ramp replaces Filament's blue Slate so the
                // whole CMS canvas — page background, sidebar/table borders and
                // muted text — leans into the brand (lavender in light mode, a deep
                // purple-black in dark mode) instead of a cold grey.
                'gray' => [
                    50 => '248, 247, 251',   // #F8F7FB  page background (light)
                    100 => '241, 239, 247',  // #F1EFF7
                    200 => '229, 224, 239',  // #E5E0EF  borders / dividers
                    300 => '208, 200, 224',  // #D0C8E0
                    400 => '167, 158, 188',  // #A79EBC
                    500 => '110, 100, 133',  // #6E6485  muted text
                    600 => '87, 78, 110',    // #574E6E
                    700 => '69, 61, 88',     // #453D58
                    800 => '47, 41, 66',     // #2F2942  sidebar (dark)
                    900 => '32, 27, 51',     // #201B33
                    950 => '21, 15, 36',     // #150F24  page background (dark)
                ],
            ])
            ->defaultThemeMode(ThemeMode::Light)
            ->navigationGroups(array_map(
                fn (string $g) => NavigationGroup::make($g)->collapsible(),
                self::GROUPS,
            ))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

}
