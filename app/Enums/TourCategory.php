<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The tour sections in the site navbar. Each one gets its own admin resource
 * and is served at /{value} (main page) and /{value}/{slug} (other pages).
 */
enum TourCategory: string implements HasLabel
{
    case Safaris = 'safaris';
    case SouthernCircuit = 'southern-circuit';
    case Kilimanjaro = 'kilimanjaro';
    case Zanzibar = 'zanzibar';
    case DayTrips = 'day-trips';

    public function getLabel(): string
    {
        return match ($this) {
            self::Safaris => 'Safaris',
            self::SouthernCircuit => 'Southern Circuit',
            self::Kilimanjaro => 'Kilimanjaro',
            self::Zanzibar => 'Zanzibar',
            self::DayTrips => 'Day Trips',
        };
    }

    /**
     * /safaris is the "all packages" page (every section, filterable), so safari
     * packages live at /safaris/{slug}. Other sections show their first page at /{section}.
     */
    public function hasListingPage(): bool
    {
        return $this === self::Safaris;
    }

    /**
     * Shown on the "coming soon" page until the category has a published page.
     */
    public function getSubtitle(): string
    {
        return match ($this) {
            self::Safaris => 'Tailor-made Tanzania safari packages for every budget — from budget to luxury.',
            self::SouthernCircuit => 'Explore Ruaha National Park, Nyerere & Selous, Mikumi and the Udzungwa Mountains.',
            self::Kilimanjaro => "Climb Africa's highest peak via Machame, Lemosho, Marangu, or take a day hike.",
            self::Zanzibar => 'Beach holidays, Stone Town tours, spice tours and boat trips & snorkeling in Zanzibar, Tanzania.',
            self::DayTrips => 'Arusha National Park, Materuni waterfalls, Lake Manyara or a cultural village visit — in a single day.',
        };
    }
}
