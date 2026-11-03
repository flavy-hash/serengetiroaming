<?php

use App\Enums\TourCategory;
use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\InstantQuoteController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TourPageController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bookings.store');

// /safaris lists every package; /packages is kept as a redirect for old links.
Route::permanentRedirect('/packages', '/safaris')->name('packages');

// Contact messages and questions are answered in the admin panel.
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/accommodations', [AccommodationController::class, 'index'])->name('accommodations');
Route::get('/accommodations/{slug}', [AccommodationController::class, 'show'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('accommodations.show');

// Parks, mountains and islands, managed in the admin under Website Pages → Destinations.
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations');
Route::get('/destinations/{slug}', [DestinationController::class, 'show'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('destinations.show');

// The fleet (About → Our Vehicles), managed in the admin under Website Pages → Vehicles.
Route::get('/vehicles', VehicleController::class)->name('vehicles');

Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
Route::post('/reviews', [ReviewController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('reviews.store');

Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::post('/faq', [FaqController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('faq.store');

Route::view('/photo-credits', 'pages.photo-credits', [
    'title' => 'Photo Credits',
    'subtitle' => 'Placeholder photography used on this site while our own galleries are being put together.',
])->name('photo-credits');

// Legal pages; texts are edited in the admin under Site Settings → Legal Pages.
Route::get('/policy', [LegalController::class, 'policy'])->name('policy');
Route::get('/terms-and-conditions', [LegalController::class, 'terms'])->name('terms');
Route::permanentRedirect('/privacy', '/policy')->name('privacy');

// Tour sections and the About page are managed in the admin panel (/admin).
Route::get('/about', AboutPageController::class)->name('about');

$categories = array_column(TourCategory::cases(), 'value');

Route::get('/{category}', [TourPageController::class, 'index'])
    ->whereIn('category', $categories)
    ->name('tours.index');

Route::get('/{category}/{slug}/itinerary.pdf', [TourPageController::class, 'pdf'])
    ->whereIn('category', $categories)
    ->middleware('throttle:20,1')
    ->name('tours.pdf');

// Instant quote from a package page; the PDF is downloaded through a signed link (valid 7 days).
Route::post('/{category}/{slug}/quote', [InstantQuoteController::class, 'store'])
    ->whereIn('category', $categories)
    ->middleware('throttle:5,1')
    ->name('tours.quote');

Route::get('/quotes/{quote}/pdf', [InstantQuoteController::class, 'download'])
    ->middleware('signed')
    ->name('quotes.download');

Route::get('/{category}/{slug}', [TourPageController::class, 'show'])
    ->whereIn('category', $categories)
    ->name('tours.show');
