<?php

namespace App\Models;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TourPage extends Model
{
    protected $fillable = [
        'category',
        'tier',
        'title',
        'slug',
        'is_published',
        'sort_order',
        'seo_title',
        'meta_description',
        'banner_image',
        'banner_badge',
        'banner_text',
        'location',
        'package_name',
        'duration',
        'card_tagline',
        'card_highlight',
        'card_summary',
        'overview_heading',
        'overview_body',
        'price_from',
        'price_note',
        'facts',
        'itinerary',
        'included',
        'excluded',
        'experiences_heading',
        'experiences',
    ];

    protected $casts = [
        'category' => TourCategory::class,
        'tier' => SafariTier::class,
        'is_published' => 'boolean',
        'price_from' => 'integer',
        'facts' => 'array',
        'itinerary' => 'array',
        'included' => 'array',
        'excluded' => 'array',
        'experiences' => 'array',
    ];

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The first page of a category (by sort order) is served at /{category};
     * the rest at /{category}/{slug}. Tiered sections (safaris) have a listing
     * page at /{category} instead, so none of their pages is the main one.
     */
    public static function mainFor(TourCategory $category, bool $includeDrafts = false): ?self
    {
        if ($category->hasListingPage()) {
            return null;
        }

        return static::where('category', $category)
            ->when(! $includeDrafts, fn (Builder $q) => $q->published())
            ->ordered()
            ->first();
    }

    public function isMain(): bool
    {
        return static::mainFor($this->category, includeDrafts: true)?->is($this) ?? false;
    }

    public function url(): string
    {
        return $this->isMain()
            ? url($this->category->value)
            : url("{$this->category->value}/{$this->slug}");
    }

    /**
     * Local file path of an uploaded image, for the PDF renderer (which doesn't
     * fetch images over HTTP). Full URLs and missing files return null.
     */
    public static function imagePath(?string $path): ?string
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://'])) {
            return null;
        }

        $file = storage_path('app/public/'.ltrim($path, '/'));

        return is_file($file) ? $file : null;
    }

    /**
     * The search-engine title before the brand is added, e.g. "Machame Route – 7 Days".
     * A section's main page (/kilimanjaro) also names the section, which is what people
     * search for: "Kilimanjaro: Machame Route – 7 Days".
     */
    public function seoTitle(): string
    {
        if ($this->seo_title) {
            return $this->seo_title;
        }

        $title = collect([$this->package_name, $this->duration])->filter()->implode(' – ');
        $section = $this->category->getLabel();

        return $this->isMain() && ! Str::contains($this->package_name, $section, ignoreCase: true)
            ? "{$section}: {$title}"
            : $title;
    }

    public function pdfUrl(): string
    {
        return url("{$this->category->value}/{$this->slug}/itinerary.pdf");
    }

    /**
     * Number of days, read from the duration ("7 Days" → 7), for the duration filter.
     */
    public function days(): ?int
    {
        return preg_match('/\d+/', (string) $this->duration, $m) ? (int) $m[0] : null;
    }

    /**
     * How the trip appears in the booking form, e.g. "Zanzibar Beach Escape · 4 Days · From $650".
     */
    public function bookingLabel(): string
    {
        return collect([
            $this->package_name,
            $this->duration,
            $this->price_from ? 'From $'.number_format($this->price_from) : null,
        ])->filter()->implode(' · ');
    }

    /**
     * Uploaded images are stored on the public disk; full URLs are passed through.
     */
    public static function imageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : asset('storage/'.ltrim($path, '/'));
    }
}
