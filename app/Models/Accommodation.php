<?php

namespace App\Models;

use App\Enums\AccommodationLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Accommodation extends Model
{
    /** Common areas offered as suggestions in the admin (admins can type new ones). */
    public const AREAS = [
        'Arusha', 'Moshi', 'Tarangire', 'Lake Manyara', 'Karatu', 'Ngorongoro', 'Serengeti',
        'Ruaha', 'Nyerere (Selous)', 'Mikumi', 'Kilimanjaro', 'Stone Town', 'Nungwi', 'Paje',
    ];

    public const TYPES = ['Hotel', 'Lodge', 'Tented camp', 'Mobile camp', 'Beach resort', 'Guesthouse', 'Mountain hut'];

    protected $fillable = [
        'name',
        'location',
        'type',
        'level',
        'description',
        'image',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'level' => AccommodationLevel::class,
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

    /**
     * "Serengeti" → "serengeti": the value used in /accommodations?location=serengeti.
     */
    protected function locationKey(): Attribute
    {
        return Attribute::get(fn () => Str::slug((string) $this->location));
    }

    public function imageUrl(): ?string
    {
        return TourPage::imageUrl($this->image);
    }

    /**
     * Only uploaded files can be copied into an itinerary photo field (not external URLs).
     */
    public function hasLocalImage(): bool
    {
        return filled($this->image) && ! Str::startsWith($this->image, ['http://', 'https://']);
    }

    /**
     * Areas already in use, for the admin's suggestions and the page's location chips.
     *
     * @return array<string, string> slug => area name
     */
    public static function locationOptions(bool $publishedOnly = true): array
    {
        return static::query()
            ->when($publishedOnly, fn (Builder $q) => $q->published())
            ->whereNotNull('location')
            ->orderBy('location')
            ->distinct()
            ->pluck('location')
            ->mapWithKeys(fn (string $location) => [Str::slug($location) => $location])
            ->all();
    }
}
