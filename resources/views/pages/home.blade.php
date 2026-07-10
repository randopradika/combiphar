@php
    use Illuminate\Support\Facades\Storage;
    $img = fn ($p) => $p ? Storage::url($p) : null;
@endphp
<x-layout title="Combiphar — Championing a Healthy Tomorrow" description="Sejak 1971, Combiphar menghadirkan solusi kesehatan terintegrasi untuk Indonesia." navMode="overlay">

    {{-- HERO --}}
    <section class="hero @if($img($page?->hero_image)) hero--photo @endif" aria-label="Hero">
        @if($img($page?->hero_image))
            <img class="hero__img" src="{{ $img($page?->hero_image) }}" alt="Championing a Healthy Tomorrow">
        @endif
        <div class="hero__inner">
            <h1 class="hero__title display">
                <span class="l1">{{ $page?->tr('hero_line1') ?: 'Championing a' }}</span>
                <span class="l2">{{ $page?->tr('hero_line2') ?: 'Healthy Tomorrow' }}</span>
            </h1>
        </div>
        <div class="hero__scroll" aria-hidden="true"><span class="mouse"></span><span>{{ __('site.scroll') }}</span></div>
    </section>

    {{-- OUR IMPACT --}}
    @if($impacts->isNotEmpty())
    <section class="section impact-section">
        <div class="container">
            <div class="sec-head sec-head--center rv">
                <span class="eyebrow eyebrow--magenta">Our Impact</span>
                <h2 class="display">Dampak Nyata untuk Sekitar</h2>
            </div>
            <div class="impact slider rv" data-slider data-autoplay="3000">
                <div class="impact__track slider__track" data-track>
                    @foreach($impacts as $p)
                    <article class="impact-card">
                        <div class="impact-card__media" style="{{ $img($p->image) ? "background-image:url('".$img($p->image)."')" : '' }}"></div>
                        <div class="impact-card__panel">
                            <h3 class="display">{{ $p->tr('title') }}</h3>
                            <p>{{ $p->tr('body') }}</p>
                        </div>
                    </article>
                    @endforeach
                </div>
                <div class="dots impact__dots" data-dots></div>
            </div>
        </div>
    </section>
    @endif

    {{-- MANIFESTO --}}
    <section class="manifesto @if($img($page?->manifesto_image)) manifesto--photo @endif">
        @if($img($page?->manifesto_image))
            <img class="manifesto__img" src="{{ $img($page?->manifesto_image) }}" alt="">
        @endif
        <div class="container manifesto__content rv">
            <h2 class="display">{{ $page?->tr('manifesto_title') ?: 'Manifesto 55 Years of Combiphar' }}</h2>
            @if($page?->manifesto_video)
                <a class="manifesto__play" href="{{ $page->manifesto_video }}" target="_blank" rel="noopener" aria-label="Putar video manifesto"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg></a>
            @else
                <button class="manifesto__play" aria-label="Putar video manifesto"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg></button>
            @endif
            <span class="manifesto__label">Play Video</span>
        </div>
    </section>

    {{-- DARKZONE: JOURNEY + PRODUCTS --}}
    <section class="section darkzone">
        <div class="container journey-head rv">
            <span class="eyebrow eyebrow--lavender">{{ __('site.nav.about') }}</span>
            <h2 class="display">Sekilas Perjalanan</h2>
        </div>

        @if($milestones->isNotEmpty())
        <div class="milestone rv" data-milestone data-autoplay="4000">
            <div class="container">
                <div class="milestone__track" data-track>
                    @foreach($milestones as $m)
                    <figure class="milestone__slide" data-year="{{ $m->year }}" data-caption="{{ e($m->tr('caption')) }}">
                        <div class="milestone__img" style="{{ $img($m->photo) ? "background-image:url('".$img($m->photo)."')" : '' }}"></div>
                    </figure>
                    @endforeach
                </div>
                <div class="milestone__bar"><span class="milestone__bar-fill" data-bar></span></div>
                <div class="milestone__foot">
                    <div class="milestone__year display" data-year-out>{{ $milestones->first()?->year }}</div>
                    <div class="milestone__foot-right">
                        <p class="milestone__caption" data-caption-out>{{ $milestones->first()?->tr('caption') }}</p>
                        <div class="milestone__nav">
                            <button class="arrow-btn arrow-btn--sm" data-prev aria-label="Sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg></button>
                            <button class="arrow-btn arrow-btn--sm" data-next aria-label="Berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="container" style="margin-top:clamp(56px,7vw,104px)">
            <div class="products__head rv">
                <span class="eyebrow eyebrow--lavender">{{ __('site.nav.products') }}</span>
                <h2 class="display">Produk Berstandar Terbaik untuk&nbsp;Indonesia</h2>
                <p>Diformulasikan dengan teknologi terkini dan memenuhi standar Cara Pembuatan Obat yang Baik (CPOB), setiap produk Combiphar dirancang untuk memberikan kualitas terbaik yang bisa Anda andalkan.</p>
                <a class="btn btn--outline-light" href="{{ route('products') }}">{{ __('site.learn_more') }}</a>
            </div>
            <div class="bento rv">
                @foreach($categories as $i => $cat)
                <a class="bcard {{ $i === 0 ? 'bcard--wide' : '' }} {{ $i === 2 ? 'bcard--sq' : '' }}" href="{{ route('products') }}">
                    @if($img($cat->image))<div class="bcard__art" style="background-image:url('{{ $img($cat->image) }}')"></div>@endif
                    <h3>{{ $cat->tr('name') }}</h3>
                    <span class="bcard__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- BERITA TERKINI --}}
    @if($articles->isNotEmpty())
    <section class="section" style="background:var(--surface)">
        <div class="container">
            <div class="sec-head sec-head--center rv">
                <span class="eyebrow eyebrow--magenta">Berita &amp; Info Kesehatan</span>
                <h2 class="display">Berita Terkini</h2>
            </div>
            <div class="grid grid--3" style="margin-top:44px">
                @foreach($articles as $a)
                <article class="ncard rv">
                    <div class="ncard__img" style="{{ $img($a->cover_image) ? "background-image:url('".$img($a->cover_image)."')" : '' }}"></div>
                    <div class="ncard__body">
                        <span class="ncard__date">{{ optional($a->published_at)->translatedFormat('j F Y') }}</span>
                        <h3 class="ncard__title">{{ $a->tr('title') }}</h3>
                        <hr>
                        <p class="ncard__excerpt">{{ $a->tr('excerpt') }}</p>
                        <a class="btn btn--fill" href="{{ route('news.show', ['slug' => $a->slug]) }}">{{ __('site.read_more') }}</a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section class="cta" aria-label="Ajakan berkarir" @if($img($page?->cta_image)) style="background-image:linear-gradient(140deg,rgba(58,24,96,.7),rgba(42,0,90,.85)),url('{{ $img($page?->cta_image) }}');background-size:cover;background-position:center" @endif>
        <h2 class="display rv">{{ $page?->tr('cta_title') ?: 'Growing Through a Journey to Build a Healthy Tomorrow' }}</h2>
    </section>

</x-layout>