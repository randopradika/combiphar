@php
    use Illuminate\Support\Facades\Storage;
    $img = fn ($p) => $p ? Storage::url($p) : null;
    $en = app()->getLocale() === 'en';
@endphp
<x-layout :title="($page?->tr('meta_title') ?: __('site.nav.csr') . ' — Combiphar')">

    <section class="banner banner--about" @if($img($page?->banner_image)) style="background-image:linear-gradient(150deg,rgba(63,110,59,.5),rgba(58,24,96,.6)),url('{{ $img($page?->banner_image) }}');background-size:cover;background-position:center" @endif>
        <div class="container">
            <span class="banner__crumb"><a href="{{ route('home') }}">Home</a> &rsaquo; {{ __('site.nav.csr') }}</span>
            <h1 class="display">{{ $page?->tr('banner_title') ?: __('site.nav.csr') }}</h1>
        </div>
    </section>

    @if($page?->tr('banner_subtitle') || $page?->tr('intro'))
    <section class="section"><div class="container">
        @if($page?->tr('banner_subtitle'))<h2 class="display rv" style="color:var(--purple-600);font-size:clamp(28px,2.9vw,50px);max-width:20ch">{{ $page->tr('banner_subtitle') }}</h2>@endif
        @if($page?->tr('intro'))<p class="rv" style="margin-top:24px;max-width:1273px;font-size:clamp(16px,1.25vw,20px);line-height:1.7;color:var(--text-muted)">{{ $page->tr('intro') }}</p>@endif
    </div></section>
    @endif

    @if($esg->isNotEmpty())
    <section class="section section--purple">
        <div class="container">
            <div class="sec-head rv"><span class="eyebrow eyebrow--lavender">ESG</span><h2 class="display">Environmental, Social, and Governance</h2><p>{{ $en ? 'Combiphar applies ESG principles as part of its long-term commitment to responsible, sustainable growth with impact for society and the environment.' : 'Combiphar menerapkan prinsip ESG sebagai bagian dari komitmen jangka panjang dalam menciptakan pertumbuhan yang bertanggung jawab, berkelanjutan, dan berdampak bagi masyarakat serta lingkungan.' }}</p></div>
            <div class="csr-list">
                @foreach($esg as $i => $p)
                <article class="csr-item rv @if($i % 2 === 1) csr-item--flip @endif">
                    <div class="csr-item__media" style="{{ $img($p->image) ? "background-image:url('".$img($p->image)."')" : '' }}"></div>
                    <div class="csr-item__body"><h3>{{ $p->tr('title') }}</h3><p>{{ $p->tr('body') }}</p></div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($health->isNotEmpty())
    <section class="section">
        <div class="container">
            <div class="sec-head rv"><span class="eyebrow eyebrow--magenta">Health Campaign</span><h2 class="display">Health Campaign</h2><p>{{ $en ? 'Through various Health Campaigns, Combiphar delivers initiatives supporting health, empowerment, education, and an active lifestyle to create positive impact for Indonesian society.' : 'Melalui berbagai Health Campaign, Combiphar menghadirkan inisiatif yang mendukung kesehatan, pemberdayaan, pendidikan, dan gaya hidup aktif untuk menciptakan dampak positif bagi masyarakat Indonesia.' }}</p></div>
            <div class="csr-list">
                @foreach($health as $i => $p)
                <article class="csr-item rv @if($i % 2 === 1) csr-item--flip @endif">
                    <div class="csr-item__media" style="{{ $img($p->image) ? "background-image:url('".$img($p->image)."')" : '' }}"></div>
                    <div class="csr-item__body"><h3>{{ $p->tr('title') }}</h3><p>{{ $p->tr('body') }}</p></div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($sports->isNotEmpty())
    <section class="section section--dark">
        <div class="container">
            <div class="sec-head rv"><span class="eyebrow eyebrow--lavender">Sports</span><h2 class="display">Sports</h2><p>{{ $en ? 'Encouraging an active, high-achieving spirit through real support for the development of Indonesian sports.' : 'Mendorong semangat aktif dan berprestasi melalui dukungan nyata terhadap perkembangan olahraga Indonesia.' }}</p></div>
            <div class="sport-grid">
                @foreach($sports as $p)
                <div class="sport-card rv"><div class="sport-card__img" style="{{ $img($p->image) ? "background-image:url('".$img($p->image)."')" : '' }}"></div><h3>{{ $p->tr('title') }}</h3></div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

</x-layout>