@extends('layouts.app')

@section('title', 'Contact Us')
@section('description', 'Talk to our safari experts — we reply within 24 hours.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        ['@type' => 'ContactPage', 'url' => route('contact'), 'about' => ['@id' => url('/').'#organization']],
        \App\Support\Seo::breadcrumbs(['Contact' => route('contact')]),
    ]) !!}
@endpush

@php
    $whatsapp = config('site.whatsapp');
    $email = config('site.email');
@endphp

@section('content')
@php($banner = \App\Models\PageBanner::for('contact'))
<section class="page-banner" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
    <x-banner-video :src="$banner->videoUrl()" :poster="$banner->imageUrl()" />
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <span aria-current="page">Contact</span>
        </nav>

        <h1 class="page-banner__title">{{ $banner->heading() }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
    </div>
</section>

<section class="py-20">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 lg:grid-cols-[1fr_22.5rem] lg:items-start lg:px-8">
        <div class="rounded-2xl border border-forest-100 bg-white p-6 lg:p-8">
            <h2 class="font-display text-2xl font-semibold text-forest-900">Send us a message</h2>
            <p class="mt-1 text-sm text-charcoal-600">Planning a trip, or just have a question? Tell us a little and we'll get back to you.</p>

            <form class="booking-form mt-6" id="contact-form" action="{{ route('contact.store') }}" method="POST" data-ajax-form>
                @csrf
                <div class="booking-form__feedback" data-form-feedback hidden></div>

                {{-- Honeypot: hidden from people, bots fill it in and get rejected. --}}
                <div class="hidden" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="booking-form__row">
                    <label class="booking-form__field">
                        <span>Your Name *</span>
                        <input type="text" name="name" required maxlength="255" autocomplete="name">
                    </label>
                    <label class="booking-form__field">
                        <span>Email *</span>
                        <input type="email" name="email" required maxlength="255" autocomplete="email">
                    </label>
                </div>

                <div class="booking-form__row">
                    <label class="booking-form__field">
                        <span>Phone or WhatsApp</span>
                        <input type="tel" name="phone" maxlength="50" autocomplete="tel">
                    </label>
                    <label class="booking-form__field">
                        <span>Subject</span>
                        {{-- Prefilled from links such as "Enquire" on /accommodations (?subject=…); escaped and length-limited. --}}
                        <input type="text" name="subject" maxlength="255" placeholder="e.g. Honeymoon in August" value="{{ \Illuminate\Support\Str::limit(is_string(request()->query('subject')) ? request()->query('subject') : '', 255, '') }}">
                    </label>
                </div>

                <label class="booking-form__field">
                    <span>Message *</span>
                    <textarea name="message" rows="6" required minlength="5" maxlength="5000"></textarea>
                </label>

                <button type="submit" class="booking-form__submit">
                    <span data-submit-label>Send Message</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                </button>
            </form>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl bg-forest-900 p-7 text-cream-50">
                <h2 class="font-display text-xl font-semibold">Get in touch</h2>
                <ul class="mt-5 space-y-4 text-sm">
                    <li class="flex items-start gap-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-gold-300" aria-hidden="true"><path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.5-4.3a8.5 8.5 0 1 1 15.5-4.6z" /></svg>
                        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="hover:text-gold-300">WhatsApp {{ config('site.whatsapp_display') }}<span class="block text-xs text-cream-100/60">Fastest reply</span></a>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-gold-300" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m4 7 8 6 8-6" /></svg>
                        <a href="mailto:{{ $email }}" class="break-all hover:text-gold-300">{{ $email }}</a>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-gold-300" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
                        {{ config('site.location') }}
                    </li>
                </ul>
                <p class="mt-6 border-t border-white/15 pt-5 text-xs text-cream-100/70">We reply within 24 hours.</p>
                @if (collect(config('site.social'))->filter()->isNotEmpty())
                    <p class="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">Follow us</p>
                    <x-social-links class="footer__socials !mt-3" />
                @endif
            </div>

            <div class="rounded-2xl border border-forest-100 bg-white p-6">
                <h2 class="font-display text-lg font-semibold text-forest-900">Quick question?</h2>
                @if ($faqs->isNotEmpty())
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($faqs as $faq)
                            <li><a href="{{ route('faq') }}#faq-{{ $faq->id }}" class="text-charcoal-700 hover:text-gold-600">{{ $faq->question }}</a></li>
                        @endforeach
                    </ul>
                @endif
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('faq') }}" class="text-sm font-semibold text-gold-600 hover:text-gold-500">Read the FAQ →</a>
                    <button type="button" data-dialog-open="question" class="text-sm font-semibold text-forest-800 hover:text-forest-600">Ask a question</button>
                </div>
            </div>
        </aside>
    </div>
</section>

<x-question-dialog />
@endsection
