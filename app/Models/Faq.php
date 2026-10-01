<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    protected $fillable = [
        'tour_page_id',
        'name',
        'email',
        'question',
        'answer',
        'answered_at',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Stamp when an answer is first written; an unanswered question can't be public.
        static::saving(function (Faq $faq) {
            if (filled($faq->answer) && ! $faq->answered_at) {
                $faq->answered_at = now();
            }

            if (blank($faq->answer)) {
                $faq->answered_at = null;
                $faq->is_published = false;
            }
        });
    }

    public function tourPage(): BelongsTo
    {
        return $this->belongsTo(TourPage::class);
    }

    /**
     * Answered and approved for the public FAQ page.
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_published', true)->whereNotNull('answer');
    }

    public function scopeUnanswered(Builder $query): void
    {
        $query->whereNull('answer');
    }

    public function isAnswered(): bool
    {
        return filled($this->answer);
    }
}
