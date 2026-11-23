<?php

namespace App\Http\Controllers;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Models\Faq;
use App\Models\Review;
use App\Models\TourPage;
use App\Support\ItineraryPdf;
use App\Support\ListingFilters;
use App\Support\RouteMap;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class TourPageController extends Controller
{
    /**
     * /{category} — the category's main page, or "coming soon" until one is published.
     * /safaris is the exception: it lists every package on the site.
     */
    public function index(string $category): View
    {
        $category = TourCategory::from($category);

        if ($category->hasListingPage()) {
            return $this->listing();
        }

        $page = TourPage::mainFor($category, includeDrafts: $this->canPreview());

        if (! $page) {
            return view('pages.placeholder', [
                'title' => $category->getLabel(),
                'subtitle' => $category->getSubtitle(),
            ]);
        }

        return $this->render($page);
    }

    /**
     * /{category}/{slug} — any other page in the category.
     */
    public function show(string $category, string $slug): View
    {
        $page = TourPage::where('category', TourCategory::from($category))
            ->where('slug', $slug)
            ->when(! $this->canPreview(), fn ($q) => $q->published())
            ->firstOrFail();

        return $this->render($page);
    }

    /**
     * /{category}/{slug}/itinerary.pdf — the package as a printable, downloadable itinerary.
     */
    public function pdf(string $category, string $slug): Response
    {
        $page = TourPage::where('category', TourCategory::from($category))
            ->where('slug', $slug)
            ->when(! $this->canPreview(), fn ($q) => $q->published())
            ->firstOrFail();

        return ItineraryPdf::make($page)->download(ItineraryPdf::filename($page));
    }

    /**
     * /safaris — every published package from every section, filterable by
     * destination, style (tier) and duration.
     */
    private function listing(): View
    {
        $sections = TourCategory::cases();

        $pages = TourPage::query()
            ->when(! $this->canPreview(), fn ($q) => $q->published())
            ->ordered()
            ->get()
            ->sortBy(fn (TourPage $page) => array_search($page->category, $sections, true))
            ->values();

        // Filter options (key => label), built from what is actually on offer.
        $options = [
            'section' => collect($sections)->filter(fn (TourCategory $c) => $pages->contains('category', $c))
                ->mapWithKeys(fn (TourCategory $c) => [$c->value => $c->getLabel()])->all(),
            'tier' => collect(SafariTier::cases())->mapWithKeys(fn (SafariTier $t) => [$t->value => $t->getLabel()])->all(),
            'days' => $pages->map->days()->filter()->unique()->sort()
                ->mapWithKeys(fn (int $d) => [(string) $d => $d.' '.Str::plural('day', $d)])->all(),
        ];

        // ?tier=luxury&section=kilimanjaro&days=7 — unknown values are ignored.
        $active = collect($options)
            ->map(fn (array $opts, string $param) => ListingFilters::activeFilter(request()->query($param), $opts))
            ->all();
        $keep = ['category' => TourCategory::Safaris->value, ...array_filter($active)];

        $chips = [
            'section' => ListingFilters::filterLinks('tours.index', 'section', $options['section'], $active['section'], 'All', $keep),
            'tier' => ListingFilters::filterLinks('tours.index', 'tier', $options['tier'], $active['tier'], 'All', $keep),
            'days' => ListingFilters::filterLinks('tours.index', 'days', $options['days'], $active['days'], 'Any', $keep),
        ];

        $matches = fn (TourPage $p) => (! $active['section'] || $p->category->value === $active['section'])
            && (! $active['tier'] || $p->tier?->value === $active['tier'])
            && (! $active['days'] || (string) $p->days() === $active['days']);

        $heading = self::listingHeading($active);

        return view('pages.safaris', compact('pages', 'options', 'active', 'chips', 'matches', 'heading'));
    }

    /**
     * "Luxury Kilimanjaro Packages · 8 days" for the active filters, or null when unfiltered.
     *
     * @param  array<string, ?string>  $active
     */
    public static function listingHeading(array $active): ?string
    {
        if (! array_filter($active)) {
            return null;
        }

        $tier = $active['tier'] ? SafariTier::from($active['tier'])->getLabel().' ' : '';
        $section = $active['section'] ? TourCategory::from($active['section'])->getPackagesHeading() : 'Safaris & Tours';
        $days = $active['days'] ? ' · '.$active['days'].' days' : '';

        return $tier.$section.$days;
    }

    private function render(TourPage $page): View
    {
        $others = TourPage::where('category', $page->category)
            ->whereKeyNot($page->getKey())
            ->published()
            ->ordered()
            ->get();

        $faqs = Faq::visible()->where('tour_page_id', $page->getKey())->orderBy('sort_order')->get();

        $reviews = Review::published()->where('tour_page_id', $page->getKey())->showcase()->limit(6)->get();
        $reviewStats = Review::published()->where('tour_page_id', $page->getKey())->selectRaw('count(*) as total, avg(rating) as average')->first();

        // Route map: only worth showing with at least two stops.
        $stops = RouteMap::stops($page->itinerary);
        $routeStops = count($stops) >= 2 ? $stops : [];

        request()->attributes->set('viewed_package_id', $page->getKey());

        return view('pages.tour', compact('page', 'others', 'faqs', 'reviews', 'reviewStats', 'routeStops'));
    }

    /**
     * Logged-in admins can preview unpublished pages.
     */
    private function canPreview(): bool
    {
        return auth()->check();
    }
}
