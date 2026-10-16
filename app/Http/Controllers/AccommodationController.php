<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
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
}
