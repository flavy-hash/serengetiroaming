<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\TourPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::published()->with('tourPage')->showcase()->paginate(12);

        $summary = Review::published()->selectRaw('count(*) as total, avg(rating) as average')->first();

        return view('pages.reviews', [
            'reviews' => $reviews,
            'total' => (int) $summary->total,
            'average' => $summary->total ? round((float) $summary->average, 1) : null,
        ]);
    }

    /**
     * Guest reviews always start as pending and are published from the admin.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'trip_date' => ['nullable', 'string', 'max:50'],
            'tour_page_id' => ['nullable', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'website' => ['prohibited'], // honeypot: hidden from people, filled in by bots
        ], [
            'photos.max' => 'Please choose up to 3 photos.',
            'photos.*.max' => 'Each photo must be 5 MB or smaller.',
            'body.min' => 'Please write a little more about your trip (at least 20 characters).',
        ]);

        $validated['tour_page_id'] = TourPage::published()->whereKey($validated['tour_page_id'] ?? null)->value('id');
        $validated['photos'] = collect($request->file('photos', []))
            ->map(fn ($photo) => $photo->store('reviews', 'public'))
            ->all();
        $validated['status'] = ReviewStatus::Pending;

        unset($validated['website']);
        Review::create($validated);

        return response()->json([
            'message' => 'Thank you for sharing your trip! Your review will appear once our team has checked it.',
        ]);
    }
}
