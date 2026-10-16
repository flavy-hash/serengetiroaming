<?php

namespace App\Support;

/**
 * Turns a package itinerary into points on a map.
 *
 * Each day has an optional `map_place`:
 *   - 'custom'      → the day's own map_lat / map_lng (and map_name)
 *   - 'none'        → not shown on the map
 *   - a place key   → that place from config/places.php
 *   - empty         → guessed from the day title, else the photo caption, else the overnight area
 */
class RouteMap
{
    public const CUSTOM = 'custom';

    public const NONE = 'none';

    /**
     * @return array<string, array{name: string, lat: float, lng: float, match: array<int, string>}>
     */
    public static function places(): array
    {
        return config('places', []);
    }

    /**
     * The place key a piece of text mentions. The place mentioned last wins
     * ("Serengeti to Ngorongoro" → Ngorongoro); for matches ending at the same point
     * the longer, more specific phrase wins ("Northern Serengeti" over "Serengeti").
     */
    public static function guess(?string $text): ?string
    {
        if (blank($text)) {
            return null;
        }

        $best = null; // [end, length, key]
        foreach (self::places() as $key => $place) {
            foreach ($place['match'] ?? [] as $keyword) {
                $pattern = '/(?<![\pL\pN])'.preg_quote($keyword, '/').'(?![\pL\pN])/iu';
                if (! preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                    continue;
                }
                foreach ($matches[0] as [$found, $offset]) {
                    $candidate = [$offset + strlen($found), strlen($found), $key];
                    if ($best === null || $candidate[0] > $best[0] || ($candidate[0] === $best[0] && $candidate[1] > $best[1])) {
                        $best = $candidate;
                    }
                }
            }
        }

        return $best[2] ?? null;
    }

    /**
     * The guessed place key for a day (title, then photo caption, then overnight area).
     *
     * @param  array<string, mixed>  $day
     */
    public static function guessForDay(array $day): ?string
    {
        foreach (['title', 'hero_caption', 'accommodation'] as $field) {
            if ($key = self::guess($day[$field] ?? null)) {
                return $key;
            }
        }

        return null;
    }

    public static function validCoordinates(mixed $lat, mixed $lng): bool
    {
        return is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180;
    }

    /**
     * Where a day is on the map, or null if it isn't shown.
     *
     * @param  array<string, mixed>  $day
     * @return array{key: string, name: string, lat: float, lng: float}|null
     */
    public static function resolve(array $day): ?array
    {
        $choice = (string) ($day['map_place'] ?? '');

        if ($choice === self::NONE) {
            return null;
        }

        if ($choice === self::CUSTOM) {
            if (! self::validCoordinates($day['map_lat'] ?? null, $day['map_lng'] ?? null)) {
                return null;
            }
            $lat = round((float) $day['map_lat'], 5);
            $lng = round((float) $day['map_lng'], 5);

            return [
                'key' => "custom:{$lat},{$lng}",
                'name' => filled($day['map_name'] ?? null) ? (string) $day['map_name'] : (string) ($day['title'] ?? 'Custom place'),
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        $key = ($choice !== '' && isset(self::places()[$choice])) ? $choice : self::guessForDay($day);
        if (! $key) {
            return null;
        }

        $place = self::places()[$key];

        return ['key' => $key, 'name' => $place['name'], 'lat' => (float) $place['lat'], 'lng' => (float) $place['lng']];
    }

    /**
     * Route stops in day order; consecutive days at the same place are merged ("Day 3-4").
     *
     * @param  array<int, array<string, mixed>>|null  $itinerary
     * @return array<int, array{label: string, name: string, lat: float, lng: float, from: int, to: int}>
     */
    public static function stops(?array $itinerary): array
    {
        $stops = [];

        foreach (array_values($itinerary ?? []) as $i => $day) {
            $point = is_array($day) ? self::resolve($day) : null;
            if (! $point) {
                continue;
            }

            $n = $i + 1;
            $last = array_key_last($stops);

            if ($last !== null && $stops[$last]['key'] === $point['key'] && $stops[$last]['to'] === $n - 1) {
                $stops[$last]['to'] = $n;
            } else {
                $stops[] = [...$point, 'from' => $n, 'to' => $n];
            }
        }

        return array_map(fn (array $s) => [
            'label' => $s['from'] === $s['to'] ? "Day {$s['from']}" : "Day {$s['from']}-{$s['to']}",
            'name' => $s['name'],
            'lat' => $s['lat'],
            'lng' => $s['lng'],
            'from' => $s['from'],
            'to' => $s['to'],
        ], $stops);
    }

    /**
     * Keys of the listed places an itinerary visits (custom and hidden days are ignored).
     * Used to find the packages that go to a destination.
     *
     * @param  array<int, array<string, mixed>>|null  $itinerary
     * @return array<int, string>
     */
    public static function placeKeys(?array $itinerary): array
    {
        return collect($itinerary ?? [])
            ->filter(fn ($day) => is_array($day))
            ->map(fn (array $day) => self::resolve($day)['key'] ?? null)
            ->filter(fn ($key) => $key && isset(self::places()[$key]))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * "Day 1 Arusha → Day 2 Tarangire National Park → …" (text fallback and SEO).
     *
     * @param  array<int, array{label: string, name: string}>  $stops
     */
    public static function describe(array $stops): string
    {
        return implode(' → ', array_map(fn (array $s) => $s['label'].' '.$s['name'], $stops));
    }

    /**
     * Map tiles: Mapbox when MAPBOX_TOKEN is set, otherwise OpenStreetMap.
     *
     * @return array{url: string, attribution: string, tileSize: int, zoomOffset: int, maxZoom: int}
     */
    public static function tiles(): array
    {
        $token = config('services.mapbox.token');

        if (filled($token)) {
            return [
                'url' => 'https://api.mapbox.com/styles/v1/mapbox/outdoors-v12/tiles/512/{z}/{x}/{y}@2x?access_token='.urlencode($token),
                'attribution' => '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                'tileSize' => 512,
                'zoomOffset' => -1,
                'maxZoom' => 18,
            ];
        }

        return [
            'url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            'attribution' => '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            'tileSize' => 256,
            'zoomOffset' => 0,
            'maxZoom' => 19,
        ];
    }

    /**
     * Options for the admin's "Map location" select.
     *
     * @return array<string, string>
     */
    public static function selectOptions(): array
    {
        return [
            '' => 'Automatic (from the day title)',
            ...collect(self::places())->map(fn (array $p) => $p['name'])->sort()->all(),
            self::CUSTOM => 'Other place (enter coordinates)',
            self::NONE => "Don't show on the map",
        ];
    }
}
