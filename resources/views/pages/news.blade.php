@php
    use Illuminate\Support\Facades\Storage;
    $img = fn ($p) => $p ? Storage::url($p) : null;
    $en = app()->getLocale() === 'en';
    $card = null;
@endphp
<x-layout :title="($page?->tr('meta_title') ?: __('site.nav.news') . ' — Combiphar')">

    <section class="banner banner--about" @if($img($page?->banner_image)) style="background-image:linear-gradient(150deg,rgba(192,86,127,.5),rgba(58,24,96,.6)),url('{{ $img($page?->banner_image) }}');background-size:cover;background-position:center" @endif>
        <div class="container">
            <span class="banner__crumb"><a href="{{ route('home') }}">Home</a> &rsaquo; {{ __('site.nav.news') }}</span>
            <h1 class="display">{{ $page?->tr('banner_title') ?: ($en ? 'News & Health Info' : 'Berita & Info Kesehatan') }}</h1>
        </div>
    </section>

    <nav class="subnav" data-tabs aria-label="Kategori berita">
        <div class="container subnav__inner">
            <button type="button" data-tab="health" class="on">{{ $en ? 'Health Info' : 'Info Kesehatan' }}</button>
            <button type="button" data-tab="invest">{{ $en ? 'Investor Updates' : 'Investor Updates' }}</button>
        </div>
    </nav>

    <section class="section" data-panel="health">
        <div class="container">
            <div class="sec-head rv"><span class="eyebrow eyebrow--magenta">{{ $en ? 'Health Info' : 'Info Kesehatan' }}</span><h2 class="display">{{ $en ? 'Education & Healthy Lifestyle' : 'Edukasi & Gaya Hidup Sehat' }}</h2></div>
            <div class="grid grid--3" style="margin-top:40px">
                @forelse($health as $a)
                <article class="ncard rv">
                    <div class="ncard__img" style="{{ $img($a->cover_image) ? "background-image:url('".$img($a->cover_image)."')" : '' }}"></div>
                    <div class="ncard__body">
                        <span class="ncard__date">{{ optional($a->published_at)->translatedFormat('j F Y') }}</span>
                        <h3 class="ncard__title">{{ $a->tr('title') }}</h3>
                        <hr>
                        <p class="ncard__excerpt">{{ $a->tr('excerpt') }}</p>
                        <a class="btn btn--fill" href="{{ route('news.show', ['slug' => $a->slug]) }}">{{ $en ? 'Read More' : 'Selengkapnya' }}</a>
                    </div>
                </article>
                @empty
                <p class="lead">{{ $en ? 'No articles yet.' : 'Belum ada artikel.' }}</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section" data-panel="invest" style="background:var(--surface)" hidden>
        <div class="container">
            <div class="sec-head rv"><span class="eyebrow eyebrow--magenta">Investor Updates</span><h2 class="display">{{ $en ? 'Corporate Updates & Actions' : 'Pembaruan & Aksi Korporasi' }}</h2></div>
            <div class="grid grid--3" style="margin-top:40px">
                @forelse($corporate as $a)
                <article class="ncard rv">
                    <div class="ncard__img" style="{{ $img($a->cover_image) ? "background-image:url('".$img($a->cover_image)."')" : '' }}"></div>
                    <div class="ncard__body">
                        <span class="ncard__date">{{ optional($a->published_at)->translatedFormat('j F Y') }}</span>
                        <h3 class="ncard__title">{{ $a->tr('title') }}</h3>
                        <hr>
                        <p class="ncard__excerpt">{{ $a->tr('excerpt') }}</p>
                        <a class="btn btn--fill" href="{{ route('news.show', ['slug' => $a->slug]) }}">{{ $en ? 'Read More' : 'Selengkapnya' }}</a>
                    </div>
                </article>
                @empty
                <p class="lead">{{ $en ? 'No updates yet.' : 'Belum ada pembaruan.' }}</p>
                @endforelse
            </div>
        </div>
    </section>

</x-layout>