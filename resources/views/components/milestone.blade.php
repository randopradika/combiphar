@props(['items'])
@php use Illuminate\Support\Facades\Storage; $img = fn ($p) => $p ? Storage::url($p) : null; @endphp
@if($items->isNotEmpty())
<div class="milestone rv" data-milestone data-autoplay="4000">
    <div class="container">
        <div class="milestone__track" data-track>
            @foreach($items as $m)
            <figure class="milestone__slide" data-year="{{ $m->year }}" data-caption="{{ e($m->tr('caption')) }}">
                <div class="milestone__img" style="{{ $img($m->photo) ? "background-image:url('".$img($m->photo)."')" : '' }}"></div>
            </figure>
            @endforeach
        </div>
        <div class="milestone__bar"><span class="milestone__bar-fill" data-bar></span></div>
        <div class="milestone__foot">
            <div class="milestone__year display" data-year-out>{{ $items->first()?->year }}</div>
            <div class="milestone__foot-right">
                <p class="milestone__caption" data-caption-out>{{ $items->first()?->tr('caption') }}</p>
                <div class="milestone__nav">
                    <button class="arrow-btn arrow-btn--sm" data-prev aria-label="Sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg></button>
                    <button class="arrow-btn arrow-btn--sm" data-next aria-label="Berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif