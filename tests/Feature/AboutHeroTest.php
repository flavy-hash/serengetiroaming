<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutHeroTest extends TestCase
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

    public function test_the_about_page_has_the_split_hero(): void
    {
        $this->about();

        $this->get('/about')
            ->assertOk()
            ->assertSee('class="about-hero"', false)
            ->assertSee('About <em>Us</em>', false)   // last word in italic
            ->assertSee('Locally owned, globally trusted, best prices.')
            ->assertSee('Arusha · Tanzania')
            ->assertSee('Karibu')
            ->assertSee('about-hero__arch', false)
            ->assertSee('about-hero__frame', false)
            ->assertDontSee('class="page-banner', false);
    }

    public function test_default_photos_are_used_until_some_are_uploaded(): void
    {
        $this->about();

        $this->get('/about')
            ->assertSee(asset('images/banners/about.jpg'), false)
            ->assertSee(asset('images/banners/about-frame.jpg'), false);

        $this->about_with_photos();
    }

    private function about_with_photos(): void
    {
        AboutPage::query()->update(['banner_image' => 'about/hero.jpg', 'intro_image' => 'about/intro.jpg']);

        $this->get('/about')
            ->assertSee('/storage/about/hero.jpg', false)
            ->assertSee('/storage/about/intro.jpg', false)
            ->assertDontSee(asset('images/banners/about.jpg'), false);
    }

    public function test_buttons_follow_whether_there_is_a_team(): void
    {
        $this->about();
        $this->get('/about')->assertSee('Plan my trip')->assertSee('See our safaris')->assertDontSee('Meet the team');

        AboutPage::query()->update(['team' => [['name' => 'Asha', 'role' => 'Guide']]]);
        $this->get('/about')->assertSee('href="#team"', false)->assertSee('Meet the team');
    }

    public function test_a_one_word_heading_is_not_split_and_text_is_escaped(): void
    {
        $this->about(['title' => 'Karibuni', 'banner_text' => '<script>alert(1)</script>']);

        $this->get('/about')
            ->assertOk()
            ->assertDontSee('<em>', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_other_pages_keep_their_own_banner(): void
    {
        $this->get('/contact')->assertOk()->assertSee('class="page-banner', false)->assertDontSee('about-hero', false);
        $this->get('/faq')->assertOk()->assertDontSee('about-hero', false);
    }
}
