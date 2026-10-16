<?php

namespace App\Support;

use Illuminate\Http\Request;
use MaxMind\Db\Reader;

/**
 * The visitor's country (ISO-2), from Cloudflare's header or the local DB-IP database.
 * Only the country code is ever kept — never the IP address.
 */
class GeoCountry
{
    private static ?Reader $reader = null;

    private static ?string $readerPath = null;

    public static function forRequest(Request $request): ?string
    {
        $header = strtoupper(trim((string) $request->header('CF-IPCountry')));
        if (preg_match('/^[A-Z]{2}$/', $header) && ! in_array($header, ['XX', 'T1'], true)) {
            return $header;
        }

        return self::lookup((string) $request->ip());
    }

    public static function lookup(string $ip, ?string $database = null): ?string
    {
        // Private, loopback and reserved addresses have no country.
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        $reader = self::reader($database ?? config('analytics.geo_db_path'));
        if (! $reader) {
            return null;
        }

        try {
            $record = $reader->get($ip);
        } catch (\Throwable) {
            return null;
        }

        $code = strtoupper((string) ($record['country']['iso_code'] ?? ''));

        return preg_match('/^[A-Z]{2}$/', $code) ? $code : null;
    }

    private static function reader(?string $path): ?Reader
    {
        if (blank($path) || ! is_file($path)) {
            return null;
        }

        if (self::$reader && self::$readerPath === $path) {
            return self::$reader;
        }

        try {
            self::$reader = new Reader($path);
            self::$readerPath = $path;
        } catch (\Throwable) {
            return null;
        }

        return self::$reader;
    }

    /**
     * Forget the open database (after it has been replaced).
     */
    public static function reset(): void
    {
        self::$reader?->close();
        self::$reader = null;
        self::$readerPath = null;
    }
}
