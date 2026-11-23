@extends('layouts.app')

@php
    $img = fn (?string $path) => \App\Models\TourPage::imageUrl($path);

    $title = $page->team_title ?: 'Meet Our Experts’ Team';
    $heading = $page->team_heading ?: 'Meet Our Professional Team';
    $bannerPhoto = $img($page->team_banner_image) ?: $img($page->banner_image) ?: asset('images/banners/about.jpg');
    $paragraphs = fn (?string $text) => \App\Models\AboutPage::paragraphs($text);
@endphp

@section('title', $title)
{{-- Never pass null here: @section with a null value opens a block section. --}}
@section('description', \Illuminate\Support\Str::limit($paragraphs($page->team_intro)[0] ?? 'Meet the guides and travel planners behind your safari in Tanzania.', 155))

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        \App\Support\Seo::breadcrumbs(['About' => route('about'), 'Our Team' => route('team')]),
    ]) !!}
@endpush

@section('content')
<section class="page-banner" style="background-image: url('{{ $bannerPhoto }}')">
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ route('about') }}">About</a>
            <span>/</span>
            <span aria-current="page">Our Team</span>
        </nav>

        <h1 class="page-banner__title">{{ $title }}</h1>
    </div>
</section>

<section class="about-page about-page--team">
    <h2 class="about-heading">{{ $heading }}</h2>
    @if (filled($page->team_intro))
        <div class="about-prose">
            @foreach ($paragraphs($page->team_intro) as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    @endif

    @foreach ($page->teamGroups() as $group)
        @if (filled($group['title'] ?? null))
            <h2 class="about-heading">{{ $group['title'] }}</h2>
        @endif
        @if (filled($group['description'] ?? null))
            <div class="about-prose">
                @foreach ($paragraphs($group['description']) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        @endif

        <div class="team-members">
            @foreach ($group['members'] as $member)
                @php($photo = $img(is_array($member['photo'] ?? null) ? (reset($member['photo']) ?: null) : ($member['photo'] ?? null)))
                <article class="team-member">
                    @if ($photo)
                        <img class="team-member__photo" src="{{ $photo }}" alt="{{ $member['name'] }}" loading="lazy" decoding="async">
                    @else
                        <span class="team-member__photo team-member__photo--blank" aria-hidden="true">{{ \Illuminate\Support\Str::of($member['name'])->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}</span>
                    @endif
                    <h3 class="team-member__name">{{ $member['name'] }}</h3>
                    @if (filled($member['role'] ?? null))
                        <p class="team-member__role">{{ $member['role'] }}</p>
                    @endif
                    @if (filled($member['bio'] ?? null))
                        <p class="team-member__bio">{{ $member['bio'] }}</p>
                    @endif
                    @if (filled($member['link'] ?? null))
                        <a class="team-member__link" href="{{ $member['link'] }}" target="_blank" rel="noopener">{{ str_contains($member['link'], 'linkedin') ? 'LinkedIn' : 'Profile' }}</a>
                    @endif
                </article>
            @endforeach
        </div>
    @endforeach

    <div class="about-actions">
        <a class="btn btn--primary" href="{{ url('/contact') }}">Plan my trip</a>
    </div>
</section>
@endsection
