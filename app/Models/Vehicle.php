<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A vehicle in the company's fleet, shown on /vehicles (About → Our Vehicles).
 */
class Vehicle extends Model
{
    /** Suggestions in the admin; admins can type their own. */
    public const CATEGORIES = ['Safari 4×4', 'Open-sided game viewer', 'Airport transfer', 'Group transfer', 'Mountain transfer'];

    protected $fillable = [
        'name',
        'category',
        'seats',
        'description',
        'features',
        'image',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'seats' => 'integer',
        'is_published' => 'boolean',
    ];

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    public function imageUrl(): ?string
    {
        return TourPage::imageUrl($this->image);
    }

    /**
     * Features with blanks removed.
     *
     * @return array<int, string>
     */
    public function featureList(): array
    {
        return collect($this->features ?? [])
            ->map(fn ($f) => is_array($f) ? ($f['feature'] ?? null) : $f)
            ->filter(fn ($f) => filled($f))
            ->map(fn ($f) => Str::limit(trim((string) $f), 120))
            ->values()
            ->all();
    }

    /**
     * "#vehicle-3" anchor on /vehicles.
     */
    public function anchor(): string
    {
        return 'vehicle-'.$this->getKey();
    }
}
