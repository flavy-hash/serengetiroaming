<?php

use App\Models\NavItem;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Our Vehicles" to the About dropdown (after "Our Team") on sites whose menu was
 * already set up — NavigationSeeder only runs on an empty menu. Does nothing if there
 * is no About item or the link is already there.
 */
return new class extends Migration
{
    public const LINK = [
        'label' => 'Our Vehicles',
        'href' => '/vehicles',
        'preview_title' => 'Our Safari Vehicles',
        'preview_text' => 'Private 4×4 Land Cruisers with pop-up roofs, driven by our own guides.',
        'preview_cta' => 'See our vehicles',
    ];

    public function up(): void
    {
        $about = NavItem::where('href', '/about')->first();
        if (! $about) {
            return;
        }

        $links = array_values($about->links ?? []);
        if (collect($links)->contains(fn ($l) => ($l['href'] ?? null) === self::LINK['href'])) {
            return;
        }

        $after = collect($links)->search(fn ($l) => ($l['href'] ?? null) === '/about#team');
        $position = $after === false ? count($links) : $after + 1;
        array_splice($links, $position, 0, [self::LINK]);

        $about->update(['links' => $links]);
    }

    public function down(): void
    {
        $about = NavItem::where('href', '/about')->first();
        if (! $about) {
            return;
        }

        $about->update(['links' => collect($about->links ?? [])
            ->reject(fn ($l) => ($l['href'] ?? null) === self::LINK['href'])
            ->values()
            ->all()]);
    }
};
