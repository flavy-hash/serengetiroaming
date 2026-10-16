<?php

namespace App\Support;

use App\Enums\SafariTier;
use App\Models\NavItem;
use App\Models\TourPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Works out the preview (photo, title, text, button) shown in a mega menu panel when a
 * visitor hovers one of its links. Admins can set a preview per link in the Navigation
 * admin; otherwise it is taken from what the link points to:
 *   - a package page                        → its photo, name and short description
 *   - an Explore card anchor on a package    → that card
 *   - a style on /safaris (#luxury, ?tier=)  → the style description
 * and when nothing matches, the menu item's own panel is shown.
 *
 * Images are kept as storage paths (turned into URLs when rendering), and the result is
 * cached until a menu item or package changes.
 */
class NavPreview
{
    public const CACHE_KEY = 'navbar.previews';

    /** @var array<string, TourPage>|null path => page */
    private ?array $pages = null;

    /**
     * @param  Collection<int, NavItem>  $menus
     * @return array<int, array{default: array<string, mixed>, links: array<int, array<string, mixed>>}>
     */
    public static function panels(Collection $menus): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () use ($menus) {
            $resolver = new self;

            return $menus->filter->hasDropdown()->mapWithKeys(fn (NavItem $item) => [$item->id => [
                'default' => $resolver->default($item),
                'links' => collect($item->links)->map(fn (array $link) => $resolver->forLink($item, $link))->all(),
            ]])->all();
        });
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{title: string, text: ?string, cta: string, href: string, image: ?string}
     */
    public function default(NavItem $item): array
    {
        return [
            'title' => $item->title ?: $item->label,
            'text' => $item->description,
            'cta' => $item->cta_label ?: 'Explore '.$item->label,
            'href' => $item->href,
            'image' => $item->image,
        ];
    }

    /**
     * @param  array<string, mixed>  $link
     * @return array{title: string, text: ?string, cta: string, href: string, image: ?string}
     */
    public function forLink(NavItem $item, array $link): array
    {
        $href = (string) ($link['href'] ?? '#');
        $base = $this->derived($href, $item) ?? $this->default($item);

        // Anything the admin typed for this link wins over the automatic preview.
        $pick = fn (string $field, string $key) => filled($link[$field] ?? null) ? $link[$field] : $base[$key];

        return [
            'title' => $pick('preview_title', 'title'),
            'text' => $pick('preview_text', 'text'),
            'cta' => $pick('preview_cta', 'cta'),
            'href' => $href,
            'image' => $pick('preview_image', 'image'),
        ];
    }

    /**
     * @return array{title: string, text: ?string, cta: string, href: string, image: ?string}|null
     */
    private function derived(string $href, NavItem $item): ?array
    {
        $parts = parse_url($href) ?: [];
        $path = '/'.trim($parts['path'] ?? '', '/');
        parse_str($parts['query'] ?? '', $query);
        $fragment = $parts['fragment'] ?? null;

        // A style on the all-packages page: /safaris#luxury or /safaris?tier=luxury.
        $tier = SafariTier::tryFrom((string) ($query['tier'] ?? $fragment ?? ''));
        if ($path === '/safaris' && $tier) {
            return [
                'title' => $tier->getLabel().' Safaris',
                'text' => $tier->getDescription(),
                'cta' => 'See '.Str::lower($tier->getLabel()).' trips',
                'href' => $href,
                'image' => $item->image,
            ];
        }

        $page = $this->pages()[$path] ?? null;
        if (! $page) {
            return null;
        }

        if ($fragment) {
            // An Explore card on that page (/zanzibar#stone-town).
            $card = collect($page->experiences)->firstWhere('anchor', $fragment);

            return $card ? [
                'title' => $card['title'] ?? $page->package_name,
                'text' => $card['copy'] ?? null,
                'cta' => 'Discover',
                'href' => $href,
                'image' => ($card['image'] ?? null) ?: $page->banner_image,
            ] : null;
        }

        return [
            'title' => $page->package_name.($page->duration ? ' · '.$page->duration : ''),
            'text' => $page->card_summary ?: Str::limit((string) ($page->overview_body ?: $page->banner_text), 160),
            'cta' => 'View package',
            'href' => $href,
            'image' => $page->banner_image ?: $item->image,
        ];
    }

    /**
     * @return array<string, TourPage>
     */
    private function pages(): array
    {
        return $this->pages ??= TourPage::published()->ordered()->get()
            ->mapWithKeys(fn (TourPage $p) => [parse_url($p->url(), PHP_URL_PATH) ?: '/' => $p])
            ->all();
    }
}
