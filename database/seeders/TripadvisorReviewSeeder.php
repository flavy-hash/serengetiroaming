<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Database\Seeder;

/**
 * The guest reviews of Serengeti Roaming African Safaris on Tripadvisor, shown on the home page and
 * /reviews. The words are the guests' own; where a review is long, only its opening sentences are
 * here until the full text is pasted in the admin (Reviews → open the review → Review).
 *
 * Safe to re-run: a review that is already there (same guest and title) is left alone, so edits
 * made in the admin stay. It also clears the local-only "Sample Review" cards.
 *   php artisan db:seed --class=TripadvisorReviewSeeder --force
 */
class TripadvisorReviewSeeder extends Seeder
{
    public function run(): void
    {
        Review::where('email', SampleReviewSeeder::MARKER)->delete();

        $added = 0;

        foreach (self::reviews() as $review) {
            $written = $review['written'];
            unset($review['written']);

            if (Review::where('name', $review['name'])->where('title', $review['title'])->exists()) {
                continue;
            }

            $row = Review::create([...$review, 'rating' => 5, 'status' => ReviewStatus::Published, 'is_featured' => true]);
            $row->forceFill(['created_at' => $written, 'updated_at' => $written])->saveQuietly();
            $added++;
        }

        $this->command?->info("Tripadvisor reviews: {$added} added.");
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function reviews(): array
    {
        return [
            [
                'name' => 'Luke Colin',
                'country' => 'Penarth, United Kingdom',
                'trip_date' => 'August 2026',
                'trip_type' => 'Friends',
                'written' => '2026-08-08 10:00:00',
                'title' => '10-day Northern Circuit safari',
                'body' => 'Our 10-day Northern Circuit safari with Serengeti Roaming African Safaris was truly unforgettable. From the first inquiry through to the final day of the trip, the team was professional, responsive, and attentive to every detail.',
            ],
            [
                'name' => 'Catherine P',
                'country' => null,
                'trip_date' => 'August 2026',
                'trip_type' => 'Family',
                'written' => '2026-09-26 10:00:00',
                'title' => 'Booking a safari from Spain',
                'body' => 'Booking a safari from Spain without being there in person can feel complicated, but Serengeti Roaming African Safaris made the entire process smooth and stress-free.',
            ],
            [
                'name' => 'Passenger62233458905',
                'country' => null,
                'trip_date' => 'June 2026',
                'trip_type' => 'Family',
                'written' => '2026-08-01 10:00:00',
                'title' => '8-day safari',
                'body' => 'Our 8-day safari with Serengeti Roaming African Safaris was one of the best travel experiences we have ever had. From the moment we made our first inquiry, David was professional, responsive, and organized every detail perfectly.',
            ],
        ];
    }
}
