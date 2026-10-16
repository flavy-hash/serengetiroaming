<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Query-string filters shared by the listing pages (/safaris, /accommodations).
 * Menu links such as /safaris?tier=luxury open a page already filtered.
 */
class ListingFilters
{
    /**
     * The query value, but only if it is a known option key, so an unknown or
     * outdated value (?location=atlantis) simply shows everything.
     *
     * @param  array<string, string>  $options  key => label
     */
    public static function activeFilter(mixed $value, array $options): ?string
    {
        return is_string($value) && array_key_exists($value, $options) ? $value : null;
    }

    /**
     * The "chips" above a list of cards: an "All …" chip first, then one per option.
     *
     * @param  array<string, string>  $options  key => label
     * @param  array<string, mixed>  $keep  other active filters to keep in each link
     * @return array<int, array{label: string, value: string, url: string, active: bool}>
     */
    public static function filterLinks(string $route, string $param, array $options, ?string $active, string $allLabel, array $keep = []): array
    {
        $url = fn (?string $value) => route($route, array_filter([...Arr::except($keep, $param), $param => $value], fn ($v) => filled($v)));

        $links = [['label' => $allLabel, 'value' => '', 'url' => $url(null), 'active' => $active === null]];

        foreach ($options as $value => $label) {
            $links[] = ['label' => $label, 'value' => (string) $value, 'url' => $url((string) $value), 'active' => $active === (string) $value];
        }

        return $links;
    }
}
