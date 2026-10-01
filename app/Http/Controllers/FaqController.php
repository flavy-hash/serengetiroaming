<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\TourPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * /faq — answered questions that have been published in the admin.
     */
    public function index(): View
    {
        $faqs = Faq::visible()
            ->with('tourPage')
            ->orderBy('sort_order')
            ->orderByDesc('answered_at')
            ->get();

        return view('pages.faq', compact('faqs'));
    }

    /**
     * The "Ask a Question" form. Questions are private until answered and published.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'question' => ['required', 'string', 'min:5', 'max:2000'],
            'tour_page_id' => ['nullable', 'integer'],
            'website' => ['prohibited'], // honeypot: hidden from people, filled in by bots
        ]);

        $validated['tour_page_id'] = TourPage::published()->whereKey($validated['tour_page_id'] ?? null)->value('id');

        Faq::create($validated);

        return response()->json([
            'message' => "Thanks! We'll email you an answer within 24 hours — useful answers are also added to our FAQ.",
        ]);
    }
}
