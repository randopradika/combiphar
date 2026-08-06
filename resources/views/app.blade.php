<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
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
</head>
<body>
    @inertia
</body>
</html>