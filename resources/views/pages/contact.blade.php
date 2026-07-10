@php
    use Illuminate\Support\Facades\Storage;
    $en = app()->getLocale() === 'en';
@endphp
<x-layout :title="__('site.nav.contact') . ' — Combiphar'">

    <section class="banner banner--about" @if($page?->banner_image) style="background-image:linear-gradient(150deg,rgba(94,127,184,.5),rgba(58,24,96,.6)),url('{{ Storage::url($page->banner_image) }}');background-size:cover;background-position:center" @endif>
        <div class="container">
            <span class="banner__crumb"><a href="{{ route('home') }}">Home</a> &rsaquo; {{ __('site.nav.contact') }}</span>
            <h1 class="display">{{ __('site.nav.contact') }}</h1>
        </div>
    </section>

    <nav class="subnav" data-tabs aria-label="Karir & Kontak">
        <div class="container subnav__inner">
            <button type="button" data-tab="karir" class="{{ session('contact_success') ? '' : 'on' }}">{{ $en ? 'Careers' : 'Karir' }}</button>
            <button type="button" data-tab="kontak" class="{{ session('contact_success') ? 'on' : '' }}">{{ $en ? 'Contact' : 'Kontak' }}</button>
        </div>
    </nav>

    {{-- KARIR --}}
    <section class="section" data-panel="karir" @if(session('contact_success')) hidden @endif>
        <div class="container">
            <div class="sec-head rv"><span class="eyebrow eyebrow--magenta">{{ $en ? 'Join Us' : 'Bergabung Bersama Kami' }}</span><h2 class="display">Available Vacancies</h2><p>{{ $en ? 'Join Combiphar in building a healthier future for Indonesia.' : 'Bergabunglah bersama Combiphar membangun masa depan yang lebih sehat untuk Indonesia.' }}</p></div>
            <div class="grid" style="gap:14px;margin-top:36px">
                @forelse($vacancies as $v)
                <div class="vac-row rv" data-vacancy data-title="{{ e($v->tr('title')) }}" data-meta="{{ e(trim(($v->location ? $v->location : '') . ($v->tr('department') ? '  ·  ' . $v->tr('department') : ''), ' ·')) }}" data-desc="{{ e($v->tr('description')) }}">
                    <h3>{{ $v->tr('title') }}</h3>
                    @if($v->location)<span class="loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-5.2-7-11a7 7 0 0 1 14 0c0 5.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>{{ $v->location }}</span>@endif
                    @if($v->tr('department'))<span class="loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>{{ $v->tr('department') }}</span>@endif
                    <button type="button" class="btn btn--purple">{{ $en ? 'View Detail' : 'Lihat Detail' }}</button>
                </div>
                @empty
                <p class="lead">{{ $en ? 'No open positions right now.' : 'Belum ada lowongan saat ini.' }}</p>
                @endforelse
            </div>

            <div class="sec-head sec-head--center rv" style="margin-top:clamp(48px,5vw,80px)"><span class="eyebrow eyebrow--magenta">{{ $en ? 'Selection Process' : 'Proses Seleksi' }}</span><h2 class="display">Recruitment Process</h2></div>
            <div class="steps rv">
                @foreach(['Application Review','HR Interview','User Interview','Assessment','Offering','Onboarding'] as $i => $s)
                @if($i > 0)<span class="sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>@endif
                <div class="step"><span class="n">{{ $i + 1 }}</span><p>{{ $s }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- KONTAK --}}
    <section class="section" data-panel="kontak" @unless(session('contact_success')) hidden @endunless>
        <div class="container">
            <div class="sec-head rv"><span class="eyebrow eyebrow--magenta">{{ $en ? 'Contact Us' : 'Hubungi Kami' }}</span><h2 class="display">Have a Question?</h2><p>{{ $en ? 'Reach us directly by filling in the form below. Our team will respond as soon as possible.' : 'Hubungi kami dengan mengisi formulir di bawah. Tim kami akan merespons pertanyaan Anda sesegera mungkin.' }}</p></div>
            <div class="grid grid--2" style="align-items:start;margin-top:36px">
                <div class="grid" style="gap:18px">
                    <div class="office-card rv" style="background:var(--grad-card-light)"><h4>Combiphar Head Office</h4><p>Jl. Jenderal Sudirman Kav. 52-53, Senayan, Kebayoran Baru, Jakarta Pusat, DKI Jakarta 12190.</p><p style="margin-top:6px;font-weight:600;color:var(--text)">Phone: 0800-1-800088 (Toll Free)</p></div>
                    <div class="office-card rv"><h4>Combi Care Center</h4><p>{{ $en ? 'Consumer information service for product and health questions.' : 'Layanan informasi konsumen untuk pertanyaan seputar produk dan kesehatan.' }}</p><p style="margin-top:6px;font-weight:600;color:var(--text)">care@combiphar.com</p></div>
                </div>
                <form class="form-wrap rv" method="POST" action="{{ route('contact.submit') }}">
                    @csrf
                    @if(session('contact_success'))<div class="form-success">{{ $en ? 'Thank you! Your message has been sent.' : 'Terima kasih! Pesan Anda telah terkirim.' }}</div>@endif
                    <div class="form">
                        <div class="field full"><label for="nm">{{ $en ? 'Your Name' : 'Nama Anda' }} *</label><input id="nm" name="name" value="{{ old('name') }}" placeholder="Type your name" required>@error('name')<small>{{ $message }}</small>@enderror</div>
                        <div class="field"><label for="em">Email *</label><input id="em" type="email" name="email" value="{{ old('email') }}" placeholder="Type your email" required>@error('email')<small>{{ $message }}</small>@enderror</div>
                        <div class="field"><label for="ph">{{ $en ? 'Phone' : 'Telepon' }}</label><input id="ph" name="phone" value="{{ old('phone') }}" placeholder="Type your phone"></div>
                        <div class="field full"><label for="sb">{{ $en ? 'Subject' : 'Subjek' }}</label><input id="sb" name="subject" value="{{ old('subject') }}" placeholder="Type your subject"></div>
                        <div class="field full"><label for="msg">{{ $en ? 'Message' : 'Pesan' }} *</label><textarea id="msg" name="message" placeholder="Enter your message here ..." required>{{ old('message') }}</textarea>@error('message')<small>{{ $message }}</small>@enderror</div>
                        <div class="full"><button class="btn btn--fill" type="submit">{{ $en ? 'Send Message' : 'Kirim Pesan' }}</button></div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="warning">
        <div class="container warning__inner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
            <div>
                <h3>{{ $en ? 'Beware of Recruitment Fraud' : 'Waspada Penipuan Rekrutmen' }}</h3>
                <p>{{ $en ? 'Combiphar never charges any fees during recruitment and never partners with travel agents for accommodation. All official recruitment is conducted only through Combiphar official channels. Please ignore and report any payment requests made in the company name.' : 'Combiphar tidak pernah memungut biaya apa pun selama proses rekrutmen dan tidak pernah bekerja sama dengan agen perjalanan untuk akomodasi. Seluruh proses rekrutmen resmi hanya dilakukan melalui kanal resmi Combiphar. Abaikan dan laporkan setiap permintaan pembayaran yang mengatasnamakan perusahaan.' }}</p>
            </div>
        </div>
    </section>

    <div class="modal" id="vac-modal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal__backdrop" data-close></div>
        <div class="modal__box vac-modal">
            <button class="modal__close" data-close aria-label="{{ __('site.close') }}">{{ __('site.close') }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            <h3 id="vm-title"></h3>
            <p id="vm-meta" style="font-weight:600;color:var(--magenta);margin-bottom:14px"></p>
            <div id="vm-desc" style="white-space:pre-line;color:var(--text-muted);line-height:1.7"></div>
        </div>
    </div>

</x-layout>