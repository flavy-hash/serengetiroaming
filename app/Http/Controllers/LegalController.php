<?php

namespace App\Http\Controllers;

use App\Models\PageBanner;
use App\Support\LegalDocument;
use Illuminate\Contracts\View\View;

/**
 * The legal pages. Texts are edited in the admin under Site Settings → Legal Pages;
 * the banners under Site Settings → Page Banners.
 */
class LegalController extends Controller
{
    /**
     * /policy — Privacy Policy and Booking Terms & Conditions on one page.
     */
    public function policy(): View
    {
        return view('pages.legal', [
            'banner' => PageBanner::for('policy'),
            'pageTitle' => 'Privacy Policy & Booking Terms',
            'cta' => 'Read the policy',
            'documents' => [LegalDocument::render('privacy'), LegalDocument::render('booking')],
        ]);
    }

    /**
     * /terms-and-conditions
     */
    public function terms(): View
    {
        return view('pages.legal', [
            'banner' => PageBanner::for('terms'),
            'pageTitle' => 'Terms & Conditions',
            'cta' => 'Read the terms',
            'documents' => [LegalDocument::render('terms')],
        ]);
    }
}
