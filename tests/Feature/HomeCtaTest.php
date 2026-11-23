<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The home page leads visitors to the safaris and to the enquiry form. */
class HomeCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_hero_and_the_closing_band_have_the_two_main_buttons(): void
    {
        $html = $this->get('/')->assertOk()
            ->assertSee('Discover the Magic of Tanzania with Local Safari Experts')
            ->assertSee('Our promise is simple: your adventure, your expectations, and your unforgettable memories are our priority.')
            ->getContent();

        $this->assertSame(2, substr_count($html, '>Explore Safaris</a>'));
        $this->assertSame(2, substr_count($html, '>Plan Your Safari</a>'));
        $this->assertStringContainsString('href="'.url('/safaris').'"', $html);
        $this->assertStringContainsString('href="'.url('/contact').'#contact-form"', $html);
        $this->assertStringContainsString('class="home-cta"', $html);
    }

    public function test_every_page_has_the_email_bar_above_the_menu(): void
    {
        foreach (['/', '/contact', '/safaris'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('class="topbar"', false)
                ->assertSee('href="mailto:info@serengetiroamingafricansafaris.com"', false)
                ->assertSeeInOrder(['class="topbar"', 'id="siteHeader"'], false);
        }
    }

    public function test_supporting_local_communities_sits_under_why_choose_us(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['The details that make a safari go right', 'Supporting Local Communities', 'we are proud to support local communities', 'Learn more about us', 'Featured Packages']);
    }

    public function test_the_community_text_follows_the_admin_when_it_is_set(): void
    {
        \App\Models\AboutPage::create(['title' => 'About Us', 'community_heading' => 'Giving Back', 'community_text' => "First line.\n\nSecond line."]);

        $this->get('/')
            ->assertSee('Giving Back')->assertSee('First line.')->assertSee('Second line.')
            ->assertDontSee('Supporting Local Communities')
            ->assertDontSee('we are proud to support local communities');
    }

    public function test_the_community_section_has_a_photo_default_or_uploaded(): void
    {
        $this->get('/')->assertSee('class="community__photo"', false)->assertSee(asset('images/banners/community.jpg'), false);

        // The About page's team photo does not change it...
        $about = \App\Models\AboutPage::create(['title' => 'About Us', 'intro_image' => 'about/team.jpg']);
        $this->get('/')->assertSee(asset('images/banners/community.jpg'), false)->assertDontSee('/storage/about/team.jpg', false);

        // ...an uploaded community photo wins.
        $about->update(['community_image' => 'about/community.jpg']);
        $this->get('/')->assertSee('/storage/about/community.jpg', false)->assertDontSee(asset('images/banners/community.jpg'), false);
    }
}
