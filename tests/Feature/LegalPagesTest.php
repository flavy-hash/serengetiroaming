<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageLegalPages;
use App\Models\Setting;
use App\Models\User;
use App\Support\LegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_page_has_privacy_and_booking_terms_with_a_table_of_contents(): void
    {
        $html = $this->get('/policy')
            ->assertOk()
            ->assertSee('<title>Privacy Policy &amp; Booking Terms', false)
            ->assertSee('id="privacy"', false)
            ->assertSee('id="booking"', false)
            ->assertSee('On this page')
            ->assertSee('href="#privacy-what-we-collect"', false)
            ->assertSee('<h2 id="privacy-what-we-collect">What we collect</h2>', false)
            ->assertSee('href="#booking-cancellations-and-refunds"', false)
            ->assertSee('A 30% deposit is required')
            ->assertSee('The balance is due 60 days before your trip starts.')
            ->assertSee(config('site.email'))
            ->assertSee('Last updated:')
            ->getContent();

        $this->assertStringContainsString('Need help?', $html);
        $this->assertStringNotContainsString('tanzaniamemories', strtolower($html));
    }

    public function test_terms_page_and_old_privacy_address(): void
    {
        $this->get('/terms-and-conditions')
            ->assertOk()
            ->assertSee('<title>Terms &amp; Conditions', false);

        $this->get('/terms-and-conditions')
            ->assertSee('Events outside our control')
            ->assertSee('href="#terms-events-outside-our-control"', false)
            ->assertSee('href="/policy#booking"', false);

        $this->get('/privacy')->assertRedirect('/policy')->assertStatus(301);

        $this->get('/')->assertSee('href="'.route('terms').'"', false)->assertSee('Booking Terms');
        $this->get('/sitemap.xml')->assertSee(route('policy'))->assertSee(route('terms'));
    }

    public function test_admin_edits_the_texts_and_html_is_stripped(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageLegalPages::class)
            ->assertSchemaStateSet(['booking_terms' => LegalDocument::defaultMarkdown('booking')])
            ->fillForm([
                'terms_conditions' => "## Our rules\n\nBe kind. <script>alert(1)</script>\n\n## Our rules\n\n[bad](javascript:alert(1))",
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Only the edited text is stored, so the others keep following the defaults.
        $this->assertNotNull(Setting::get('terms_conditions'));
        $this->assertNull(Setting::get('booking_terms'));

        $this->get('/terms-and-conditions')
            ->assertOk()
            ->assertSee('<h2 id="terms-our-rules">Our rules</h2>', false)
            ->assertSee('<h2 id="terms-our-rules-2">Our rules</h2>', false) // duplicate headings get unique ids
            ->assertSee('Be kind.')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:alert', false)
            ->assertSee('Last updated: '.now()->format('F j, Y'));

        Livewire::test(ManageLegalPages::class)->callAction('reset');
        $this->assertNull(Setting::get('terms_conditions'));
        $this->get('/terms-and-conditions')->assertSee('Using this website');
    }

    public function test_texts_are_required(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageLegalPages::class)
            ->fillForm(['privacy_policy' => ''])
            ->call('save')
            ->assertHasFormErrors(['privacy_policy' => 'required']);
    }
}
