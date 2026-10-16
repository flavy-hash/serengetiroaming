<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Locale;

/**
 * One anonymous page view. Old rows are deleted by `model:prune` (config analytics.retention_days).
 */
class PageView extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    protected $fillable = [
        'visitor_hash',
        'path',
        'page_type',
        'package_id',
        'device',
        'referrer_host',
        'country',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(TourPage::class, 'package_id');
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(max(1, (int) config('analytics.retention_days', 180))));
    }

    /**
     * "🇩🇪 Germany" for "DE".
     */
    public static function countryLabel(?string $code): ?string
    {
        if (blank($code) || ! preg_match('/^[A-Z]{2}$/', $code)) {
            return null;
        }

        $flag = implode('', array_map(fn (string $c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split($code)));
        $name = class_exists(Locale::class) ? Locale::getDisplayRegion('-'.$code, 'en') : $code;

        return $flag.' '.($name ?: $code);
    }
}
