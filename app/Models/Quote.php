<?php

namespace App\Models;

use App\Enums\QuoteSource;
use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'source',
        'status',
        'package_id',
        'booking_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_country',
        'adults',
        'children',
        'travel_start',
        'travel_end',
        'items',
        'discount',
        'notes',
        'show_bank_details',
        'valid_until',
    ];

    protected $attributes = [
        'source' => 'admin',
        'status' => 'draft',
        'adults' => 1,
        'children' => 0,
        'discount' => 0,
        'show_bank_details' => true,
    ];

    protected $casts = [
        'source' => QuoteSource::class,
        'status' => QuoteStatus::class,
        'items' => 'array',
        'discount' => 'decimal:2',
        'show_bank_details' => 'boolean',
        'travel_start' => 'date',
        'travel_end' => 'date',
        'valid_until' => 'date',
        'adults' => 'integer',
        'children' => 'integer',
    ];

    protected static function booted(): void
    {
        // The reference comes from the id, so it can only be set once the row exists.
        static::created(function (Quote $quote) {
            $quote->reference = self::referenceFor($quote);
            $quote->saveQuietly();
        });
    }

    public static function referenceFor(Quote $quote): string
    {
        return sprintf('QT-%s-%06d', ($quote->created_at ?? now())->format('Y'), $quote->getKey());
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPage::class, 'package_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Sum of quantity × unit price for every line (not stored).
     */
    protected function subtotal(): Attribute
    {
        return Attribute::get(fn () => self::subtotalOf($this->items));
    }

    /**
     * Subtotal less the discount, never below zero (not stored).
     */
    protected function total(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->subtotal - (float) $this->discount));
    }

    /**
     * @param  array<int|string, mixed>|null  $items
     */
    public static function subtotalOf(?array $items): float
    {
        return round(collect($items ?? [])
            ->filter(fn ($item) => is_array($item))
            ->sum(fn (array $item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)), 2);
    }

    public static function money(float|int|string|null $amount): string
    {
        $amount = (float) $amount;

        return 'US$ '.number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2);
    }

    public function pdfFilename(): string
    {
        // The reference is generated (QT-2026-000042), so it is already filename-safe.
        return 'Serengeti-Roaming-Quote-'.($this->reference ?: 'draft').'.pdf';
    }

    /**
     * Number of days the trip lasts (dates first, else the package length).
     */
    public function durationDays(): ?int
    {
        if ($this->travel_start && $this->travel_end) {
            return (int) $this->travel_start->diffInDays($this->travel_end) + 1;
        }

        return $this->package?->days();
    }

    /**
     * Line items priced from a package: adults at the package price, children at the child rate.
     *
     * @return array<int, array{description: string, quantity: int, unit_price: float}>
     */
    public static function itemsForPackage(?TourPage $package, int $adults, int $children): array
    {
        if (! $package || ! $package->price_from) {
            return [];
        }

        $name = collect([$package->package_name, $package->duration])->filter()->implode(' · ');
        $items = [];

        if ($adults > 0) {
            $items[] = ['description' => $name.' — adult', 'quantity' => $adults, 'unit_price' => (float) $package->price_from];
        }

        if ($children > 0) {
            $items[] = [
                'description' => $name.' — child (under 16)',
                'quantity' => $children,
                'unit_price' => round($package->price_from * Setting::childRate(), 2),
            ];
        }

        return $items;
    }
}
