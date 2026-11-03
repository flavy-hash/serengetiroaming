<?php

namespace Tests\Feature;

use App\Models\NavItem;
use App\Models\TourPage;
use App\Support\ListingFilters;
use Database\Seeders\ItineraryPackagesSeeder;
use Database\Seeders\NavigationSeeder;
use Database\Seeders\PackageSeeder;
use Database\Seeders\TourPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MegaMenuAndFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed([TourPageSeeder::class, PackageSeeder::class, ItineraryPackagesSeeder::class, NavigationSeeder::class]);
    }

    /**
     * The HTML of one preview block, by id.
     */
    private function preview(string $html, string $id): string
    {
        $this->assertMatchesRegularExpression('#<div class="mega__preview" id="'.$id.'"#', $html);
        preg_match('#<div class="mega__preview" id="'.$id.'".*?</div>\s*(?:<img[^>]*>|<div class="mega__img"[^>]*></div>)\s*</div>#s', $html, $m);

        return $m[0] ?? '';
    }

    public function test_every_dropdown_has_a_default_preview_and_one_per_link(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-menu-group data-menu-hover', $html);
        $this->assertStringContainsString('data-menu-trigger aria-expanded="false" aria-controls="mega-kilimanjaro"', $html);
        $this->assertStringContainsString('id="mega-kilimanjaro" data-mega-panel hidden', $html);

        // The menu content itself is unchanged.
        $this->assertStringContainsString('>Lemosho · 8 Days</a>', $html);
        $this->assertStringContainsString('data-mega-target="preview-kilimanjaro-2"', $html);

        // Default preview visible, the per-link ones hidden until hovered.
        $this->assertStringContainsString('id="preview-kilimanjaro-default" data-mega-preview data-mega-default', $html);
        $this->assertStringContainsString('id="preview-kilimanjaro-2" data-mega-preview hidden', $html);
        $this->assertStringContainsString('Climbing the Roof of Africa', $this->preview($html, 'preview-kilimanjaro-default'));
    }

    public function test_link_previews_are_taken_from_the_page_they_open(): void
    {
        $html = $this->get('/')->getContent();

        // A package link: its name, duration and short description.
        $lemosho = $this->preview($html, 'preview-kilimanjaro-2');
        $this->assertStringContainsString('Lemosho Route · 8 Days', $lemosho);
        $this->assertStringContainsString('Eight days from Londorossi Gate', $lemosho);
        $this->assertStringContainsString('View package', $lemosho);
        $this->assertStringContainsString('/storage/tour-pages/kilimanjaro/kilimanjaro-amboseli.jpg', $lemosho);
        $this->assertStringContainsString('loading="lazy"', $lemosho);

        // A style link on /safaris: the style description.
        $luxury = $this->preview($html, 'preview-safaris-4');
        $this->assertStringContainsString('Luxury Safaris', $luxury);
        $this->assertStringContainsString('Exclusive camps and lodges', $luxury);

        // An Explore card anchor (/zanzibar#stone-town).
        $this->assertStringContainsString('Stone Town Tours', $this->preview($html, 'preview-zanzibar-1'));
    }

    public function test_admins_can_override_a_link_preview_and_package_edits_refresh_it(): void
    {
        $this->get('/'); // fills the preview cache

        $menu = NavItem::where('href', '/kilimanjaro')->first();
        $links = $menu->links;
        $links[3]['preview_title'] = 'The hut route';
        $menu->update(['links' => $links]);

        TourPage::where('slug', 'lemosho-route')->first()->update(['card_summary' => 'Updated Lemosho summary.']);

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('The hut route', $this->preview($html, 'preview-kilimanjaro-3'));
        $this->assertStringContainsString('Updated Lemosho summary.', $this->preview($html, 'preview-kilimanjaro-2'));
    }

    public function test_the_current_section_is_underlined(): void
    {
        $html = $this->get('/kilimanjaro/lemosho-route')->getContent();

        $this->assertStringContainsString('class="nav-trigger is-current" type="button" data-menu-trigger aria-expanded="false" aria-controls="mega-kilimanjaro"', $html);
        $this->assertStringContainsString('class="nav-trigger" type="button" data-menu-trigger aria-expanded="false" aria-controls="mega-zanzibar"', $html);

        $this->get('/contact')->assertSee('class="nav-link is-current" href="'.url('/contact').'" aria-current="page"', false);
    }

    public function test_the_mobile_menu_has_the_same_items_and_marks_the_current_section(): void
    {
        $html = $this->get('/kilimanjaro')->assertOk()->getContent();

        $this->assertStringContainsString('id="mobileMenu" data-mobile-menu hidden', $html);
        $this->assertSame(2, substr_count($html, 'aria-controls="mobileMenu"')); // header ☰ and bottom-bar "Menu"

        $drawer = substr($html, strpos($html, 'id="mobileMenu"'));
        foreach (NavItem::forNavbar() as $menu) {
            $this->assertStringContainsString(e($menu->label), $drawer);
            foreach ($menu->links as $link) {
                $this->assertStringContainsString('href="'.e(url($link['href'])).'"', $drawer);
            }
        }

        // The section the visitor is in starts expanded (mobile-menu.js opens .is-current).
        $this->assertMatchesRegularExpression('/class="mobile-menu__link is-current"\s+data-mobile-section[^>]*aria-controls="mobile-sub-kilimanjaro"/', $drawer);
        $this->assertStringContainsString('Send a Request', $drawer);
    }

    public function test_filter_helpers_ignore_unknown_values_and_build_chips(): void
    {
        $options = ['serengeti' => 'Serengeti', 'zanzibar' => 'Zanzibar'];

        $this->assertSame('serengeti', ListingFilters::activeFilter('serengeti', $options));
        $this->assertNull(ListingFilters::activeFilter('atlantis', $options));
        $this->assertNull(ListingFilters::activeFilter(['serengeti'], $options));
        $this->assertNull(ListingFilters::activeFilter(null, $options));

        $chips = ListingFilters::filterLinks('tours.index', 'tier', ['luxury' => 'Luxury'], 'luxury', 'All', ['category' => 'safaris', 'days' => '5']);
        $this->assertSame(['All', 'Luxury'], array_column($chips, 'label'));
        $this->assertSame([false, true], array_column($chips, 'active'));
        $this->assertSame(url('/safaris?days=5'), $chips[0]['url']);
        $this->assertSame(url('/safaris?days=5&tier=luxury'), $chips[1]['url']);
    }

    public function test_menu_links_open_the_listing_already_filtered(): void
    {
        $response = $this->get('/safaris?tier=luxury');

        $response->assertOk()
            ->assertSee('<title>Luxury Safaris &amp; Tours | Serengeti Roaming</title>', false)
            ->assertSee('data-filter-heading data-heading-default="Tanzania Safari Adventures">Luxury Safaris &amp; Tours</h1>', false)
            ->assertSee('<span aria-current="page" data-filter-crumb>Luxury Safaris &amp; Tours</span>', false)
            ->assertSee('data-filter="tier" data-value="luxury"', false)
            ->assertSee('data-section="northern-circuit" data-tier="luxury" data-days="8" >', false)
            ->assertSee('data-section="kilimanjaro" data-tier="classic" data-days="6"  hidden', false);

        $this->assertStringContainsString('<span class="font-semibold text-forest-900" data-filter-count>2</span>', $response->getContent());

        $this->get('/safaris?section=kilimanjaro&tier=classic')
            ->assertSee('Classic Kilimanjaro Packages');
    }

    public function test_unknown_filter_values_show_everything(): void
    {
        $this->get('/safaris?tier=atlantis&section=moon&days=99')
            ->assertOk()
            ->assertSee('<title>Tanzania Safaris &amp; Tours | Serengeti Roaming</title>', false)
            ->assertDontSee('data-filter-crumb', false)
            ->assertSee('data-filter-count>7</span>', false);
    }

    public function test_a_filter_with_no_matches_shows_a_friendly_empty_state(): void
    {
        $html = $this->get('/safaris?tier=budget')->assertOk()->getContent();

        $this->assertStringContainsString('<div class=" py-12 text-center" data-filter-empty>', $html);
        $this->assertStringContainsString('See all packages →', $html);
        $this->assertStringContainsString('data-filter-count>0</span>', $html);
    }
}
