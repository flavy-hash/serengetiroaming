<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\TourPage;
use App\Support\ListingFilters;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    /**
     * /accommodations, optionally filtered: /accommodations?location=serengeti
     */
    public function index(Request $request): View
    {
        // Options come from the areas actually in use, so a new area added in the admin
        // gets a chip automatically and menu links like ?location=serengeti keep working.
        $locations = Accommodation::locationOptions();
        $active = ListingFilters::activeFilter($request->query('location'), $locations);

        $stays = Accommodation::published()
            ->ordered()
            ->get()
            ->when($active, fn ($all) => $all->filter(fn (Accommodation $s) => $s->location_key === $active))
            ->values();

        return view('pages.accommodations', [
            'stays' => $stays,
            'chips' => ListingFilters::filterLinks('accommodations', 'location', $locations, $active, 'All areas'),
            'activeLocation' => $active ? $locations[$active] : null,
        ]);
    }

    /**
     * /accommodations/{slug}: one stay with its description and photo gallery.
     */
    public function show(string $slug): View
    {
        $stay = Accommodation::where('slug', $slug)
            ->when(! auth()->check(), fn ($q) => $q->published())
            ->firstOrFail();

        // Published packages whose itinerary has a night at this stay.
        $packages = TourPage::published()
            ->ordered()
            ->get()
            ->filter(fn (TourPage $page) => collect($page->itinerary)
                ->contains(fn ($day) => (int) ($day['accommodation_id'] ?? 0) === $stay->id))
            ->values();

        // Other stays: the same area first.
        $others = Accommodation::published()
            ->ordered()
            ->whereKeyNot($stay->getKey())
            ->get()
            ->sortBy(fn (Accommodation $other) => $other->location_key === $stay->location_key ? 0 : 1)
            ->take(3)
            ->values();

        return view('pages.accommodation', [
            'stay' => $stay,
            'photos' => $stay->photoUrls(),
            'packages' => $packages,
            'others' => $others,
        ]);
    }
}
