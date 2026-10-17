<?php

use App\Models\NavItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

/**
 * Turns the "Destinations" menu item into a dropdown (main destinations + Accommodation) on
 * sites whose menu was already set up. Only fills it in while the item has no links, so
 * anything an admin has set up in Navigation is never overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        $item = NavItem::where('href', '/destinations')->first();
        if (! $item || ! empty($item->links)) {
            return;
        }

        // Menu photo: the bundled one, unless the admin already chose a photo.
        $image = $item->image;
        $bundled = database_path('seeders/images/navigation/destinations.jpg');
        if (blank($image) && is_file($bundled)) {
            $image = 'menus/navigation/destinations.jpg';
            if (! Storage::disk('public')->exists($image)) {
                Storage::disk('public')->put($image, file_get_contents($bundled));
            }
        }

        $item->update([
            'title' => $item->title ?: 'Tanzania Safari Destinations',
            'description' => $item->description ?: 'The parks, mountains and islands we visit — and the lodges and camps where you will stay.',
            'cta_label' => $item->cta_label ?: 'See All Destinations',
            'image' => $image,
            'links' => self::links(),
        ]);
    }

    public function down(): void
    {
        $item = NavItem::where('href', '/destinations')->first();
        if ($item && $item->links === self::links()) {
            $item->update(['links' => []]);
        }
    }

    /**
     * @return array<int, array{label: string, href: string}>
     */
    public static function links(): array
    {
        return [
            ['label' => 'View All Destinations', 'href' => '/destinations'],
            ['label' => 'Serengeti National Park', 'href' => '/destinations/serengeti'],
            ['label' => 'Ngorongoro Crater', 'href' => '/destinations/ngorongoro'],
            ['label' => 'Tarangire National Park', 'href' => '/destinations/tarangire'],
            ['label' => 'Zanzibar Island', 'href' => '/destinations/zanzibar'],
            ['label' => 'Accommodation', 'href' => '/accommodations'],
        ];
    }
};
