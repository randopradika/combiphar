<?php echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($urls as $u)
<url><loc>{{ $u['loc'] }}</loc>@if (! empty($u['lastmod']))<lastmod>{{ $u['lastmod'] }}</lastmod>@endif
@foreach ($u['alternates'] ?? [] as $hreflang => $href)
<xhtml:link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}"/>
@endforeach
<priority>{{ $u['priority'] }}</priority></url>
@endforeach
</urlset>
