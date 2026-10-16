<?php

use App\Models\NavItem;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Destinations" to the navbar, just before "About", on sites whose menu was already
 * set up (NavigationSeeder only runs on an empty menu). Does nothing if the menu is empty
 * or the item already exists. Admins can move or hide it under Site Settings → Navigation.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! NavItem::exists() || NavItem::where('href', '/destinations')->exists()) {
            return;
        }

        $before = NavItem::where('href', '/about')->value('sort_order')
            ?? ((int) NavItem::max('sort_order') + 1);
        NavItem::where('sort_order', '>=', $before)->increment('sort_order');

        NavItem::create([
            'label' => 'Destinations',
            'href' => '/destinations',
            'is_visible' => true,
            'links' => [],
            'sort_order' => $before,
        ]);
    }

    public function down(): void
    {
        NavItem::where('href', '/destinations')->delete();
    }
};
