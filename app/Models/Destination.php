<?php

namespace App\Models;

use App\Support\RouteMap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A park, mountain or island, shown on /destinations (Website Pages → Destinations).
 */
class Destination extends Model
{
    /** Suggestions in the admin; admins can type their own. */
    public const REGIONS = ['Northern Circuit', 'Southern Circuit', 'Kilimanjaro', 'Zanzibar & Islands'];

    protected $fillable = [
        'name',
        'slug',
        'region',
        'tagline',
        'description',
        'highlights',
        'best_time',
        'places',
        'image',
        'meta_description',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'highlights' => 'array',
        'places' => 'array',
        'is_published' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    public function url(): string
    {
        return route('destinations.show', $this);
    }

    public function imageUrl(): ?string
    {
        return TourPage::imageUrl($this->image);
    }

    /**
     * "Northern Circuit" → "northern-circuit": the value used in /destinations?region=…
     */
    protected function regionKey(): Attribute
    {
        return Attribute::get(fn () => Str::slug((string) $this->region));
    }

    /**
     * @return array<int, string>
     */
    public function highlightList(): array
    {
        return collect($this->highlights ?? [])
            ->map(fn ($h) => is_array($h) ? ($h['highlight'] ?? null) : $h)
            ->filter(fn ($h) => filled($h))
            ->map(fn ($h) => Str::limit(trim((string) $h), 120))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function paragraphs(): array
    {
        return array_values(array_filter(preg_split('/\R{2,}/', trim((string) $this->description)) ?: [], 'filled'));
    }

    /**
     * Published packages whose itinerary visits one of this destination's places.
     *
     * @return Collection<int, TourPage>
     */
    public function packages(): Collection
    {
        $places = array_values(array_filter((array) ($this->places ?? [])));
        if (! $places) {
            return new Collection;
        }

        return new Collection(collect(self::packagePlaces())
            ->filter(fn (array $row) => array_intersect($places, $row['places']) !== [])
            ->pluck('page')
            ->all());
    }

    /**
     * Every published package with the places its itinerary visits, worked out once per request.
     *
     * @return array<int, array{page: TourPage, places: array<int, string>}>
     */
    private static function packagePlaces(): array
    {
        return once(fn () => TourPage::published()
            ->ordered()
            ->get()
            ->map(fn (TourPage $page) => ['page' => $page, 'places' => RouteMap::placeKeys($page->itinerary)])
            ->all());
    }

    /**
     * Regions in use, for the filter chips on /destinations.
     *
     * @return array<string, string> slug => region
     */
    public static function regionOptions(bool $publishedOnly = true): array
    {
        $order = array_flip(self::REGIONS);

        return static::query()
            ->when($publishedOnly, fn (Builder $q) => $q->published())
            ->whereNotNull('region')
            ->distinct()
            ->pluck('region')
            ->sortBy(fn (string $r) => [$order[$r] ?? 99, $r])
            ->mapWithKeys(fn (string $region) => [Str::slug($region) => $region])
            ->all();
    }
}
