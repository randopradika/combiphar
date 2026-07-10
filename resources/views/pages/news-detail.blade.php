@php
    use Illuminate\Support\Facades\Storage;
    $img = fn ($p) => $p ? Storage::url($p) : null;
    $en = app()->getLocale() === 'en';
@endphp
<x-layout :title="$article->tr('title') . ' — Combiphar'" :description="$article->tr('excerpt')">

    <section class="detail-hero" @if($img($article->cover_image)) style="background-image:url('{{ $img($article->cover_image) }}');background-size:cover;background-position:center" @endif>
        <div class="detail-hero__overlay"></div>
        <div class="container">
            <span class="detail-hero__cat">{{ $article->category === 'pembaruan_korporasi' ? ($en ? 'Investor Updates' : 'Pembaruan Korporasi') : ($en ? 'Health Info' : 'Info Kesehatan') }}</span>
            <h1 class="display article__title">{{ $article->tr('title') }}</h1>
            <span class="detail-hero__date">{{ optional($article->published_at)->translatedFormat('j F Y') }}</span>
        </div>
    </section>

    <section class="section">
        <div class="container article-layout">
            <article class="article-body rv">
                @if($article->tr('excerpt'))<p class="article-lead">{{ $article->tr('excerpt') }}</p>@endif
                {!! $article->tr('body') !!}
                <a class="btn btn--outline" href="{{ route('news') }}" style="margin-top:24px">&larr; {{ $en ? 'Back to News' : 'Kembali ke Berita' }}</a>
            </article>
            <aside class="article-aside rv">
                <h2>{{ $en ? 'Other News' : 'Berita Lainnya' }}</h2>
                @foreach($others as $o)
                <a class="mini-card" href="{{ route('news.show', ['slug' => $o->slug]) }}">
                    <div class="mini-card__img" style="{{ $img($o->cover_image) ? "background-image:url('".$img($o->cover_image)."')" : '' }}"></div>
                    <div><span>{{ optional($o->published_at)->translatedFormat('j M Y') }}</span><h4>{{ $o->tr('title') }}</h4></div>
                </a>
                @endforeach
            </aside>
        </div>
    </section>

</x-layout>