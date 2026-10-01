<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show(): View
    {
        $faqs = Faq::visible()->orderBy('sort_order')->orderByDesc('answered_at')->limit(4)->get();

        return view('pages.contact', compact('faqs'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
            'website' => ['prohibited'], // honeypot: hidden from people, filled in by bots
        ]);

        ContactMessage::create($validated);

        return response()->json([
            'message' => "Thanks for getting in touch — we'll reply within 24 hours.",
        ]);
    }
}
