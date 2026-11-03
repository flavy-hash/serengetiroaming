<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_instagram_and_facebook_are_linked_in_the_footer_and_contact_page(): void
    {
        $instagram = e(config('site.social.instagram'));
        $facebook = e(config('site.social.facebook'));

        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.$instagram.'"', false)
            ->assertSee('href="'.$facebook.'"', false)
            ->assertSee('aria-label="Serengeti Roaming on Instagram"', false)
            ->assertDontSee('aria-label="Serengeti Roaming on YouTube"', false) // no link set, so hidden
            ->assertDontSee('href="#" aria-label', false);

        $this->get('/contact')->assertSee('Follow us')->assertSee('href="'.$facebook.'"', false);
    }

    public function test_profiles_are_in_the_search_engine_business_details(): void
    {
        $html = $this->get('/')->getContent();
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $agency = collect(json_decode($m[1], true)['@graph'])->firstWhere('@type', 'TravelAgency');

        $this->assertContains(config('site.social.instagram'), $agency['sameAs']);
        $this->assertContains(config('site.social.facebook'), $agency['sameAs']);
    }

    public function test_social_icons_disappear_when_no_links_are_set(): void
    {
        config(['site.social' => ['instagram' => null, 'facebook' => null]]);

        $this->get('/')->assertDontSee('footer__socials', false);
        $this->get('/contact')->assertDontSee('Follow us');
    }
}
