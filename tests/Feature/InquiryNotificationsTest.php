<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Mail\InquiryReceived;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class InquiryNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->admin = User::factory()->create();
    }

    private function booking(array $overrides = []): void
    {
        $this->postJson('/bookings', [
            'trip' => 'Great Migration Luxury Safari · 8 Days',
            'name' => 'Jane Traveller',
            'email' => 'jane@example.test',
            'phone' => '+44 7700 900123',
            'travellers' => 2,
            'departure_date' => now()->addMonths(3)->toDateString(),
            'notes' => 'Honeymoon, please!',
            ...$overrides,
        ])->assertOk();
    }

    public function test_a_booking_enquiry_is_saved_alerts_the_admin_and_is_emailed(): void
    {
        $this->booking();

        $booking = Booking::sole();

        // Admin bell.
        $alert = $this->admin->notifications()->sole();
        $this->assertSame('New booking enquiry from Jane Traveller', $alert->data['title']);
        $this->assertStringContainsString('/admin/bookings/'.$booking->id, json_encode($alert->data, JSON_UNESCAPED_SLASHES));

        // Email to the office, replying to the visitor.
        Mail::assertSent(InquiryReceived::class, function (InquiryReceived $mail) use ($booking) {
            $html = $mail->render();

            return $mail->hasTo(config('site.email'))
                && $mail->hasReplyTo('jane@example.test', 'Jane Traveller')
                && $mail->hasSubject('New booking enquiry from Jane Traveller')
                && str_contains($html, 'Great Migration Luxury Safari')
                && str_contains($html, 'Honeymoon, please!')
                && str_contains($html, '/admin/bookings/'.$booking->id);
        });
    }

    public function test_contact_messages_and_questions_are_emailed_too(): void
    {
        $this->postJson('/contact', ['name' => 'Sam', 'email' => 'sam@example.test', 'subject' => 'Stay: Serena', 'message' => 'Do you have rooms in July?'])->assertOk();
        $this->postJson('/faq', ['name' => 'Ana', 'email' => 'ana@example.test', 'question' => 'Is the Serengeti good in April?'])->assertOk();

        $this->assertSame(1, ContactMessage::count());
        $this->assertSame(1, Faq::count());
        $this->assertSame(2, $this->admin->notifications()->count());

        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $m) => $m->hasSubject('New message from Sam') && $m->hasReplyTo('sam@example.test'));
        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $m) => $m->hasSubject('New question from Ana') && str_contains($m->render(), 'Is the Serengeti good in April?'));
        Mail::assertSent(InquiryReceived::class, 2);
    }

    public function test_recipients_come_from_site_settings_and_emails_can_be_switched_off(): void
    {
        Setting::put('inquiry_emails', ['office@example.test', 'owner@example.test', 'not-an-email']);
        $this->booking();

        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $m) => $m->hasTo('office@example.test') && $m->hasTo('owner@example.test') && ! $m->hasTo(config('site.email')));

        Setting::put('inquiry_email_enabled', false);
        $this->booking(['name' => 'Second Person']);

        Mail::assertSent(InquiryReceived::class, 1);              // no email the second time…
        $this->assertSame(2, $this->admin->notifications()->count()); // …but the bell still rings
    }

    public function test_a_mail_failure_never_breaks_the_form(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->booking();

        $this->assertSame(1, Booking::count());
        $this->assertSame(1, $this->admin->notifications()->count());
    }

    public function test_visitor_text_is_escaped_in_the_email(): void
    {
        $this->booking(['name' => '<script>alert(1)</script>', 'notes' => '<img src=x onerror=alert(1)>']);

        Mail::assertSent(InquiryReceived::class, function (InquiryReceived $mail) {
            $html = $mail->render();

            return ! str_contains($html, '<script>alert(1)</script>')
                && ! str_contains($html, '<img src=x')
                && str_contains($html, '&lt;script&gt;');
        });
    }

    public function test_admin_sets_recipients_and_sends_a_test_email(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(SiteSettings::class)
            ->assertSchemaStateSet(['inquiry_email_enabled' => true, 'inquiry_emails' => [config('site.email')]])
            ->fillForm(['inquiry_emails' => ['bookings@example.test', 'wrong']])
            ->call('save')
            ->assertHasFormErrors();

        Livewire::test(SiteSettings::class)
            ->fillForm(['inquiry_emails' => ['bookings@example.test']])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame(['bookings@example.test'], Setting::inquiryRecipients());

        Livewire::test(SiteSettings::class)
            ->callAction('testEmail')
            ->assertNotified('Test email sent to bookings@example.test');

        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $m) => $m->hasTo('bookings@example.test') && $m->hasSubject('Test: enquiry emails are working'));
    }
}
