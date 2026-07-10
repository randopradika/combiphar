@php
    use Illuminate\Support\Facades\Storage;
    $img = fn ($p) => $p ? Storage::url($p) : null;
    $en = app()->getLocale() === 'en';
    $arrow = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
    $docRow = function ($d, $img, $fallback) {
        return $d;
    };
@endphp
<x-layout :title="($page?->tr('meta_title') ?: 'Investor Relations — Combiphar')">

    <section class="banner banner--about" @if($img($page?->banner_image)) style="background-image:linear-gradient(120deg,rgba(107,90,142,.5),rgba(58,24,96,.6)),url('{{ $img($page?->banner_image) }}');background-size:cover;background-position:center" @endif>
        <div class="container">
            <span class="banner__crumb"><a href="{{ route('home') }}">Home</a> &rsaquo; {{ __('site.nav.investor') }}</span>
            <h1 class="display">Investor Relations</h1>
        </div>
    </section>

    <nav class="subnav" aria-label="Investor submenu">
        <div class="container subnav__inner">
            <a href="#overview" class="on">Overview</a>
            <a href="#stock">Stock Information</a>
            <a href="#annual">Annual Reports</a>
            <a href="#sustainability">Sustainability</a>
            <a href="#disclosures">Disclosures</a>
            <a href="#ir-contact">IR Contact</a>
        </div>
    </nav>

    <section class="section" id="overview">
        <div class="container">
            <div class="sec-head sec-head--center rv"><span class="eyebrow eyebrow--magenta">Investor Relations</span><h2 class="display">{{ $en ? 'Investor Information Center' : 'Pusat Informasi Investor' }}</h2><p>{{ $en ? 'Transparent access to Combiphar financial information, stock performance, and disclosures to support informed investment decisions.' : 'Akses informasi keuangan, kinerja saham, dan keterbukaan informasi Combiphar secara transparan untuk mendukung keputusan investasi yang terinformasi.' }}</p></div>
            <div class="grid grid--4 rv" style="margin-top:44px">
                @foreach(['#stock'=>'Stock Information','#annual'=>'Annual Reports','#sustainability'=>'Sustainability Report','#disclosures'=>'Information Disclosures'] as $href => $label)
                <a class="hub-card" href="{{ $href }}"><h3>{{ $label }}</h3><span class="arrow-btn arrow-btn--dark">{!! $arrow !!}</span></a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" id="stock" style="padding-top:0">
        <div class="container">
            <div class="doc-head rv"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5m0 14h17M8 16v-5m5 5V8m5 8v-3"/></svg><h2>Stock Information</h2></div>
            <article class="stock rv">
                <p class="stock__meta"><b>Bursa Efek Indonesia</b></p>
                <div class="ir-price"><span class="ticker">COMB</span><span class="exchange">IDX &middot; IDR</span><span class="change">2.290 &#9662; -10 (-0,44%)</span></div>
                <p class="stock__meta">Quotes Delayed: 10 Minutes &nbsp;&middot;&nbsp; Last Transaction: 6 May 2026, 16:14</p>
                <div class="stock__grid">
                    <div class="stock__cell"><div class="k">Volume</div><div class="v">574.559.700</div></div>
                    <div class="stock__cell"><div class="k">Day's Range</div><div class="v">2.240 &ndash; 2.430</div></div>
                    <div class="stock__cell"><div class="k">52 Weeks' Range</div><div class="v">765 &ndash; 4.530</div></div>
                    <div class="stock__cell"><div class="k">% Change</div><div class="v down">-0,44%</div></div>
                </div>
                <div class="stock__chart">
                    <svg viewBox="0 0 900 260" preserveAspectRatio="none"><defs><linearGradient id="area" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#D62E87" stop-opacity=".35"/><stop offset="1" stop-color="#D62E87" stop-opacity="0"/></linearGradient></defs><path d="M0 195 C85 180 95 130 180 142 S320 212 420 160 S560 70 650 100 S790 170 900 88 L900 260 L0 260 Z" fill="url(#area)"/><path d="M0 195 C85 180 95 130 180 142 S320 212 420 160 S560 70 650 100 S790 170 900 88" fill="none" stroke="#D62E87" stroke-width="5" stroke-linecap="round"/></svg>
                </div>
                <p class="stock__note">{{ $en ? 'Stock information is generated from official Bursa Efek Indonesia (BEI) data, which Combiphar is legally permitted to display.' : 'Informasi saham bersumber dari data resmi Bursa Efek Indonesia (BEI) yang secara sah dapat ditampilkan oleh Combiphar.' }}</p>
            </article>
        </div>
    </section>

    @foreach(['annual'=>['Annual Reports',$annual],'sustainability'=>['Sustainability Report',$sustainability],'financial'=>['Financial Information',$financial]] as $anchor => $group)
    @if($group[1]->isNotEmpty())
    <section class="section" id="{{ $anchor }}" style="padding-top:0">
        <div class="container">
            <div class="doc-head rv"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5M9 13h7M9 17h7"/></svg><h2>{{ $group[0] }}</h2></div>
            <div class="doc-list rv">
                @foreach($group[1] as $d)
                <div class="doc-row">
                    <h3>{{ $d->tr('title') ?: (($d->year ? $d->year.' ' : '').$group[0]) }}</h3>
                    @if($img($d->file_en))<a class="doc-act" href="{{ $img($d->file_en) }}" target="_blank" rel="noopener">View EN</a><a class="doc-act" href="{{ $img($d->file_en) }}" download>Download EN</a>@endif
                    @if($img($d->file_id))<a class="doc-act" href="{{ $img($d->file_id) }}" target="_blank" rel="noopener">View ID</a><a class="doc-act" href="{{ $img($d->file_id) }}" download>Download ID</a>@endif
                    @if(!$img($d->file_en) && !$img($d->file_id))<span class="doc-act" style="opacity:.5">{{ $en ? 'File coming soon' : 'Berkas segera hadir' }}</span>@endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endforeach

    @if($disclosures->isNotEmpty())
    <section class="section" id="disclosures">
        <div class="container">
            <div class="doc-head rv"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M4 8h16M6 13h12M8 18h8"/></svg><h2>Information Disclosures</h2></div>
            <div class="grid grid--3 rv">
                @foreach($disclosures as $d)
                <article class="disc-card"><div class="date">{{ $d->year ?: '' }}</div><div><h3>{{ $d->tr('title') }}</h3>@if($img($d->file_id) || $img($d->file_en))<a class="link-more" href="{{ $img($d->file_id) ?: $img($d->file_en) }}" target="_blank" rel="noopener">Read More</a>@endif</div></article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="section" id="ir-contact" style="background:var(--surface)">
        <div class="container">
            <div class="doc-head rv"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/></svg><h2>Investor Relations Contact</h2></div>
            <div class="ir-contact-grid rv">
                <div class="ir-address"><h3>{{ $en ? 'Contact Us' : 'Hubungi Kami' }}</h3><p><b>Combiphar Head Office</b></p><p>Jl. Jenderal Sudirman Kav. 52-53, Senayan, Kebayoran Baru, Jakarta Pusat, DKI Jakarta 12190.</p><p><b>Phone:</b> 0800-1-800088 (Toll Free)</p></div>
                <div class="ir-people"><h3>IR Contact</h3><div class="ir-person"><div class="ir-avatar">IR</div><div><b>Investor Relations</b><br><a href="mailto:investor.relations@combiphar.com">investor.relations@combiphar.com</a></div></div></div>
            </div>
        </div>
    </section>

</x-layout>