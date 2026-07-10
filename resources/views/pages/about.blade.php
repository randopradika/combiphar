@php
    use Illuminate\Support\Facades\Storage;
    $img = fn ($p) => $p ? Storage::url($p) : null;
    $en = app()->getLocale() === 'en';
@endphp
<x-layout :title="($page?->tr('meta_title') ?: __('site.nav.about') . ' — Combiphar')">

    {{-- BANNER --}}
    <section class="banner banner--about" @if($img($page?->banner_image)) style="background-image:linear-gradient(120deg,rgba(74,31,122,.55),rgba(43,0,90,.5)),url('{{ $img($page?->banner_image) }}');background-size:cover;background-position:center" @endif>
        <div class="container">
            <span class="banner__crumb"><a href="{{ route('home') }}">Home</a> &rsaquo; {{ __('site.nav.about') }}</span>
            <h1 class="display">{{ $page?->tr('banner_title') ?: 'Championing a Healthy Tomorrow' }}</h1>
        </div>
    </section>

    {{-- INTRO --}}
    @if($page?->tr('intro'))
    <section class="section"><div class="container"><p class="about-intro rv">{{ $page->tr('intro') }}</p></div></section>
    @endif

    {{-- SEJARAH & TUJUAN --}}
    @if($milestones->isNotEmpty())
    <section class="section darkzone">
        <div class="container journey-head rv">
            <span class="eyebrow eyebrow--lavender">{{ __('site.nav.about') }}</span>
            <h2 class="display">{{ $en ? 'Our History & Purpose' : 'Sejarah & Tujuan Kami' }}</h2>
        </div>
        <x-milestone :items="$milestones" />
    </section>
    @endif

    {{-- VISI MISI NILAI --}}
    @if($page?->tr('vision') || $page?->tr('mission') || $page?->tr('values'))
    <section class="section"><div class="container"><div class="vmv-list">
        @if($page?->tr('vision'))<div class="vmv-row rv"><h3>{{ $en ? 'Our Vision' : 'Visi Kami' }}</h3><span class="arrow"><svg viewBox="0 0 48 32" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 8h34m0 0-10-6m10 6-10 6"/><path d="M30 24h14"/></svg></span><p>{{ $page->tr('vision') }}</p></div>@endif
        @if($page?->tr('mission'))<div class="vmv-row rv"><h3>{{ $en ? 'Our Mission' : 'Misi Kami' }}</h3><span class="arrow"><svg viewBox="0 0 48 32" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 8h34m0 0-10-6m10 6-10 6"/><path d="M30 24h14"/></svg></span><p>{{ $page->tr('mission') }}</p></div>@endif
        @if($page?->tr('values'))<div class="vmv-row rv"><h3>{{ $en ? 'Our Values' : 'Nilai Kami' }}</h3><span class="arrow"><svg viewBox="0 0 48 32" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 8h34m0 0-10-6m10 6-10 6"/><path d="M30 24h14"/></svg></span><p>{{ $page->tr('values') }}</p></div>@endif
    </div></div></section>
    @endif

    {{-- BOARD --}}
    @if($commissioners->isNotEmpty() || $directors->isNotEmpty())
    <section class="section section--purple"><div class="container">
        @if($commissioners->isNotEmpty())
        <div class="sec-head sec-head--center rv"><span class="eyebrow eyebrow--lavender">{{ $en ? 'Leadership' : 'Kepemimpinan' }}</span><h2 class="display">Board of Commissioners</h2></div>
        <div class="board-grid" style="margin-top:44px">
            @foreach($commissioners as $p)
            <div class="board-card rv" data-board data-name="{{ e($p->name) }}" data-role="{{ e($p->tr('role')) }}" data-bio="{{ e($p->tr('bio')) }}" data-img="{{ $img($p->photo) }}">
                <div class="board-card__photo" style="{{ $img($p->photo) ? "background-image:url('".$img($p->photo)."')" : '' }}"></div>
                <div class="board-card__info"><h4>{{ $p->name }}</h4><span class="board-card__role">{{ $p->tr('role') }}</span><span class="board-card__bio-btn">{{ $en ? 'Biography' : 'Biografi' }}</span></div>
            </div>
            @endforeach
        </div>
        @endif
        @if($directors->isNotEmpty())
        <div class="sec-head sec-head--center rv" style="margin-top:clamp(48px,5vw,80px)"><h2 class="display">Board of Directors</h2></div>
        <div class="board-grid" style="margin-top:44px">
            @foreach($directors as $p)
            <div class="board-card rv" data-board data-name="{{ e($p->name) }}" data-role="{{ e($p->tr('role')) }}" data-bio="{{ e($p->tr('bio')) }}" data-img="{{ $img($p->photo) }}">
                <div class="board-card__photo" style="{{ $img($p->photo) ? "background-image:url('".$img($p->photo)."')" : '' }}"></div>
                <div class="board-card__info"><h4>{{ $p->name }}</h4><span class="board-card__role">{{ $p->tr('role') }}</span><span class="board-card__bio-btn">{{ $en ? 'Biography' : 'Biografi' }}</span></div>
            </div>
            @endforeach
        </div>
        @endif
    </div></section>
    @endif

    {{-- PENCAPAIAN & PENGHARGAAN --}}
    @if($awards->isNotEmpty())
    <section class="section"><div class="container">
        <div class="sec-head sec-head--center rv"><span class="eyebrow eyebrow--magenta">{{ $en ? 'Achievements' : 'Pencapaian' }}</span><h2 class="display">{{ $en ? 'Achievements & Awards' : 'Pencapaian & Penghargaan' }}</h2><p>{{ $en ? 'Our commitment to quality and sustainability is recognised through national and international awards.' : 'Komitmen kami terhadap kualitas dan keberlanjutan diakui melalui berbagai penghargaan nasional maupun internasional.' }}</p></div>
        <div class="awards rv" style="margin-top:44px">
            @foreach($awards as $a)<div class="award-tile">@if($img($a->image))<img src="{{ $img($a->image) }}" alt="{{ $a->tr('title') }}">@else<span>{{ $a->tr('title') }}@if($a->year)<br><b>{{ $a->year }}</b>@endif</span>@endif</div>@endforeach
        </div>
        @if($page?->stat1_value || $page?->stat2_value)
        <div class="stat-cards rv">
            @if($page?->stat1_value)<div class="stat-card"><div class="num">{{ $page->stat1_value }}</div><p>{{ $page->tr('stat1_label') }}</p></div>@endif
            @if($page?->stat2_value)<div class="stat-card"><div class="num">{{ $page->stat2_value }}</div><p>{{ $page->tr('stat2_label') }}</p></div>@endif
        </div>
        @endif
    </div></section>
    @endif

    {{-- KEHADIRAN KAMI (world map) --}}
    @if($facilities->isNotEmpty() || $sites->isNotEmpty())
    <section class="section" style="background:var(--surface)"><div class="container">
        <div class="sec-head sec-head--center rv"><span class="eyebrow eyebrow--magenta">{{ $en ? 'Global Reach' : 'Jangkauan Global' }}</span><h2 class="display">{{ $en ? 'Our Presence' : 'Kehadiran Kami' }}</h2><p>{{ $en ? 'From Indonesia to the world - click a pin to see our production facilities.' : 'Dari Indonesia untuk dunia - klik pin untuk melihat fasilitas produksi kami.' }}</p></div>
        <div class="world-map rv">
            <img class="world-map__img" src="{{ asset('img/world-map.png') }}" alt="{{ $en ? 'Combiphar global presence map' : 'Peta kehadiran global Combiphar' }}">
            <button type="button" class="map-pin" data-facilities style="left:15.6%;top:38%"><i style="--pin:#ffffff"></i><span>North America</span></button>
            <button type="button" class="map-pin" data-facilities style="left:48.4%;top:44%"><i style="--pin:#DCC4F6"></i><span>Africa</span></button>
            <button type="button" class="map-pin" data-facilities style="left:76.5%;top:41%"><i style="--pin:#F46EB5"></i><span>Asia</span></button>
            <button type="button" class="map-pin" data-facilities style="left:90%;top:63%"><i style="--pin:#F46EB5"></i><span>Indonesia</span></button>
            <button type="button" class="map-pin" data-facilities style="left:92.5%;top:79%"><i style="--pin:#9D7EC6"></i><span>Australia</span></button>
        </div>
        <div class="map-legend rv">
            <span><i style="background:#F46EB5"></i>Asia</span><span><i style="background:#9D7EC6"></i>Australia</span>
            <span><i style="background:#DCC4F6"></i>Africa</span><span><i style="background:#fff;border:1px solid #ccc"></i>North America</span>
            <button type="button" class="btn btn--outline" data-facilities style="margin-left:auto">{{ $en ? 'View Production Facilities' : 'Lihat Fasilitas Produksi' }}</button>
        </div>
    </div></section>
    @endif

    {{-- OFFICES --}}
    @if($offices->isNotEmpty())
    <section class="section"><div class="container">
        <div class="loc-head rv">
            <h3 class="display" style="font-size:clamp(24px,2.4vw,34px);color:var(--purple-800)">{{ $en ? 'Our Locations' : 'Lokasi Kami' }}</h3>
            <div class="loc-filters">
                <span class="selectbox"><select data-office-city aria-label="Location"><option value="">{{ $en?'Location: All':'Lokasi: Semua' }}</option>@foreach($offices->pluck('city')->filter()->unique() as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></span>
                <span class="selectbox"><select data-office-cat aria-label="Category"><option value="">{{ $en?'Category: All':'Kategori: Semua' }}</option>@foreach($offices->pluck('category')->filter()->unique() as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></span>
            </div>
        </div>
        <div class="grid grid--4" data-office-grid style="margin-top:24px">
            @foreach($offices as $o)
            <div class="office-card rv" data-office data-city="{{ e($o->city) }}" data-cat="{{ e($o->category) }}">
                <h4>{{ $o->name }}</h4>
                <p>{{ $o->tr('description') }}@if($o->phone)<br>Phone: {{ $o->phone }}@endif</p>
            </div>
            @endforeach
        </div>
        <p class="toolbar-empty" data-office-empty hidden>{{ $en?'No offices match your filter.':'Tidak ada kantor yang cocok.' }}</p>
    </div></section>
    @endif

    {{-- TOKO ONLINE --}}
    @if($shops->isNotEmpty())
    <section class="section darkzone"><div class="container">
        <div class="sec-head sec-head--center rv"><span class="eyebrow eyebrow--lavender">{{ $en ? 'Official Store' : 'Belanja Resmi' }}</span><h2 class="display">{{ $en ? 'Our Official Online Stores' : 'Toko Online Resmi Kami' }}</h2><p>{{ $en ? 'Original Combiphar & Combicare products on your favourite marketplaces.' : 'Produk original Combiphar & Combicare di marketplace favorit Anda.' }}</p></div>
        <div class="market rv">
            @foreach($shops as $s)<a href="{{ $s->url ?: '#' }}" target="_blank" rel="noopener" aria-label="{{ $s->name }}">@if($img($s->logo))<img src="{{ $img($s->logo) }}" alt="{{ $s->name }}">@else{{ $s->name }}@endif</a>@endforeach
        </div>
    </div></section>
    @endif

    {{-- MODAL: Board bio --}}
    <div class="modal" id="board-modal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal__backdrop" data-close></div>
        <div class="modal__box">
            <button class="modal__close" data-close aria-label="{{ __('site.close') }}">{{ __('site.close') }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            <div class="pmodal__grid">
                <div class="pmodal__img" id="bm-img" style="aspect-ratio:3/4"></div>
                <div><h3 id="bm-name"></h3><p class="bm-role" id="bm-role"></p><p id="bm-bio"></p></div>
            </div>
        </div>
    </div>

    {{-- MODAL: Facilities + accreditation --}}
    <div class="modal" id="facilities-modal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal__backdrop" data-close></div>
        <div class="modal__box modal__box--wide">
            <button class="modal__close" data-close aria-label="{{ __('site.close') }}">{{ __('site.close') }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            <h3 style="text-align:center">{{ $en ? 'Internationally Standardized Production Facilities' : 'Fasilitas Produksi Berstandar Internasional' }}</h3>
            <p style="text-align:center;color:var(--text-muted)">{{ $en ? 'Our facilities serve local & global markets including contract manufacturing.' : 'Fasilitas kami melayani pasar lokal dan global termasuk contract manufacturing.' }}</p>
            @if($facilities->isNotEmpty())
            <div class="fac-grid">
                @foreach($facilities as $f)
                <div class="fac-card">
                    <div class="fac-card__img" style="{{ $img($f->image) ? "background-image:url('".$img($f->image)."')" : '' }}"></div>
                    <h4>{{ $f->name }}@if($f->region)<small>, {{ $f->region }}</small>@endif</h4>
                    <p class="plant">{{ $f->plants }}@if($f->area) <small>&mdash; {{ $f->area }}</small>@endif</p>
                    <p class="dose">{{ $f->tr('detail') }}</p>
                </div>
                @endforeach
            </div>
            @endif
            @if($accreditations->isNotEmpty())
            <h4 class="accr-title">{{ $en ? 'Various Accreditations' : 'Berbagai Akreditasi' }}</h4>
            <div class="accr-grid">
                @foreach($accreditations as $ac)<div><b>{{ $ac->name }}</b>@if($ac->issuer)<span>{{ $ac->issuer }}</span>@endif</div>@endforeach
            </div>
            @endif
        </div>
    </div>

</x-layout>