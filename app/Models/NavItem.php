<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NavItem extends Model
{
    private const CACHE_KEY = 'navbar.items';

    protected $fillable = [
        'label',
        'href',
        'is_visible',
        'sort_order',
        'title',
        'description',
        'cta_label',
        'image',
        'links',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'links' => 'array',
    ];

    protected static function booted(): void
    {
        // The navbar is on every page, so it is cached until an item changes.
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * Also called after bulk updates (like drag-to-reorder) that skip model events.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return Collection<int, self>
     */
    public static function forNavbar(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get());
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function hasDropdown(): bool
    {
        return ! empty($this->links);
    }

    /**
     * Used for the dropdown panel's element id (mega-{key}).
     */
    public function key(): string
    {
        return Str::slug($this->label) ?: (string) $this->id;
    }

    public function imageUrl(): ?string
    {
        return TourPage::imageUrl($this->image);
    }
}
