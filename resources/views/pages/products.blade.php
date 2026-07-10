@php
    use Illuminate\Support\Facades\Storage;
    $img = fn ($p) => $p ? Storage::url($p) : null;
    $en = app()->getLocale() === 'en';
@endphp
<x-layout :title="($page?->tr('meta_title') ?: __('site.nav.products') . ' — Combiphar')">

    <section class="banner banner--about" @if($img($page?->banner_image)) style="background-image:linear-gradient(150deg,rgba(91,45,142,.55),rgba(58,24,96,.6)),url('{{ $img($page?->banner_image) }}');background-size:cover;background-position:center" @endif>
        <div class="container">
            <span class="banner__crumb"><a href="{{ route('home') }}">Home</a> &rsaquo; {{ __('site.nav.products') }}</span>
            <h1 class="display">{{ $page?->tr('banner_title') ?: __('site.nav.products') }}</h1>
        </div>
    </section>

    @if($categories->isNotEmpty())
    <nav class="subnav" data-tabs aria-label="Kategori produk">
        <div class="container subnav__inner">
            @foreach($categories as $i => $cat)
                <button type="button" data-tab="{{ $cat->slug }}" @class(['on' => $i === 0])>{{ $cat->tr('name') }}</button>
            @endforeach
        </div>
    </nav>

    @foreach($categories as $i => $cat)
    <section class="section" data-panel="{{ $cat->slug }}" @if($i > 0) hidden @endif>
        <div class="container">
            <div class="sec-head rv">
                <span class="eyebrow eyebrow--magenta">{{ $en ? 'Category' : 'Kategori' }}</span>
                <h2 class="display">{{ $cat->tr('name') }}</h2>
                @if($cat->tr('description'))<p>{{ $cat->tr('description') }}</p>@endif
            </div>

            <div class="toolbar rv">
                <label class="searchbox">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                    <input type="search" data-search placeholder="{{ $en ? 'Search products…' : 'Cari produk…' }}">
                </label>
                <span class="selectbox">
                    <select data-sort aria-label="{{ $en ? 'Sort' : 'Urutkan' }}">
                        <option value="az">A&ndash;Z</option>
                        <option value="za">Z&ndash;A</option>
                    </select>
                </span>
            </div>

            <div class="grid grid--4" data-grid style="margin-top:24px">
                @forelse($cat->products as $p)
                <article class="pcard rv" data-product data-name="{{ e($p->tr('name')) }}" data-cat="{{ e($cat->tr('name')) }}" data-desc="{{ e($p->tr('description')) }}" data-img="{{ $img($p->image) }}">
                    <div class="pcard__img" style="{{ $img($p->image) ? "background-image:url('".$img($p->image)."')" : '' }}"></div>
                    <div class="pcard__body"><span class="cat">{{ $cat->tr('name') }}</span><h3>{{ $p->tr('name') }}</h3></div>
                </article>
                @empty
                <p class="lead">{{ $en ? 'Products coming soon.' : 'Produk akan segera hadir.' }}</p>
                @endforelse
            </div>
            <p class="toolbar-empty" data-empty hidden>{{ $en ? 'No products match your search.' : 'Tidak ada produk yang cocok.' }}</p>
        </div>
    </section>
    @endforeach
    @endif

    {{-- PRODUCT DETAIL MODAL --}}
    <div class="modal" id="product-modal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal__backdrop" data-close></div>
        <div class="modal__box">
            <button class="modal__close" data-close aria-label="{{ __('site.close') }}">{{ __('site.close') }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            <div class="pmodal__grid">
                <div class="pmodal__img" id="pm-img"></div>
                <div>
                    <span class="eyebrow eyebrow--magenta" id="pm-cat"></span>
                    <h3 id="pm-name"></h3>
                    <p id="pm-desc"></p>
                    @if($shops->isNotEmpty())
                    <div class="pmodal__shops">
                        <h4>{{ $en ? 'Available at' : 'Tersedia di' }}</h4>
                        <div class="market market--sm">
                            @foreach($shops as $s)<a href="{{ $s->url ?: '#' }}" target="_blank" rel="noopener" aria-label="{{ $s->name }}">@if($img($s->logo))<img src="{{ $img($s->logo) }}" alt="{{ $s->name }}">@else{{ $s->name }}@endif</a>@endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-layout>