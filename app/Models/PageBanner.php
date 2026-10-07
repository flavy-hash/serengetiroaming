<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Editable banner (hero) for a fixed page. The pages and their default text are
 * listed in PAGES; empty fields fall back to those defaults.
 */
class PageBanner extends Model
{
    public const PAGES = [
        'safaris' => [
            'label' => 'Safaris (all packages)',
            'path' => '/safaris',
            'title' => 'Tanzania Safari Adventures',
            'text' => 'Every safari, Kilimanjaro climb, Zanzibar escape and day trip we offer — from budget to luxury.',
            // Placeholder photo credited on /photo-credits, used until one is uploaded.
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/020_The_lion_king_Snyggve_in_the_Serengeti_National_Park_Photo_by_Giles_Laurent.jpg/1920px-020_The_lion_king_Snyggve_in_the_Serengeti_National_Park_Photo_by_Giles_Laurent.jpg',
        ],
        'contact' => [
            'label' => 'Contact',
            'path' => '/contact',
            'title' => 'Contact Us',
            'text' => 'Talk to our safari experts — we reply within 24 hours.',
            'image' => null,
        ],
        'faq' => [
            'label' => 'FAQ',
            'path' => '/faq',
            'title' => 'Frequently Asked Questions',
            'text' => 'Real questions from travellers, answered by our safari experts.',
            'image' => null,
        ],
        'reviews' => [
            'label' => 'Reviews',
            'path' => '/reviews',
            'title' => 'What Travelers Are Saying',
            'text' => 'Honest reviews from guests who explored Tanzania with us.',
            'image' => null,
        ],
    ];

    protected $fillable = ['key', 'title', 'text', 'image'];

    /**
     * Makes sure every page in PAGES has a row the admin can edit.
     */
    public static function ensureDefaults(): void
    {
        foreach (array_keys(self::PAGES) as $key) {
            static::firstOrCreate(['key' => $key]);
        }
    }

    public static function for(string $key): self
    {
        return static::firstWhere('key', $key) ?? new static(['key' => $key]);
    }

    public function label(): string
    {
        return self::PAGES[$this->key]['label'] ?? $this->key;
    }

    public function path(): string
    {
        return self::PAGES[$this->key]['path'] ?? '/';
    }

    public function heading(): string
    {
        return $this->title ?: (self::PAGES[$this->key]['title'] ?? '');
    }

    public function subheading(): string
    {
        return $this->text ?: (self::PAGES[$this->key]['text'] ?? '');
    }

    public function imageUrl(): ?string
    {
        return TourPage::imageUrl($this->image ?: (self::PAGES[$this->key]['image'] ?? null));
    }
}
