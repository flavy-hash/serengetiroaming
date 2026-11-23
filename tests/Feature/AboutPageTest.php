<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Models\NavItem;
use Database\Seeders\AboutPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** /about is a photo banner, a framed picture and centred text; /our-team shows the people in groups. */
class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    private function about(array $attributes = []): AboutPage
    {
        return AboutPage::create([
            'is_published' => true,
            'title' => 'About Us',
            'banner_text' => 'Locally owned, globally trusted, best prices.',
            ...$attributes,
        ]);
    }

    private function team(): array
    {
        return [
            'team_groups' => [
                ['title' => null, 'description' => null, 'members' => [['name' => 'Jesse Msaghaa', 'role' => 'Managing Director', 'bio' => 'Five years in travel.', 'link' => 'https://linkedin.com/in/jesse']]],
                ['title' => 'Our Sales Team', 'description' => 'We plan your trip.', 'members' => [['name' => 'Simel Laizer'], ['name' => 'Esther Godfrey']]],
                ['title' => 'Empty Group', 'members' => []],
            ],
        ];
    }

    public function test_the_about_page_has_a_banner_a_framed_photo_and_the_story(): void
    {
        $this->about(['intro_body' => "First paragraph.\n\nSecond paragraph."]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('class="page-banner"', false)
            ->assertSee('page-banner__title">About Us<', false)
            ->assertSee('Locally owned, globally trusted, best prices.')
            ->assertSee('class="about-photo"', false)
            ->assertSee('Who Are We?')
            ->assertSee('First paragraph.')
            ->assertSee('Second paragraph.')
            ->assertDontSee('about-hero', false);
    }

    public function test_default_photos_are_used_until_some_are_uploaded(): void
    {
        $this->about();

        $this->get('/about')
            ->assertSee(asset('images/banners/about.jpg'), false)
            ->assertSee(asset('images/banners/about-frame.jpg'), false);

        AboutPage::query()->update(['banner_image' => 'about/hero.jpg', 'intro_image' => 'about/intro.jpg']);

        $this->get('/about')
            ->assertSee('/storage/about/hero.jpg', false)
            ->assertSee('/storage/about/intro.jpg', false)
            ->assertDontSee(asset('images/banners/about.jpg'), false);
    }

    public function test_the_why_choose_us_list_mission_and_vision_show_when_filled(): void
    {
        $this->about();
        $this->get('/about')->assertDontSee('Our Mission')->assertDontSee('Why Travel With Us?');

        AboutPage::query()->update([
            'mission' => 'Authentic, safe journeys.',
            'vision' => 'Tanzania’s most trusted travel company.',
            'why_heading' => 'Why Choose Us?',
            'why_intro' => 'What makes us your ideal partner?',
            'highlights' => [['title' => 'Professional Guides', 'copy' => 'Knowledgeable and passionate.']],
            'closing_text' => 'Karibu sana.',
        ]);

        $this->get('/about')
            ->assertSee('Our Mission')->assertSee('Authentic, safe journeys.')
            ->assertSee('Our Vision')
            ->assertSee('Why Choose Us?')->assertSee('What makes us your ideal partner?')
            ->assertSee('class="why-us"', false)
            ->assertSee('class="why-card"', false)
            ->assertSee('why-us__question', false)->assertSee('What makes us your ideal partner?')
            ->assertSee('Professional Guides')->assertSee('Knowledgeable and passionate.')
            ->assertSee('Karibu sana.');
    }

    public function test_the_community_section_shows_when_it_has_text(): void
    {
        $this->about();
        $this->get('/about')->assertDontSee('Supporting Local Communities');

        AboutPage::query()->update(['community_text' => "We support local families.

We work with local artisans."]);
        $this->get('/about')->assertSee('Supporting Local Communities')->assertSee('We support local families.')->assertSee('We work with local artisans.');

        AboutPage::query()->update(['community_heading' => 'Giving Back']);
        $this->get('/about')->assertSee('Giving Back')->assertDontSee('Supporting Local Communities');
    }

    public function test_text_is_escaped(): void
    {
        $this->about(['title' => 'Karibuni', 'banner_text' => '<script>alert(1)</script>']);

        $this->get('/about')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_the_team_button_follows_whether_there_is_a_team(): void
    {
        $this->about();
        $this->get('/about')->assertDontSee('Meet our team')->assertSee('Plan my trip');

        AboutPage::query()->update($this->team());
        $this->get('/about')->assertSee('href="'.route('team').'"', false)->assertSee('Meet our team');
    }

    public function test_the_team_page_shows_the_groups_and_their_people(): void
    {
        $this->about($this->team() + [
            'team_title' => 'Meet Our Experts’ Team',
            'team_heading' => 'Meet Our Professional Team',
            'team_intro' => 'Dedicated to your journey.',
        ]);

        $this->get('/our-team')
            ->assertOk()
            ->assertSee('Meet Our Experts’ Team')
            ->assertSee('Meet Our Professional Team')
            ->assertSee('Dedicated to your journey.')
            ->assertSeeInOrder(['Jesse Msaghaa', 'Managing Director', 'Five years in travel.', 'LinkedIn', 'Our Sales Team', 'We plan your trip.', 'Simel Laizer', 'Esther Godfrey'])
            ->assertSee('href="https://linkedin.com/in/jesse"', false)
            ->assertDontSee('Empty Group');
    }

    public function test_the_team_page_uses_default_titles_and_initials_when_there_is_no_photo(): void
    {
        $this->about($this->team());

        $this->get('/our-team')
            ->assertSee('Meet Our Experts’ Team')
            ->assertSee('Meet Our Professional Team')
            ->assertSee('team-member__photo--blank', false);

        AboutPage::query()->update(['team_groups' => [['members' => [['name' => 'Asha', 'photo' => 'about/asha.jpg']]]]]);
        $this->get('/our-team')->assertSee('/storage/about/asha.jpg', false)->assertDontSee('team-member__photo--blank', false);
    }

    public function test_the_team_page_is_hidden_until_published_and_without_people(): void
    {
        $this->get('/our-team')->assertOk()->assertSee('coming soon');

        $this->about($this->team() + ['is_published' => false]);
        $this->get('/our-team')->assertSee('coming soon')->assertDontSee('Simel Laizer');

        AboutPage::query()->update(['is_published' => true, 'team_groups' => null]);
        $this->get('/our-team')->assertSee('coming soon');
    }

    public function test_the_team_page_is_in_the_sitemap_only_when_there_is_a_team(): void
    {
        $this->about();
        $this->get('/sitemap.xml')->assertSee(route('about'))->assertDontSee(route('team'));

        AboutPage::query()->update($this->team());
        $this->get('/sitemap.xml')->assertSee(route('team'));
    }

    public function test_the_migration_turns_an_old_team_list_into_the_first_group_and_fixes_the_menu_link(): void
    {
        NavItem::create(['label' => 'About', 'href' => '/about', 'links' => [['label' => 'Our Team', 'href' => '/about#team']]]);

        $migration = require database_path('migrations/2026_11_09_100000_add_about_sections_and_team_page.php');
        $migration->down();
        \Illuminate\Support\Facades\DB::table('about_pages')->insert(['title' => 'About Us', 'team' => json_encode([['name' => 'Asha', 'role' => 'Guide']])]);
        $migration->up();

        $groups = AboutPage::first()->team_groups;
        $this->assertSame('Asha', $groups[0]['members'][0]['name']);
        $this->assertSame('/our-team', NavItem::first()->links[0]['href']);
    }

    public function test_the_seeder_adds_the_about_text_without_overwriting_admin_edits(): void
    {
        $this->seed(AboutPageSeeder::class);

        $page = AboutPage::first();
        $this->assertTrue($page->is_published);
        $this->assertCount(8, $page->highlights);
        $this->assertStringContainsString('local artisans', $page->community_text);
        $this->get('/about')->assertSee('Serengeti Roaming African Safaris is a Tanzania-based safari company')
            ->assertSee('Why Choose Serengeti Roaming African Safaris?')
            ->assertSee('Responsible Tourism');

        $page->update(['intro_heading' => 'Our Story']);
        $this->seed(AboutPageSeeder::class);
        $this->assertSame('Our Story', AboutPage::first()->intro_heading);
        $this->assertSame(1, AboutPage::count());
    }

    public function test_the_seeder_can_replace_existing_text_when_forced(): void
    {
        $this->about(['is_published' => false, 'intro_heading' => 'Old heading', 'intro_body' => 'Old text.', 'highlights' => [['title' => 'Old point']], 'banner_text' => 'Keep this line.']);

        putenv('ABOUT_FORCE=1');
        try {
            $this->seed(AboutPageSeeder::class);
        } finally {
            putenv('ABOUT_FORCE');
        }

        $page = AboutPage::first();
        $this->assertSame('Who Are We?', $page->intro_heading);
        $this->assertStringStartsWith('Serengeti Roaming African Safaris is a Tanzania-based', $page->intro_body);
        $this->assertCount(8, $page->highlights);
        $this->assertSame('Keep this line.', $page->banner_text);
        $this->assertTrue($page->is_published);
        $this->get('/about')->assertSee('Why Choose Serengeti Roaming African Safaris?')->assertDontSee('coming soon');
    }

    public function test_other_pages_keep_their_own_banner(): void
    {
        $this->get('/contact')->assertOk()->assertSee('class="page-banner', false);
        $this->get('/faq')->assertOk()->assertDontSee('about-photo', false);
    }
}
