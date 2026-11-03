<?php

namespace App\Http\Controllers;

use App\Enums\TourCategory;
use App\Models\AboutPage;
use App\Models\Faq;
use App\Models\Review;
use App\Models\TourPage;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SeoController extends Controller
{
    /**
     * /sitemap.xml — every public page, for Google Search Console.
     */
    public function sitemap(): Response
    {
        $pages = TourPage::published()->ordered()->get();
        $latest = fn ($query) => $query->max('updated_at') ? Carbon::parse($query->max('updated_at')) : null;

        $urls = collect([
            ['loc' => url('/'), 'lastmod' => $pages->max('updated_at'), 'priority' => '1.0'],
            ['loc' => url('/safaris'), 'lastmod' => $pages->max('updated_at'), 'priority' => '0.9'],
        ]);

        // Section pages (/zanzibar, /kilimanjaro, …) and every package page.
        foreach ($pages as $page) {
            $urls->push(['loc' => $page->url(), 'lastmod' => $page->updated_at, 'priority' => $page->isMain() ? '0.9' : '0.8']);
        }

        $about = AboutPage::first();
        if ($about?->is_published) {
            $urls->push(['loc' => route('about'), 'lastmod' => $about->updated_at, 'priority' => '0.6']);
        }

        $urls->push(['loc' => route('accommodations'), 'lastmod' => $latest(\App\Models\Accommodation::published()), 'priority' => '0.7']);
        foreach (\App\Models\Accommodation::published()->ordered()->get() as $stay) {
            $urls->push(['loc' => $stay->url(), 'lastmod' => $stay->updated_at, 'priority' => '0.5']);
        }
        $urls->push(['loc' => route('reviews'), 'lastmod' => $latest(Review::published()), 'priority' => '0.6']);
        $urls->push(['loc' => route('destinations'), 'lastmod' => $latest(\App\Models\Destination::published()), 'priority' => '0.8']);
        foreach (\App\Models\Destination::published()->ordered()->get() as $destination) {
            $urls->push(['loc' => $destination->url(), 'lastmod' => $destination->updated_at, 'priority' => '0.7']);
        }
        $urls->push(['loc' => route('vehicles'), 'lastmod' => $latest(\App\Models\Vehicle::published()), 'priority' => '0.5']);
        $urls->push(['loc' => route('faq'), 'lastmod' => $latest(Faq::visible()), 'priority' => '0.6']);
        $urls->push(['loc' => route('contact'), 'lastmod' => null, 'priority' => '0.5']);
        $urls->push(['loc' => route('policy'), 'lastmod' => null, 'priority' => '0.2']);
        $urls->push(['loc' => route('terms'), 'lastmod' => null, 'priority' => '0.2']);

        $xml = view('seo.sitemap', ['urls' => $urls->unique('loc')->values()])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * /robots.txt — keep the admin and form endpoints out of search, point to the sitemap.
     */
    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /livewire',
                'Disallow: /*/itinerary.pdf',
                'Allow: /',
                '',
                'Sitemap: '.url('/sitemap.xml'),
            ]
            // Never let a local or staging copy get indexed.
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
