<?php

namespace App\Support;

use App\Models\Quote;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

/**
 * Builds the quotation PDF (view: pdf.quote).
 */
class QuotePdf
{
    public static function make(Quote $quote): DomPdf
    {
        $quote->loadMissing('package');

        return Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'logo' => self::logo(),
            'accounts' => $quote->show_bank_details ? Setting::bankAccounts() : [],
            'instructions' => $quote->show_bank_details ? Setting::paymentInstructions() : null,
            'terms' => Setting::quoteTerms(),
        ])
            ->setPaper('a4')
            // Only embed the glyphs actually used: keeps a one-page quote around 30 KB instead of ~900 KB.
            ->setOption('isFontSubsettingEnabled', true);
    }

    public static function output(Quote $quote): string
    {
        return self::make($quote)->output();
    }

    /**
     * The logo as a data URI, so dompdf never makes a web request. logo-pdf.png is a
     * 180px copy of the site logo (the full-size one alone would add ~170 KB).
     */
    private static function logo(): ?string
    {
        $file = collect([public_path('images/logo-pdf.png'), public_path('images/Logo-2-croped.png')])->first(fn ($f) => is_file($f));
        if (! $file) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($file));
    }
}
