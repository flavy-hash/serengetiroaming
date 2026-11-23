<?php

use App\Models\NavItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The About page gets optional Mission and Vision, a "Why choose us" heading and intro, and a closing paragraph, and the team moves to its own
 * page (/our-team) where people are grouped (Sales, Reservations, Guides...). An existing team
 * list becomes the first group so nothing is lost; the old `team` column is left in place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            $table->text('mission')->nullable()->after('intro_image');
            $table->text('vision')->nullable()->after('mission');
            $table->string('why_heading')->nullable()->after('vision');
            $table->text('why_intro')->nullable()->after('why_heading');
            $table->text('closing_text')->nullable()->after('highlights');

            $table->string('team_title')->nullable()->after('team');
            $table->string('team_banner_image')->nullable()->after('team_title');
            $table->string('team_heading')->nullable()->after('team_banner_image');
            $table->text('team_intro')->nullable()->after('team_heading');
            $table->json('team_groups')->nullable()->after('team_intro');
        });

        DB::table('about_pages')->whereNotNull('team')->get()->each(function ($row) {
            $members = json_decode($row->team, true);

            if (is_array($members) && $members !== []) {
                DB::table('about_pages')->where('id', $row->id)->update([
                    'team_groups' => json_encode([['title' => null, 'description' => null, 'members' => array_values($members)]]),
                ]);
            }
        });

        // Menus that were already set up still point "Our Team" at the old section of the About page.
        NavItem::where('href', '/about')->first()?->update([
            'links' => collect(NavItem::where('href', '/about')->first()->links ?? [])
                ->map(fn ($link) => ($link['href'] ?? null) === '/about#team' ? [...$link, 'href' => '/our-team'] : $link)
                ->all(),
        ]);
    }

    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table) {
            $table->dropColumn(['mission', 'vision', 'why_heading', 'why_intro', 'closing_text', 'team_title', 'team_banner_image', 'team_heading', 'team_intro', 'team_groups']);
        });
    }
};
