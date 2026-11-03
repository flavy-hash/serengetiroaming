<?php

use App\Models\PageView;
use App\Models\Review;
use Database\Seeders\SampleReviewSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reviews:remove-samples', function () {
    $count = Review::where('email', SampleReviewSeeder::MARKER)->delete();

    $this->info("Removed {$count} sample ".str('review')->plural($count).'.');
})->purpose('Delete the placeholder reviews added by SampleReviewSeeder');

// Anonymous visitor statistics: delete old page views, refresh the country database monthly.
Schedule::command('model:prune', ['--model' => [PageView::class]])->daily();
Schedule::command('analytics:update-geo-db')->monthlyOn(3, '04:00');
