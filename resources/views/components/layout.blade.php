@props(['title' => 'Combiphar', 'description' => null, 'navMode' => 'solid'])
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if($description)<meta name="description" content="{{ $description }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;500;600;700;800&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/site.css', 'resources/js/site.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <x-navbar :mode="$navMode" />
    <main id="main">{{ $slot }}</main>
    <x-footer />
</body>
</html>