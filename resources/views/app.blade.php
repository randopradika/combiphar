<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    {{-- FS Albert renders the nav + every heading on every page; preloading skips
         the CSS-parse round trip before the fetch. crossorigin is mandatory for
         font preloads even same-origin, or the browser fetches the file twice. --}}
    <link rel="preload" href="/fonts/FSAlbert-Regular.otf" as="font" type="font/otf" crossorigin>
    <link rel="preload" href="/fonts/FSAlbert-Bold.otf" as="font" type="font/otf" crossorigin>
    {{-- Barlow Condensed is used only by the recruitment-fraud headline (Figma 987:51); it rides on the existing request rather than opening a new one. --}}
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;500;600;700;800&family=Barlow:wght@300;400;500;600;700&family=Barlow+Condensed:wght@700&display=swap" rel="stylesheet">
    @if (app()->environment('production'))
        {{-- Same GA4 property the old combiphar.com build reports to --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-P2PSJXT6M6" nonce="{{ $cspNonce ?? '' }}"></script>
        {{-- nonce comes from SecurityHeaders; without it the CSP blocks this. --}}
        <script nonce="{{ $cspNonce ?? '' }}">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-P2PSJXT6M6');
        </script>
    @endif
    @viteReactRefresh
    @vite(['resources/css/site.css', 'resources/js/app.jsx'])
    @inertiaHead
    @php
        $__socials = \App\Models\SocialLink::orderBy('sort')->pluck('url')->filter()->values();
        $__ld = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Combiphar',
            'url' => url('/'),
            'logo' => url('/img/logo-header.svg'),
            'sameAs' => $__socials,
        ], JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json" nonce="{{ $cspNonce ?? '' }}">
    {!! $__ld !!}
    </script>
    {{-- Skema per-halaman (NewsArticle, BreadcrumbList) dikirim controller lewat
         ->withViewData(['jsonLd' => [...]]), jadi tidak ikut membengkakkan payload
         Inertia. Halaman lain tidak menyetelnya sama sekali. --}}
    @foreach ((array) ($jsonLd ?? []) as $__node)
        @php $__nodeJson = json_encode($__node, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); @endphp
        <script type="application/ld+json" nonce="{{ $cspNonce ?? '' }}">
        {!! $__nodeJson !!}
        </script>
    @endforeach
</head>
<body>
    @inertia
</body>
</html>