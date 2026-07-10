@props(['mode' => 'solid'])
@php
    $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
    $menu = [
        'about' => __('site.nav.about'),
        'products' => __('site.nav.products'),
        'csr' => __('site.nav.csr'),
        'investor' => __('site.nav.investor'),
        'news' => __('site.nav.news'),
        'contact' => __('site.nav.contact'),
    ];
    $params = request()->route() ? request()->route()->parameters() : [];
    $altId = $routeName ? route($routeName, array_merge($params, ['locale' => 'id'])) : url('/id');
    $altEn = $routeName ? route($routeName, array_merge($params, ['locale' => 'en'])) : url('/en');
@endphp
<header class="nav @if($mode === 'overlay') nav--overlay @endif" id="nav">
    <div class="container nav__inner">
        <a class="nav__logo" href="{{ route('home') }}" aria-label="Combiphar">
            <img class="logo-color" src="{{ asset('img/logo-header.svg') }}" alt="Combiphar">
            <img class="logo-white" src="{{ asset('img/logo-combiphar-white.svg') }}" alt="">
        </a>
        <nav class="nav__menu" aria-label="Main menu">
            @foreach($menu as $slug => $label)
                <a href="{{ route($slug) }}" @class(['active' => $routeName === $slug])>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="nav__tools">
            <span class="nav__lang" aria-label="Language">
                <a href="{{ $altId }}" @class(['active' => app()->getLocale() === 'id'])>ID</a>
                <span class="sep"></span>
                <a href="{{ $altEn }}" @class(['active' => app()->getLocale() === 'en'])>EN</a>
            </span>
            <button class="nav__search" aria-label="{{ __('site.search') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                <span>{{ __('site.search') }}</span>
            </button>
            <button class="nav__burger" id="burger" aria-label="{{ __('site.menu') }}" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>
</header>
<div class="mobilemenu" id="mobilemenu">
    <div class="mobilemenu__top">
        <img src="{{ asset('img/logo-combiphar-white.svg') }}" alt="Combiphar">
        <button class="mobilemenu__close" data-close aria-label="{{ __('site.close') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>
    <nav aria-label="Mobile menu">
        @foreach($menu as $slug => $label)<a href="{{ route($slug) }}">{{ $label }}</a>@endforeach
    </nav>
    <div class="mobilemenu__lang">
        <a href="{{ $altId }}">ID</a><span class="sep"></span><a href="{{ $altEn }}">EN</a>
    </div>
</div>