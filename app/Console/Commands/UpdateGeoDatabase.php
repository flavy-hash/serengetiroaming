<?php

namespace App\Console\Commands;

use App\Support\GeoCountry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use MaxMind\Db\Reader;

/**
 * Downloads the free DB-IP "IP to Country Lite" database used for visitor countries.
 * Tries this month's file, then last month's; the new file only replaces the old one
 * once it has been checked. Scheduled monthly (routes/console.php).
 */
class UpdateGeoDatabase extends Command
{
    protected $signature = 'analytics:update-geo-db';

    protected $description = 'Download the latest DB-IP country database for visitor statistics';

    public function handle(): int
    {
        $target = config('analytics.geo_db_path');
        File::ensureDirectoryExists(dirname($target));

        foreach ([now(), now()->subMonthNoOverflow()] as $month) {
            $url = str_replace('{month}', $month->format('Y-m'), config('analytics.geo_db_url'));
            $gz = $target.'.download.gz';
            $tmp = $target.'.new';

            try {
                $this->line("Downloading {$url}");
                $response = Http::timeout(180)->get($url);

                if (! $response->successful()) {
                    $this->warn("Not available ({$response->status()}).");

                    continue;
                }

                File::put($gz, $response->body());

                $this->gunzip($gz, $tmp);

                if (! $this->isValid($tmp)) {
                    $this->warn('The downloaded file is not a valid country database.');

                    continue;
                }

                // Same directory, so the swap is a single atomic rename.
                GeoCountry::reset();
                if (! @rename($tmp, $target)) {
                    File::move($tmp, $target);
                }

                $this->info('Country database updated ('.round(filesize($target) / 1048576, 1).' MB). IP geolocation by DB-IP.');

                return self::SUCCESS;
            } catch (\Throwable $e) {
                report($e);
                $this->warn('Download failed: '.$e->getMessage());
            } finally {
                File::delete([$gz, $tmp]);
            }
        }

        $this->error('Could not update the country database; the previous one (if any) is still in use.');

        return self::FAILURE;
    }

    private function gunzip(string $from, string $to): void
    {
        $in = gzopen($from, 'rb');
        $out = fopen($to, 'wb');

        if (! $in || ! $out) {
            throw new \RuntimeException('Could not unpack the download.');
        }

        try {
            while (! gzeof($in)) {
                fwrite($out, (string) gzread($in, 1 << 20));
            }
        } finally {
            gzclose($in);
            fclose($out);
        }
    }

    private function isValid(string $file): bool
    {
        if (! is_file($file) || filesize($file) < 64) {
            return false;
        }

        try {
            $reader = new Reader($file);
            $type = $reader->metadata()->databaseType;
            $sample = $reader->get('8.8.8.8');
            $reader->close();
        } catch (\Throwable) {
            return false;
        }

        return str_contains(strtolower($type), 'country') && preg_match('/^[A-Z]{2}$/', (string) ($sample['country']['iso_code'] ?? ''));
    }
}
