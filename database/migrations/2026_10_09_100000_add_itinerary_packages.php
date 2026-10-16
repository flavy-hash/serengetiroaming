<?php

use App\Models\NavItem;
use App\Models\TourPage;
use Database\Seeders\ItineraryPackagesSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the Marangu, Lemosho and real 6-day Machame climbs and two Northern Circuit
 * safaris to sites that already have packages (fresh installs get them from
 * DatabaseSeeder), and points the Kilimanjaro menu links at the new pages.
 * Safe to run on a site where some of them already exist.
 */
return new class extends Migration
{
    /** Old menu link => [new label, new link], changed only if still untouched. */
    private const MENU_LINKS = [
        '/kilimanjaro#machame' => ['Machame · 6 Days', '/kilimanjaro'],
        '/kilimanjaro#lemosho' => ['Lemosho · 8 Days', '/kilimanjaro/lemosho-route'],
        '/kilimanjaro#marangu' => ['Marangu · 5 Days', '/kilimanjaro/marangu-route'],
    ];

    public function up(): void
    {
        if (! TourPage::exists()) {
            return;
        }

        app(ItineraryPackagesSeeder::class)->run();

        $menu = NavItem::where('href', '/kilimanjaro')->first();
        if ($menu) {
            $links = collect($menu->links)->map(function (array $link) {
                [$label, $href] = self::MENU_LINKS[$link['href'] ?? ''] ?? [null, null];

                return $href ? ['label' => $label, 'href' => $href] : $link;
            })->all();

            if ($links !== $menu->links) {
                $menu->update(['links' => $links]); // also clears the cached navbar
            }
        }
    }

    public function down(): void
    {
        // Packages may have been edited in the admin since, so they are left in place.
    }
};
