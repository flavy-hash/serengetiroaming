<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Support\GeoCountry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Privacy-friendly visitor statistics. Runs after the response has been sent (terminate),
 * sets no cookies and never stores the IP address: visitors are grouped per day by a
 * one-way hash that changes every day.
 */
class LogPageView
{
    private const BOTS = '/bot|crawl|spider|slurp|preview|headless|curl|wget|python|java\/|httpclient|okhttp|go-http|scrapy|lighthouse|pingdom|uptime|monitor|facebookexternalhit|whatsapp|telegram|discord|semrush|ahrefs|bing|yandex|baidu|duckduck/i';

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if ($this->shouldLog($request, $response)) {
                PageView::create($this->record($request));
            }
        } catch (\Throwable $e) {
            // Statistics must never break the site.
            report($e);
        }
    }

    public function shouldLog(Request $request, Response $response): bool
    {
        if (! config('analytics.enabled')) {
            return false;
        }

        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return false;
        }

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        $route = $request->route()?->getName();
        if (! $route || in_array($route, config('analytics.skip_routes', []), true)) {
            return false;
        }

        if ($request->is('admin', 'admin/*', 'livewire/*', 'livewire-*', 'storage/*', 'media/*', 'chat/*', '*/download', '*.pdf')) {
            return false;
        }

        // Staff browsing the site are not visitors.
        if ($request->user()) {
            return false;
        }

        // Respect "Do Not Track" and "Global Privacy Control".
        if ($request->header('DNT') === '1' || $request->header('Sec-GPC') === '1') {
            return false;
        }

        // Link prefetch / prerender is not a visit.
        $purpose = strtolower($request->header('Sec-Purpose', '').' '.$request->header('Purpose', '').' '.$request->header('X-Moz', ''));
        if (str_contains($purpose, 'prefetch') || str_contains($purpose, 'prerender')) {
            return false;
        }

        $agent = (string) $request->userAgent();

        return $agent !== '' && ! preg_match(self::BOTS, $agent);
    }

    /**
     * @return array<string, mixed>
     */
    public function record(Request $request): array
    {
        $route = (string) $request->route()?->getName();

        return [
            'visitor_hash' => self::visitorHash((string) $request->ip(), (string) $request->userAgent()),
            'path' => Str::limit('/'.ltrim($request->path(), '/'), 490, ''),
            // Route names contain dots, so look them up in the array rather than with config's dot syntax.
            'page_type' => config('analytics.page_types', [])[$route] ?? Str::limit($route, 50, ''),
            'package_id' => $request->attributes->get('viewed_package_id'),
            'device' => self::device((string) $request->userAgent()),
            'referrer_host' => self::referrerHost($request),
            'country' => GeoCountry::forRequest($request),
            'created_at' => now(),
        ];
    }

    /**
     * Groups one visitor's views on one day; changes daily and cannot be turned back into the IP.
     */
    public static function visitorHash(string $ip, string $userAgent, ?string $day = null): string
    {
        return substr(hash_hmac('sha256', $ip.'|'.$userAgent.'|'.($day ?? now()->toDateString()), (string) config('app.key')), 0, 16);
    }

    public static function device(string $userAgent): string
    {
        if (preg_match('/ipad|tablet|kindle|silk|playbook|(android(?!.*mobile))/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobi|iphone|ipod|android|blackberry|opera mini|iemobile|windows phone/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * The referring website ("google.com"), only when it is another site.
     */
    public static function referrerHost(Request $request): ?string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        $bare = fn (?string $h) => strtolower((string) preg_replace('/^www\./i', '', (string) $h));
        $own = [$bare($request->getHost()), $bare(parse_url((string) config('app.url'), PHP_URL_HOST))];

        return in_array($bare($host), $own, true) ? null : Str::limit($bare($host), 250, '');
    }
}
