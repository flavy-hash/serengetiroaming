@props(['rating' => 5, 'size' => 14])

<div {{ $attributes->merge(['class' => 'flex gap-0.5']) }} role="img" aria-label="{{ $rating }} out of 5 stars">
    @for ($i = 1; $i <= 5; $i++)
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="currentColor" class="{{ $i <= $rating ? 'text-gold-500' : 'text-forest-100' }}" aria-hidden="true"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z" /></svg>
    @endfor
</div>
