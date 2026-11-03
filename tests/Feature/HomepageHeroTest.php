<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_is_a_background_video_instead_of_a_carousel(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<video', false)
            ->assertSee('autoplay', false)
            ->assertSee('muted', false)
            ->assertSee('playsinline', false)
            ->assertSee(asset('videos/hero-1080.mp4'))
            ->assertSee(asset('videos/hero-720.mp4'))
            ->assertSee(asset('videos/hero-poster.jpg'))
            ->assertSee('id="heroPause"', false)
            ->assertSee('Watch the film')
            ->assertDontSee('heroThumbs', false)
            ->assertDontSee('aria-roledescription="carousel"', false);
    }

    public function test_the_video_files_exist(): void
    {
        foreach (['hero-1080.mp4', 'hero-720.mp4', 'hero-poster.jpg', 'tanzania-safari-film.mp4'] as $file) {
            $this->assertFileExists(public_path("videos/{$file}"));
        }
    }
}
