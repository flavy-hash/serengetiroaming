<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Enums\TourCategory;
use App\Filament\Resources\Reviews\Pages\CreateReview;
use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use App\Models\TourPage;
use App\Models\User;
use Database\Seeders\SampleReviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewsTest extends TestCase
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

    private function review(array $attributes = []): Review
    {
        return Review::create([
            'name' => 'Lena Fischer',
            'country' => 'Germany',
            'trip_date' => 'August 2026',
            'rating' => 5,
            'title' => 'Dhow sunset to remember',
            'body' => 'Our guide knew every corner of Stone Town and the beach days were perfect.',
            'status' => ReviewStatus::Published,
            ...$attributes,
        ]);
    }

    public function test_homepage_keeps_placeholders_until_a_review_is_published(): void
    {
        $this->get('/')->assertOk()->assertSee('Your review could be here')->assertSee(url('/reviews'));

        $this->review(['status' => ReviewStatus::Pending]);
        $this->get('/')->assertSee('Your review could be here')->assertDontSee('Dhow sunset to remember');

        Review::query()->update(['status' => ReviewStatus::Published]);
        $this->get('/')
            ->assertDontSee('Your review could be here')
            ->assertSee('Dhow sunset to remember')
            ->assertSee('Lena Fischer')
            ->assertSee('Germany · August 2026')
            ->assertSee('id="reviewDialog"', false);
    }

    public function test_reviews_page_shows_the_placeholder_cards_until_a_review_is_published(): void
    {
        $this->get('/reviews')
            ->assertOk()
            ->assertSee('Your review could be here')
            ->assertSee('data-read-review', false)
            ->assertSee('id="reviewDialog"', false)
            ->assertDontSee('Based on'); // placeholders never count towards a rating

        $this->review();

        $this->get('/reviews')->assertDontSee('Your review could be here')->assertSee('Dhow sunset to remember');
    }

    public function test_sample_reviews_are_labelled_local_only_and_removable(): void
    {
        Storage::fake('public');
        $this->review(['title' => 'A real one']);

        $this->seed(SampleReviewSeeder::class);
        $this->seed(SampleReviewSeeder::class); // re-running must not duplicate
        $this->assertSame(3, Review::where('email', SampleReviewSeeder::MARKER)->count());
        $this->get('/reviews')->assertSee('Sample Review 1')->assertSee('not from a real guest');

        $this->artisan('reviews:remove-samples')->expectsOutput('Removed 3 sample reviews.')->assertSuccessful();
        $this->assertSame(['A real one'], Review::pluck('title')->all());

        $this->app->detectEnvironment(fn () => 'production');
        $this->expectExceptionMessage('local previews only');
        $this->app->make(SampleReviewSeeder::class)->run();
    }

    public function test_tripadvisor_and_google_badges_link_to_the_listings_below_reviews(): void
    {
        foreach (['/', '/reviews'] as $path) {
            $this->get($path)
                ->assertSee('href="'.config('site.tripadvisor_url').'"', false)
                ->assertSee('href="'.e(config('site.google_reviews_url')).'"', false)
                ->assertSee('target="_blank" rel="noopener"', false)
                ->assertSee(asset('images/tripadvisor.png'))
                ->assertSeeInOrder(['data-review-card', 'Read our reviews on Tripadvisor', 'Read our reviews on Google'], false);
        }

        $this->assertStringContainsString('d34293248', config('site.tripadvisor_url'));
        $this->assertStringContainsString('lrd=0x18371ddf4fe541df:0xcf87298ffccf007', config('site.google_reviews_url'));
    }

    public function test_homepage_shows_featured_reviews_first(): void
    {
        foreach (range(1, 4) as $i) {
            $this->review(['title' => "Review number {$i}"]);
        }
        Review::where('title', 'Review number 1')->update(['is_featured' => true]);

        $this->get('/')
            ->assertSeeInOrder(['Review number 1', 'Review number 4', 'Review number 3'])
            ->assertDontSee('Review number 2');
    }

    public function test_guest_can_submit_a_review_with_photos_for_approval(): void
    {
        Storage::fake('public');
        $page = $this->zanzibar();

        $this->get('/reviews')->assertOk()->assertSee('Write a Review')->assertSee('data-dialog="review"', false);

        $this->post('/reviews', [
            'name' => 'Tom Baker',
            'email' => 'tom@example.com',
            'country' => 'UK',
            'tour_page_id' => $page->id,
            'rating' => 4,
            'title' => 'Great spice tour',
            'body' => 'Loved the spice farm and the beach hotel was lovely.',
            'photos' => [UploadedFile::fake()->image('beach.jpg'), UploadedFile::fake()->image('spices.png')],
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonStructure(['message']);

        $review = Review::sole();
        $this->assertSame(ReviewStatus::Pending, $review->status);
        $this->assertSame($page->id, $review->tour_page_id);
        $this->assertCount(2, $review->photos);
        Storage::disk('public')->assertExists($review->photos[0]);

        $this->get('/reviews')->assertDontSee('Great spice tour');
    }

    public function test_review_form_validation_and_spam_protection(): void
    {
        $this->postJson('/reviews', ['name' => 'X', 'email' => 'bad', 'rating' => 9, 'title' => '', 'body' => 'short'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'rating', 'title', 'body']);

        $this->postJson('/reviews', [
            'name' => 'Bot', 'email' => 'bot@example.com', 'rating' => 5, 'title' => 'Spam',
            'body' => 'This is a long enough spam message body.', 'website' => 'http://spam.example',
        ])->assertUnprocessable();

        $this->postJson('/reviews', [
            'name' => 'Many', 'email' => 'many@example.com', 'rating' => 5, 'title' => 'Photos',
            'body' => 'This is a long enough review body text.',
            'photos' => array_fill(0, 4, UploadedFile::fake()->image('x.jpg')),
        ])->assertUnprocessable()->assertJsonValidationErrors(['photos']);

        $this->assertSame(0, Review::count());
    }

    public function test_reviews_page_shows_average_and_hides_private_details(): void
    {
        $this->review(['rating' => 5, 'email' => 'lena@example.com']);
        $this->review(['rating' => 4, 'title' => 'Very good climb']);
        $this->review(['rating' => 1, 'title' => 'Rejected one', 'status' => ReviewStatus::Rejected]);

        $this->get('/reviews')
            ->assertOk()
            ->assertSee('4.5')
            ->assertSee('Based on 2 reviews')
            ->assertSee('Very good climb')
            ->assertDontSee('Rejected one')
            ->assertDontSee('lena@example.com');
    }

    public function test_package_page_shows_its_reviews(): void
    {
        $page = $this->zanzibar();
        $this->review(['tour_page_id' => $page->id]);
        $this->review(['title' => 'Kilimanjaro review', 'tour_page_id' => null]);

        $this->get('/zanzibar')
            ->assertSee('Reviews of this trip')
            ->assertSee('Dhow sunset to remember')
            ->assertSee('5.0 · 1 review')
            ->assertDontSee('Kilimanjaro review')
            ->assertSee('<option value="'.$page->id.'" selected>', false);
    }

    public function test_admin_approves_features_and_adds_reviews(): void
    {
        $this->actingAs(User::factory()->create());
        $pending = $this->review(['status' => ReviewStatus::Pending, 'title' => 'Pending one']);
        $published = $this->review(['title' => 'Already live']);

        $this->get('/admin/reviews')->assertOk();

        Livewire::test(ListReviews::class)
            ->assertSet('activeTab', 'pending')
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$published])
            ->callTableAction('approve', $pending);

        $this->assertSame(ReviewStatus::Published, $pending->fresh()->status);

        // Featuring only sticks for published reviews.
        $pending->update(['is_featured' => true]);
        Livewire::test(EditReview::class, ['record' => $pending->getRouteKey()])->callAction('reject');
        $this->assertSame(ReviewStatus::Rejected, $pending->fresh()->status);
        $this->assertFalse($pending->fresh()->is_featured);

        Livewire::test(CreateReview::class)
            ->fillForm([
                'name' => 'Priya',
                'rating' => 5,
                'title' => 'Sent by email',
                'body' => 'Copied from a guest email with permission.',
                'status' => ReviewStatus::Published,
                'is_featured' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/')->assertSeeInOrder(['Sent by email', 'Already live']);
    }
}
