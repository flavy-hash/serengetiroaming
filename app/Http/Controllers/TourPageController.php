<?php

namespace App\Http\Controllers;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Models\Faq;
use App\Models\TourPage;
use Illuminate\Contracts\View\View;

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

        $filters = [
            'sections' => collect($sections)->filter(fn (TourCategory $c) => $pages->contains('category', $c))->values(),
            'tiers' => SafariTier::cases(),
            'days' => $pages->map->days()->filter()->unique()->sort()->values(),
        ];

        return view('pages.safaris', compact('pages', 'filters'));
    }

    private function render(TourPage $page): View
    {
        $others = TourPage::where('category', $page->category)
            ->whereKeyNot($page->getKey())
            ->published()
            ->ordered()
            ->get();

        $faqs = Faq::visible()->where('tour_page_id', $page->getKey())->orderBy('sort_order')->get();

        return view('pages.tour', compact('page', 'others', 'faqs'));
    }

    /**
     * Logged-in admins can preview unpublished pages.
     */
    private function canPreview(): bool
    {
        return auth()->check();
    }
}
