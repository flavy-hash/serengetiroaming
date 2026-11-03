@extends('layouts.app')

@section('title', 'Tanzania Travel FAQ')
@section('description', 'Answers to the questions travellers ask us about Tanzania safaris, Kilimanjaro climbs and Zanzibar holidays.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        \App\Support\Seo::faqPage($faqs),
        \App\Support\Seo::breadcrumbs(['FAQ' => route('faq')]),
    ]) !!}
@endpush

@section('content')
@php($banner = \App\Models\PageBanner::for('faq'))
<section class="page-banner" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
    <x-banner-video :src="$banner->videoUrl()" :poster="$banner->imageUrl()" />
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <span aria-current="page">FAQ</span>
        </nav>

        <h1 class="page-banner__title">{{ $banner->heading() }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    @if ($faqs->isEmpty())
        <p class="text-center text-sm text-charcoal-600">No questions answered here yet — be the first to ask.</p>
    @else
        <div class="divide-y divide-forest-100 overflow-hidden rounded-2xl border border-forest-100 bg-white">
            @foreach ($faqs as $faq)
                <details class="group" id="faq-{{ $faq->id }}">
                    <summary class="flex cursor-pointer list-none items-start justify-between gap-4 px-6 py-5 marker:content-none">
                        <span>
                            <span class="block font-display text-lg font-semibold text-forest-900">{{ $faq->question }}</span>
                            @if ($faq->tourPage?->is_published)
                                <span class="mt-1 inline-block rounded-full bg-cream-100 px-3 py-0.5 text-xs font-medium text-charcoal-700">{{ $faq->tourPage->package_name }}</span>
                            @endif
                        </span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-1 shrink-0 text-charcoal-500 transition duration-200 group-open:rotate-180" aria-hidden="true"><path d="m18 15-6-6-6 6" /></svg>
                    </summary>
                    <div class="space-y-3 px-6 pb-6 text-sm leading-relaxed text-charcoal-600">
                        @foreach (preg_split('/\R{2,}/', trim($faq->answer)) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                        @if ($faq->tourPage?->is_published)
                            <a href="{{ $faq->tourPage->url() }}" class="inline-block font-semibold text-gold-600 hover:text-gold-500">View {{ $faq->tourPage->package_name }} →</a>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
    @endif

    <div class="mt-12 rounded-2xl bg-forest-900 px-6 py-10 text-center text-cream-50">
        <h2 class="font-display text-2xl font-semibold">Can't find your answer?</h2>
        <p class="mx-auto mt-2 max-w-md text-sm text-cream-100/80">Ask us anything about your trip — a real person replies within 24 hours.</p>
        <button type="button" data-dialog-open="question" class="mt-6 inline-flex items-center gap-2 rounded-full bg-gold-500 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-forest-900 transition hover:bg-gold-400">
            Ask a Question
        </button>
    </div>
</section>

<x-question-dialog />
@endsection
