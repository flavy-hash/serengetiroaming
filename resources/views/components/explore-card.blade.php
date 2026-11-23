@props(['area'])

{{-- Large photo card for /explore-zanzibar. On a computer, hovering (or tabbing to) the card slides a light
     panel over the photo that lists the activities with an "Explore" button. Phones and tablets have no
     hover, so they keep the photo with the name and a small "Explore" link. The whole card is one link. --}}
@php
    $activities = filled($area->activities) ? $area->activities : \Illuminate\Support\Str::limit($area->paragraphs()[0] ?? (string) $area->tagline, 170);
@endphp

<article class="zan-card">
    @if ($src = $area->imageUrl())
        <img class="zan-card__img" src="{{ $src }}" alt="" loading="lazy" decoding="async">
    @endif
    <div class="zan-card__shade" aria-hidden="true"></div>

    <div class="zan-card__front">
        <h3 class="zan-card__name"><a class="zan-card__link" href="{{ $area->url() }}">{{ $area->name }}</a></h3>
        @if (filled($area->tagline))
            <p class="zan-card__tag">{{ $area->tagline }}</p>
        @endif
        <span class="zan-card__more" aria-hidden="true">Explore <span>→</span></span>
    </div>

    <div class="zan-card__back">
        <p class="zan-card__back-title">Activities:</p>
        <p class="zan-card__back-text">{{ $activities }}</p>
        <span class="zan-card__btn" aria-hidden="true">Explore</span>
    </div>
</article>
