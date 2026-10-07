<?php

namespace App\Support;

use App\Models\TourPage;
use Illuminate\Support\Str;

/**
 * Small helpers for page titles, descriptions and schema.org structured data.
 */
class Seo
{
    /** Google shows roughly this many characters of a title or description. */
    public const TITLE_LIMIT = 60;

    public const DESCRIPTION_LIMIT = 160;

    /**
     * "Machame Route – 7 Days" → "Machame Route – 7 Days | Serengeti Roaming", adding the
     * brand only while the whole title still fits in Google's results.
     */
    public static function title(string $title): string
    {
        $branded = $title.' | '.config('site.short_name');

        return mb_strlen($branded) <= self::TITLE_LIMIT ? $branded : $title;
    }

    public static function description(?string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', (string) $text));

        return Str::limit($text ?: config('site.description'), self::DESCRIPTION_LIMIT - 3);
    }

    /**
     * The business itself, shown on every page.
     *
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        return array_filter([
            '@type' => 'TravelAgency',
            '@id' => url('/').'#organization',
            'name' => config('site.name'),
            'alternateName' => config('site.short_name'),
            'url' => url('/'),
            'logo' => asset('images/Logo-2-croped.png'),
            'image' => asset('icons/icon-512.png'),
            'description' => config('site.description'),
            'email' => config('site.email'),
            'telephone' => '+'.config('site.whatsapp'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => config('site.city'),
                'addressCountry' => config('site.country_code'),
            ],
            'areaServed' => ['@type' => 'Country', 'name' => 'Tanzania'],
            'sameAs' => config('site.profiles') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'name' => config('site.name'),
            'alternateName' => config('site.short_name'),
            'url' => url('/'),
            'publisher' => ['@id' => url('/').'#organization'],
            'inLanguage' => 'en',
        ];
    }

    /**
     * @param  array<string, string>  $crumbs  label => url, starting after "Home"
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $crumbs): array
    {
        $items = ['Home' => url('/')] + $crumbs;

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (string $url, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => array_keys($items)[$i],
                'item' => $url,
            ])->all(),
        ];
    }

    /**
     * A package as a schema.org TouristTrip, with its day-by-day itinerary and price.
     *
     * @return array<string, mixed>
     */
    public static function trip(TourPage $page): array
    {
        $image = TourPage::imageUrl($page->banner_image);

        return array_filter([
            '@type' => 'TouristTrip',
            '@id' => $page->url().'#trip',
            'name' => $page->package_name,
            'description' => self::description($page->meta_description ?: $page->card_summary ?: $page->overview_body),
            'url' => $page->url(),
            'image' => $image,
            'touristType' => $page->tier?->getLabel(),
            'provider' => ['@id' => url('/').'#organization'],
            'itinerary' => empty($page->itinerary) ? null : [
                '@type' => 'ItemList',
                'numberOfItems' => count($page->itinerary),
                'itemListElement' => collect($page->itinerary)->values()->map(fn (array $day, int $i) => array_filter([
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => trim(($day['day'] ?? '').': '.($day['title'] ?? ''), ': '),
                    'description' => $day['copy'] ?? null,
                ]))->all(),
            ],
            'offers' => $page->price_from ? [
                '@type' => 'Offer',
                'price' => $page->price_from,
                'priceCurrency' => 'USD',
                'description' => $page->price_note ?: 'From, per person',
                'url' => $page->url(),
                'availability' => 'https://schema.org/InStock',
                'offeredBy' => ['@id' => url('/').'#organization'],
            ] : null,
            'contentLocation' => $page->location ? ['@type' => 'Place', 'name' => $page->location] : null,
        ]);
    }

    /**
     * Answered FAQs as schema.org FAQPage.
     *
     * @param  iterable<int, \App\Models\Faq>  $faqs
     * @return array<string, mixed>|null
     */
    public static function faqPage(iterable $faqs): ?array
    {
        $questions = collect($faqs)->map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq->question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
        ])->values()->all();

        return $questions ? ['@type' => 'FAQPage', 'mainEntity' => $questions] : null;
    }

    /**
     * Renders schema.org objects as one JSON-LD <script> block.
     *
     * @param  array<int, array<string, mixed>>  $objects
     */
    public static function jsonLd(array $objects): string
    {
        $json = json_encode(
            ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($objects))],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_PRETTY_PRINT,
        );

        return '<script type="application/ld+json">'.$json.'</script>';
    }
}
