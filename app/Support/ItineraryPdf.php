<?php

namespace App\Support;

use App\Models\TourPage;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Str;

/**
 * Builds the downloadable itinerary PDF for a package (view: pdf.itinerary).
 */
class ItineraryPdf
{
    public static function make(TourPage $page): DomPdf
    {
        $pdf = Pdf::loadView('pdf.itinerary', ['page' => $page])->setPaper('a4');

        // CSS can't print the total page count in dompdf, so stamp "Page x of y" after layout.
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text($canvas->get_width() - 100, $canvas->get_height() - 40, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 7, [0.42, 0.42, 0.38]);

        return $pdf;
    }

    public static function filename(TourPage $page): string
    {
        return Str::slug($page->package_name).'-itinerary.pdf';
    }
}
