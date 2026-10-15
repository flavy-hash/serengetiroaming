<?php

namespace App\Filament\Resources\PageViews\Pages;

use App\Filament\Resources\PageViews\PageViewResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListPageViews extends ListRecords
{
    protected static string $resource = PageViewResource::class;

    public function getTitle(): string
    {
        return 'Visitor activity';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $days = (int) config('analytics.retention_days', 180);

        return new HtmlString(
            'Anonymous page views from website visitors. Collected: the page, device type, referring website and country. '
            .'<strong>Not collected:</strong> IP addresses, cookies, names or anything that identifies a person; the visitor ID changes every day. '
            .'Logged-in staff, bots and browsers asking not to be tracked are skipped. Views are deleted after '.$days.' days. '
            .'<a href="https://db-ip.com" target="_blank" rel="noopener" class="underline">IP geolocation by DB-IP</a>.'
        );
    }
}
