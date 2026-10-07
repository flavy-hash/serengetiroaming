<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'tour_page_id',
        'name',
        'email',
        'country',
        'trip_date',
        'rating',
        'title',
        'body',
        'photos',
        'status',
        'is_featured',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'rating' => 'integer',
        'photos' => 'array',
        'status' => ReviewStatus::class,
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Only published reviews can be featured on the homepage.
        static::saving(function (Review $review) {
            if ($review->status !== ReviewStatus::Published) {
                $review->is_featured = false;
            }
        });
    }

    public function tourPage(): BelongsTo
    {
        return $this->belongsTo(TourPage::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', ReviewStatus::Published);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', ReviewStatus::Pending);
    }

    /**
     * Featured reviews first, then the newest.
     */
    public function scopeShowcase(Builder $query): void
    {
        $query->orderByDesc('is_featured')->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Reviews for the homepage: featured ones first, topped up with the latest.
     *
     * @return Collection<int, self>
     */
    public static function forHomepage(int $limit = 3): Collection
    {
        return static::published()->with('tourPage')->showcase()->limit($limit)->get();
    }

    /**
     * @return array<int, string>
     */
    public function photoUrls(): array
    {
        return collect($this->photos)->map(fn ($path) => TourPage::imageUrl($path))->filter()->values()->all();
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    }
}
