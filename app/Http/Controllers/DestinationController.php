<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Support\ListingFilters;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    /**
     * /destinations, optionally filtered: /destinations?region=southern-circuit
     */
    public function index(Request $request): View
    {
        $regions = Destination::regionOptions();
        $active = ListingFilters::activeFilter($request->query('region'), $regions);

        $destinations = Destination::published()
            ->ordered()
            ->get()
            ->when($active, fn ($all) => $all->filter(fn (Destination $d) => $d->region_key === $active))
            ->values();

        return view('pages.destinations', [
            'destinations' => $destinations,
            'chips' => ListingFilters::filterLinks('destinations', 'region', $regions, $active, 'All destinations'),
            'activeRegion' => $active ? $regions[$active] : null,
        ]);
    }

    /**
     * /destinations/{slug}
     */
    public function show(string $slug): View
    {
        $destination = Destination::where('slug', $slug)
            ->when(! auth()->check(), fn ($q) => $q->published())
            ->firstOrFail();

        // Other destinations: the same region first.
        $others = Destination::published()
            ->ordered()
            ->whereKeyNot($destination->getKey())
            ->get()
            ->sortBy(fn (Destination $d) => $d->region === $destination->region ? 0 : 1)
            ->take(3)
            ->values();

        return view('pages.destination', [
            'destination' => $destination,
            'packages' => $destination->packages(),
            'others' => $others,
        ]);
    }
}
