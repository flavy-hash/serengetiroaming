<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * An activity in the homepage's "Top activities" section (Website Pages → Activities).
 */
class Activity extends Model
{
    /** Section text, stored as settings and edited from the Activities admin screen. */
    public const SECTION_DEFAULTS = [
        'activities_eyebrow' => 'More than game drives',
        'activities_heading' => 'Top activities on a Tanzania safari',
        'activities_intro' => 'A safari with Serengeti Roaming is about more than the drive: walk with rangers, glide past hippos, share a Maasai welcome and taste coffee where it grows — then wash the dust off in the Indian Ocean.',
    ];

    protected $fillable = [
        'kicker',
        'title',
        'description',
        'image',
        'image_label',
        'link_url',
        'link_label',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return TourPage::imageUrl($this->image);
    }

    /**
     * Only links to this site ("/zanzibar") or to http(s) addresses are shown.
     */
    public function safeLinkUrl(): ?string
    {
        $url = trim((string) $this->link_url);
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return url($url);
        }

        return preg_match('#^https?://#i', $url) ? $url : null;
    }

    /**
     * @return array{eyebrow: string, heading: string, intro: string}
     */
    public static function sectionText(): array
    {
        return [
            'eyebrow' => (string) Setting::get('activities_eyebrow', self::SECTION_DEFAULTS['activities_eyebrow']),
            'heading' => (string) Setting::get('activities_heading', self::SECTION_DEFAULTS['activities_heading']),
            'intro' => (string) Setting::get('activities_intro', self::SECTION_DEFAULTS['activities_intro']),
        ];
    }
}
