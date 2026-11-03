<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Support\InquiryNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trip' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'travellers' => ['required', 'integer', 'min:1', 'max:20'],
            'departure_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Saved for the admin, then the team is alerted (bell + email) right after this response.
        InquiryNotifier::notify(Booking::create($validated));

        return response()->json([
            'message' => "Thanks! Your enquiry is in — we'll be in touch within 24 hours.",
        ]);
    }
}
