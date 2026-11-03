@extends('layouts.app')

@php
    $first = $documents[0];
    $whatsapp = config('site.whatsapp');
@endphp

@section('title', $pageTitle)
@section('description', $banner->subheading())

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        \App\Support\Seo::breadcrumbs([$pageTitle => request()->url()]),
    ]) !!}
@endpush

@section('content')
<section class="page-banner page-banner--center" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
    <x-banner-video :src="$banner->videoUrl()" :poster="$banner->imageUrl()" />
    <div class="page-banner__inner">
        <p class="page-banner__eyebrow">{{ config('site.short_name') }} · Legal</p>
        <h1 class="page-banner__title">{{ $banner->heading() }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
        <a href="#{{ $first['id'] }}" class="legal-banner__cta">{{ $cta }}</a>
    </div>
</section>

<div class="legal-layout">
    <aside class="legal-aside">
        <nav class="legal-card legal-toc" aria-label="On this page">
            <p class="legal-label">On this page</p>
            <ul>
                @foreach ($documents as $doc)
                    @if (count($documents) > 1)
                        <li><a href="#{{ $doc['id'] }}" class="legal-toc__doc">{{ $doc['title'] }}</a></li>
                    @endif
                    @foreach ($doc['toc'] as $item)
                        <li><a href="#{{ $item['id'] }}" @class(['legal-toc__sub' => count($documents) > 1])>{{ $item['title'] }}</a></li>
                    @endforeach
                @endforeach
            </ul>
            <p class="legal-toc__updated">Last updated: <strong>{{ collect($documents)->max('updated_at')->format('F j, Y') }}</strong></p>
        </nav>

        <div class="legal-card legal-help">
            <p class="legal-label">Need help?</p>
            <p>We're happy to answer questions.</p>
            <a href="mailto:{{ config('site.email') }}" class="legal-help__link notranslate">{{ config('site.email') }}</a>
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="legal-help__link notranslate">WhatsApp {{ config('site.whatsapp_display') }}</a>
        </div>
    </aside>

    <div class="legal-main">
        @foreach ($documents as $doc)
            <article class="legal-card legal-doc" id="{{ $doc['id'] }}">
                <p class="legal-label">{{ $doc['eyebrow'] }}</p>
                <h2 class="legal-doc__title">{{ $doc['title'] }}</h2>
                <p class="legal-doc__updated">Last updated: {{ $doc['updated_at']->format('F j, Y') }}</p>

                {{-- Markdown from the admin, with raw HTML stripped (LegalDocument::toHtml). --}}
                <div class="legal-text">{!! $doc['html'] !!}</div>

                <dl class="legal-contact">
                    <div>
                        <dt>Address</dt>
                        <dd>{{ config('site.location') }}</dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><a href="mailto:{{ config('site.email') }}" class="notranslate">{{ config('site.email') }}</a></dd>
                    </div>
                    <div>
                        <dt>Phone / WhatsApp</dt>
                        <dd><a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="notranslate">{{ config('site.whatsapp_display') }}</a></dd>
                    </div>
                </dl>

                @if ($doc['key'] !== 'privacy')
                    <p class="legal-note">
                        Friendly note: wildlife, weather and park authority decisions can affect timing. We plan carefully,
                        communicate clearly and always put your safety first.
                    </p>
                @endif
            </article>
        @endforeach
    </div>
</div>
@endsection
