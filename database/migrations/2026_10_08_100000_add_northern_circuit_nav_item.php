<?php

use App\Models\NavItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

/**
 * Adds "Northern Circuit" to the navbar, right after "Safaris", on sites whose menu
 * was already set up (NavigationSeeder only runs on an empty menu). Does nothing if
 * the menu is empty or the item already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! NavItem::exists() || NavItem::where('href', '/northern-circuit')->exists()) {
            return;
        }

        // Menu photo: the bundled one, or the Ngorongoro photo already in storage, or none.
        $disk = Storage::disk('public');
        $image = 'menus/navigation/northern-circuit.jpg';
        $bundled = database_path('seeders/images/navigation/northern-circuit.jpg');
        if (! $disk->exists($image)) {
            if (is_file($bundled)) {
                $disk->put($image, file_get_contents($bundled));
            } elseif ($disk->exists('tour-pages/safaris/ngorongoro.jpg')) {
                $image = 'tour-pages/safaris/ngorongoro.jpg';
            } else {
                $image = null;
            }
        }

        $after = NavItem::where('href', '/safaris')->value('sort_order') ?? 0;
        NavItem::where('sort_order', '>', $after)->increment('sort_order');

        NavItem::create([...self::item(), 'image' => $image, 'sort_order' => $after + 1]);
    }

    public function down(): void
    {
        NavItem::where('href', '/northern-circuit')->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public static function item(): array
    {
        return [
            'label' => 'Northern Circuit',
            'href' => '/northern-circuit',
            'is_visible' => true,
            'title' => 'Tanzania Northern Circuit',
            'description' => 'The classic safari route — the Serengeti plains and the Great Migration, the Ngorongoro Crater, Tarangire\'s elephants and Lake Manyara.',
            'cta_label' => 'Explore the North',
            'links' => [
                ['label' => 'View All Northern', 'href' => '/northern-circuit'],
                ['label' => 'Serengeti National Park', 'href' => '/northern-circuit#serengeti'],
                ['label' => 'Ngorongoro Crater', 'href' => '/northern-circuit#ngorongoro'],
                ['label' => 'Tarangire National Park', 'href' => '/northern-circuit#tarangire'],
                ['label' => 'Lake Manyara', 'href' => '/northern-circuit#lake-manyara'],
            ],
        ];
    }
};
