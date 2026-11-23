<?php

use App\Models\NavItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Destinations get an "Activities" line (shown on each Explore Zanzibar area's page), and the
 * Zanzibar menu gets an "Explore Zanzibar" link on sites whose menu was already set up
 * (NavigationSeeder only runs on an empty menu).
 */
return new class extends Migration
{
    public const LINK = ['label' => 'Explore Zanzibar', 'href' => '/explore-zanzibar'];

    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->text('activities')->nullable()->after('highlights');
        });

        $zanzibar = NavItem::where('href', '/zanzibar')->first();
        if (! $zanzibar) {
            return;
        }

        $links = array_values($zanzibar->links ?? []);
        if (! collect($links)->contains(fn ($l) => ($l['href'] ?? null) === self::LINK['href'])) {
            $links[] = self::LINK;
            $zanzibar->update(['links' => $links]);
        }
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn('activities');
        });

        $zanzibar = NavItem::where('href', '/zanzibar')->first();
        $zanzibar?->update(['links' => collect($zanzibar->links ?? [])
            ->reject(fn ($l) => ($l['href'] ?? null) === self::LINK['href'])
            ->values()
            ->all()]);
    }
};
