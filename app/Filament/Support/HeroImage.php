<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;

/**
 * The upload settings shared by every banner / hero photo in the admin (page banners, packages,
 * the About page and destinations): up to 30 MB, so large originals can be uploaded as they are.
 * Photos wider than 2560px are shrunk in the browser while uploading (never enlarged), so the
 * public pages never serve a huge file.
 */
class HeroImage
{
    public const MAX_KB = 30720; // 30 MB

    public const MAX_WIDTH = '2560';

    public static function apply(FileUpload $field): FileUpload
    {
        return $field
            ->image()
            ->maxSize(self::MAX_KB)
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth(self::MAX_WIDTH)
            ->imageResizeUpscale(false);
    }

    public static function help(string $extra = ''): string
    {
        return trim('Wide landscape photo, up to 30 MB. Photos wider than 2560px are shrunk automatically so the page stays fast. '.$extra);
    }
}
