<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\TourPage;
use Database\Seeders\Concerns\CopiesSeedImages;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Three clearly-labelled SAMPLE reviews for previewing the review layouts locally.
 * They are not real guest reviews, so this seeder refuses to run in production.
 *
 *   php artisan db:seed --class=SampleReviewSeeder     add them
 *   php artisan reviews:remove-samples                 remove them
 */
class SampleReviewSeeder extends Seeder
{
    use CopiesSeedImages;

    public const MARKER = 'sample-review@example.com';

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Sample reviews are for local previews only — never publish them on the live site.');
        }

        $trip = fn (string $slug) => TourPage::where('slug', $slug)->value('id');

        $samples = [
            [
                'name' => 'Sample Review 1',
                'trip' => 'zanzibar-beach-escape',
                'photos' => [['zanzibar', 'nungwi.jpg'], ['zanzibar', 'dhow.jpg']],
                'title' => 'Sample: beach & Stone Town',
            ],
            [
                'name' => 'Sample Review 2',
                'trip' => 'machame-route',
                'photos' => [['kilimanjaro', 'kilimanjaro-amboseli.jpg'], ['kilimanjaro', 'mawenzi.jpg']],
                'title' => 'Sample: Kilimanjaro climb',
            ],
            [
                'name' => 'Sample Review 3',
                'trip' => 'ngorongoro-crater-safari',
                'photos' => [['safaris', 'ngorongoro.jpg'], ['safaris', 'lion.jpg']],
                'title' => 'Sample: crater safari',
            ],
        ];

        foreach ($samples as $sample) {
            Review::firstOrCreate(
                ['email' => self::MARKER, 'name' => $sample['name']],
                [
                    'country' => 'Sample',
                    'trip_date' => 'Sample date',
                    'tour_page_id' => $trip($sample['trip']),
                    'rating' => 5,
                    'title' => $sample['title'],
                    'body' => "This is a placeholder review used to preview the layout — it is not from a real guest.\n\nReplace it with real reviews in the admin (Enquiries → Reviews) and remove the samples with: php artisan reviews:remove-samples",
                    'photos' => collect($sample['photos'])->map(fn ($p) => $this->copyImage($p[0], $p[1]))->all(),
                    'status' => ReviewStatus::Published,
                ],
            );
        }
    }
}
