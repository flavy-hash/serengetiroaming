<?php

namespace Tests\Feature;

use App\Enums\TourCategory;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Mail\FaqAnswered;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\TourPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class FaqAndContactTest extends TestCase
{
    use RefreshDatabase;

    private function zanzibar(): TourPage
    {
        return TourPage::create([
            'category' => TourCategory::Zanzibar,
            'title' => 'Zanzibar',
            'slug' => 'zanzibar-beach-escape',
            'package_name' => 'Zanzibar Beach Escape',
            'is_published' => true,
        ]);
    }

    public function test_package_page_has_an_ask_a_question_popup_linked_to_the_package(): void
    {
        $page = $this->zanzibar();

        $this->get('/zanzibar')
            ->assertOk()
            ->assertSee('data-dialog-open="question"', false)
            ->assertSee('data-dialog="question"', false)
            ->assertSee('name="tour_page_id" value="'.$page->id.'"', false);
    }

    public function test_visitor_question_is_stored_privately_until_answered_and_published(): void
    {
        $page = $this->zanzibar();

        $this->postJson('/faq', [
            'name' => 'Amina',
            'email' => 'amina@example.com',
            'question' => 'Can we see turtles in Nungwi?',
            'tour_page_id' => $page->id,
        ])->assertOk()->assertJsonStructure(['message']);

        $faq = Faq::firstOrFail();
        $this->assertSame($page->id, $faq->tour_page_id);
        $this->assertFalse($faq->is_published);

        $this->get('/faq')->assertOk()->assertDontSee('Can we see turtles in Nungwi?');

        // Publishing without an answer is not allowed.
        $faq->update(['is_published' => true]);
        $this->assertFalse($faq->fresh()->is_published);

        $faq->update(['answer' => 'Yes — the Mnarani turtle aquarium is a short walk away.', 'is_published' => true]);
        $this->assertNotNull($faq->fresh()->answered_at);

        $this->get('/faq')
            ->assertSee('Can we see turtles in Nungwi?')
            ->assertSee('Yes — the Mnarani turtle aquarium is a short walk away.')
            ->assertSee('Zanzibar Beach Escape')
            ->assertDontSee('amina@example.com');

        $this->get('/zanzibar')->assertSee('Questions about this trip')->assertSee('Can we see turtles in Nungwi?');
    }

    public function test_question_form_validation_and_spam_protection(): void
    {
        $this->postJson('/faq', ['name' => 'A', 'email' => 'not-an-email', 'question' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'question']);

        $this->postJson('/faq', ['name' => 'Bot', 'email' => 'bot@example.com', 'question' => 'Buy cheap stuff', 'website' => 'http://spam.example'])
            ->assertUnprocessable();

        // An unknown or unpublished package id is ignored rather than trusted.
        $this->postJson('/faq', ['name' => 'Sam', 'email' => 'sam@example.com', 'question' => 'General question here', 'tour_page_id' => 999])->assertOk();
        $this->assertNull(Faq::firstOrFail()->tour_page_id);
    }

    public function test_admin_answers_a_question_and_emails_the_visitor(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());

        $faq = Faq::create(['name' => 'Amina', 'email' => 'amina@example.com', 'question' => 'Do I need a visa?']);
        $answered = Faq::create(['question' => 'Old one', 'answer' => 'Done', 'is_published' => true]);

        $this->get('/admin/faq')->assertOk();

        Livewire::test(ListFaqs::class)
            ->assertSet('activeTab', 'unanswered')
            ->assertCanSeeTableRecords([$faq])
            ->assertCanNotSeeTableRecords([$answered])
            ->callTableAction('answer', $faq, data: [
                'answer' => 'Most visitors can get one on arrival.',
                'is_published' => true,
                'send_email' => true,
            ])
            ->assertHasNoTableActionErrors();

        $faq->refresh();
        $this->assertSame('Most visitors can get one on arrival.', $faq->answer);
        $this->assertTrue($faq->is_published);

        Mail::assertSent(FaqAnswered::class, fn (FaqAnswered $mail) => $mail->hasTo('amina@example.com'));
        $this->assertStringContainsString('Most visitors can get one on arrival.', (new FaqAnswered($faq))->render());
    }

    public function test_admin_can_add_an_faq_directly(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateFaq::class)
            ->fillForm(['question' => 'What should I pack?', 'answer' => 'Layers, a hat and sunscreen.', 'is_published' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/faq')->assertSee('What should I pack?')->assertSee('Layers, a hat and sunscreen.');
    }

    public function test_contact_page_form_lands_in_the_admin(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Send us a message')
            ->assertSee(config('site.email'))
            ->assertSee('data-ajax-form', false);

        $this->postJson('/contact', [
            'name' => 'Jonas',
            'email' => 'jonas@example.com',
            'subject' => 'Honeymoon in August',
            'message' => 'We would love a Serengeti and Zanzibar combination.',
        ])->assertOk();

        $this->postJson('/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'spam spam', 'website' => 'x'])->assertUnprocessable();

        $message = ContactMessage::sole();
        $this->assertFalse($message->isHandled());

        $this->actingAs(User::factory()->create());
        $this->get('/admin/contact-messages')->assertOk()->assertSee('Honeymoon in August');
        $this->get('/admin/contact-messages/create')->assertNotFound();
        $this->get("/admin/contact-messages/{$message->id}")->assertOk()->assertSee('Serengeti and Zanzibar combination');

        Livewire::test(ListContactMessages::class)->callTableAction('toggleHandled', $message);
        $this->assertTrue($message->fresh()->isHandled());

        Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])->callAction('toggleHandled');
        $this->assertFalse($message->fresh()->isHandled());
    }

    public function test_faq_link_is_in_the_footer(): void
    {
        $this->get('/faq')->assertOk()->assertSee("Can't find your answer?", false)->assertSee(url('/faq'));
    }
}
