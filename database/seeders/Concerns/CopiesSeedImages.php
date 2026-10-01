<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Storage;

trait CopiesSeedImages
{
    /**
     * Copies a bundled image (database/seeders/images/{folder}/{file}) onto the
     * public disk under {prefix}/{folder}/ and returns its stored path.
     */
    protected function copyImage(string $folder, string $file, string $prefix = 'tour-pages'): string
    {
        $path = "{$prefix}/{$folder}/{$file}";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path("seeders/images/{$folder}/{$file}")));
        }

        return $path;
    }
}
