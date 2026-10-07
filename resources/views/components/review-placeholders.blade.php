{{-- No real reviews yet — these cards preview the layout real reviews will use,
     without inventing fake reviewers, ratings or photos to fill them. "Read Review"
     opens the full text in a popup so long reviews don't have to be truncated on the card. --}}
<div class="mx-auto mt-10 grid max-w-3xl gap-6 text-left sm:grid-cols-2">
    @foreach ([
        ['gradients' => ['from-forest-700 to-forest-950', 'from-gold-500 to-forest-700'], 'name' => 'Your Name', 'date' => 'Trip date'],
        ['gradients' => ['from-forest-600 to-forest-900', 'from-gold-400 to-forest-800'], 'name' => 'Your Name', 'date' => 'Trip date'],
    ] as $preview)
        <div class="overflow-hidden rounded-2xl border border-dashed border-forest-100 bg-white p-5 shadow-sm" data-review-card>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($preview['gradients'] as $gradient)
                    <div class="flex h-28 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="text-cream-100/50" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path d="m21 15-5-5L5 21" /></svg>
                    </div>
                @endforeach
            </div>
    
            <div class="mt-4 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-cream-100 text-charcoal-500">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="3.5" /><path d="M4.5 20c1.3-3.5 4-5.5 7.5-5.5s6.2 2 7.5 5.5" /></svg>
                </span>
                <div>
                    <div class="flex gap-0.5 text-gold-500">
                        @for ($i = 0; $i < 5; $i++)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z" /></svg>
                        @endfor
                    </div>
                    <p class="text-sm text-charcoal-900">
                        <span class="font-semibold">{{ $preview['name'] }}</span>
                        <span class="text-charcoal-500">/ {{ $preview['date'] }}</span>
                    </p>
                </div>
            </div>
    
            <h3 class="mt-4 font-display text-lg font-semibold text-forest-900">Your review could be here</h3>
            <p class="mt-2 text-sm leading-relaxed text-charcoal-600">
                We're just getting started — no reviews yet. Share how your Tanzania trip went and we'll feature it in this space.
            </p>
    
            <div class="mt-5 flex items-center justify-between border-t border-forest-100 pt-4">
                <button type="button" data-read-review class="text-sm font-semibold text-charcoal-900 hover:text-gold-600">
                    Read Review
                </button>
                <span class="text-xs font-medium">
                    <span class="text-charcoal-500">No reviews</span> <span class="text-gold-600">yet</span>
                </span>
            </div>
    
            <template>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($preview['gradients'] as $gradient)
                        <div class="flex h-40 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }}">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="text-cream-100/50" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" /><circle cx="8.5" cy="8.5" r="1.5" /><path d="m21 15-5-5L5 21" /></svg>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-cream-100 text-charcoal-500">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="3.5" /><path d="M4.5 20c1.3-3.5 4-5.5 7.5-5.5s6.2 2 7.5 5.5" /></svg>
                    </span>
                    <div>
                        <div class="flex gap-0.5 text-gold-500">
                            @for ($i = 0; $i < 5; $i++)
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z" /></svg>
                            @endfor
                        </div>
                        <p class="text-sm text-charcoal-900">
                            <span class="font-semibold">{{ $preview['name'] }}</span>
                            <span class="text-charcoal-500">/ {{ $preview['date'] }}</span>
                        </p>
                    </div>
                </div>
                <h3 class="mt-4 font-display text-xl font-semibold text-forest-900">Your review could be here</h3>
                <p class="mt-3 text-sm leading-relaxed text-charcoal-600">
                    We're just getting started — no reviews yet. Once a guest shares their trip, the full review will
                    open here instead of being cut off on the card, however long it runs.
                </p>
            </template>
        </div>
    @endforeach
</div>
