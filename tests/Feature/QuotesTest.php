<?php

namespace Tests\Feature;

use App\Enums\QuoteSource;
use App\Enums\QuoteStatus;
use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Quotes\Pages\CreateQuote;
use App\Filament\Resources\Quotes\Pages\EditQuote;
use App\Filament\Resources\Quotes\Pages\ListQuotes;
use App\Mail\QuoteMail;
use App\Models\Booking;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\TourPage;
use App\Models\User;
use App\Support\QuotePdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class QuotesTest extends TestCase
{
    use RefreshDatabase;

    private function package(array $attributes = []): TourPage
    {
        return TourPage::create([
            'category' => TourCategory::Zanzibar,
            'title' => 'Zanzibar',
            'slug' => 'beach-escape',
            'package_name' => 'Zanzibar Beach Escape',
            'duration' => '4 Days',
            'tier' => SafariTier::Classic,
            'price_from' => 650,
            'is_published' => true,
            ...$attributes,
        ]);
    }

    private function quote(array $attributes = []): Quote
    {
        return Quote::create([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'adults' => 2,
            'items' => [
                ['description' => 'Safari — adult', 'quantity' => 2, 'unit_price' => 650],
                ['description' => 'Park fees', 'quantity' => 3, 'unit_price' => 70.5],
            ],
            'discount' => 100,
            ...$attributes,
        ]);
    }

    private function bankAccounts(): void
    {
        Setting::put('bank_accounts', [
            ['bank_name' => 'CRDB Bank', 'account_name' => 'Serengeti Roaming Ltd', 'account_number' => '0150123456789', 'swift' => 'CORUTZTZ', 'currency' => 'USD'],
            ['bank_name' => 'Half-filled Bank', 'account_name' => '', 'account_number' => ''],
        ]);
    }

    private function pdfHtml(Quote $quote): string
    {
        return view('pdf.quote', [
            'quote' => $quote,
            'logo' => null,
            'accounts' => $quote->show_bank_details ? Setting::bankAccounts() : [],
            'instructions' => $quote->show_bank_details ? Setting::paymentInstructions() : null,
            'terms' => Setting::quoteTerms(),
        ])->render();
    }

    public function test_reference_and_totals(): void
    {
        $quote = $this->quote();

        $this->assertSame(sprintf('QT-%s-%06d', now()->year, $quote->id), $quote->fresh()->reference);
        $this->assertEquals(1511.5, $quote->subtotal);
        $this->assertEquals(1411.5, $quote->total);
        $this->assertSame('US$ 1,411.50', Quote::money($quote->total));
        $this->assertSame('US$ 650', Quote::money(650));
        $this->assertSame("Serengeti-Roaming-Quote-{$quote->fresh()->reference}.pdf", $quote->fresh()->pdfFilename());

        // The total never goes below zero.
        $quote->discount = 5000;
        $this->assertEquals(0, $quote->total);
    }

    public function test_items_from_a_package_use_the_child_rate(): void
    {
        Setting::put('child_rate', 50);

        $items = Quote::itemsForPackage($this->package(), 2, 1);

        $this->assertSame(['quantity' => 2, 'unit_price' => 650.0], array_intersect_key($items[0], ['quantity' => 1, 'unit_price' => 1]));
        $this->assertSame(['quantity' => 1, 'unit_price' => 325.0], array_intersect_key($items[1], ['quantity' => 1, 'unit_price' => 1]));
        $this->assertSame([], Quote::itemsForPackage($this->package(['slug' => 'no-price', 'price_from' => null]), 2, 0));
    }

    public function test_pdf_contains_the_quote_and_complete_bank_accounts_only(): void
    {
        $this->bankAccounts();
        $quote = $this->quote(['notes' => 'Includes <b>park fees</b>'])->fresh();

        $html = $this->pdfHtml($quote);

        $this->assertStringContainsString('QUOTATION', $html);
        $this->assertStringContainsString($quote->reference, $html);
        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('US$ 1,411.50', $html);
        $this->assertStringContainsString('Includes &lt;b&gt;park fees&lt;/b&gt;', $html);
        $this->assertStringContainsString('CRDB Bank', $html);
        $this->assertStringContainsString('Payment reference: '.$quote->reference, $html);
        $this->assertStringNotContainsString('Half-filled Bank', $html);

        $pdf = QuotePdf::output($quote);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_bank_details_can_be_hidden_per_quote(): void
    {
        $this->bankAccounts();
        $quote = $this->quote(['show_bank_details' => false])->fresh();

        $html = $this->pdfHtml($quote);

        $this->assertStringNotContainsString('CRDB Bank', $html);
        $this->assertStringNotContainsString('Payment details', $html);
    }

    public function test_admin_saves_site_settings(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SiteSettings::class)
            ->fillForm([
                'bank_accounts' => [['bank_name' => 'NMB', 'account_name' => 'Serengeti Roaming Ltd', 'account_number' => '2233', 'currency' => 'TZS']],
                'payment_instructions' => 'Use the reference.',
                'quote_validity_days' => 21,
                'child_rate' => 60,
                'quote_terms' => 'Deposit 30%.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('NMB', Setting::bankAccounts()[0]['bank_name']);
        $this->assertSame(21, Setting::quoteValidityDays());
        $this->assertSame(0.6, Setting::childRate());
        $this->assertSame('Deposit 30%.', Setting::quoteTerms());
    }

    public function test_admin_creates_a_quote_from_a_booking(): void
    {
        $this->actingAs(User::factory()->create());
        $package = $this->package();
        $booking = Booking::create([
            'trip' => $package->bookingLabel(),
            'name' => 'Sam Traveller',
            'email' => 'sam@example.test',
            'phone' => '+1 555 0100',
            'travellers' => 3,
            'departure_date' => '2026-12-01',
            'notes' => 'Honeymoon',
        ]);

        Livewire::withQueryParams(['booking' => $booking->id])
            ->test(CreateQuote::class)
            ->assertSchemaStateSet([
                'customer_name' => 'Sam Traveller',
                'customer_email' => 'sam@example.test',
                'package_id' => $package->id,
                'adults' => 3,
                'travel_start' => '2026-12-01',
                'travel_end' => '2026-12-04', // 4-day package: start + 3
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $quote = Quote::firstOrFail();
        $this->assertSame($booking->id, $quote->booking_id);
        $this->assertSame(QuoteSource::Admin, $quote->source);
        $this->assertEquals(1950, $quote->total); // 3 × $650
        $this->assertSame(now()->addDays(14)->toDateString(), $quote->valid_until->toDateString());
    }

    public function test_picking_a_package_fills_empty_items_only(): void
    {
        $this->actingAs(User::factory()->create());
        $package = $this->package();

        $component = Livewire::test(CreateQuote::class)
            ->fillForm(['adults' => 2, 'children' => 0])
            ->set('data.package_id', $package->id);

        $items = array_values($component->get('data.items'));
        $this->assertCount(1, $items);
        $this->assertEquals(650, $items[0]['unit_price']);
        $this->assertEquals(2, $items[0]['quantity']);
    }

    public function test_download_and_email_actions(): void
    {
        $this->actingAs(User::factory()->create());
        Mail::fake();
        $quote = $this->quote()->fresh();

        Livewire::test(ListQuotes::class)
            ->assertCanSeeTableRecords([$quote])
            ->callTableAction('downloadPdf', $quote)
            ->assertFileDownloaded($quote->pdfFilename());

        Livewire::test(EditQuote::class, ['record' => $quote->getRouteKey()])
            ->callAction('emailCustomer')
            ->assertNotified("Quote sent to {$quote->customer_email}");

        Mail::assertSent(QuoteMail::class, fn (QuoteMail $mail) => $mail->hasTo('jane@example.test')
            && ! $mail->forOffice
            && collect($mail->attachments())->contains(fn ($a) => $a->as === $quote->pdfFilename() && $a->mime === 'application/pdf'));
        $this->assertSame(QuoteStatus::Sent, $quote->fresh()->status);
    }

    public function test_a_failed_email_does_not_mark_the_quote_sent(): void
    {
        $this->actingAs(User::factory()->create());
        $quote = $this->quote()->fresh();

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        Livewire::test(EditQuote::class, ['record' => $quote->getRouteKey()])
            ->callAction('emailCustomer')
            ->assertNotified('The email could not be sent');

        $this->assertSame(QuoteStatus::Draft, $quote->fresh()->status);
    }

    public function test_visitor_gets_an_instant_quote(): void
    {
        Mail::fake();
        $admin = User::factory()->create();
        $page = $this->package();

        $response = $this->post(route('tours.quote', ['category' => 'zanzibar', 'slug' => 'beach-escape']), [
            'name' => 'Ana Visitor',
            'email' => 'ana@example.test',
            'country' => 'Spain',
            'travel_date' => now()->addMonth()->toDateString(),
            'adults' => 2,
            'children' => 1,
            'website' => '',
        ]);

        $response->assertRedirect($page->url().'#instant-quote');
        $quote = Quote::firstOrFail();
        $this->assertSame(QuoteSource::Website, $quote->source);
        $this->assertSame($page->id, $quote->package_id);
        $this->assertCount(2, $quote->items);
        $this->assertSame(now()->addMonth()->addDays(3)->toDateString(), $quote->travel_end->toDateString());
        $this->assertSame(config('quotes.instant_note'), $quote->notes);

        Mail::assertSent(QuoteMail::class, fn (QuoteMail $mail) => $mail->forOffice && $mail->hasTo(config('site.email')));
        $this->assertSame(1, $admin->notifications()->count());
        $this->assertStringContainsString($quote->reference, $admin->notifications()->first()->data['title']);

        $result = session('instant_quote');
        $this->get($page->url())
            ->assertSee('Your quote is ready')
            ->assertSee('data-open-on-load', false)
            ->assertSee(e($result['download_url']), false);

        $this->get($result['download_url'])->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_quote_links_must_be_signed_and_expire_after_7_days(): void
    {
        $quote = $this->quote();
        $url = URL::temporarySignedRoute('quotes.download', now()->addDays(7), ['quote' => $quote]);

        $this->get(route('quotes.download', $quote))->assertForbidden();
        $this->get($url.'x')->assertForbidden();

        $this->travel(8)->days();
        $this->get($url)->assertForbidden();
    }

    public function test_instant_quote_validation_and_honeypot(): void
    {
        Mail::fake();
        $page = $this->package();
        $url = route('tours.quote', ['category' => 'zanzibar', 'slug' => 'beach-escape']);

        $this->post($url, ['name' => 'Ana', 'email' => 'ana@example.test', 'travel_date' => now()->subDay()->toDateString(), 'adults' => 2])
            ->assertRedirect($page->url().'#instant-quote')
            ->assertSessionHasErrors(['travel_date'], null, 'instantQuote');

        $this->get($page->url())
            ->assertSee('Please choose a travel date from today onwards.')
            ->assertSee('data-open-on-load', false)
            ->assertSee('value="Ana"', false);

        $this->post($url, ['name' => 'Bot', 'email' => 'bot@example.test', 'travel_date' => now()->addWeek()->toDateString(), 'adults' => 2, 'website' => 'http://spam.test'])
            ->assertSessionHasErrors(['website'], null, 'instantQuote');

        $this->assertSame(0, Quote::count());
        Mail::assertNothingSent();
    }

    public function test_instant_quote_needs_a_priced_published_package(): void
    {
        $this->package(['slug' => 'on-request', 'price_from' => null]);

        $this->get('/zanzibar/on-request')->assertOk()->assertDontSee('Get an Instant Quote');
        $this->post(route('tours.quote', ['category' => 'zanzibar', 'slug' => 'on-request']), [])->assertNotFound();

        $this->package(['slug' => 'priced']);
        $this->get('/zanzibar/priced')->assertOk()->assertSee('Get an Instant Quote');
    }
}
